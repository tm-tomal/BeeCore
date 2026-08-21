<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NetworkIntegration extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'type', 'host', 'version', 'is_active',
        'health_status', 'last_checked_at', 'credentials',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
        'credentials' => 'encrypted:array',
    ];

    protected $hidden = ['credentials'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(NetworkIntegrationLog::class);
    }
}
