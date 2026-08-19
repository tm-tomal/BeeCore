<?php

namespace App\Http\Middleware;

use App\Support\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantContext
{
    public function __construct(private readonly CurrentTenant $currentTenant)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('tenant', $this->currentTenant->resolve()
            ?? abort(403, 'Select an active tenant before opening this workspace.'));

        return $next($request);
    }
}
