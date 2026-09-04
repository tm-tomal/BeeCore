<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantRolePermission extends Model
{
    protected $fillable = ['tenant_id', 'role', 'module', 'allowed'];

    protected $casts = ['allowed' => 'boolean'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
