<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\TenantPermissions;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows only staff whose role has the given workspace module enabled.
 * ISP Owners and platform super admins always pass.
 */
class EnsureTenantModule
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $user = $request->user();

        abort_unless($user instanceof User, 401);

        if ($user->role === User::ROLE_SUPER_ADMIN || $user->role === User::ROLE_TENANT_ADMIN) {
            return $next($request);
        }

        abort_unless(TenantPermissions::isEnabled((int) $user->tenant_id, $user->role, $module), 403);

        return $next($request);
    }
}
