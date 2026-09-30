# Specyfikacja: Portal rekrutacyjny dla Urzędu Miasta

> Dokument przeznaczony dla AI/programisty implementującego system. Wszystkie wymagania oznaczone **MUST** są obowiązkowe, **SHOULD** – zalecane.

---

## 1. Cel i zakres

Publiczny portal, na którym Urząd Miasta publikuje oferty pracy (nabory), a kandydaci składają aplikacje online **bez zakładania konta i bez logowania**. Cała komunikacja z kandydatem odbywa się przez e-mail. Pracownicy urzędu zarządzają ogłoszeniami i zgłoszeniami w panelu administracyjnym dostępnym **wyłącznie z sieci lokalnej**.

### Poza zakresem (v1)
- Konta kandydatów, logowanie kandydatów.
- Czat, komunikator, powiadomienia push.
- Płatności.

---

## 2. Stos technologiczny

| Warstwa | Technologia |
|---|---|
| Backend | PHP 8.3+, Laravel 11 (LTS/aktualna stabilna wersja) |
| Baza danych | MySQL 8 (InnoDB, utf8mb4_unicode_ci) |
| Frontend | Blade + Tailwind CSS (lub czysty CSS z własnymi zmiennymi), minimalny JS (Alpine.js dopuszczalne). Bez SPA. |
| Kolejki | Laravel Queue (driver `database` lub `redis`) – wysyłka maili asynchronicznie |
| Mail | SMTP (konfiguracja w `.env`), szablony Blade Markdown Mailables |
| Pliki | Laravel Storage, dysk `local` (prywatny, poza `public/`) |
| Panel admina | Blade + własne widoki (lub Filament ograniczony middleware’em IP – do decyzji; preferowane własne widoki dla pełnej kontroli WCAG) |
| Testy | Pest/PHPUnit, Laravel Dusk lub Playwright (opcjonalnie), pa11y/axe do testów dostępności |

Architektura: klasyczna aplikacja MVC, renderowanie po stronie serwera, formularze POST z CSRF.

---

## 3. Role i dostęp

### 3.1 Kandydat (niezalogowany, publiczny)
- Przegląda listę i szczegóły ofert.
- Filtruje oferty.
- Wypełnia formularz zgłoszeniowy, dołącza pliki.
- Otrzymuje e-mail z potwierdzeniem i linkiem do anulowania.
- Może anulować zgłoszenie przez link z e-maila.

### 3.2 Panel administracyjny (tylko sieć lokalna)
Role:
- **Administrator** – pełny dostęp, zarządzanie użytkownikami, ustawieniami, szablonami maili.
- **Rekruter** – ogłoszenia i zgłoszenia.
- **Przeglądający** (read-only) – podgląd zgłoszeń (np. członkowie komisji).

### 3.3 Ograniczenie dostępu do panelu do sieci lokalnej (MUST)
Wielowarstwowo:
1. **Serwer WWW / reverse proxy** (Nginx/Apache): trasy `/admin/*` dostępne tylko z zakresów IP z listy (`allow 10.0.0.0/8; allow 192.168.0.0/16; deny all;` – zakresy dostosować do sieci urzędu).
2. **Middleware Laravel `EnsureLocalNetwork`**: sprawdza `$request->ip()` względem listy CIDR z `config/admin.php` (wartości z `.env`: `ADMIN_ALLOWED_CIDRS`). Przy niezgodności zwraca **404** (nie 403, by nie ujawniać istnienia panelu).
3. Poprawna konfiguracja `TrustProxies` – ufać wyłącznie znanym proxy, aby nie dało się sfałszować IP nagłówkiem `X-Forwarded-For`.
4. Rozważyć osobny host/subdomenę dla panelu (np. `admin.rekrutacja.miasto.local`) dostępną tylko w DNS wewnętrznym.
5. Logowanie do panelu: e-mail + hasło, **logowanie dwuskładnikowe (2FA) kodem wysyłanym e-mailem** – obowiązkowe dla wszystkich użytkowników panelu (patrz 8.3), blokada po 5 nieudanych próbach (throttle), wymuszenie silnych haseł (min. 12 znaków), hashowanie Argon2id/bcrypt, wylogowanie po bezczynności (np. 30 min).
6. Zablokowana publiczna rejestracja – konta tworzy wyłącznie Administrator.

---

## 4. Strona publiczna

### 4.1 Wygląd
- Prosty, urzędowy, czytelny styl wzorowany na https://www.nask.pl/kariera : biała/jasna baza, nagłówek z logo urzędu i menu, wyraźne nagłówki, szeroka czytelna lista ofert, dużo białej przestrzeni, brak zbędnych animacji i grafik.
- Układ responsywny (mobile-first): 1 kolumna na telefonie, na desktopie filtry po lewej lub nad listą.
- Nagłówek: logo (podmieniane w ustawieniach), nazwa urzędu, link „Przejdź do treści”, przełącznik motywu jasny/ciemny.
- Stopka: dane kontaktowe urzędu, link do polityki prywatności, deklaracji dostępności, klauzuli RODO, link do BIP.

