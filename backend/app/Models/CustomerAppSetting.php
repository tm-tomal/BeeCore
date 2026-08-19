<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAppSetting extends Model
{
    protected $fillable = [
        'current_version', 'minimum_supported_version', 'force_update_enabled',
        'maintenance_mode_enabled', 'maintenance_message', 'push_notifications_enabled',
    ];

    protected $casts = [
        'force_update_enabled' => 'boolean',
        'maintenance_mode_enabled' => 'boolean',
        'push_notifications_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'current_version' => '1.0.0',
            'minimum_supported_version' => '1.0.0',
        ]);
    }
}
