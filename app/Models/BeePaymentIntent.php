<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BeePaymentIntent extends Model
{
    public const KIND_INVOICE = 'invoice';

    public const KIND_SAAS_PLAN = 'saas_plan';

    public const KIND_SAAS_ADDON = 'saas_addon';

    public const STATUS_CREATED = 'created';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'token', 'merchant_invoice_number', 'tenant_id', 'kind', 'amount',
        'status', 'meta', 'bkash_payment_id', 'bkash_trx_id', 'callback_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
        'callback_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function createFor(string $kind, int $tenantId, float $amount, array $meta = []): self
    {
        return self::create([
            'token' => Str::random(40),
            'merchant_invoice_number' => 'BEE'.now()->format('ymd').strtoupper(Str::random(6)),
            'tenant_id' => $tenantId,
            'kind' => $kind,
            'amount' => $amount,
            'status' => self::STATUS_CREATED,
            'meta' => $meta,
        ]);
    }

    /**
     * Reuse an existing open intent for the same order when possible, so the
     * customer can retry from the same payment link instead of getting stuck.
     */
    public static function findOpen(string $kind, int $tenantId, array $meta): ?self
    {
        return self::query()
            ->where('tenant_id', $tenantId)
            ->where('kind', $kind)
            ->where('status', '!=', self::STATUS_SUCCESS)
            ->where(function ($query) use ($meta) {
                foreach ($meta as $key => $value) {
                    $query->where("meta->{$key}", $value);
                }
            })
            ->latest('id')
            ->first();
    }

    public function isSettled(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Clear bKash session leftovers so the customer can pay again from the
     * same token (used after a cancelled/failed attempt).
     */
    public function resetForRetry(): self
    {
        $this->update([
            'status' => self::STATUS_CREATED,
            'bkash_payment_id' => null,
            'bkash_trx_id' => null,
            'callback_at' => null,
        ]);

        return $this;
    }
}
