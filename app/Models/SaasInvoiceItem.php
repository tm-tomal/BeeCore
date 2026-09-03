<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaasInvoiceItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['saas_invoice_id', 'type', 'description', 'amount', 'created_by', 'created_at'];

    protected $casts = ['amount' => 'decimal:2', 'created_at' => 'datetime'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SaasInvoice::class, 'saas_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
