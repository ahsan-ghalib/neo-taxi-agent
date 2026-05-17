<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Runtime key-value configuration store for operator-managed settings.
 * Values are jsonb so they can hold any scalar, array, or object.
 *
 * Usage:
 *   SystemConfig::get('pricing.surge_enabled', false)
 *   SystemConfig::set('dispatch.radius_km', 5)
 */
class SystemConfig extends Model
{
    use HasUuids;

    protected $fillable = [
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Static helpers — cached reads to avoid repeated DB hits
    // -------------------------------------------------------------------------

    /**
     * Get a config value by dot-notation key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "system_config:{$key}";

        $record = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($key) {
            return static::where('key', $key)->first();
        });

        return $record?->value ?? $default;
    }

    /**
     * Set (upsert) a config value and bust the cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("system_config:{$key}");
    }
}
