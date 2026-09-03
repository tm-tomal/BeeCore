<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever('system_setting.'.$key, function () use ($key, $default) {
            return self::where('key', $key)->value('value') ?? $default;
        });
    }

    public static function set(string $key, mixed $value, ?int $updatedBy = null): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => $updatedBy]);
        Cache::forget('system_setting.'.$key);
    }

    /**
     * Platform-managed Bee Payment Gateway processing fee (%).
     * ISPs only enable the gateway; the fee is set here on the platform side.
     */
    public static function beeFeePercent(): int
    {
        return (int) self::get('bee_gateway_fee_percent', 2);
    }
}
