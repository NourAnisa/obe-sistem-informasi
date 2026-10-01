<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'type', 'group', 'label'];

    /**
     * Ambil satu nilai setting. Fallback ke config/obe.php jika belum di DB.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 600, function () use ($key, $default) {
            $setting = static::find($key);
            return $setting?->value ?? $default;
        });
    }

    /**
     * Set dan simpan satu nilai, clear cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    /**
     * Ambil semua setting dalam satu group, di-key-kan.
     */
    public static function group(string $group): \Illuminate\Support\Collection
    {
        return static::where('group', $group)->get()->keyBy('key');
    }

    /**
     * Semua setting sebagai array key => value (untuk backward compat dengan config('obe.*')).
     */
    public static function allAsArray(): array
    {
        return Cache::remember('settings.all', 600, fn() =>
            static::all()->pluck('value', 'key')->toArray()
        );
    }

    public static function clearAllCache(): void
    {
        Cache::forget('settings.all');
        static::all()->each(fn($s) => Cache::forget("setting.{$s->key}"));
    }
}
