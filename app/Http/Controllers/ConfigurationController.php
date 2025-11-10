<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class ConfigurationController extends Controller
{
    /**
     * Mostrar la página de configuración
     */
    public function index(): Response
    {
        $user = Auth::user();
        
        return Inertia::render('Configuration/Index', [
            'settings' => [
                'theme' => $user->theme ?? 'light',
                'language' => $user->language ?? 'es',
                'notification_settings' => $user->notification_settings ?? [
                    'push' => false,
                    'modules' => [],
                ],
                'preferences' => $user->preferences ?? [
                    'items_per_page' => 15,
                    'date_format' => 'd/m/Y',
                    'time_format' => 'H:i',
                    'currency_symbol' => 'Bs',
                    'show_tooltips' => true,
                ],
            ],
        ]);
    }

    /**
     * Actualizar configuración del usuario
     */
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'nullable|in:light,dark,auto',
            'language' => 'nullable|in:es,en',
            'notification_settings' => 'nullable|array',
            'notification_settings.push' => 'nullable|boolean',
            'notification_settings.modules' => 'nullable|array',
            'notification_settings.modules.*' => 'nullable|in:sales,presales,inventory,payments,clients,reports',
            'preferences' => 'nullable|array',
            'preferences.items_per_page' => 'nullable|integer|min:5|max:100',
            'preferences.date_format' => 'nullable|string',
            'preferences.time_format' => 'nullable|string',
            'preferences.currency_symbol' => 'nullable|string',
            'preferences.show_tooltips' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        
        $data = [];
        
        if ($request->has('theme')) {
            $data['theme'] = $request->theme;
        }
        
        if ($request->has('language')) {
            $data['language'] = $request->language;
        }
        
        if ($request->has('notification_settings')) {
            $data['notification_settings'] = $request->notification_settings;
        }
        
        if ($request->has('preferences')) {
            $data['preferences'] = $request->preferences;
        }

        $user->update($data);

        return back()->with('success', 'Configuración actualizada correctamente');
    }

    /**
     * Actualizar solo el tema
     */
    public function updateTheme(Request $request)
    {
        $request->validate([
            'theme' => 'required|in:light,dark,auto',
        ]);

        Auth::user()->update([
            'theme' => $request->theme,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tema actualizado correctamente',
            'theme' => $request->theme,
        ]);
    }
}
