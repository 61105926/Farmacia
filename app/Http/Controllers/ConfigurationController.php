<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\SystemSetting;

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

    /**
     * Actualizar configuración global del sistema (nombre y logos)
     */
    public function updateSystem(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:80',
            'logo'      => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048',
            'logo_icon' => 'nullable|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048',
        ]);

        try {
            $settings = SystemSetting::firstOrCreate([]);

            if ($request->filled('site_name')) {
                $settings->site_name = $request->site_name;
            }

            // Crear directorio físico directamente (sin depender del symlink)
            $logosDir = storage_path('app/public/logos');
            if (!is_dir($logosDir)) {
                mkdir($logosDir, 0775, true);
            }

            if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
                if ($settings->logo_path) {
                    $oldPath = storage_path('app/public/' . $settings->logo_path);
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $path = $request->file('logo')->store('logos', 'public');
                $settings->logo_path = $path;
            }

            if ($request->hasFile('logo_icon') && $request->file('logo_icon')->isValid()) {
                if ($settings->logo_icon_path) {
                    $oldPath = storage_path('app/public/' . $settings->logo_icon_path);
                    if (file_exists($oldPath)) @unlink($oldPath);
                }
                $path = $request->file('logo_icon')->store('logos', 'public');
                $settings->logo_icon_path = $path;
            }

            $settings->save();
            SystemSetting::clearCache();

            return back()->with('success', 'Configuración del sistema actualizada correctamente');

        } catch (\Exception $e) {
            \Log::error('updateSystem error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
}
