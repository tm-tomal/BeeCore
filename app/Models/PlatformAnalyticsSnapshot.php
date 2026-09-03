<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlatformAnalyticsSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'total_tenants', 'active_tenants', 'trial_tenants', 'suspended_tenants',
        'total_customers', 'total_resellers', 'mrr', 'arr', 'arpu', 'churn_rate',
        'recorded_by', 'recorded_at',
    ];

    protected $casts = [
        'mrr' => 'decimal:2', 'arr' => 'decimal:2', 'arpu' => 'decimal:2', 'churn_rate' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
