@extends('layouts.admin')

@section('title', 'Ustawienia portalu rekrutacyjnego')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Ustawienia portalu</h1>
    <p style="color: var(--color-text-muted); font-size: 0.95rem;">Zarządzaj danymi urzędu, okresami retencji RODO, limitami plików i klauzulami prawnymi</p>
</div>

<div style="max-width: 800px; margin: 0 auto;">
    <form action="{{ route('admin.settings.update') }}" method="POST" class="card" style="padding: 2rem;">
        @csrf

        <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            1. Dane teleadresowe Urzędu Miasta
        </h2>

        <div class="form-group">
            <label for="office_name" class="form-label">Nazwa urzędu / instytucji: <span style="color: var(--color-danger);">*</span></label>
            <input type="text" id="office_name" name="office_name" value="{{ old('office_name', $settings['office_name']) }}" required class="form-control">
        </div>

        <div class="form-group">
            <label for="office_address" class="form-label">Adres siedziby: <span style="color: var(--color-danger);">*</span></label>
            <input type="text" id="office_address" name="office_address" value="{{ old('office_address', $settings['office_address']) }}" required class="form-control">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="office_email" class="form-label">Adres e-mail działu kadr: <span style="color: var(--color-danger);">*</span></label>
                <input type="email" id="office_email" name="office_email" value="{{ old('office_email', $settings['office_email']) }}" required class="form-control">
            </div>

            <div class="form-group">
                <label for="office_phone" class="form-label">Telefon kontaktowy: <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="office_phone" name="office_phone" value="{{ old('office_phone', $settings['office_phone']) }}" required class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="bip_url" class="form-label">Adres Biuletynu Informacji Publicznej (BIP):</label>
            <input type="url" id="bip_url" name="bip_url" value="{{ old('bip_url', $settings['bip_url']) }}" class="form-control" placeholder="https://bip.miasto.gov.pl">
        </div>

        <h2 style="font-size: 1.25rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            2. Wygląd strony głównej
        </h2>

        <div class="form-group">
            <label for="home_intro" class="form-label">Tekst wprowadzenia na stronie głównej: <span style="color: var(--color-danger);">*</span></label>
            <textarea id="home_intro" name="home_intro" rows="3" required class="form-control">{{ old('home_intro', $settings['home_intro']) }}</textarea>
        </div>

        <h2 style="font-size: 1.25rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            3. Bezpieczeństwo, limity plików i retencja RODO
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="retention_months" class="form-label">Okres retencji danych (miesiące): <span style="color: var(--color-danger);">*</span></label>
                <input type="number" id="retention_months" name="retention_months" min="1" max="60" value="{{ old('retention_months', $settings['retention_months']) }}" required class="form-control">
                <div class="form-help">Po ilu miesiącach od zakończenia naboru dane i pliki kandydatów podlegają anonimizacji.</div>
            </div>

            <div class="form-group">
                <label for="file_max_mb" class="form-label">Maksymalny rozmiar pliku PDF (MB): <span style="color: var(--color-danger);">*</span></label>
                <input type="number" id="file_max_mb" name="file_max_mb" min="1" max="20" value="{{ old('file_max_mb', $settings['file_max_mb']) }}" required class="form-control">
                <div class="form-help">Format plików jest zawsze ściśle ograniczony do formatu PDF.</div>
            </div>
        </div>

        <div class="form-group" style="margin-top: 1rem; padding: 1.25rem; background-color: var(--color-surface-subtle); border-radius: var(--radius-md); border: 1px solid var(--color-border);">
            <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;">
                <input type="checkbox" name="admin_2fa_enabled" value="1" {{ !empty($settings['admin_2fa_enabled']) ? 'checked' : '' }} style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--color-primary);">
                <div>
                    <strong style="color: var(--color-text); font-size: 0.95rem;">Włącz uwierzytelnianie dwuskładnikowe (2FA E-mail)</strong>
                    <div class="form-help" style="margin-top: 0.25rem;">
                        Gdy ta opcja jest wyłączona (domyślnie), logowanie do panelu administracyjnego odbywa się bezpośrednio przy użyciu loginu i hasła.
                        Po jej włączeniu wymagany jest 6-cyfrowy kod wysyłany na adres e-mail pracownika.
                    </div>
                </div>
            </label>
        </div>

        <h2 style="font-size: 1.25rem; font-weight: 700; margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            4. Klauzula RODO (z wersjonowaniem)
        </h2>

        <div class="form-group">
            <label for="rodo_version" class="form-label">Numer wersji klauzuli RODO:</label>
            <input type="text" id="rodo_version" name="rodo_version" value="{{ old('rodo_version', $latestConsent ? $latestConsent->version : '1.0') }}" class="form-control" style="max-width: 150px;">
        </div>

        <div class="form-group">
            <label for="rodo_content" class="form-label">Treść klauzuli informacyjnej RODO:</label>
            <textarea id="rodo_content" name="rodo_content" rows="6" class="form-control">{{ old('rodo_content', $latestConsent ? $latestConsent->content : "Zgodnie z art. 13 ogólnego rozporządzenia o ochronie danych osobowych z dnia 27 kwietnia 2016 r. (RODO) informujemy, iż:\n1. Administratorem danych jest Urząd Miasta.\n2. Dane przetwarzane są w celu rekrutacji.\n3. Przysługuje Państwu prawo dostępu, sprostowania oraz usunięcia danych.") }}</textarea>
            <div class="form-help">Zmiana treści lub numeru wersji utworzy nową wersję klauzuli, która będzie przypisywana do nowo składanych zgłoszeń.</div>
        </div>

        <div style="text-align: right; margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 2rem;">
                Zapisz ustawienia
            </button>
        </div>
    </form>
</div>
@endsection
