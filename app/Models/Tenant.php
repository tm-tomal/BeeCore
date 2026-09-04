<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    public const MODE_AUTOMATIC = 'automatic';
    public const MODE_MANUAL = 'manual';

    protected $fillable = [
        'name',
        'slug',
        'status',
        'currency',
        'timezone',
        'language',
        'operation_mode',
        'settings',
        'archived_at',
        'company_legal_name',
        'business_type',
        'owner_name',
        'owner_email',
        'owner_phone',
        'contact_phone',
        'contact_address',
        'subdomain',
        'custom_domain',
        'onboarding_status',
        'onboarding_completed_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'archived_at' => 'datetime',
        'onboarding_completed_at' => 'datetime',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function resellers(): HasMany
    {
        return $this->hasMany(Reseller::class);
    }

    public function saasSubscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    public function featureOverrides(): HasMany
    {
        return $this->hasMany(TenantFeatureOverride::class);
    }

    public function branding(): HasOne
    {
        return $this->hasOne(TenantBranding::class);
    }

    public function isAutomatic(): bool
    {
        return ($this->operation_mode ?? self::MODE_AUTOMATIC) === self::MODE_AUTOMATIC;
    }

    public function isManual(): bool
    {
        return ! $this->isAutomatic();
    }

    public function operationModeLabel(): string
    {
        return $this->isAutomatic() ? __('Automatic') : __('Manual');
    }

    public function billingSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings['billing'] ?? [], $key, $default);
    }

    public function hasFeature(string $key): bool
    {
        $feature = Feature::where('key', $key)->first();

        if (!$feature || !$feature->is_globally_enabled) {
            return false;
        }

        $override = $this->featureOverrides()->where('feature_id', $feature->id)->first();
        if ($override) {
            return $override->is_enabled;
        }

        $plan = $this->saasSubscriptions()->latest('id')->first()?->plan;
        if ($plan) {
            $planFeature = PlanFeature::where('saas_plan_id', $plan->id)->where('feature_id', $feature->id)->first();
            if ($planFeature) {
                return $planFeature->is_enabled;
            }
        }

        return true;
    }
}
