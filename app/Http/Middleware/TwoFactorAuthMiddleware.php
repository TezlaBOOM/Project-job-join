<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && in_array($user->role, ['recruiter', 'moderator', 'superadmin'])) {
            if (!$request->session()->get('2fa_verified', false)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '2FA verification required.',
                        'requires_2fa' => true,
                    ], 403);
                }

                return redirect()->route('2fa.challenge');
            }
        }

        return $next($request);
    }
}
