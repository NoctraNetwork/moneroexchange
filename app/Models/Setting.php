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
    ];

    /**
     * Get setting value with caching
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set setting value with cache invalidation
     */
    public static function set(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget("setting_{$key}");
    }

    /**
     * Get boolean setting
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get integer setting
     */
    public static function getInt(string $key, int $default = 0): int
    {
        $value = static::get($key, $default);
        return (int) $value;
    }

    /**
     * Get float setting
     */
    public static function getFloat(string $key, float $default = 0.0): float
    {
        $value = static::get($key, $default);
        return (float) $value;
    }

    /**
     * Get array setting
     */
    public static function getArray(string $key, array $default = []): array
    {
        $value = static::get($key, $default);
        return is_array($value) ? $value : $default;
    }
}