### 4.2 Strona główna (`/`)
- Krótki wstęp (edytowalny w ustawieniach) o pracy w urzędzie.
- **Filtry ofert** (formularz GET, działa bez JS, parametry w URL). Zestaw filtrów jest **konfigurowalny przez administratora w panelu** (patrz 5.5); poniższa lista to konfiguracja domyślna:
  - wyszukiwanie tekstowe (tytuł, opis),
  - wydział/jednostka organizacyjna,
  - rodzaj umowy (umowa o pracę, zlecenie, staż itp.),
  - wymiar czasu pracy (pełny etat / część etatu),
  - miejsce pracy / lokalizacja,
  - kategoria stanowiska (urzędnicze, kierownicze, pomocnicze i obsługi, inne),
  - termin składania dokumentów (np. „kończące się w ciągu 7 dni”),
  - przycisk „Wyczyść filtry”.
- Administrator może w panelu: włączać/wyłączać poszczególne filtry, zmieniać ich nazwy (etykiety) i kolejność, dodawać nowe filtry oparte na słownikach lub polach ogłoszenia oraz edytować wartości słowników (np. nowe wydziały, rodzaje umów). Zmiany są widoczne na stronie głównej natychmiast po zapisaniu.
- Lista ofert: tytuł, jednostka, lokalizacja, wymiar etatu, termin składania, przycisk „Zobacz ofertę”.
- Sortowanie: najnowsze / termin kończący się najszybciej.
- Paginacja (np. 10–20 na stronę), liczba wyników, komunikat „Brak ofert spełniających kryteria”.
- Wyświetlane tylko oferty ze statusem *opublikowana* i terminem nieprzekroczonym. Zakończone nabory mogą być dostępne w zakładce „Archiwum” (bez możliwości aplikowania).

### 4.3 Szczegóły oferty (`/oferty/{slug-lub-token}`)
- Tytuł, jednostka, lokalizacja, rodzaj umowy, wymiar etatu, termin składania, data publikacji.
- Sekcje: zakres obowiązków, wymagania niezbędne, wymagania dodatkowe, oferujemy, wymagane dokumenty, informacja o przetwarzaniu danych.
- Wyraźny przycisk „Aplikuj”.
- Po terminie – przycisk nieaktywny z komunikatem.

### 4.4 Formularz zgłoszeniowy (`/oferty/{oferta}/aplikuj`)
Formularz jest **w pełni edytowalny przez administratora/rekrutera w panelu** (patrz 5.6): pola można dodawać, usuwać, edytować (etykieta, opis pomocniczy, typ, wymagalność, walidacja) i zmieniać ich kolejność, a formularz można dostosować per oferta. Pola systemowe (imię, nazwisko, e-mail, zgoda RODO) nie mogą zostać usunięte. Poniżej konfiguracja domyślna:
- Imię, nazwisko (wymagane)
- E-mail (wymagany), telefon (opcjonalny/wymagany wg oferty)
- Adres korespondencyjny (opcjonalny)
- Wykształcenie, doświadczenie zawodowe (pola tekstowe)
- List motywacyjny (tekst lub plik)
- **CV (plik PDF)** oraz **dodatkowe dokumenty (wiele plików PDF)** – lub samo wypełnienie formularza bez załączników, jeśli oferta na to pozwala
- Oświadczenia wymagane w naborach do urzędów (np. obywatelstwo, korzystanie z pełni praw publicznych – zgodnie z ogłoszeniem; treść oświadczeń edytowalna per oferta)
- Zgoda/klauzula RODO (obowiązkowy checkbox, treść z wersjonowaniem), opcjonalna zgoda na udział w przyszłych naborach
- CAPTCHA/ochrona antyspamowa (SHOULD: rozwiązanie przyjazne prywatności i dostępności, np. honeypot + time-trap + rate limiting; ewentualnie Cloudflare Turnstile/altcha; unikać reCAPTCHA v2 z obrazkami ze względu na dostępność)

Pliki:
- Dozwolony jest **wyłącznie format PDF** (`application/pdf`, rozszerzenie `.pdf`). Inne formaty są odrzucane z czytelnym komunikatem („Dołącz plik w formacie PDF”). Ograniczenie nie jest konfigurowalne w panelu.
- Limit: np. 5 MB/plik, 10 plików, 20 MB łącznie (konfigurowalne).
- Walidacja typu po zawartości pliku (nagłówek `%PDF`, MIME `application/pdf`, nie tylko rozszerzenie); odrzucanie PDF-ów z osadzonym JavaScriptem, akcjami uruchamianymi i osadzonymi plikami (SHOULD), przemianowanie plików na losowe nazwy (UUID), oryginalna nazwa przechowywana w bazie po sanityzacji.
- Skan antywirusowy ClamAV (SHOULD; pliki w statusie *oczekuje na skan* do czasu zakończenia).
- Pliki przechowywane poza katalogiem publicznym, udostępniane wyłącznie przez kontroler po autoryzacji (panel admina).

