<?php

namespace Tests\Feature;

use App\Mail\ApplicationCancelledMail;
use App\Mail\ApplicationConfirmationMail;
use App\Models\Application;
use App\Models\JobOffer;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class CandidateApplicationAndCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_candidate_can_browse_and_filter_offers(): void
    {
        $response = $this->call('GET', '/', ['q' => 'Cyberbezpieczeństwa']);
        $response->assertStatus(200);
        $response->assertSee('Główny Specjalista ds. Cyberbezpieczeństwa');
    }

    public function test_candidate_applies_without_account_and_receives_confirmation_email(): void
    {
        Mail::fake();

        $offer = JobOffer::where('status', 'published')->first();
        $cvFile = UploadedFile::fake()->createWithContent('zyciorys.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        $response = $this->post("/oferty/{$offer->slug}/aplikuj", [
            'first_name' => 'Marek',
            'last_name' => 'Kowalski',
            'email' => 'marek.kowalski@example.com',
            'phone' => '600700800',
            'rodo_consent' => '1',
            'cv_file' => $cvFile,
            '_rendered_at' => time() - 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Verify application in DB
        $application = Application::where('email', 'marek.kowalski@example.com')->first();
        $this->assertNotNull($application);
        $this->assertStringStartsWith('REK-', $application->reference_code);
        $this->assertEquals(Application::STATUS_NEW, $application->status);

        // Verify file stored
        $this->assertCount(1, $application->files);
        $this->assertEquals('zyciorys.pdf', $application->files->first()->original_name);

        // Verify confirmation email sent with cancellation token
        Mail::assertSent(ApplicationConfirmationMail::class, function ($mail) {
            return $mail->hasTo('marek.kowalski@example.com') && ! empty($mail->cancelToken);
        });
    }

    public function test_honeypot_blocks_automated_spam_submission(): void
    {
        $offer = JobOffer::where('status', 'published')->first();

        $response = $this->post("/oferty/{$offer->slug}/aplikuj", [
            '_hp_name' => 'Bot submission', // Bot filled hidden field
            'first_name' => 'Spam',
            'last_name' => 'Bot',
            'email' => 'bot@example.com',
            'rodo_consent' => '1',
        ]);

        $this->assertDatabaseMissing('applications', ['email' => 'bot@example.com']);
    }

    public function test_duplicate_submission_by_same_email_is_blocked(): void
    {
        $offer = JobOffer::where('status', 'published')->first();
        $cvFile = UploadedFile::fake()->createWithContent('cv.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");

        // First application
        $this->post("/oferty/{$offer->slug}/aplikuj", [
            'first_name' => 'Tomasz',
            'last_name' => 'Nowak',
            'email' => 'tomasz@example.com',
            'rodo_consent' => '1',
            'cv_file' => $cvFile,
            '_rendered_at' => time() - 10,
        ]);

        // Second application with identical email
        $response = $this->post("/oferty/{$offer->slug}/aplikuj", [
            'first_name' => 'Tomasz',
            'last_name' => 'Nowak',
            'email' => 'tomasz@example.com',
            'rodo_consent' => '1',
            'cv_file' => $cvFile,
            '_rendered_at' => time() - 10,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_two_step_cancellation_flow(): void
    {
        Mail::fake();

        $plainToken = Str::random(64);
        $offer = JobOffer::where('status', 'published')->first();

        $application = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => 'Ewa',
            'last_name' => 'Dąbrowska',
            'email' => 'ewa@example.com',
            'status' => Application::STATUS_NEW,
            'consent_at' => now(),
            'cancel_token_hash' => hash('sha256', $plainToken),
            'cancel_token_expires_at' => now()->addDays(30),
        ]);

        // Step 1: GET request does NOT cancel anything
        $getResponse = $this->get("/zgloszenie/anuluj/{$plainToken}");
        $getResponse->assertStatus(200);
        $getResponse->assertSee('Czy na pewno chcesz anulować zgłoszenie?');

        $this->assertNull($application->fresh()->cancelled_at);
        $this->assertEquals(Application::STATUS_NEW, $application->fresh()->status);

        // Step 2: POST request performs cancellation
        $postResponse = $this->post("/zgloszenie/anuluj/{$plainToken}");
        $postResponse->assertStatus(200);
        $postResponse->assertSee('Zgłoszenie zostało anulowane');

        $this->assertNotNull($application->fresh()->cancelled_at);
        $this->assertEquals(Application::STATUS_CANCELLED, $application->fresh()->status);

        // Verification email sent
        Mail::assertSent(ApplicationCancelledMail::class, function ($mail) {
            return $mail->hasTo('ewa@example.com');
        });

        // Step 3: Subsequent visit shows already cancelled message
        $subsequentResponse = $this->get("/zgloszenie/anuluj/{$plainToken}");
        $subsequentResponse->assertStatus(200);
        $subsequentResponse->assertSee('zostało już wcześniej anulowane');
    }
}
