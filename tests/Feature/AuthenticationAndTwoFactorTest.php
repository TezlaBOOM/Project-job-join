<?php

namespace Tests\Feature;

use App\Mail\TwoFactorCodeMail;
use App\Models\LoginCode;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthenticationAndTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_direct_without_2fa_by_default(): void
    {
        Mail::fake();

        // 1. Submit valid credentials when 2FA is disabled (default)
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/admin/login', [
                'email' => 'admin@admin.pl',
                'password' => 'Secret123456!',
            ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticated();

        // No 2FA email should be sent
        Mail::assertNothingSent();
    }

    public function test_login_requires_two_factor_authentication_when_enabled(): void
    {
        Mail::fake();
        Setting::set('admin_2fa_enabled', '1');

        // 1. Submit valid credentials
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/admin/login', [
                'email' => 'admin@admin.pl',
                'password' => 'Secret123456!',
            ]);

        $response->assertRedirect('/admin/2fa');
        $this->assertGuest(); // Not authenticated yet!

        // Assert 2FA email was sent
        Mail::assertSent(TwoFactorCodeMail::class, function ($mail) {
            return $mail->hasTo('admin@admin.pl') && strlen($mail->code) === 6;
        });

        // 2. Fetch created login code
        $user = User::where('email', 'admin@admin.pl')->first();
        $loginCode = LoginCode::where('user_id', $user->id)->first();
        $this->assertNotNull($loginCode);

        // 3. Attempting to access dashboard before 2FA redirects to login
        $dashboardResponse = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/admin/dashboard');
        $dashboardResponse->assertRedirect('/admin/login');
    }

    public function test_2fa_verification_with_valid_and_invalid_codes(): void
    {
        Mail::fake();
        Setting::set('admin_2fa_enabled', '1');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/admin/login', [
                'email' => 'admin@admin.pl',
                'password' => 'Secret123456!',
            ]);

        // 1. Test wrong code
        $wrongResponse = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/admin/2fa', [
                'code' => '000000',
            ]);

        $wrongResponse->assertSessionHasErrors('code');
        $this->assertGuest();

        // 2. Test correct code
        $user = User::where('email', 'admin@admin.pl')->first();
        $knownCode = '123456';
        $loginCode = LoginCode::where('user_id', $user->id)->first();
        $loginCode->update(['code_hash' => hash('sha256', $knownCode)]);

        $validResponse = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/admin/2fa', [
                'code' => $knownCode,
            ]);

        $validResponse->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($user);

        // Check code marked used
        $this->assertNotNull($loginCode->fresh()->used_at);

        // Check audit log
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'login_success',
        ]);
    }

    public function test_5_failed_2fa_attempts_locks_out_code(): void
    {
        Mail::fake();
        Setting::set('admin_2fa_enabled', '1');

        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/admin/login', [
                'email' => 'admin@admin.pl',
                'password' => 'Secret123456!',
            ]);

        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
                ->post('/admin/2fa', ['code' => '999999']);
        }

        $user = User::where('email', 'admin@admin.pl')->first();
        $loginCode = LoginCode::where('user_id', $user->id)->first();
        $this->assertNotNull($loginCode->used_at); // Code invalidated
        $this->assertGuest();
    }
}
