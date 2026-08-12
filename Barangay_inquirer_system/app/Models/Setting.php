<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $fillable = ['key', 'value'];
    public $timestamps = false;

    /**
     * Get a setting value by key
     */
    /**
     * Normalize keys to avoid duplicates caused by casing or stray spaces.
     * Keys are stored lowercase and trimmed.
     */
    protected static function normalizeKey(string $key): string
    {
        return trim(strtolower($key));
    }

    public static function get($key, $default = null)
    {
        $normalized = self::normalizeKey($key);
        $setting = self::where('key', $normalized)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting value; existing records are updated instead of inserting.
     */
    public static function set($key, $value)
    {
        $normalized = self::normalizeKey($key);
        return self::updateOrCreate(
            ['key' => $normalized],
            ['value' => $value]
        );
    }
}
