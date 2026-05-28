<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        try {
            if (!$request->user()->hasPermissionTo($permission)) {
                abort(403, 'No tienes permiso para realizar esta acción.');
            }
        } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
            abort(403, "El permiso '{$permission}' no está configurado en el sistema.");
        }

        return $next($request);
    }
}