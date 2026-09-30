<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLocalNetwork
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $clientIp = $request->ip();
        $allowed = config('admin.allowed_cidrs', []);

        if (! $this->isIpAllowed($clientIp, $allowed)) {
            abort(404);
        }

        return $next($request);
    }

    protected function isIpAllowed(?string $ip, array $allowedList): bool
    {
        if (empty($ip)) {
            return false;
        }

        foreach ($allowedList as $allowed) {
            $allowed = trim($allowed);
            if (empty($allowed)) {
                continue;
            }

            if ($allowed === '*' || $allowed === 'any') {
                return true;
            }

            // Direct string match (e.g. 127.0.0.1, ::1)
            if ($ip === $allowed) {
                return true;
            }

            // CIDR check for IPv4
            if (str_contains($allowed, '/')) {
                if ($this->cidrMatch($ip, $allowed)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function cidrMatch(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr, 2);

        // Check if both are valid IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskInt = (int) $mask;

            if ($maskInt < 0 || $maskInt > 32) {
                return false;
            }

            if ($maskInt === 0) {
                return true;
            }

            $bitmask = ~((1 << (32 - $maskInt)) - 1);

            return ($ipLong & $bitmask) === ($subnetLong & $bitmask);
        }

        return false;
    }
}