Po wysłaniu:
- Strona potwierdzenia „Dziękujemy, zgłoszenie zostało przyjęte. Sprawdź skrzynkę e-mail.”
- E-mail do kandydata (patrz pkt 6).
- Ochrona przed podwójnym wysłaniem (token idempotencji / blokada przycisku).

---

## 5. Panel administracyjny

Dostępny pod `/admin` (tylko sieć lokalna, patrz 3.3).

### 5.1 Ogłoszenia
- CRUD ogłoszeń: tytuł, jednostka, lokalizacja, rodzaj umowy, wymiar etatu, kategoria, opis (edytor tekstu z sanitizacją HTML – biblioteka typu HTMLPurifier, dozwolone tylko bezpieczne znaczniki: p, ul, ol, li, strong, em, h3, h4, a, br), termin składania, data publikacji, status (szkic / opublikowana / zakończona / zarchiwizowana), formularz zgłoszeniowy (szablon z 5.6, z możliwością dostosowania dla konkretnej oferty), treść oświadczeń.
- Duplikowanie ogłoszenia.
- Słowniki: jednostki, rodzaje umów, kategorie, lokalizacje (CRUD).

### 5.2 Zgłoszenia
- Lista z filtrami (oferta, status, data, wyszukiwanie po imieniu/nazwisku/e-mailu), sortowanie, paginacja, eksport do CSV/XLSX.
- Widok szczegółów zgłoszenia: wszystkie dane, podgląd/pobranie plików, historia zmian, notatki wewnętrzne, log wysłanych maili.
- **Pełna edycja zgłoszenia**: wszystkie pola formularza, dodawanie/usuwanie/podmiana plików, dodanie zgłoszenia ręcznie (np. dokumenty papierowe/e-mail – rekruter wprowadza dane kandydata).
- Zmiana statusu: *nowe → w trakcie oceny → zaproszony na rozmowę → odrzucone → zakwalifikowane/zatrudniony → anulowane przez kandydata*.
- Wysyłka e-maila do kandydata z panelu (wybór szablonu lub własna treść), zapisywana w historii.
- Akcje masowe (zmiana statusu, wysyłka wiadomości do wielu kandydatów).
- Każda operacja (podgląd, edycja, zmiana statusu, pobranie pliku, wysyłka maila, usunięcie) jest rejestrowana i widoczna w zakładce **Logi** (patrz 5.8).

### 5.3 Ustawienia
- Dane urzędu, logo, teksty strony głównej, stopka, klauzula RODO (wersjonowanie), deklaracja dostępności.
- Szablony e-mail (potwierdzenie, zmiana statusu, zaproszenie, odrzucenie, anulowanie).
- Limity plików (rozmiar, liczba); format zawsze wyłącznie PDF.
- Okres retencji danych (patrz pkt 9).
- Zarządzanie użytkownikami panelu i rolami – patrz 5.7; przegląd wszystkich operacji – patrz 5.8 (Logi).

### 5.4 Pulpit
Liczba aktywnych ofert, nowych zgłoszeń, ofert kończących się wkrótce, ostatnie zgłoszenia.

### 5.5 Konfigurator filtrów (strona główna)
Zakładka `/admin/filtry` (Administrator i Rekruter z uprawnieniem):
- lista filtrów wyświetlanych na stronie głównej (domyślne z pkt 4.2),
- włączanie/wyłączanie filtra, zmiana etykiety i kolejności (przeciąganie lub przyciski „w górę/w dół” – dostępne z klawiatury),
- dodawanie nowego filtra: wybór źródła (słownik: jednostka, rodzaj umowy, kategoria, lokalizacja, wymiar etatu; lub pole ogłoszenia, np. termin), typ kontrolki (lista rozwijana, pola wyboru, wyszukiwarka tekstowa, zakres dat),
- edycja wartości słowników (dodawanie, zmiana nazwy, dezaktywacja),
- podgląd zmian przed zapisaniem.

### 5.6 Konfigurator formularza zgłoszeniowego
Zakładka `/admin/formularze` (Administrator i Rekruter z uprawnieniem):
- **szablony formularzy** (np. „Stanowisko urzędnicze”, „Staż”); ogłoszenie korzysta z wybranego szablonu i może go dostosować,
- **dodawanie pól**: typ (tekst krótki, tekst długi, e-mail, telefon, liczba, data, lista rozwijana, pola wyboru, przełącznik tak/nie, oświadczenie z checkboxem, plik PDF), etykieta, tekst pomocniczy, wymagane/opcjonalne, reguły walidacji (długość, format), limity dla plików PDF,
- **edycja i usuwanie** pól (usunięcie pola, które ma już odpowiedzi w złożonych zgłoszeniach, powoduje jego dezaktywację – dane historyczne pozostają dostępne w panelu),
- **zmiana kolejności** pól i grupowanie w sekcje,
- pola systemowe (imię, nazwisko, e-mail, zgoda RODO) są zablokowane przed usunięciem,
- podgląd formularza w wersji jasnej i ciemnej, duplikowanie szablonu,
- wygenerowany formularz musi spełniać wymagania dostępności z pkt 10 (etykiety, komunikaty błędów, `autocomplete`) niezależnie od konfiguracji.

