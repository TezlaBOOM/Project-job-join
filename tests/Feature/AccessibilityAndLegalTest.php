<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityAndLegalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_accessibility_declaration_page(): void
    {
        $response = $this->get('/deklaracja-dostepnosci');

        $response->assertStatus(200);
        $response->assertSee('Deklaracja dostępności cyfrowej');
        $response->assertSee('WCAG 2.1');
        $response->assertSee('lang="pl"', false);
        $response->assertSee('Przejdź do treści głównej');
    }

    public function test_privacy_policy_page(): void
    {
        $response = $this->get('/polityka-prywatnosci');

        $response->assertStatus(200);
        $response->assertSee('Polityka prywatności i ochrona danych');
        $response->assertSee('Inspektor Ochrony Danych');
        $response->assertSee('Okres retencji');
    }
}
