<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantMediaSetting extends Model
{
    protected $fillable = [
        'tenant_id', 'is_enabled', 'storage_allocated_gb', 'storage_used_gb',
        'streaming_used_gb', 'bandwidth_used_gb', 'content_policy',
    ];

    protected $casts = ['is_enabled' => 'boolean'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