### 5.7 Zarządzanie użytkownikami i rekruterami
Zakładka `/admin/uzytkownicy` (tylko Administrator):
- **dodawanie nowych rekruterów** (imię, nazwisko, e-mail służbowy, rola, przypisane jednostki/oferty opcjonalnie); nowe konto otrzymuje e-mail z jednorazowym linkiem do ustawienia hasła (bez wysyłania haseł w treści),
- edycja danych i zmiana roli (Administrator / Rekruter / Przeglądający),
- **dezaktywacja i ponowna aktywacja** konta (bez fizycznego usuwania, by zachować historię w logach), usunięcie konta tylko przez Administratora z potwierdzeniem,
- reset hasła i wymuszenie zmiany hasła, wymuszenie wylogowania użytkownika (unieważnienie sesji),
- podgląd ostatniego logowania i aktywnych sesji,
- zabezpieczenie: nie można usunąć ani zdegradować ostatniego aktywnego Administratora oraz własnego konta.

### 5.8 Zakładka „Logi”
Zakładka `/admin/logi` (Administrator; Rekruter widzi tylko własne operacje – konfigurowalne):
- **Rejestrowane są wszystkie operacje** w panelu: logowania (udane i nieudane), wysłanie i weryfikacja kodu 2FA, wylogowania, tworzenie/edycja/usuwanie ofert, zmiany filtrów i formularzy, zgłoszenia (podgląd, edycja, zmiana statusu, pobranie pliku, usunięcie), wysyłka maili, operacje na użytkownikach i rolach, zmiany ustawień, eksporty danych.
- Każdy wpis zawiera: datę i godzinę, użytkownika, akcję, obiekt (typ i identyfikator), wartości przed/po zmianie, adres IP, przeglądarkę.
- Filtrowanie (użytkownik, typ akcji, obiekt, zakres dat, wynik), wyszukiwanie, paginacja, eksport do CSV.
- Logi są **tylko do odczytu** – nie można ich edytować ani usuwać z poziomu aplikacji; przechowywane min. 12 miesięcy (konfigurowalne), potem archiwizowane.
- Dane wrażliwe (hasła, kody 2FA, tokeny) nigdy nie trafiają do logów.

---

## 6. Komunikacja e-mail i anulowanie zgłoszenia

Kandydat **nie loguje się nigdzie**. Cała komunikacja jest przez e-mail.

### 6.1 E-mail po złożeniu zgłoszenia (MUST)
Zawiera:
- podziękowanie, nazwę oferty, numer zgłoszenia (np. losowy kod referencyjny `REK-2026-XXXXXX`, nie kolejny ID),
- podsumowanie przekazanych danych i listę nazw załączonych plików,
- informację o przetwarzaniu danych i terminie/przebiegu naboru,
- **link do anulowania zgłoszenia**,
- kontakt do urzędu; adres „reply-to” do działu kadr.
Wersja HTML + tekstowa (plain text).

### 6.2 Przepływ anulowania (MUST)
1. Kandydat klika link w e-mailu: `GET /zgloszenie/anuluj/{token}`.
2. Serwer weryfikuje token i **nie anuluje jeszcze niczego**. Wyświetla stronę z informacją: nazwa oferty, kod zgłoszenia oraz pytaniem **„Czy na pewno chcesz anulować zgłoszenie?”** i dwoma przyciskami: „Tak, anuluj zgłoszenie” (POST) oraz „Nie, wróć / zamknij”.
3. Po kliknięciu „Tak” → `POST /zgloszenie/anuluj/{token}` (z CSRF) – zgłoszenie otrzymuje status *anulowane przez kandydata*, ustawiane `cancelled_at`.
4. Strona potwierdzenia + e-mail potwierdzający anulowanie do kandydata + informacja w panelu admina.
5. Ponowne wejście w link po anulowaniu → komunikat „Zgłoszenie zostało już anulowane”.
6. Anulowanie niemożliwe po zamknięciu naboru/zmianie statusu na końcowy (konfigurowalne) → komunikat z kontaktem do urzędu.

Uzasadnienie dwuetapowości: skanery linków w poczcie i prefetch nie mogą przypadkowo anulować zgłoszenia (GET jest bezpieczny, zmiana stanu tylko przez POST).

### 6.3 Pozostałe e-maile
Zmiana statusu, zaproszenie na rozmowę, odrzucenie, wiadomość ręczna od rekrutera, potwierdzenie anulowania. Wszystkie w kolejce (queue), z ponawianiem w razie błędu i logiem w bazie (`mail_logs`).

---

## 7. Bezpieczne (szyfrowane) adresy URL

Cel: brak możliwości odgadnięcia/enumeracji zasobów (brak sekwencyjnych ID w URL).

- **MUST**: w publicznych URL-ach nie używać auto-increment ID. Stosować:
  - `ULID`/`UUID v7` jako publiczny identyfikator (kolumna `public_id`, unikalny indeks), **lub** slug + krótki losowy sufiks dla ofert (np. `/oferty/referent-wydzial-kadr-k7f3q`).
