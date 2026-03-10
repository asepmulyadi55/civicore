<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'group'];

    /**
     * Get a setting value by key with an optional default.
     * Results are cached for 1 hour to avoid repeated DB hits.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    /**
     * Upsert a setting value by key and invalidate the cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrInsert(['key' => $key], ['value' => $value]);
        Cache::forget("setting:{$key}");
    }

    // All settings for a given group
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
