<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasPlan extends Model
{
    public const DEFAULT_YEARLY_DISCOUNT_PERCENT = 25;

    protected $fillable = [
        'name', 'slug', 'description', 'monthly_price', 'yearly_price', 'yearly_discount_percent',
        'customer_limit', 'overflow_rate', 'staff_limit', 'reseller_limit', 'storage_limit_mb',
        'api_limit', 'sms_limit', 'email_limit', 'features', 'trial_days',
        'grace_days', 'operation_mode', 'is_active', 'archived_at',
    ];

    /**
     * Yearly price is never a fixed amount — it is always derived from the
     * monthly price minus the configured yearly discount (20–30% recommended).
     */
    protected function yearlyPrice(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes) => $this->computedYearlyPrice($attributes),
        );
    }

    public function yearlyDiscountPercent(): int
    {
        $discount = (int) ($this->attributes['yearly_discount_percent'] ?? self::DEFAULT_YEARLY_DISCOUNT_PERCENT);

        return max(0, min(100, $discount));
    }

    public function computedYearlyPrice(?array $attributes = null): float
    {
        $attributes ??= $this->attributes;
        $monthly = (float) ($attributes['monthly_price'] ?? 0);
        $discount = (int) ($attributes['yearly_discount_percent'] ?? self::DEFAULT_YEARLY_DISCOUNT_PERCENT);
        $discount = max(0, min(100, $discount));

        return round($monthly * 12 * (1 - $discount / 100), 2);
    }

    public function yearlySavings(): float
    {
        return round((float) $this->monthly_price * 12 - (float) $this->yearly_price, 2);
    }

    public function operationModeLabel(): string
    {
        return match ($this->operation_mode ?? 'both') {
            'automatic' => __('Automatic ISPs'),
            'manual' => __('Manual ISPs'),
            default => __('Both types'),
        };
    }

    public function matchesMode(string $mode): bool
    {
        return in_array($this->operation_mode ?? 'both', ['both', $mode], true);
    }

    /**
     * Overflow charge for customers above the included limit.
     * Returns 0 when no included limit or no overflow rate is configured.
     */
    public function overflowChargeFor(int $customerCount): float
    {
        if ($this->customer_limit === null || $this->customer_limit <= 0) {
            return 0.0;
        }

        $overflow = max(0, $customerCount - $this->customer_limit);

        return round($overflow * (float) $this->overflow_rate, 2);
    }

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'archived_at' => 'datetime',
        'monthly_price' => 'decimal:2',
        'overflow_rate' => 'decimal:2',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }
}