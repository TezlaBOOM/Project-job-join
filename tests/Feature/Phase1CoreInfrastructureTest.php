<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1CoreInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_creation_and_user_roles(): void
    {
        $tenant = Tenant::create([
            'name' => 'Urząd Gminy Testowa',
            'slug' => 'gmina-testowa',
        ]);

        $recruiter = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Jan Kowalski',
            'email' => 'jan@gmina.pl',
            'password' => bcrypt('password'),
            'role' => 'recruiter',
        ]);

        $superadmin = User::create([
            'name' => 'Admin Systemu',
            'email' => 'admin@portal.pl',
            'password' => bcrypt('password'),
            'role' => 'superadmin',
        ]);

        $this->assertTrue($recruiter->isRecruiter());
        $this->assertFalse($recruiter->isSuperAdmin());
        $this->assertTrue($superadmin->isSuperAdmin());
        $this->assertEquals($tenant->id, $recruiter->tenant->id);
    }

    public function test_two_factor_service_workflow(): void
    {
        $user = User::create([
            'name' => 'Anna Urzędnik',
            'email' => 'anna@gmina.pl',
            'password' => bcrypt('password'),
            'role' => 'recruiter',
        ]);

        $service = new TwoFactorService();
        $code = $service->generateCode($user);

        $this->assertNotNull($user->two_factor_code);
        $this->assertTrue($service->verifyCode($user, $code));
        $this->assertNull($user->fresh()->two_factor_code);
    }

    public function test_tenant_resolver_middleware_via_header(): void
    {
        $tenant = Tenant::create([
            'name' => 'Urząd Miasta Kraków',
            'slug' => 'krakow',
        ]);

        $this->withHeaders([
            'X-Tenant-Slug' => 'krakow',
        ])->get('/');

        $this->assertTrue(app()->bound('currentTenantId'));
        $this->assertEquals($tenant->id, app('currentTenantId'));
    }
}
