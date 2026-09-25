<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\SanitizeInertiaData::class,
            \App\Http\Middleware\HandleInertiaNullValues::class,
        ]);

        $middleware->alias([
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'role'       => \Spatie\Permission\Middleware\RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Sin permiso (403): en vez de la página de error, volver a donde estaba
        // el usuario y mostrarle un aviso. Las peticiones JSON siguen igual.
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 403 || $request->expectsJson() || !$request->user() || $request->is('dashboard')) {
                return null;
            }

            $message = $e->getMessage();
            if ($message === '' || str_contains($message, 'User does not have the right')) {
                $message = 'No tienes permiso para acceder a esta sección.';
            }

            $previous = url()->previous();
            $target = ($previous && $previous !== $request->fullUrl()) ? $previous : url('/dashboard');

            return redirect()->to($target)->with('permission_denied', $message);
        });
    })->create();