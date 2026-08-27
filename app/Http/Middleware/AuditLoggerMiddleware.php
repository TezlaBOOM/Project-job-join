<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLoggerMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Record audit logs for state-changing HTTP methods
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = $request->user();
            $tenantId = app()->bound('currentTenantId') ? app('currentTenantId') : ($user?->tenant_id);

            // Filter out sensitive keys from payload
            $sensitiveKeys = ['password', 'password_confirmation', 'two_factor_code', 'token', '_token'];
            $payload = array_diff_key($request->all(), array_flip($sensitiveKeys));

            AuditLog::create([
                'tenant_id' => $tenantId,
                'user_id' => $user?->id,
                'action' => strtoupper($request->method()) . ' ' . $request->path(),
                'ip_address' => $request->ip(),
                'payload' => $payload,
            ]);
        }

        return $response;
    }
}
