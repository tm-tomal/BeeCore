<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Feature extends Model
{
    protected $fillable = ['key', 'name', 'description', 'is_globally_enabled'];

    protected $casts = ['is_globally_enabled' => 'boolean'];

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function tenantOverrides(): HasMany
    {
        return $this->hasMany(TenantFeatureOverride::class);
    }
}