- **Linki w e-mailach (anulowanie itp.)**: losowy token min. 64 znaki (`Str::random(64)`), w bazie przechowywany jako **hash SHA-256** (w mailu wersja jawna), z datą ważności i flagą użycia. Alternatywnie `URL::temporarySignedRoute` / `URL::signedRoute` (podpis HMAC) z parametrem tokenu.
- Dla parametrów wymagających ukrycia użyć `Crypt::encryptString()` / hashids z sekretem – tylko jeśli to konieczne; preferowane opaque ID + autoryzacja po stronie serwera.
- Panel admina: również UUID/ULID w URL, dodatkowo pełna autoryzacja (Policies) – ukrycie ID nie zastępuje kontroli dostępu.
- Rate limiting na endpointach z tokenem (np. 10 prób/min/IP) i jednakowy komunikat błędu dla nieistniejącego i wygasłego tokenu (brak wycieku informacji).
- Nagłówek `Referrer-Policy: no-referrer` na stronach z tokenem, `Cache-Control: no-store`, `X-Robots-Tag: noindex`.
- Wymuszony HTTPS (HSTS).

---

## 8. Bezpieczeństwo aplikacji (MUST)

### 8.1 Ochrona przed OWASP Top 10
- **SQL Injection**: wyłącznie Eloquent / Query Builder z bindowaniem parametrów; zakaz surowych zapytań z konkatenacją; przy `whereRaw/orderByRaw` – wyłącznie bindingi lub biała lista kolumn (sortowanie i filtry z whitelisty).
- **XSS**: automatyczne escapowanie Blade `{{ }}`; `{!! !!}` tylko dla wcześniej oczyszczonego HTML (HTMLPurifier); nagłówek **Content-Security-Policy** (bez `unsafe-inline`, nonce dla skryptów/stylów), `X-Content-Type-Options: nosniff`.
- **CSRF**: middleware `VerifyCsrfToken` na wszystkich formularzach POST/PUT/DELETE.
- **Mass assignment**: `$fillable` w modelach, walidacja przez Form Requests.
- **IDOR / broken access control**: Policies/Gates, sprawdzenie własności w każdym kontrolerze; testy automatyczne.
- **Upload plików**: patrz pkt 4.4; brak wykonywania plików, katalog storage poza webrootem, `Content-Disposition: attachment`, `nosniff`.
- **Brute force / DoS**: `RateLimiter` na logowaniu, formularzu zgłoszeń (np. 5 zgłoszeń/godz./IP i max. 1 zgłoszenie na e-mail na ofertę), wyszukiwaniu, endpointach z tokenem.
- **Session/Cookies**: `HttpOnly`, `Secure`, `SameSite=Lax`, regeneracja ID sesji po logowaniu, osobny cookie/sesja dla panelu.
- **Nagłówki bezpieczeństwa**: HSTS, CSP, X-Frame-Options `DENY` (lub `frame-ancestors 'none'`), Referrer-Policy, Permissions-Policy.
- **Header/Email injection**: walidacja adresów e-mail, brak wstawiania danych użytkownika do nagłówków.
- **Konfiguracja**: `APP_DEBUG=false` na produkcji, sekrety tylko w `.env`, `composer audit` i `npm audit` w CI, aktualizacje zależności.
- **Baza danych**: osobny użytkownik MySQL z minimalnymi uprawnieniami (bez `DROP`, `GRANT`, `FILE`), połączenie tylko z localhost/sieci wewnętrznej.
- **Szyfrowanie danych wrażliwych** w spoczynku (SHOULD): pola z danymi osobowymi opcjonalnie przez `encrypted` cast; szyfrowanie dysku/backupów.
- **Logowanie i monitoring**: logi bezpieczeństwa (nieudane logowania, zablokowane IP, odrzucone uploady), alerty, fail2ban/WAF (ModSecurity z regułami OWASP CRS – SHOULD).
- **Backup**: codzienna kopia bazy i plików, szyfrowana, przechowywana poza serwerem; test odtwarzania.

### 8.2 Testy bezpieczeństwa
- Testy automatyczne dla: dostępu do panelu spoza dozwolonej sieci (404), tokenów, autoryzacji, walidacji plików.
- Skan OWASP ZAP przed wdrożeniem, przegląd zależności.
- Testy automatyczne przepływu 2FA (poprawny kod, błędny kod, wygasły kod, ponowne użycie kodu, przekroczony limit prób).

### 8.3 Logowanie dwuskładnikowe (2FA) – kod w e-mailu (MUST)
Dotyczy wszystkich użytkowników panelu (Administrator, Rekruter, Przeglądający).

Przebieg:
1. Użytkownik podaje e-mail i hasło (pierwszy składnik).
2. Po poprawnej weryfikacji hasła system generuje jednorazowy **6-cyfrowy kod** (generator kryptograficzny, `random_int`), wysyła go na e-mail użytkownika i przekierowuje na ekran „Wpisz kod z wiadomości”. Do tego momentu sesja nie jest uznawana za zalogowaną.
3. Użytkownik wpisuje kod; po poprawnej weryfikacji następuje pełne zalogowanie i regeneracja ID sesji.

