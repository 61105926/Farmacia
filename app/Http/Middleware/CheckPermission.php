<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user = auth()->user();

        // Si el usuario es administrador, permitir acceso a todo
        if ($user->hasRole('Administrador')) {
            return $next($request);
        }

        // Verificar si el usuario tiene al menos uno de los permisos requeridos
        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return $next($request);
            }
        }

        // Si no tiene permisos, redirigir a página de error
        return redirect()->route('error.403');
    }
}