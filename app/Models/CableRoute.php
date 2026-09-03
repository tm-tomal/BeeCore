<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CableRoute extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'source',
        'destination',
        'fiber_cores',
        'length_km',
        'latitude',
        'longitude',
        'notes',
    ];

    protected $casts = [
        'fiber_cores' => 'integer',
        'length_km' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function splitters(): HasMany
    {
        return $this->hasMany(CableSplitter::class, 'cable_route_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(CableIssue::class, 'cable_route_id');
    }

    public function openIssues(): HasMany
    {
        return $this->issues()->where('status', 'open');
    }
}
