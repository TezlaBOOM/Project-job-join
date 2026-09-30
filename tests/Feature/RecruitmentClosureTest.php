<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ApplicationFile;
use App\Models\JobOffer;
use App\Models\User;
use App\Services\RecruitmentClosureService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RecruitmentClosureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_service_preserves_offer_and_qualified_candidate_while_purging_unqualified(): void
    {
        Storage::fake('local');

        $recruiter = User::where('role', 'recruiter')->first();

        // 1. Create a published job offer
        $offer = JobOffer::create([
            'title' => 'Inżynier Sieci i Bezpieczeństwa',
            'working_time' => 'Pełny etat',
            'description' => '<p>Opis oferty rekrutacyjnej</p>',
            'deadline_at' => now()->addDays(5),
            'status' => JobOffer::STATUS_PUBLISHED,
            'created_by' => $recruiter->id,
        ]);

        // 2. Create the WINNER candidate (qualified)
        $winner = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Wiktoria',
            'last_name' => 'Wygrana',
            'email' => 'wiktoria.wygrana@example.com',
            'phone' => '111-222-333',
            'address' => 'ul. Zwycięska 1, Warszawa',
            'status' => Application::STATUS_QUALIFIED,
            'consent_at' => now(),
            'source' => 'web',
        ]);

        $winnerFilePath = 'applications/'.$winner->id.'/cv_winner.pdf';
        Storage::disk('local')->put($winnerFilePath, 'CV of the qualified candidate');

        ApplicationFile::create([
            'application_id' => $winner->id,
            'type' => 'cv',
            'original_name' => 'cv_winner.pdf',
            'stored_path' => $winnerFilePath,
            'mime' => 'application/pdf',
            'size' => 1024,
            'checksum' => 'checksum-winner',
        ]);

        // 3. Create UNQUALIFIED candidates (rejected and under_review)
        $candidate1 = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Tomasz',
            'last_name' => 'Odrzucony',
            'email' => 'tomasz.odrzucony@example.com',
            'phone' => '444-555-666',
            'status' => Application::STATUS_REJECTED,
            'consent_at' => now(),
            'source' => 'web',
        ]);

        $c1FilePath = 'applications/'.$candidate1->id.'/cv_tomasz.pdf';
        Storage::disk('local')->put($c1FilePath, 'CV of rejected candidate');

        ApplicationFile::create([
            'application_id' => $candidate1->id,
            'type' => 'cv',
            'original_name' => 'cv_tomasz.pdf',
            'stored_path' => $c1FilePath,
            'mime' => 'application/pdf',
            'size' => 2048,
            'checksum' => 'checksum-tomasz',
        ]);

        $candidate2 = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Krzysztof',
            'last_name' => 'Przegrany',
            'email' => 'krzysztof.przegrany@example.com',
            'phone' => '777-888-999',
            'status' => Application::STATUS_UNDER_REVIEW,
            'consent_at' => now(),
            'source' => 'web',
        ]);

        $c2FilePath = 'applications/'.$candidate2->id.'/cv_krzysztof.pdf';
        Storage::disk('local')->put($c2FilePath, 'CV of candidate 2');

        ApplicationFile::create([
            'application_id' => $candidate2->id,
            'type' => 'cv',
            'original_name' => 'cv_krzysztof.pdf',
            'stored_path' => $c2FilePath,
            'mime' => 'application/pdf',
            'size' => 3072,
            'checksum' => 'checksum-krzysztof',
        ]);

        // 4. Run recruitment closure service
        $service = app(RecruitmentClosureService::class);
        $result = $service->completeRecruitment($offer, $recruiter->id);

        // 5. Verification: Job offer is preserved and marked as completed
        $this->assertDatabaseHas('job_offers', [
            'id' => $offer->id,
            'title' => 'Inżynier Sieci i Bezpieczeństwa',
            'status' => JobOffer::STATUS_COMPLETED,
        ]);
        $offer->refresh();
        $this->assertTrue($offer->isCompleted());

        // 6. Verification: Winner candidate data and CV file are preserved
        $this->assertDatabaseHas('applications', [
            'id' => $winner->id,
            'email' => 'wiktoria.wygrana@example.com',
            'status' => Application::STATUS_QUALIFIED,
        ]);
        Storage::disk('local')->assertExists($winnerFilePath);

        // 7. Verification: Unqualified candidates data and CV files are permanently deleted
        $this->assertDatabaseMissing('applications', ['id' => $candidate1->id]);
        $this->assertDatabaseMissing('applications', ['id' => $candidate2->id]);
        Storage::disk('local')->assertMissing($c1FilePath);
        Storage::disk('local')->assertMissing($c2FilePath);

        // 8. Result counts
        $this->assertEquals(2, $result['purged_candidates_count']);
        $this->assertEquals(2, $result['purged_files_count']);
        $this->assertEquals(1, $result['qualified_candidates_count']);

        // 9. Audit log entry created
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'recruitment_completed_unqualified_purged',
            'auditable_type' => JobOffer::class,
            'auditable_id' => $offer->id,
        ]);
    }

    public function test_recruiter_can_complete_recruitment_via_http_post(): void
    {
        Storage::fake('local');

        $recruiter = User::where('role', 'recruiter')->first();

        $offer = JobOffer::create([
            'title' => 'Kierownik Wydziału Prawnego',
            'working_time' => 'Pełny etat',
            'description' => '<p>Opis</p>',
            'deadline_at' => now()->addDays(7),
            'status' => JobOffer::STATUS_PUBLISHED,
            'created_by' => $recruiter->id,
        ]);

        $winner = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Alicja',
            'last_name' => 'Wygrywająca',
            'email' => 'alicja@example.com',
            'status' => Application::STATUS_QUALIFIED,
            'consent_at' => now(),
        ]);

        $unqualified = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Piotr',
            'last_name' => 'Odrzucony',
            'email' => 'piotr@example.com',
            'status' => Application::STATUS_REJECTED,
            'consent_at' => now(),
        ]);

        $filePath = 'applications/'.$unqualified->id.'/cv.pdf';
        Storage::disk('local')->put($filePath, 'cv');

        ApplicationFile::create([
            'application_id' => $unqualified->id,
            'type' => 'cv',
            'original_name' => 'cv.pdf',
            'stored_path' => $filePath,
            'mime' => 'application/pdf',
            'size' => 100,
            'checksum' => 'hash',
        ]);

        $response = $this->actingAs($recruiter, 'web')
            ->post(route('admin.offers.complete', $offer->public_id));

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('job_offers', ['id' => $offer->id, 'status' => JobOffer::STATUS_COMPLETED]);
        $this->assertDatabaseHas('applications', ['id' => $winner->id]);
        $this->assertDatabaseMissing('applications', ['id' => $unqualified->id]);
        Storage::disk('local')->assertMissing($filePath);
    }

    public function test_viewer_cannot_complete_recruitment(): void
    {
        $viewer = User::where('role', 'viewer')->first();

        $offer = JobOffer::first();

        $response = $this->actingAs($viewer, 'web')
            ->post(route('admin.offers.complete', $offer->public_id));

        $response->assertStatus(403);
    }

    public function test_updating_offer_to_completed_with_purge_checkbox_triggers_cleanup(): void
    {
        Storage::fake('local');

        $recruiter = User::where('role', 'recruiter')->first();

        $offer = JobOffer::create([
            'title' => 'Architekt IT',
            'working_time' => 'Pełny etat',
            'description' => '<p>Opis</p>',
            'deadline_at' => now()->addDays(3),
            'status' => JobOffer::STATUS_PUBLISHED,
            'created_by' => $recruiter->id,
        ]);

        $winner = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Stanisław',
            'last_name' => 'Zatrudniony',
            'email' => 'stanislaw@example.com',
            'status' => Application::STATUS_QUALIFIED,
            'consent_at' => now(),
        ]);

        $unqualified = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Michał',
            'last_name' => 'Niezakwalifikowany',
            'email' => 'michal@example.com',
            'status' => Application::STATUS_NEW,
            'consent_at' => now(),
        ]);

        $response = $this->actingAs($recruiter, 'web')
            ->put(route('admin.offers.update', $offer->public_id), [
                'title' => 'Architekt IT',
                'working_time' => 'Pełny etat',
                'description' => '<p>Opis</p>',
                'deadline_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
                'status' => JobOffer::STATUS_COMPLETED,
                'purge_unqualified' => '1',
            ]);

        $response->assertRedirect(route('admin.offers.index'));
        $this->assertDatabaseHas('job_offers', ['id' => $offer->id, 'status' => JobOffer::STATUS_COMPLETED]);
        $this->assertDatabaseHas('applications', ['id' => $winner->id]);
        $this->assertDatabaseMissing('applications', ['id' => $unqualified->id]);
    }

    public function test_artisan_recruitment_complete_command(): void
    {
        Storage::fake('local');

        $recruiter = User::where('role', 'recruiter')->first();

        $offer = JobOffer::create([
            'title' => 'Specjalista ds. Zamówień Publicznych',
            'working_time' => 'Pełny etat',
            'description' => '<p>Opis</p>',
            'deadline_at' => now()->addDays(5),
            'status' => JobOffer::STATUS_PUBLISHED,
            'created_by' => $recruiter->id,
        ]);

        $winner = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Ewa',
            'last_name' => 'Zwycięzca',
            'email' => 'ewa@example.com',
            'status' => Application::STATUS_QUALIFIED,
            'consent_at' => now(),
        ]);

        $unqualified = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Robert',
            'last_name' => 'Odrzucony',
            'email' => 'robert@example.com',
            'status' => Application::STATUS_REJECTED,
            'consent_at' => now(),
        ]);

        $this->artisan('recruitment:complete', [
            'offer' => $offer->public_id,
            '--force' => true,
        ])
            ->expectsOutputToContain('został pomyślnie zakończony')
            ->assertExitCode(0);

        $this->assertDatabaseHas('job_offers', ['id' => $offer->id, 'status' => JobOffer::STATUS_COMPLETED]);
        $this->assertDatabaseHas('applications', ['id' => $winner->id]);
        $this->assertDatabaseMissing('applications', ['id' => $unqualified->id]);
    }
}
