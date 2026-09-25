<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;

class NotificationHelper
{
    /**
     * Crear una notificación para un usuario
     */
    public static function create(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $link = null,
        ?array $data = null
    ): Notification {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'link' => $link,
            'data' => $data,
        ]);
    }

    /**
     * Crear notificación de éxito
     */
    public static function success(
        User $user,
        string $title,
        string $message,
        ?string $link = null,
        ?array $data = null
    ): Notification {
        return self::create($user, $title, $message, 'success', $link, $data);
    }

    /**
     * Crear notificación de error
     */
    public static function error(
        User $user,
        string $title,
        string $message,
        ?string $link = null,
        ?array $data = null
    ): Notification {
        return self::create($user, $title, $message, 'error', $link, $data);
    }

    /**
     * Crear notificación de advertencia
     */
    public static function warning(
        User $user,
        string $title,
        string $message,
        ?string $link = null,
        ?array $data = null
    ): Notification {
        return self::create($user, $title, $message, 'warning', $link, $data);
    }

    /**
     * Crear notificación informativa
     */
    public static function info(
        User $user,
        string $title,
        string $message,
        ?string $link = null,
        ?array $data = null
    ): Notification {
        return self::create($user, $title, $message, 'info', $link, $data);
    }

    /**
     * Crear notificación para múltiples usuarios
     */
    public static function createForUsers(
        array $users,
        string $title,
        string $message,
        string $type = 'info',
        ?string $link = null,
        ?array $data = null
    ): array {
        $notifications = [];
        foreach ($users as $user) {
            $notifications[] = self::create($user, $title, $message, $type, $link, $data);
        }
        return $notifications;
    }

    /**
     * Crear notificación para todos los usuarios activos
     */
    public static function createForAllActive(
        string $title,
        string $message,
        string $type = 'info',
        ?string $link = null,
        ?array $data = null
    ): array {
        $users = User::active()->get();
        return self::createForUsers($users->all(), $title, $message, $type, $link, $data);
    }
}

