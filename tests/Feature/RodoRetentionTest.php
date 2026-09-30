<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationFile;
use App\Models\JobOffer;
use App\Models\Setting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RodoRetentionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_rodo_purge_command_removes_expired_applications_and_files(): void
    {
        Storage::fake('local');
        Setting::set('retention_months', '3');

        // Create expired offer (ended 6 months ago)
        $expiredOffer = JobOffer::create([
            'title' => 'Stary nabór',
            'working_time' => 'Pełny etat',
            'description' => '<p>Stare ogłoszenie</p>',
            'deadline_at' => now()->subMonths(6),
            'status' => 'completed',
            'created_by' => 1,
        ]);

        $expiredApp = Application::create([
            'job_offer_id' => $expiredOffer->id,
            'first_name' => 'Jan',
            'last_name' => 'Przeszły',
            'email' => 'jan.przeszly@example.com',
            'status' => Application::STATUS_REJECTED,
            'consent_at' => now()->subMonths(6),
        ]);

        $filePath = 'applications/'.$expiredApp->id.'/cv.pdf';
        Storage::disk('local')->put($filePath, 'fake content');

        ApplicationFile::create([
            'application_id' => $expiredApp->id,
            'type' => 'cv',
            'original_name' => 'cv.pdf',
            'stored_path' => $filePath,
            'mime' => 'application/pdf',
            'size' => 1234,
            'checksum' => 'checksum',
        ]);

        // Create active application that should NOT be purged
        $activeOffer = JobOffer::where('deadline_at', '>', now())->first();
        $activeApp = Application::create([
            'job_offer_id' => $activeOffer->id,
            'first_name' => 'Adam',
            'last_name' => 'Aktualny',
            'email' => 'adam.aktualny@example.com',
            'status' => Application::STATUS_NEW,
            'consent_at' => now(),
        ]);

        $this->artisan('rodo:purge')
            ->expectsOutputToContain('RODO retention purge finished')
            ->assertExitCode(0);

        // Verify expired application is deleted
        $this->assertDatabaseMissing('applications', ['id' => $expiredApp->id]);
        Storage::disk('local')->assertMissing($filePath);

        // Verify active application is preserved
        $this->assertDatabaseHas('applications', ['id' => $activeApp->id]);
    }
}
