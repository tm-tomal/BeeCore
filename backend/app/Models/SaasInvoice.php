<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SaasInvoice extends Model
{
    protected $fillable = [
        'tenant_id', 'tenant_subscription_id', 'tenant_addon_id', 'invoice_number', 'status',
        'period_start', 'period_end', 'amount', 'due_date', 'paid_at', 'reminder_sent_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    /**
     * Temporary placeholder — replaced by a short sequential number once the
     * row exists, so the human-friendly number is unique without races.
     */
    public static function draftNumber(): string
    {
        return 'TMP-'.Str::uuid();
    }

    /**
     * Replaces the draft number with a short, easy-to-say reference derived
     * from the auto-increment id, e.g. INV-000123. Never collide because ids
     * are unique.
     */
    public function setSequentialNumber(string $prefix = 'INV'): self
    {
        if (! $this->exists) {
            return $this;
        }

        $number = $prefix.'-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);

        if ($this->invoice_number !== $number) {
            $this->forceFill(['invoice_number' => $number])->save();
        }

        return $this;
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'tenant_subscription_id');
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(TenantAddon::class, 'tenant_addon_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SaasPayment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaasInvoiceItem::class);
    }
}
