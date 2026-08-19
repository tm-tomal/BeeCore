<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscriptionEvent extends Model
{
    public $timestamps = false;

    protected $fillable = ['tenant_subscription_id', 'user_id', 'event', 'from_status', 'to_status', 'metadata', 'created_at'];

    protected $casts = ['metadata' => 'array', 'created_at' => 'datetime'];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'tenant_subscription_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}