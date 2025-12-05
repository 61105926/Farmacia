<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'theme' => $request->user()->theme ?? 'light',
                    'language' => $request->user()->language ?? 'es',
                ] : null,
                'permissions' => $request->user()
                    ? $request->user()->getAllPermissions()->pluck('name')->toArray()
                    : [],
                'roles' => $request->user()
                    ? $request->user()->roles->pluck('name')->toArray()
                    : [],
            ],
            'notifications' => fn () => $request->user() 
                ? (function() use ($request) {
                    $user = $request->user();
                    $isAdmin = $user->hasAnyRole(['super-admin', 'Administrador', 'administrador']);
                    
                    if ($isAdmin) {
                        // Administradores ven todas las notificaciones
                        $recent = \App\Models\Notification::with('user:id,name,email')
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->map(fn ($notification) => [
                                'id' => $notification->id,
                                'title' => $notification->title,
                                'message' => $notification->message,
                                'type' => $notification->type,
                                'read' => $notification->read,
                                'link' => $notification->link,
                                'created_at' => $notification->created_at->toISOString(),
                                'user' => $notification->user ? [
                                    'id' => $notification->user->id,
                                    'name' => $notification->user->name,
                                    'email' => $notification->user->email,
                                ] : null,
                            ]);
                        $unreadCount = \App\Models\Notification::where('read', false)->count();
                    } else {
                        // Usuarios normales solo ven las suyas
                        $recent = $user->notifications()
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get()
                            ->map(fn ($notification) => [
                                'id' => $notification->id,
                                'title' => $notification->title,
                                'message' => $notification->message,
                                'type' => $notification->type,
                                'read' => $notification->read,
                                'link' => $notification->link,
                                'created_at' => $notification->created_at->toISOString(),
                            ]);
                        $unreadCount = $user->unreadNotifications()->count();
                    }
                    
                    return [
                        'unread_count' => $unreadCount,
                        'recent' => $recent,
                        'is_admin' => $isAdmin,
                    ];
                })()
                : ['unread_count' => 0, 'recent' => [], 'is_admin' => false],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'csrf_token' => csrf_token(),
        ];
    }
}