Zasady bezpieczeństwa:
- Kod ważny **10 minut**, jednorazowy (po użyciu lub wygaśnięciu unieważniony), w bazie przechowywany wyłącznie jako **hash**.
- Maksymalnie **5 błędnych prób** na kod; po przekroczeniu kod jest unieważniany, a logowanie czasowo blokowane (throttle); limit wysyłek nowego kodu (np. 3 na 15 min., przycisk „Wyślij kod ponownie”).
- Nowy kod unieważnia poprzednie; kod powiązany z konkretną próbą logowania (identyfikator w sesji).
- Wysyłka kodu bezpośrednio (priorytetowa kolejka lub synchronicznie), z tematem i treścią zawierającą czas ważności, adres IP i ostrzeżenie „Jeśli to nie Ty, zmień hasło i powiadom administratora”.
- Porównywanie kodów w stałym czasie (`hash_equals`); identyczne komunikaty błędów niezależnie od przyczyny.
- Opcja „zaufaj tej przeglądarce” jest wyłączona (kod wymagany przy każdym logowaniu).
- Formularz kodu dostępny (WCAG): pole z `autocomplete="one-time-code"`, `inputmode="numeric"`, czytelne komunikaty błędów, brak automatycznego przechodzenia między polami utrudniającego czytniki ekranu.
- Wszystkie zdarzenia (wysłanie kodu, błędny kod, sukces, blokada) rejestrowane w zakładce Logi (5.8).
- Wymaga poprawnie skonfigurowanego serwera SMTP dostępnego z sieci lokalnej; awaryjne odblokowanie konta możliwe tylko przez innego Administratora (bez omijania 2FA).

---

## 9. RODO i dane osobowe (MUST)

- Klauzula informacyjna przy formularzu (administrator danych, cel – rekrutacja, podstawa prawna, okres przechowywania, prawa osoby, kontakt do IOD).
- Zapis zgody (wersja treści, data, IP zanonimizowane/hash) w bazie.
- **Retencja**: automatyczne usuwanie/anonimizacja danych i plików po X miesiącach od zakończenia naboru (konfigurowalne; domyślnie zgodnie z polityką urzędu), harmonogram Laravel (`schedule`).
- Możliwość usunięcia/anonimizacji zgłoszenia na żądanie kandydata (z panelu; procedura w dokumentacji).
- Minimalizacja danych (nie żądać zbędnych pól).
- Rejestr operacji na danych (audit log).
- Brak zewnętrznych trackerów i skryptów analitycznych bez zgody; brak zewnętrznych CDN dla fontów (hostować lokalnie).

---

## 10. Dostępność cyfrowa (WCAG) (MUST)

Zgodność z **WCAG 2.1 poziom AA** (zgodnie z ustawą z 4 kwietnia 2019 r. o dostępności cyfrowej stron internetowych i aplikacji mobilnych podmiotów publicznych oraz normą EN 301 549). Dążyć do WCAG 2.2 AA.

Wymagania implementacyjne:
- Semantyczny HTML5: `header`, `nav`, `main`, `footer`, poprawna hierarchia nagłówków (jeden `h1`).
- Link „Przejdź do treści” (skip link) na początku strony.
- Pełna obsługa **klawiatury**, widoczny wskaźnik fokusu (min. kontrast 3:1), logiczna kolejność tabulacji, brak pułapek klawiaturowych.
- Kontrast tekstu min. **4.5:1** (3:1 dla dużego tekstu i elementów UI) – w obu motywach.
- Formularze: powiązane `<label for>`, opisy błędów przy polach (`aria-describedby`), `aria-invalid`, podsumowanie błędów na górze (z fokusem) po nieudanej walidacji, komunikaty nie oparte wyłącznie na kolorze, atrybuty `autocomplete` (name, email, tel), oznaczenie pól wymaganych.
- Teksty alternatywne obrazów (`alt`), dekoracyjne z `alt=""`.
- Skalowanie tekstu do 200% i zoom 400% bez utraty treści i poziomego przewijania (reflow); jednostki względne (`rem`).
- Cele dotykowe min. 24×24 px (zalecane 44×44).
- Atrybut `lang="pl"` (oraz `lang` dla fragmentów w innych językach), sensowny `<title>` każdej strony.
- Tabele z `<th scope>` i `<caption>`; paginacja z `aria-label`, `aria-current="page"`.
- Szanowanie `prefers-reduced-motion`; brak automatycznie odtwarzanych/migających elementów.
- Komunikaty statusu (np. „Zgłoszenie wysłane”) z `role="status"`/`aria-live`.
- Załączniki i dokumenty dostępne w formatach dostępnych (informacja dla redaktorów ofert).
- Strona **Deklaracja dostępności** (wymagana prawem): zakres zgodności, wyłączenia, data sporządzenia, dane kontaktowe do zgłaszania problemów, informacja o procedurze wnioskowej i skargowej.
- CAPTCHA – wyłącznie w wersji dostępnej lub jej brak.
- Panel administracyjny również spełnia WCAG 2.1 AA.
- Testy: axe-core / pa11y / Lighthouse w CI, ręczny test z czytnikiem ekranu (NVDA, VoiceOver) i samą klawiaturą.
- Opcjonalnie: przełącznik wielkości czcionki i wysokiego kontrastu (nie zastępuje poprawnego kodu).

