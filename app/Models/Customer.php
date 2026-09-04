<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'status',
        'package_name',
        'address',
        'notes',
        'notify_sms',
        'notify_email',
    ];

    protected $casts = [
        'address' => 'array',
        'notes' => 'array',
        'notify_sms' => 'boolean',
        'notify_email' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(CustomerSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(CustomerSubscription::class)->where('status', 'active')->latestOfMany();
    }

    /* ---------- Location helpers (address is a JSON array) ---------- */

    public function addressField(string $key): ?string
    {
        $value = ($this->address ?? [])[$key] ?? null;

        return $value === null || $value === '' ? null : (string) $value;
    }

    public function getHouseAttribute(): ?string
    {
        return $this->addressField('house');
    }

    public function getStreetAttribute(): ?string
    {
        return $this->addressField('street');
    }

    public function getAreaAttribute(): ?string
    {
        return $this->addressField('area');
    }

    public function getCityAttribute(): ?string
    {
        return $this->addressField('city');
    }

    public function getPostcodeAttribute(): ?string
    {
        return $this->addressField('postcode');
    }

    public function getLatitudeAttribute(): ?float
    {
        $lat = $this->addressField('latitude');

        return $lat !== null && is_numeric($lat) ? (float) $lat : null;
    }

    public function getLongitudeAttribute(): ?float
    {
        $lng = $this->addressField('longitude');

        return $lng !== null && is_numeric($lng) ? (float) $lng : null;
    }

    public function getHasMapCoordinatesAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * A single human-readable address line built from the stored parts.
     */
    public function getFullAddressAttribute(): string
    {
        return trim(collect([
            $this->house,
            $this->street,
            $this->area,
            $this->city,
            $this->postcode,
        ])->filter()->implode(', '));
    }
}
