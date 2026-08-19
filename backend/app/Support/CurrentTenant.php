<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class CurrentTenant
{
    public function resolve(?Authenticatable $actor = null): ?Tenant
    {
        $actor ??= auth()->user();

        if (!$actor instanceof User) {
            return null;
        }

        if (!$actor->isSuperAdmin()) {
            return $actor->tenant()->where('status', 'active')->first();
        }

        $tenantId = session('impersonated_tenant_id');

        return $tenantId
            ? Tenant::query()->whereKey($tenantId)->where('status', 'active')->first()
            : null;
    }

    public function id(): int
    {
        return $this->resolve()?->id ?? abort(403, 'Select an active tenant before opening this workspace.');
    }
}