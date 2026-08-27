<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantResolverMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = null;

        // 1. Check explicitly passed route parameter 'tenant_slug' or 'tenant'
        if ($request->route('tenant_slug')) {
            $tenant = Tenant::where('slug', $request->route('tenant_slug'))->first();
        } elseif ($request->header('X-Tenant-ID')) {
            $tenant = Tenant::find($request->header('X-Tenant-ID'));
        } elseif ($request->header('X-Tenant-Slug')) {
            $tenant = Tenant::where('slug', $request->header('X-Tenant-Slug'))->first();
        } elseif ($user = $request->user()) {
            if ($user->tenant_id) {
                $tenant = $user->tenant;
            }
        }

        if ($tenant && $tenant->status === 'active') {
            app()->instance('currentTenantId', $tenant->id);
            app()->instance('currentTenant', $tenant);
        } else {
            app()->instance('currentTenantId', null);
            app()->instance('currentTenant', null);
        }

        return $next($request);
    }
}
