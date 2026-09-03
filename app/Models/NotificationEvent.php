<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEvent extends Model
{
    protected $fillable = ['key', 'name', 'is_active', 'email_enabled', 'sms_enabled', 'push_enabled'];

    protected $casts = [
        'is_active' => 'boolean',
        'email_enabled' => 'boolean',
        'sms_enabled' => 'boolean',
        'push_enabled' => 'boolean',
    ];
}
