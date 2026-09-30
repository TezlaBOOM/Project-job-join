<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TwoFactorCodeMail;
use App\Models\LoginCode;
use App\Models\Setting;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $settingVal = Setting::get('admin_2fa_enabled', null);
        $is2FaEnabled = $settingVal !== null ? filter_var($settingVal, FILTER_VALIDATE_BOOLEAN) : config('admin.2fa_enabled', false);

        return view('admin.auth.login', compact('is2FaEnabled'));
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'admin-login:'.Str::lower($request->email).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'email' => "Zbyt wiele nieudanych prób logowania. Spróbuj ponownie za {$seconds} sekund.",
            ]);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey, 300);
            AuditLogger::log('login_failed', null, null, ['email' => $request->email]);

            return back()->withErrors([
                'email' => 'Nieprawidłowe dane logowania.',
            ]);
        }

        if (! $user->is_active) {
            AuditLogger::log('login_inactive_blocked', $user);

            return back()->withErrors([
                'email' => 'Twoje konto jest nieaktywne. Skontaktuj się z administratorem.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Check if 2FA is enabled (disabled by default)
        $settingVal = Setting::get('admin_2fa_enabled', null);
        $is2FaEnabled = $settingVal !== null ? filter_var($settingVal, FILTER_VALIDATE_BOOLEAN) : config('admin.2fa_enabled', false);

        if (! $is2FaEnabled) {
            Auth::login($user);
            $request->session()->regenerate();
            $user->update(['last_login_at' => now()]);
            AuditLogger::log('login_success', $user, null, ['method' => 'password_direct']);

            return redirect()->intended(route('admin.dashboard'));
        }

        // Generate 6-digit cryptographic code
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $attemptId = (string) Str::uuid();

        // Expire older unused codes
        LoginCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $validMinutes = config('admin.code_expires_minutes', 10);

        LoginCode::create([
            'user_id' => $user->id,
            'code_hash' => hash('sha256', $code),
            'attempt_id' => $attemptId,
            'expires_at' => now()->addMinutes($validMinutes),
            'attempts' => 0,
            'ip' => $request->ip() ?? '127.0.0.1',
        ]);

        // Send 2FA email
        Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code, $request->ip() ?? '127.0.0.1', $validMinutes));

        // Store attempt in session
        $request->session()->put('2fa_attempt_id', $attemptId);
        $request->session()->put('2fa_user_id', $user->id);
        $request->session()->put('2fa_resend_count', 0);

        AuditLogger::log('2fa_code_sent', $user);

        return redirect()->route('admin.2fa.show')->with('status', 'Kod weryfikacyjny został wysłany na Twój adres e-mail.');
    }

    public function show2faForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        if (! $request->session()->has('2fa_attempt_id')) {
            return redirect()->route('admin.login');
        }

        return view('admin.auth.two_factor');
    }

    public function verify2fa(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $attemptId = $request->session()->get('2fa_attempt_id');
        $userId = $request->session()->get('2fa_user_id');

        if (! $attemptId || ! $userId) {
            return redirect()->route('admin.login')->withErrors(['email' => 'Sesja logowania wygasła.']);
        }

        $user = User::find($userId);
        if (! $user || ! $user->is_active) {
            $this->clear2faSession($request);

            return redirect()->route('admin.login')->withErrors(['email' => 'Konto nieaktywne lub nie istnieje.']);
        }

        $loginCode = LoginCode::where('attempt_id', $attemptId)
            ->where('user_id', $userId)
            ->first();

        if (! $loginCode || $loginCode->isUsed() || $loginCode->isExpired()) {
            AuditLogger::log('2fa_failed_expired', $user);

            return back()->withErrors(['code' => 'Kod wygasł lub jest nieprawidłowy. Zaloguj się ponownie.']);
        }

        if ($loginCode->isLockedOut()) {
            $loginCode->update(['used_at' => now()]);
            $this->clear2faSession($request);
            AuditLogger::log('2fa_lockout', $user);

            return redirect()->route('admin.login')->withErrors(['email' => 'Przekroczono dopuszczalną liczbę prób weryfikacji. Zaloguj się ponownie.']);
        }

        $loginCode->increment('attempts');

        $inputHash = hash('sha256', trim($request->code));
        if (! hash_equals($loginCode->code_hash, $inputHash)) {
            AuditLogger::log('2fa_failed_invalid_code', $user);

            if ($loginCode->attempts >= 5) {
                $loginCode->update(['used_at' => now()]);
                $this->clear2faSession($request);

                return redirect()->route('admin.login')->withErrors(['email' => 'Przekroczono dopuszczalną liczbę prób (5). Kod został unieważniony.']);
            }

            $remaining = 5 - $loginCode->attempts;

            return back()->withErrors(['code' => "Nieprawidłowy kod weryfikacyjny. Pozostało prób: {$remaining}."]);
        }

        // Code is valid! Mark used
        $loginCode->update(['used_at' => now()]);
        $this->clear2faSession($request);

        // Fully log in the user
        Auth::login($user);
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        AuditLogger::log('login_success', $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function resend2fa(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa_user_id');
        $resendCount = (int) $request->session()->get('2fa_resend_count', 0);

        if (! $userId) {
            return redirect()->route('admin.login');
        }

        if ($resendCount >= 3) {
            return back()->withErrors(['code' => 'Osiągnięto limit ponownego wysyłania kodów. Poczekaj na wygaśnięcie lub zaloguj się ponownie.']);
        }

        $user = User::find($userId);
        if (! $user) {
            return redirect()->route('admin.login');
        }

        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $attemptId = (string) Str::uuid();

        LoginCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $validMinutes = config('admin.code_expires_minutes', 10);

        LoginCode::create([
            'user_id' => $user->id,
            'code_hash' => hash('sha256', $code),
            'attempt_id' => $attemptId,
            'expires_at' => now()->addMinutes($validMinutes),
            'attempts' => 0,
            'ip' => $request->ip() ?? '127.0.0.1',
        ]);

        Mail::to($user->email)->send(new TwoFactorCodeMail($user, $code, $request->ip() ?? '127.0.0.1', $validMinutes));

        $request->session()->put('2fa_attempt_id', $attemptId);
        $request->session()->put('2fa_resend_count', $resendCount + 1);

        AuditLogger::log('2fa_code_resent', $user);

        return back()->with('status', 'Nowy kod został wysłany na Twój adres e-mail.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            AuditLogger::log('logout', $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Wylogowano pomyślnie.');
    }

    protected function clear2faSession(Request $request): void
    {
        $request->session()->forget(['2fa_attempt_id', '2fa_user_id', '2fa_resend_count']);
    }
}
