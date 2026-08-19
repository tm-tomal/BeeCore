<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Addon extends Model
{
    protected $fillable = [
        'name', 'slug', 'category', 'description', 'price', 'billing_cycle',
        'usage_limit', 'usage_unit', 'is_active', 'archived_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'archived_at' => 'datetime',
    ];

    public function tenantAddons(): HasMany
    {
        return $this->hasMany(TenantAddon::class);
    }
}
