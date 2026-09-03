<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantAddon extends Model
{
    protected $fillable = [
        'tenant_id', 'addon_id', 'status', 'price', 'billing_cycle',
        'usage_used', 'assigned_by', 'starts_at', 'cancelled_at',
        'period_start', 'period_end', 'auto_renew',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'starts_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
        'auto_renew' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
