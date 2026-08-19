<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasPlan extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'monthly_price', 'yearly_price',
        'customer_limit', 'staff_limit', 'reseller_limit', 'storage_limit_mb',
        'api_limit', 'sms_limit', 'email_limit', 'features', 'trial_days',
        'grace_days', 'is_active', 'archived_at',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'archived_at' => 'datetime',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }
}