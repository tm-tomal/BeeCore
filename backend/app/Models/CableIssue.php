<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CableIssue extends Model
{
    protected $fillable = [
        'tenant_id',
        'cable_route_id',
        'cable_splitter_id',
        'issue_type',
        'title',
        'description',
        'status',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(CableRoute::class, 'cable_route_id');
    }

    public function splitter(): BelongsTo
    {
        return $this->belongsTo(CableSplitter::class, 'cable_splitter_id');
    }
}
