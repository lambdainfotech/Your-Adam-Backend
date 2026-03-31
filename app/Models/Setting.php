<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    public static function get(string $key, $default = null)
    {
        $settings = Cache::remember('app_settings', 3600, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
        
        return $settings[$key] ?? $default;
    }

    public static function set(string $key, $value, string $group = 'general'): self
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
            ]
        );
        
        Cache::forget('app_settings');
        
        return $setting;
    }

    public static function getGroup(string $group): array
    {
        return self::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function allSettings(): array
    {
        return Cache::remember('app_settings', 3600, function () {
            return self::all()->pluck('value', 'key')->toArray();
        });
    }
}
