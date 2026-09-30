<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\ConsentText;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\EmailTemplate;
use App\Models\Filter;
use App\Models\Form;
use App\Models\FormField;
use App\Models\JobCategory;
use App\Models\JobOffer;
use App\Models\Location;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@admin.pl'],
            [
                'name' => 'Jan Kowalski (Administrator)',
                'password' => Hash::make('Secret123456!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $recruiter = User::updateOrCreate(
            ['email' => 'rekruter@admin.pl'],
            [
                'name' => 'Anna Nowak (Kierownik Kadr)',
                'password' => Hash::make('Secret123456!'),
                'role' => 'recruiter',
                'is_active' => true,
            ]
        );

        $viewer = User::updateOrCreate(
            ['email' => 'komisja@admin.pl'],
            [
                'name' => 'Piotr Wiśniewski (Członek Komisji)',
                'password' => Hash::make('Secret123456!'),
                'role' => 'viewer',
                'is_active' => true,
            ]
        );

        // Aliasy zgodne z domeną urzędową miasto.gov.pl
        User::firstOrCreate(
            ['email' => 'admin@miasto.gov.pl'],
            [
                'name' => 'Jan Kowalski (Administrator)',
                'password' => Hash::make('Secret123456!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
        User::firstOrCreate(
            ['email' => 'rekruter@miasto.gov.pl'],
            [
                'name' => 'Anna Nowak (Kierownik Kadr)',
                'password' => Hash::make('Secret123456!'),
                'role' => 'recruiter',
                'is_active' => true,
            ]
        );
        User::firstOrCreate(
            ['email' => 'komisja@miasto.gov.pl'],
            [
                'name' => 'Piotr Wiśniewski (Członek Komisji)',
                'password' => Hash::make('Secret123456!'),
                'role' => 'viewer',
                'is_active' => true,
            ]
        );

        // 2. Settings
        $settings = [
            'office_name' => 'Urząd Miasta Stołecznego',
            'office_address' => 'plac Bankowy 3/5, 00-950 Warszawa',
            'office_email' => 'rekrutacja@um.gov.pl',
            'office_phone' => '22 443 00 00',
            'bip_url' => 'https://bip.warszawa.pl',
            'home_intro' => 'Dołącz do zespołu Urzędu Miasta. Oferujemy stabilne warunki zatrudnienia, możliwość rozwoju zawodowego, trzynastą pensję oraz realny wpływ na rozwój naszej lokalnej społeczności.',
            'retention_months' => '3',
            'file_max_mb' => '5',
        ];

        foreach ($settings as $key => $val) {
            Setting::firstOrCreate(['key' => $key], ['value' => $val]);
        }

        // 3. RODO Consent
        $consent = ConsentText::firstOrCreate(
            ['version' => '1.0'],
            [
                'content' => "Zgodnie z art. 13 ust. 1 i 2 ogólnego rozporządzenia o ochronie danych osobowych z dnia 27 kwietnia 2016 r. (RODO) informuję, iż:\n1. Administratorem Pani/Pana danych osobowych jest Urząd Miasta.\n2. Dane osobowe przetwarzane są w celu przeprowadzenia procedury naboru na wolne stanowisko urzędnicze na podstawie przepisów ustawy z dnia 21 listopada 2008 r. o pracownikach samorządowych oraz Kodeksu pracy.\n3. Posiada Pani/Pan prawo dostępu do treści swoich danych oraz ich poprawiania, a także prawo do wycofania zgody w dowolnym momencie.",
                'active_from' => now()->subMonths(1),
            ]
        );

        // 4. Dictionaries
        $depts = [
            'Wydział Informatyki i Łączności',
            'Wydział Ochrony Środowiska',
            'Wydział Spraw Obywatelskich',
            'Wydział Gospodarki Nieruchomościami',
            'Wydział Finansowo-Budżetowy',
        ];
        $deptModels = [];
        foreach ($depts as $name) {
            $deptModels[$name] = Department::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        $contracts = [
            'Umowa o pracę na czas określony',
            'Umowa o pracę na czas nieokreślony',
            'Umowa o zastępstwo',
            'Staż absolwencki',
        ];
        $contractModels = [];
        foreach ($contracts as $name) {
            $contractModels[$name] = ContractType::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        $categories = [
            'Stanowisko urzędnicze',
            'Stanowisko urzędnicze kierownicze',
            'Stanowisko pomocnicze i obsługi',
        ];
        $categoryModels = [];
        foreach ($categories as $name) {
            $categoryModels[$name] = JobCategory::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        $locations = [
            'Urząd Miasta - Gmach Główny (pl. Bankowy 3/5)',
            'Centrum Obsługi Mieszkańca (ul. Marszałkowska 77)',
        ];
        $locationModels = [];
        foreach ($locations as $name) {
            $locationModels[$name] = Location::firstOrCreate(['name' => $name], ['is_active' => true]);
        }

        // 5. Configurable Public Filters
        $filters = [
            ['key' => 'q', 'label' => 'Słowo kluczowe', 'source' => 'field', 'source_ref' => 'search', 'control_type' => 'text', 'position' => 1],
            ['key' => 'department', 'label' => 'Wydział / Jednostka', 'source' => 'dictionary', 'source_ref' => 'department', 'control_type' => 'select', 'position' => 2],
            ['key' => 'contract_type', 'label' => 'Rodzaj umowy', 'source' => 'dictionary', 'source_ref' => 'contract_type', 'control_type' => 'select', 'position' => 3],
            ['key' => 'category', 'label' => 'Kategoria', 'source' => 'dictionary', 'source_ref' => 'job_category', 'control_type' => 'select', 'position' => 4],
            ['key' => 'location', 'label' => 'Lokalizacja', 'source' => 'dictionary', 'source_ref' => 'location', 'control_type' => 'select', 'position' => 5],
            ['key' => 'working_time', 'label' => 'Wymiar etatu', 'source' => 'field', 'source_ref' => 'working_time', 'control_type' => 'select', 'position' => 6],
        ];

        foreach ($filters as $f) {
            Filter::firstOrCreate(['key' => $f['key']], array_merge($f, ['is_active' => true]));
        }

        // 6. Form Templates
        $form = Form::firstOrCreate(['name' => 'Standardowy formularz naboru'], ['is_template' => true]);

        $fields = [
            ['key' => 'first_name', 'label' => 'Imię', 'type' => 'text', 'is_required' => true, 'is_system' => true, 'position' => 1],
            ['key' => 'last_name', 'label' => 'Nazwisko', 'type' => 'text', 'is_required' => true, 'is_system' => true, 'position' => 2],
            ['key' => 'email', 'label' => 'Adres e-mail', 'type' => 'email', 'is_required' => true, 'is_system' => true, 'position' => 3],
            ['key' => 'phone', 'label' => 'Numer telefonu', 'type' => 'tel', 'is_required' => false, 'is_system' => false, 'position' => 4],
            ['key' => 'address', 'label' => 'Adres do korespondencji', 'type' => 'text', 'is_required' => false, 'is_system' => false, 'position' => 5],
            ['key' => 'education_level', 'label' => 'Wykształcenie', 'type' => 'select', 'is_required' => true, 'is_system' => false, 'options' => ['Wyższe magisterskie', 'Wyższe zawodowe / inżynierskie', 'Średnie'], 'position' => 6],
            ['key' => 'experience_years', 'label' => 'Lata stażu pracy', 'type' => 'number', 'is_required' => false, 'is_system' => false, 'position' => 7],
            ['key' => 'motivation', 'label' => 'Uzasadnienie przystąpienia do naboru', 'type' => 'textarea', 'is_required' => false, 'is_system' => false, 'position' => 8],
            ['key' => 'cv_file', 'label' => 'Życiorys (CV) - PDF', 'type' => 'file', 'is_required' => true, 'is_system' => true, 'position' => 9],
        ];

        foreach ($fields as $fieldData) {
            FormField::firstOrCreate(
                ['form_id' => $form->id, 'key' => $fieldData['key']],
                array_merge($fieldData, ['is_active' => true])
            );
        }

        // 7. Email templates
        $templates = [
            [
                'key' => 'application_confirmation',
                'subject' => 'Potwierdzenie przyjęcia zgłoszenia rekrutacyjnego',
                'body_text' => "Dziękujemy za złożenie aplikacji w naborze do Urzędu Miasta.\nTwoje zgłoszenie zostało zarejestrowane.",
                'body_html' => '<p>Dziękujemy za złożenie aplikacji w naborze do Urzędu Miasta.</p>',
            ],
            [
                'key' => 'interview_invitation',
                'subject' => 'Zaproszenie na rozmowę kwalifikacyjną',
                'body_text' => 'Z przyjemnością zapraszamy Panią/Pana na rozmowę kwalifikacyjną w siedzibie Urzędu Miasta.',
                'body_html' => '<p>Z przyjemnością zapraszamy Panią/Pana na rozmowę kwalifikacyjną w siedzibie Urzędu Miasta.</p>',
            ],
            [
                'key' => 'status_changed',
                'subject' => 'Informacja o zmianie statusu Twojego zgłoszenia',
                'body_text' => 'Informujemy, że status Twojej aplikacji uległ zmianie.',
                'body_html' => '<p>Informujemy, że status Twojej aplikacji uległ zmianie.</p>',
            ],
        ];

        foreach ($templates as $t) {
            EmailTemplate::firstOrCreate(['key' => $t['key']], $t);
        }

        // 8. Sample Job Offers
        $offer1 = JobOffer::firstOrCreate(
            ['slug' => 'glowny-specjalista-ds-cyberbezpieczenstwa-k7f3q'],
            [
                'public_id' => (string) Str::ulid(),
                'title' => 'Główny Specjalista ds. Cyberbezpieczeństwa i Sieci',
                'department_id' => $deptModels['Wydział Informatyki i Łączności']->id,
                'contract_type_id' => $contractModels['Umowa o pracę na czas nieokreślony']->id,
                'category_id' => $categoryModels['Stanowisko urzędnicze']->id,
                'location_id' => $locationModels['Urząd Miasta - Gmach Główny (pl. Bankowy 3/5)']->id,
                'working_time' => 'Pełny etat',
                'description' => '<p>Do głównych zadań osoby zatrudnionej na tym stanowisku należeć będzie:</p><ul><li>Monitorowanie bezpieczeństwa infrastruktury teleinformatycznej urzędu</li><li>Konfiguracja systemów ochrony perymetrycznej, firewalli oraz EDR</li><li>Wdrażanie wytycznych Krajowych Ram Interoperacyjności i ustawy o KSC</li><li>Prowadzenie szkoleń dla pracowników w zakresie cyberhigieny</li></ul>',
                'requirements' => "- Obywatelstwo polskie\n- Wykształcenie wyższe informatyczne lub pokrewne\n- Minimum 4 lata doświadczenia zawodowego w obszarze IT/Security\n- Znajomość standardów ISO/IEC 27001 oraz NIST",
                'nice_to_have' => "- Posiadanie certyfikatów bezpieczeństwa (CISSP, CEH, CompTIA Security+)\n- Doświadczenie w administracji środowiskami chmurowymi",
                'offer_text' => "- Stabilne zatrudnienie w oparciu o umowę o pracę\n- Dodatkowe wynagrodzenie roczne (tzw. trzynastka)\n- Możliwość pracy hybrydowej (do 2 dni w tygodniu)\n- Dofinansowanie szkoleń i certyfikatów branżowych",
                'required_documents' => "- Życiorys (CV) z opisem przebiegu kariery zawodowej\n- Oświadczenie o posiadaniu obywatelstwa polskiego i pełnej zdolności do czynności prawnych\n- Kopie dyplomów i certyfikatów",
                'form_id' => $form->id,
                'deadline_at' => now()->addDays(14),
                'published_at' => now()->subDays(2),
                'status' => 'published',
                'created_by' => $recruiter->id,
            ]
        );

        $offer2 = JobOffer::firstOrCreate(
            ['slug' => 'podinspektor-ds-ochrony-srodowiska-m8b12'],
            [
                'public_id' => (string) Str::ulid(),
                'title' => 'Podinspektor ds. Ochrony Środowiska i Gospodarki Odpadami',
                'department_id' => $deptModels['Wydział Ochrony Środowiska']->id,
                'contract_type_id' => $contractModels['Umowa o pracę na czas określony']->id,
                'category_id' => $categoryModels['Stanowisko urzędnicze']->id,
                'location_id' => $locationModels['Urząd Miasta - Gmach Główny (pl. Bankowy 3/5)']->id,
                'working_time' => 'Pełny etat',
                'description' => '<p>Osoba zatrudniona będzie odpowiedzialna za:</p><ul><li>Weryfikację sprawozdań z zakresu gospodarki odpadami komunalnymi</li><li>Prowadzenie postępowań administracyjnych i przygotowywanie decyzji</li><li>Udział w kontrolach terenowych</li></ul>',
                'requirements' => "- Wykształcenie wyższe (ochrona środowiska, inżynieria środowiska lub administracja)\n- Minimum 1 rok doświadczenia w administracji publicznej\n- Znajomość ustawy o utrzymaniu czystości i porządku w gminach",
                'nice_to_have' => "- Prawo jazdy kat. B\n- Doświadczenie w pracy w systemie EZD",
                'offer_text' => "- Dofinansowanie do wczasów pod gruszą i karty sportowej\n- Pakiet socjalny ZFŚS",
                'required_documents' => "- CV w formacie PDF\n- Oświadczenie o braku skazania prawomocnym wyrokiem sądu",
                'form_id' => $form->id,
                'deadline_at' => now()->addDays(4), // Kończy się wkrótce!
                'published_at' => now()->subDays(10),
                'status' => 'published',
                'created_by' => $recruiter->id,
            ]
        );

        $offer3 = JobOffer::firstOrCreate(
            ['slug' => 'referent-ds-ewidencji-ludnosci-p3q9x'],
            [
                'public_id' => (string) Str::ulid(),
                'title' => 'Referent ds. Ewidencji Ludności i Dowodów Osobistych',
                'department_id' => $deptModels['Wydział Spraw Obywatelskich']->id,
                'contract_type_id' => $contractModels['Umowa o pracę na czas nieokreślony']->id,
                'category_id' => $categoryModels['Stanowisko urzędnicze']->id,
                'location_id' => $locationModels['Centrum Obsługi Mieszkańca (ul. Marszałkowska 77)']->id,
                'working_time' => 'Pełny etat',
                'description' => '<p>Zakres zadań:</p><ul><li>Bezpośrednia obsługa mieszkańców w sprawach meldunkowych</li><li>Przyjmowanie wniosków o wydanie dowodu osobistego i ich wydawanie</li><li>Wprowadzanie danych do Rejestru Dowodów Osobistych i PESEL</li></ul>',
                'requirements' => "- Wykształcenie średnie lub wyższe\n- Wysoka kultura osobista i umiejętność pracy z mieszkańcami\n- Niekaralność za przestępstwa umyślne",
                'nice_to_have' => '- Doświadczenie w bezpośredniej obsłudze klienta',
                'offer_text' => "- Nowoczesne stanowisko pracy w klimatyzowanym Centrum Obsługi\n- Dofinansowanie do okularów korekcyjnych",
                'required_documents' => '- Życiorys (CV) z oświadczeniem o niekaralności',
                'form_id' => $form->id,
                'deadline_at' => now()->addDays(21),
                'published_at' => now()->subDays(1),
                'status' => 'published',
                'created_by' => $recruiter->id,
            ]
        );

        // 9. Sample Applications
        $token1 = Str::random(64);
        $app1 = Application::firstOrCreate(
            ['reference_code' => 'REK-2026-AB12CD'],
            [
                'public_id' => (string) Str::ulid(),
                'job_offer_id' => $offer1->id,
                'first_name' => 'Kamil',
                'last_name' => 'Zieliński',
                'email' => 'kamil.zielinski@example.com',
                'phone' => '500 123 456',
                'address' => 'ul. Polna 12, Warszawa',
                'status' => Application::STATUS_UNDER_REVIEW,
                'internal_notes' => 'Kandydat spełnia wszystkie wymagania formalne. Certyfikat CEH potwierdzony.',
                'consent_version_id' => $consent->id,
                'consent_at' => now()->subDays(1),
                'future_consent' => true,
                'cancel_token_hash' => hash('sha256', $token1),
                'cancel_token_expires_at' => now()->addDays(30),
                'source' => 'web',
            ]
        );

        ApplicationStatusHistory::firstOrCreate(
            ['application_id' => $app1->id, 'to_status' => Application::STATUS_UNDER_REVIEW],
            [
                'from_status' => 'new',
                'user_id' => $recruiter->id,
                'note' => 'Dokumenty kompletne, skierowano do oceny merytorycznej.',
                'created_at' => now()->subHours(5),
            ]
        );
    }
}
