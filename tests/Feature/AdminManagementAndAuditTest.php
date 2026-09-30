<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationFile;
use App\Models\Filter;
use App\Models\JobOffer;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminManagementAndAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $recruiter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('role', 'admin')->first();
        $this->recruiter = User::where('role', 'recruiter')->first();
    }

    public function test_admin_can_create_and_duplicate_job_offer(): void
    {
        $response = $this->actingAs($this->admin)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post('/admin/oferty', [
                'title' => 'Inspektor ds. Księgowości',
                'working_time' => 'Pełny etat',
                'description' => '<p>Opis obowiązków...</p>',
                'deadline_at' => now()->addDays(14)->format('Y-m-d H:i:s'),
                'status' => 'published',
            ]);

        $response->assertRedirect('/admin/oferty');
        $offer = JobOffer::where('title', 'Inspektor ds. Księgowości')->first();
        $this->assertNotNull($offer);

        // Test duplication
        $dupResponse = $this->actingAs($this->admin)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post("/admin/oferty/{$offer->public_id}/duplicate");

        $dupResponse->assertRedirect();
        $this->assertDatabaseHas('job_offers', [
            'title' => 'Kopia - Inspektor ds. Księgowości',
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_update_application_status_and_download_file(): void
    {
        Storage::fake('local');

        $offer = JobOffer::first();
        $app = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Anna',
            'last_name' => 'Kowalska',
            'email' => 'anna.k@example.com',
            'status' => Application::STATUS_NEW,
            'consent_at' => now(),
        ]);

        $filePath = 'applications/'.$app->id.'/test.pdf';
        Storage::disk('local')->put($filePath, "%PDF-1.4\ntest");

        $file = ApplicationFile::create([
            'application_id' => $app->id,
            'type' => 'cv',
            'original_name' => 'moje_cv.pdf',
            'stored_path' => $filePath,
            'mime' => 'application/pdf',
            'size' => 1024,
            'checksum' => 'fake',
        ]);

        // 1. Update status
        $statusResponse = $this->actingAs($this->recruiter)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post("/admin/zgloszenia/{$app->public_id}/status", [
                'status' => Application::STATUS_INTERVIEW,
                'note' => 'Zaproszono na 15.10',
            ]);

        $statusResponse->assertRedirect();
        $this->assertEquals(Application::STATUS_INTERVIEW, $app->fresh()->status);

        // 2. Download file securely
        $downloadResponse = $this->actingAs($this->recruiter)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get("/admin/zgloszenia/{$app->public_id}/pliki/{$file->id}");

        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('content-disposition', 'attachment; filename=moje_cv.pdf');
    }

    public function test_cannot_degrade_last_administrator(): void
    {
        // Try to change own role to recruiter
        $response = $this->actingAs($this->admin)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post("/admin/uzytkownicy/{$this->admin->id}/role", [
                'role' => 'recruiter',
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals('admin', $this->admin->fresh()->role);
    }

    public function test_filter_reordering_and_active_toggle(): void
    {
        $filter = Filter::first();
        $initialStatus = $filter->is_active;

        $toggleResponse = $this->actingAs($this->admin)
            ->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->post("/admin/filtry/{$filter->id}/toggle");

        $toggleResponse->assertRedirect();
        $this->assertEquals(! $initialStatus, $filter->fresh()->is_active);
    }
}
