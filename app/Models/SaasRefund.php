<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasRefund extends Model
{
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'saas_payment_id', 'amount', 'reason', 'refunded_by', 'refunded_at'];

    protected $casts = ['amount' => 'decimal:2', 'refunded_at' => 'datetime'];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(SaasPayment::class, 'saas_payment_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }
}
