<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name', 'slug', 'provider', 'mode', 'is_active',
        'credentials', 'webhook_secret', 'webhook_url', 'archived_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'encrypted:array',
        'webhook_secret' => 'encrypted',
        'archived_at' => 'datetime',
    ];

    protected $hidden = ['credentials', 'webhook_secret'];

    public function logs(): HasMany
    {
        return $this->hasMany(PaymentGatewayLog::class);
    }
}