---

## 11. Tryb ciemny (Dark Mode) (MUST)

- Motywy: **jasny**, **ciemny**, **automatyczny (zgodnie z systemem)** – domyślnie automatyczny (`prefers-color-scheme`).
- Przełącznik w nagłówku (przycisk z `aria-pressed`/etykietą, dostępny z klawiatury), wybór zapamiętany w `localStorage` (wyłącznie preferencja UI, bez cookie śledzącego).
- Implementacja przez **zmienne CSS** (`--color-bg`, `--color-text`, `--color-link`, `--color-border`, …) i atrybut `data-theme` na `<html>`; skrypt inline (z nonce CSP) ustawiający motyw przed renderowaniem, aby uniknąć „mignięcia”.
- Oba motywy spełniają kontrasty WCAG AA (sprawdzone dla tekstu, linków, ramek pól, fokusów, przycisków, komunikatów błędów).
- Dark mode obejmuje także panel administracyjny i szablony e-mail (e-mail: wsparcie `prefers-color-scheme` tam, gdzie klient poczty pozwala, w innym wypadku neutralny wygląd czytelny w obu trybach).
- Logo w dwóch wariantach (lub SVG z `currentColor`).

---

## 12. Model danych (propozycja)

```
users                 (id, name, email, password, role, is_active, last_login_at, password_changed_at, ...)
login_codes           (id, user_id, code_hash, attempt_id, expires_at, used_at, attempts, ip, created_at)
departments           (id, name)
contract_types        (id, name)
job_categories        (id, name)
locations             (id, name)

job_offers            (id, public_id[ULID], slug, title, department_id, contract_type_id,
                       category_id, location_id, working_time, description, requirements,
                       nice_to_have, offer_text, required_documents, form_id nullable (nadpisanie szablonu formularza),
                       statements[JSON], deadline_at, published_at, status,
                       created_by, timestamps, soft_deletes)

applications          (id, public_id[ULID], reference_code, job_offer_id, first_name, last_name,
                       email, phone, address, answers[JSON] (odpowiedzi na pola dynamiczne formularza),
                       status, internal_notes, consent_version_id, consent_at,
                       future_consent, cancelled_at, cancel_token_hash, cancel_token_expires_at,
                       source[web|manual], created_by_user_id nullable, timestamps, soft_deletes)

application_files     (id, application_id, type[cv|letter|other], original_name, stored_path,
                       mime, size, checksum, scan_status, timestamps)

application_status_history (id, application_id, from_status, to_status, user_id, note, created_at)
mail_logs             (id, application_id, template, to_email, subject, status, error, sent_at)
email_templates       (id, key, subject, body_html, body_text)
consent_texts         (id, version, content, active_from)
audit_logs            (id, user_id, action, auditable_type, auditable_id, old_values[JSON],
                       new_values[JSON], ip, user_agent, created_at)  -- źródło zakładki Logi (5.8), tylko do odczytu
forms                 (id, name, is_template, timestamps)
form_fields           (id, form_id, key, type, label, help_text, is_required, is_system, validation[JSON],
                       options[JSON], section, position, is_active, timestamps)
filters               (id, key, label, source[dictionary|field], source_ref, control_type, position, is_active)
settings              (key, value)
```

Indeksy: `job_offers(status, deadline_at)`, `job_offers(department_id, contract_type_id)`, FULLTEXT na `title, description`, `applications(job_offer_id, status)`, unikalny `public_id`, `reference_code`, `cancel_token_hash`.

Zasada: max. 1 aktywne zgłoszenie na parę (e-mail, oferta) – duplikat zwraca ogólny komunikat.

---

## 13. Struktura tras (propozycja)

Publiczne:
```
GET  /                                   lista ofert + filtry
GET  /oferty/{slug}                      szczegóły oferty
GET  /oferty/{slug}/aplikuj              formularz
POST /oferty/{slug}/aplikuj              zapis zgłoszenia
GET  /zgloszenie/dziekujemy              potwierdzenie
GET  /zgloszenie/anuluj/{token}          strona z pytaniem „Czy na pewno?”
POST /zgloszenie/anuluj/{token}          faktyczne anulowanie
GET  /deklaracja-dostepnosci
GET  /polityka-prywatnosci
```

Panel (`/admin`, middleware: `EnsureLocalNetwork`, `auth`, `role`):
```
/admin/login, /admin/logout, /admin/2fa
/admin/dashboard
/admin/oferty (resource)
/admin/zgloszenia (resource) + /pliki/{file} (pobranie)
/admin/slowniki/*
/admin/uzytkownicy (rekruterzy)
/admin/filtry
/admin/formularze
/admin/ustawienia, /admin/szablony-maili
/admin/logi
```

---

## 14. Wymagania niefunkcjonalne

