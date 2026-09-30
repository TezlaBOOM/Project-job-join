<?php

namespace Tests\Feature;

use App\Models\JobOffer;
use App\Services\PdfValidator;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LocalNetworkAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_access_from_non_local_network_returns_404(): void
    {
        // IP 203.0.113.55 is a public WAN address, not in local CIDR list
        $response = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.55'])
            ->get('/admin/login');

        $response->assertStatus(404);
    }

    public function test_admin_access_from_local_network_is_allowed(): void
    {
        // 127.0.0.1 is local
        $response = $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->get('/admin/login');

        $response->assertStatus(200);

        // 192.168.1.100 is local subnet
        $response2 = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
            ->get('/admin/login');

        $response2->assertStatus(200);
    }

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    public function test_public_urls_do_not_use_sequential_ids(): void
    {
        $offer = JobOffer::first();

        $this->assertNotEmpty($offer->public_id);
        $this->assertNotEmpty($offer->slug);
        $this->assertFalse(is_numeric($offer->slug));

        $response = $this->get('/oferty/'.$offer->slug);
        $response->assertStatus(200);
    }

    public function test_pdf_validator_rejects_non_pdf_and_corrupt_files(): void
    {
        // 1. File with PDF extension and MIME but corrupt header
        $fakeTxt = UploadedFile::fake()->createWithContent('corrupted.pdf', 'NOT_A_VALID_HEADER_DATA_123');
        [$isValid, $error] = PdfValidator::validate($fakeTxt);
        $this->assertFalse($isValid);
        $this->assertStringContainsString('nagłówek', $error);

        // 2. Non-PDF extension
        $txtFile = UploadedFile::fake()->create('document.txt', 50, 'text/plain');
        [$isValid2, $error2] = PdfValidator::validate($txtFile);
        $this->assertFalse($isValid2);
        $this->assertStringContainsString('formacie PDF', $error2);

        // 3. Valid PDF file with proper header
        $validPdf = UploadedFile::fake()->createWithContent('cv.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF");
        [$isValid3, $error3] = PdfValidator::validate($validPdf);
        $this->assertTrue($isValid3);
        $this->assertNull($error3);
    }
}
