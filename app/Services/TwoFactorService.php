<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TwoFactorService
{
    /**
     * Generate a 6-digit 2FA token and save it to the user.
     */
    public function generateCode(User $user): string
    {
        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'two_factor_code' => bcrypt($code),
            'two_factor_expires_at' => Carbon::now()->addMinutes(10),
        ])->save();

        // Log token dispatch (in production, an email notification would be sent)
        Log::info("2FA token generated for user {$user->email}: {$code}");

        return $code;
    }

    /**
     * Verify a 2FA token for a given user.
     */
    public function verifyCode(User $user, string $code): bool
    {
        if (! $user->two_factor_code || ! $user->two_factor_expires_at) {
            return false;
        }

        if (Carbon::now()->greaterThan($user->two_factor_expires_at)) {
            $this->clearCode($user);

            return false;
        }

        if (password_verify($code, $user->two_factor_code)) {
            $this->clearCode($user);

            return true;
        }

        return false;
    }

    /**
     * Clear 2FA data after verification or expiry.
     */
    public function clearCode(User $user): void
    {
        $user->forceFill([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ])->save();
    }
}
