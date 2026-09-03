<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SplitterPort extends Model
{
    protected $fillable = [
        'tenant_id',
        'cable_splitter_id',
        'port_number',
        'customer_id',
    ];

    protected $casts = [
        'port_number' => 'integer',
    ];

    public function splitter(): BelongsTo
    {
        return $this->belongsTo(CableSplitter::class, 'cable_splitter_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isFree(): bool
    {
        return $this->customer_id === null;
    }
}
