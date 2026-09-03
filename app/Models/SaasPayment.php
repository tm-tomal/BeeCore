<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasPayment extends Model
{
    public $timestamps = false;

    protected $fillable = ['tenant_id', 'saas_invoice_id', 'recorded_by', 'amount', 'method', 'reference', 'status', 'verified_at', 'verified_by', 'paid_at'];

    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'verified_at' => 'datetime'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SaasInvoice::class, 'saas_invoice_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(SaasRefund::class);
    }
}