- Wydajność: strona główna < 2 s TTFB przy 100 równoległych użytkownikach; cache zapytań listy ofert, eager loading (brak N+1).
- Kodowanie UTF-8, pełna obsługa polskich znaków, język interfejsu: polski (tłumaczenia w `lang/pl`, gotowość do dodania kolejnych języków).
- Strefa czasowa `Europe/Warsaw`.
- Kompatybilność: aktualne wersje Chrome, Firefox, Edge, Safari; graceful degradation bez JS (filtry i formularze działają bez JS).
- SEO: meta tagi, dane strukturalne `JobPosting` (schema.org) dla ofert, `sitemap.xml`, panel wyłączony z indeksowania.
- Kod: PSR-12 (Laravel Pint), typowanie, Form Requests, Policies, serwisy dla logiki biznesowej, migracje i seedery (dane demo), README z instrukcją instalacji i wdrożenia.
- CI: testy, analiza statyczna (Larastan poziom 6+), audyt zależności, testy dostępności.

---

## 15. Kryteria akceptacji (skrót)

1. Panel `/admin` z adresu spoza dozwolonej sieci zwraca 404, z sieci lokalnej wymaga logowania.
2. Kandydat składa zgłoszenie bez konta, otrzymuje e-mail z kodem zgłoszenia i linkiem anulowania.
3. Link anulowania otwiera stronę z pytaniem o potwierdzenie; anulowanie następuje dopiero po POST; ponowne użycie linku pokazuje odpowiedni komunikat.
4. W URL-ach publicznych nie występują sekwencyjne ID; tokeny są losowe i przechowywane jako hash.
5. Filtry na stronie głównej działają (także bez JS) i są odzwierciedlone w adresie URL.
6. Wszystkie dane zgłoszenia i pliki są edytowalne w panelu; zmiany trafiają do logu audytu.
7. Próby SQLi/XSS/CSRF/uploadu złośliwego pliku są blokowane (potwierdzone testami).
8. Strona przechodzi audyt axe/Lighthouse (dostępność ≥ 95) oraz ręczny test klawiaturą i czytnikiem ekranu; istnieje Deklaracja dostępności.
9. Dark mode działa na całym serwisie, zapamiętuje wybór i spełnia kontrasty AA.
10. Mechanizm retencji danych usuwa/anonimizuje dane po zadanym okresie.
11. Administrator może w panelu edytować filtry na stronie głównej (włączanie, etykiety, kolejność, nowe filtry) i zmiany są widoczne od razu.
12. Administrator/rekruter może dodawać, usuwać i edytować pola formularza oraz zmieniać ich kolejność; formularz pozostaje zgodny z WCAG.
13. System przyjmuje wyłącznie pliki PDF; inne formaty i PDF-y z niepoprawnym nagłówkiem są odrzucane.
14. Administrator dodaje, edytuje, dezaktywuje rekruterów i resetuje im hasła; nie można usunąć ostatniego Administratora.
15. Każde logowanie wymaga kodu 2FA z e-maila (ważny 10 min, jednorazowy, limit prób); brak kodu = brak dostępu.
16. Zakładka Logi zawiera wszystkie operacje w panelu (w tym logowania i 2FA) i jest tylko do odczytu.

---

## 16. Proponowany podział prac dla AI (etapy)

1. **Szkielet**: instalacja Laravel, konfiguracja MySQL, migracje, modele, seedery, layout Blade z motywami jasny/ciemny i bazową dostępnością.
2. **Strona publiczna**: lista ofert z filtrami, szczegóły oferty, strony statyczne.
3. **Formularz i pliki**: walidacja, upload, skan, zapis, e-mail potwierdzający (kolejka).
4. **Anulowanie**: tokeny, dwuetapowy przepływ, maile.
5. **Panel admina**: logowanie + 2FA + middleware sieci lokalnej, CRUD ofert, zarządzanie zgłoszeniami, edycja, statusy, wysyłka maili, audyt.
6. **Bezpieczeństwo**: nagłówki, CSP, rate limiting, przegląd OWASP, testy.
7. **Dostępność i dark mode**: audyt, poprawki, deklaracja dostępności.
8. **RODO i retencja**, eksporty, backup.
9. **Testy, dokumentacja, wdrożenie** (Nginx/Apache, PHP-FPM, HTTPS, supervisor dla kolejek, cron dla schedulera).

---

## 17. Założenia do potwierdzenia przez urząd

- Konkretne zakresy IP sieci lokalnej i czy panel ma osobną domenę.
- Docelowy serwer SMTP i adres nadawcy.
- Czy oferty mają być zgodne z wymogami naborów w jednostkach samorządowych (ogłoszenie o naborze, informacja o wyniku naboru – ewentualnie publikacja wyników na stronie/BIP).
- Okresy retencji danych i treść klauzuli RODO (IOD).
- Logo, kolory i teksty urzędu.
- Czy CV jest zawsze wymagane, czy dopuszczalne jest samo wypełnienie formularza (konfiguracja per oferta).
- Wybór rozwiązania antyspamowego (CAPTCHA).
