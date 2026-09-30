<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors(['email' => 'Twoje konto zostało zdezaktywowane przez administratora.']);
        }

        if (! empty($roles)) {
            $allowed = false;
            foreach ($roles as $role) {
                if ($role === 'admin' && $user->isAdmin()) {
                    $allowed = true;
                    break;
                }
                if ($role === 'recruiter' && $user->isRecruiter()) {
                    $allowed = true;
                    break;
                }
                if ($role === 'viewer' && $user->isViewer()) {
                    $allowed = true;
                    break;
                }
                if ($user->role === $role) {
                    $allowed = true;
                    break;
                }
            }

            if (! $allowed) {
                abort(403, 'Brak uprawnień do wykonania tej operacji.');
            }
        }

        return $next($request);
    }
}
