<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmsProvider extends Model
{
    protected $fillable = [
        'name', 'slug', 'provider', 'sender_id', 'price_per_sms',
        'is_active', 'credentials', 'archived_at',
    ];

    protected $casts = [
        'price_per_sms' => 'decimal:4',
        'is_active' => 'boolean',
        'credentials' => 'encrypted:array',
        'archived_at' => 'datetime',
    ];

    protected $hidden = ['credentials'];

    public function logs(): HasMany
    {
        return $this->hasMany(SmsLog::class);
    }
}
