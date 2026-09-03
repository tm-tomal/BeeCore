<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CableSplitter extends Model
{
    protected $fillable = [
        'tenant_id',
        'cable_route_id',
        'name',
        'location',
        'latitude',
        'longitude',
        'port_count',
        'notes',
    ];

    protected $casts = [
        'port_count' => 'integer',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(CableRoute::class, 'cable_route_id');
    }

    public function ports(): HasMany
    {
        return $this->hasMany(SplitterPort::class, 'cable_splitter_id')->orderBy('port_number');
    }

    public function customerPorts(): HasMany
    {
        return $this->ports()->whereNotNull('customer_id');
    }

    public function issues(): HasMany
    {
        return $this->hasMany(CableIssue::class, 'cable_splitter_id');
    }

    public function openIssues(): HasMany
    {
        return $this->issues()->where('status', 'open');
    }
}
