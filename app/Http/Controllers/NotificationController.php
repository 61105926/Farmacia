<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Verificar si el usuario es administrador
     */
    private function isAdmin($user): bool
    {
        return $user->hasAnyRole(['super-admin', 'Administrador', 'administrador']);
    }

    /**
     * Obtener notificaciones del usuario autenticado
     * Los administradores ven todas las notificaciones del sistema
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        // Si es administrador, obtener todas las notificaciones
        // Si no, solo las del usuario
        if ($isAdmin) {
            $query = Notification::query()
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc');
        } else {
            $query = $user->notifications()
                ->orderBy('created_at', 'desc');
        }

        // Filtrar por no leídas si se solicita
        if ($request->boolean('unread_only')) {
            $query->where('read', false);
        }

        $notifications = $query->limit(100)->get();

        // Calcular contador de no leídas
        if ($isAdmin) {
            $unreadCount = Notification::where('read', false)->count();
        } else {
            $unreadCount = $user->unreadNotifications()->count();
        }

        return response()->json([
            'notifications' => $notifications->map(function ($notification) use ($isAdmin) {
                $data = [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'read' => $notification->read,
                    'link' => $notification->link,
                    'created_at' => $notification->created_at->toISOString(),
                ];
                
                // Si es admin, agregar información del usuario
                if ($isAdmin && $notification->user) {
                    $data['user'] = [
                        'id' => $notification->user->id,
                        'name' => $notification->user->name,
                        'email' => $notification->user->email,
                    ];
                }
                
                return $data;
            }),
            'unread_count' => $unreadCount,
            'is_admin' => $isAdmin,
        ]);
    }

    /**
     * Marcar una notificación como leída
     * Los administradores pueden marcar cualquier notificación
     */
    public function markAsRead(Notification $notification)
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        // Verificar que la notificación pertenece al usuario o es admin
        if (!$isAdmin && $notification->user_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $notification->markAsRead();

        // Calcular contador actualizado
        if ($isAdmin) {
            $unreadCount = Notification::where('read', false)->count();
        } else {
            $unreadCount = $user->unreadNotifications()->count();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     * Los administradores marcan todas las del sistema
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        if ($isAdmin) {
            // Administradores marcan todas las notificaciones del sistema
            Notification::where('read', false)->update(['read' => true]);
        } else {
            // Usuarios normales solo marcan las suyas
            $user->notifications()
                ->where('read', false)
                ->update(['read' => true]);
        }

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    /**
     * Eliminar una notificación
     * Los administradores pueden eliminar cualquier notificación
     */
    public function destroy(Notification $notification)
    {
        $user = Auth::user();
        $isAdmin = $this->isAdmin($user);
        
        // Verificar que la notificación pertenece al usuario o es admin
        if (!$isAdmin && $notification->user_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $notification->delete();

        // Calcular contador actualizado
        if ($isAdmin) {
            $unreadCount = Notification::where('read', false)->count();
        } else {
            $unreadCount = $user->unreadNotifications()->count();
        }

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount,
        ]);
    }
}
