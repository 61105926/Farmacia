<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;

// Ruta principal - redirige al dashboard si está autenticado, sino al login
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Rutas de autenticación
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Ruta para errores de permisos
Route::get('/403', function () {
    return Inertia::render('Error403');
})->name('error.403');
