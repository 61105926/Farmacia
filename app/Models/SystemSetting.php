<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['site_name', 'logo_path', 'logo_icon_path'];

    // Always work with the single settings row
    public static function current(): self
    {
        return Cache::remember('system_settings', 3600, function () {
            return static::firstOrCreate([], [
                'site_name'      => 'SISPANDO',
                'logo_path'      => null,
                'logo_icon_path' => null,
            ]);
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('system_settings');
    }
}
