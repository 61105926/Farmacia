<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    /**
     * Clave pública VAPID para suscribir el navegador
     */
    public function publicKey(): JsonResponse
    {
        $key = WebPushService::publicKey();

        if (!$key) {
            return response()->json(['message' => 'Las notificaciones push no están disponibles'], 503);
        }

        return response()->json(['public_key' => $key]);
    }

    /**
     * Guardar (o actualizar) la suscripción push de este navegador
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|url|max:2000',
            'keys.p256dh' => 'required|string|max:255',
            'keys.auth' => 'required|string|max:255',
        ]);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => hash('sha256', $request->endpoint)],
            [
                'user_id' => $request->user()->id,
                'endpoint' => $request->endpoint,
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Eliminar la suscripción push de este navegador
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['endpoint' => 'required|string']);

        $request->user()->pushSubscriptions()
            ->where('endpoint_hash', hash('sha256', $request->endpoint))
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Enviar un push de prueba a los dispositivos del usuario
     */
    public function test(Request $request): JsonResponse
    {
        $sent = WebPushService::sendTest($request->user());

        if ($sent === 0) {
            return response()->json(['message' => 'No se pudo enviar la notificación a ningún dispositivo'], 422);
        }

        return response()->json(['success' => true, 'sent' => $sent]);
    }
}
