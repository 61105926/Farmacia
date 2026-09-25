<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\SystemSetting;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    /** @var Notification[] Notificaciones pendientes de enviar al terminar la petición */
    private static array $pending = [];

    /**
     * Clave pública VAPID (la genera y guarda la primera vez)
     */
    public static function publicKey(): ?string
    {
        return self::keys()['publicKey'] ?? null;
    }

    /**
     * Encolar el envío push de una notificación del sistema.
     * En peticiones web se envía después de responder, para no demorar al usuario.
     */
    public static function queue(Notification $notification): void
    {
        if (app()->runningInConsole()) {
            self::sendNotifications([$notification]);
            return;
        }

        if (empty(self::$pending)) {
            app()->terminating(function () {
                $pending = self::$pending;
                self::$pending = [];
                self::sendNotifications($pending);
            });
        }

        self::$pending[] = $notification;
    }

    /**
     * Enviar un push de prueba a todos los dispositivos del usuario
     */
    public static function sendTest(User $user): int
    {
        return self::send($user->pushSubscriptions()->get()->all(), [
            'title' => 'Notificación de Prueba',
            'body' => '¡Las notificaciones push están funcionando correctamente!',
            'url' => '/configuracion',
            'tag' => 'test-notification',
        ]);
    }

    /**
     * @param Notification[] $notifications
     */
    private static function sendNotifications(array $notifications): void
    {
        foreach ($notifications as $notification) {
            try {
                $user = $notification->user;
                if (!$user || !self::userWants($user, $notification)) {
                    continue;
                }

                $subscriptions = $user->pushSubscriptions()->get()->all();
                if (empty($subscriptions)) {
                    continue;
                }

                self::send($subscriptions, [
                    'title' => $notification->title,
                    'body' => $notification->message,
                    'url' => $notification->link ?: '/dashboard',
                    'tag' => 'notification-' . $notification->id,
                ]);
            } catch (\Throwable $e) {
                Log::warning('WebPush - error al enviar notificación', [
                    'notification_id' => $notification->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * El usuario activó push y (si eligió módulos) la notificación es de uno de ellos
     */
    private static function userWants(User $user, Notification $notification): bool
    {
        $settings = $user->notification_settings ?? [];
        if (empty($settings['push'])) {
            return false;
        }

        $modules = $settings['modules'] ?? [];
        if (empty($modules)) {
            return true;
        }

        $module = self::moduleFor($notification->link);

        return $module === null || in_array($module, $modules, true);
    }

    /**
     * Deducir el módulo a partir del enlace de la notificación
     */
    private static function moduleFor(?string $link): ?string
    {
        $path = strtolower((string) parse_url((string) $link, PHP_URL_PATH));

        $map = [
            'presales' => ['preventa', 'presale'],
            'sales' => ['venta', 'sales'],
            'payments' => ['cuentas-por-cobrar', 'account-receivable', 'cobr', 'pago', 'payment'],
            'inventory' => ['producto', 'product', 'inventario', 'inventory', 'lote', 'batch'],
            'clients' => ['cliente', 'client', 'farmacia', 'pharmac'],
            'reports' => ['reporte', 'report'],
        ];

        foreach ($map as $module => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($path, $needle)) {
                    return $module;
                }
            }
        }

        return null;
    }

    /**
     * @param PushSubscription[] $subscriptions
     * @return int Cantidad de envíos exitosos
     */
    private static function send(array $subscriptions, array $payload): int
    {
        $keys = self::keys();
        if (!$keys || empty($subscriptions)) {
            return 0;
        }

        $settings = SystemSetting::current();
        $payload['icon'] = $payload['icon'] ?? ($settings->logo_icon_path
            ? \Storage::disk('public')->url($settings->logo_icon_path)
            : '/assets/images/logo.jpeg');

        $factory = new HttpFactory();
        $webPush = new WebPush(
            ['VAPID' => [
                'subject' => str_starts_with((string) config('app.url'), 'https://')
                    ? config('app.url')
                    : 'mailto:admin@farmacia.com',
                'publicKey' => $keys['publicKey'],
                'privateKey' => $keys['privateKey'],
            ]],
            ['TTL' => 86400, 'urgency' => 'high'],
            new Client(['timeout' => 10, 'connect_timeout' => 5]),
            $factory,
            $factory,
            null,
            app('log'),
        );
        $webPush->setReuseVAPIDHeaders(true);

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->public_key,
                'authToken' => $subscription->auth_token,
                'contentEncoding' => 'aes128gcm',
            ]), $json);
        }

        $sent = 0;
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $sent++;
            } elseif ($report->isSubscriptionExpired()) {
                PushSubscription::where('endpoint_hash', hash('sha256', $report->getEndpoint()))->delete();
            } else {
                Log::warning('WebPush - envío fallido', ['reason' => $report->getReason()]);
            }
        }

        return $sent;
    }

    /**
     * Claves VAPID: de .env si existen, si no se generan una vez y se guardan en system_settings
     */
    private static function keys(): ?array
    {
        $public = env('VAPID_PUBLIC_KEY');
        $private = env('VAPID_PRIVATE_KEY');
        if ($public && $private) {
            return ['publicKey' => $public, 'privateKey' => $private];
        }

        try {
            $settings = SystemSetting::firstOrCreate([]);
            if (!$settings->vapid_public_key || !$settings->vapid_private_key) {
                $generated = VAPID::createVapidKeys();
                $settings->vapid_public_key = $generated['publicKey'];
                $settings->vapid_private_key = $generated['privateKey'];
                $settings->save();
                SystemSetting::clearCache();
            }

            return ['publicKey' => $settings->vapid_public_key, 'privateKey' => $settings->vapid_private_key];
        } catch (\Throwable $e) {
            Log::error('WebPush - no se pudieron obtener las claves VAPID: ' . $e->getMessage());
            return null;
        }
    }
}
