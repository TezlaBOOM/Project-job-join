@extends('layouts.app')

@section('title', "Aplikuj: {$offer->title}")
@section('meta_description', "Formularz zgłoszeniowy na stanowisko {$offer->title} w Urzędzie Miasta.")

@section('content')
<div style="margin-bottom: 1.75rem;">
    <a href="{{ route('public.offers.show', $offer->slug) }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
        <span>&larr;</span> Wróć do opisu ogłoszenia
    </a>
</div>

<div style="max-width: 840px; margin: 0 auto;">
    <header class="card" style="padding: 2.25rem; margin-bottom: 2rem; border-top: 4px solid var(--color-primary);">
        <div style="margin-bottom: 0.5rem;">
            <span class="badge badge-primary">Formularz aplikacji online</span>
        </div>
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--color-text); line-height: 1.25; margin-bottom: 0.75rem;">
            Nabór: {{ $offer->title }}
        </h1>
        <p style="font-size: 1.05rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 0;">
            Aplikacja na to stanowisko nie wymaga logowania. Po pomyślnym przesłaniu dokumentów wyślemy potwierdzenie z kodem referencyjnym na podany adres e-mail.
        </p>
    </header>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert" tabindex="-1" id="error-summary" autofocus style="margin-bottom: 2rem;">
            <div style="font-size: 1.5rem;" aria-hidden="true">⚠️</div>
            <div>
                <h2 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.5rem;">Proszę poprawić następujące błędy:</h2>
                <ul style="padding-left: 1.25rem; margin-bottom: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('public.applications.store', $offer->slug) }}" method="POST" enctype="multipart/form-data" novalidate id="application-form">
        @csrf

        <!-- Ochrona antyspamowa: Honeypot (musi pozostać puste) -->
        <div style="display: none;" aria-hidden="true">
            <label for="_hp_name">Nie wypełniaj tego pola (ochrona antyspamowa)</label>
            <input type="text" id="_hp_name" name="_hp_name" tabindex="-1" autocomplete="off">
        </div>

        <!-- Ochrona antyspamowa: Time-trap -->
        <input type="hidden" name="_rendered_at" value="{{ time() }}">

        <!-- Sekcja 1: Dane osobowe i kontaktowe -->
        <section class="card" style="margin-bottom: 2rem; padding: 2rem;">
            <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem; display: flex; align-items: center; gap: 0.625rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: var(--color-primary-subtle); color: var(--color-primary); font-size: 0.875rem;">1</span>
                Dane osobowe i kontaktowe
            </h2>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="first_name" class="form-label">
                        Imię <span style="color: var(--color-danger);" aria-hidden="true">*</span>
                    </label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required
                           class="form-control" autocomplete="given-name" aria-required="true"
                           @error('first_name') aria-invalid="true" aria-describedby="first_name_error" @enderror>
                    @error('first_name')
                        <div id="first_name_error" style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.35rem; font-weight: 600;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="last_name" class="form-label">
                        Nazwisko <span style="color: var(--color-danger);" aria-hidden="true">*</span>
                    </label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required
                           class="form-control" autocomplete="family-name" aria-required="true"
                           @error('last_name') aria-invalid="true" aria-describedby="last_name_error" @enderror>
                    @error('last_name')
                        <div id="last_name_error" style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.35rem; font-weight: 600;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.25rem;">
                <div class="form-group">
                    <label for="email" class="form-label">
                        Adres e-mail <span style="color: var(--color-danger);" aria-hidden="true">*</span>
                    </label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                           class="form-control" autocomplete="email" aria-required="true"
                           aria-describedby="email_help @error('email') email_error @enderror"
                           @error('email') aria-invalid="true" @enderror>
                    <div id="email_help" class="form-help">Na ten adres wyślemy potwierdzenie i link do anulowania.</div>
                    @error('email')
                        <div id="email_error" style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.35rem; font-weight: 600;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Numer telefonu</label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                           class="form-control" autocomplete="tel"
                           aria-describedby="phone_help"
                           @error('phone') aria-invalid="true" @enderror>
                    <div id="phone_help" class="form-help">Opcjonalnie, do sprawnego kontaktu telefonicznego.</div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="address" class="form-label">Adres do korespondencji</label>
                <input type="text" id="address" name="address" value="{{ old('address') }}"
                       class="form-control" autocomplete="street-address"
                       placeholder="ulica, numer domu/lokalu, kod pocztowy, miejscowość">
            </div>
        </section>

        <!-- Sekcja 2: Pola dodatkowe / dynamiczne formularza -->
        @if($fields && $fields->isNotEmpty())
            <section class="card" style="margin-bottom: 2rem; padding: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem; display: flex; align-items: center; gap: 0.625rem;">
                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: var(--color-primary-subtle); color: var(--color-primary); font-size: 0.875rem;">2</span>
                    Informacje uzupełniające
                </h2>

                @foreach($fields as $field)
                    @if(!in_array($field->key, ['first_name', 'last_name', 'email', 'phone', 'address', 'cv_file']))
                        <div class="form-group">
                            <label for="field_{{ $field->key }}" class="form-label">
                                {{ $field->label }}
                                @if($field->is_required)
                                    <span style="color: var(--color-danger);" aria-hidden="true">*</span>
                                @endif
                            </label>

                            @if($field->help_text)
                                <div class="form-help">{{ $field->help_text }}</div>
                            @endif

                            @if($field->type === 'textarea')
                                <textarea id="field_{{ $field->key }}" name="answers[{{ $field->key }}]" class="form-control" rows="4" {{ $field->is_required ? 'required' : '' }}>{{ old("answers.{$field->key}") }}</textarea>
                            @elseif($field->type === 'select' && !empty($field->options))
                                <select id="field_{{ $field->key }}" name="answers[{{ $field->key }}]" class="form-control" {{ $field->is_required ? 'required' : '' }}>
                                    <option value="">-- Wybierz opcję --</option>
                                    @foreach($field->options as $opt)
                                        <option value="{{ $opt }}" {{ old("answers.{$field->key}") == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                    @endforeach
                                </select>
                            @elseif($field->type === 'checkbox')
                                <label style="display: flex; align-items: center; gap: 0.6rem; cursor: pointer;">
                                    <input type="checkbox" id="field_{{ $field->key }}" name="answers[{{ $field->key }}]" value="1" {{ old("answers.{$field->key}") ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: var(--color-primary);">
                                    <span style="font-weight: 500;">{{ $field->label }}</span>
                                </label>
                            @else
                                <input type="{{ $field->type === 'date' ? 'date' : ($field->type === 'number' ? 'number' : 'text') }}"
                                       id="field_{{ $field->key }}" name="answers[{{ $field->key }}]"
                                       value="{{ old("answers.{$field->key}") }}"
                                       class="form-control" {{ $field->is_required ? 'required' : '' }}>
                            @endif
                        </div>
                    @endif
                @endforeach
            </section>
        @endif

        <!-- Sekcja 3: Załączniki (WYŁĄCZNIE PDF) -->
        <section class="card" style="margin-bottom: 2rem; padding: 2rem;">
            <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem; display: flex; align-items: center; gap: 0.625rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: var(--color-primary-subtle); color: var(--color-primary); font-size: 0.875rem;">3</span>
                Załączniki (wyłącznie format PDF)
            </h2>
            <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
                Zgodnie z procedurami bezpieczeństwa teleinformatycznego system akceptuje <strong>wyłącznie pliki w formacie PDF</strong>. Maksymalny rozmiar pojedynczego pliku: 5 MB (łącznie do 20 MB).
            </p>

            <div class="form-group">
                <label for="cv_file" class="form-label">
                    Życiorys / CV (plik PDF) <span style="color: var(--color-danger);" aria-hidden="true">*</span>
                </label>
                <input type="file" id="cv_file" name="cv_file" required accept=".pdf,application/pdf"
                       class="form-control" aria-required="true"
                       aria-describedby="cv_help @error('cv_file') cv_error @enderror"
                       @error('cv_file') aria-invalid="true" @enderror>
                <div id="cv_help" class="form-help">Wymagany format: PDF (plik z nagłówkiem %PDF), max 5 MB.</div>
                @error('cv_file')
                    <div id="cv_error" style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.35rem; font-weight: 600;">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="additional_files" class="form-label">
                    Dodatkowe dokumenty (np. list motywacyjny, oświadczenia, dyplomy – pliki PDF)
                </label>
                <input type="file" id="additional_files" name="additional_files[]" multiple accept=".pdf,application/pdf"
                       class="form-control" aria-describedby="additional_help">
                <div id="additional_help" class="form-help">Możesz zaznaczyć kilka plików PDF naraz (Ctrl/Cmd + kliknięcie).</div>
            </div>
        </section>

        <!-- Sekcja 4: Oświadczenia i zgody RODO -->
        <section class="card" style="margin-bottom: 2.5rem; padding: 2rem;">
            <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem; display: flex; align-items: center; gap: 0.625rem;">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; background: var(--color-primary-subtle); color: var(--color-primary); font-size: 0.875rem;">4</span>
                Oświadczenia i RODO
            </h2>

            @if(!empty($offer->statements) && is_array($offer->statements))
                <div style="margin-bottom: 1.5rem;">
                    <div style="font-weight: 700; margin-bottom: 0.75rem; color: var(--color-text);">Wymagane oświadczenia formalne:</div>
                    @foreach($offer->statements as $idx => $statement)
                        <div class="form-group" style="margin-bottom: 0.875rem;">
                            <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;">
                                <input type="checkbox" name="statements[{{ $idx }}]" value="1" required style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--color-primary);" aria-required="true">
                                <span style="font-size: 0.95rem; line-height: 1.5;">{{ is_array($statement) ? ($statement['text'] ?? '') : $statement }} <span style="color: var(--color-danger);" aria-hidden="true">*</span></span>
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Obowiązkowa zgoda RODO z wersjonowaniem -->
            <div style="margin-bottom: 1.5rem; padding: 1.25rem; background-color: var(--color-surface-subtle); border-radius: var(--radius-md); border: 1.5px solid var(--color-border);">
                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" id="rodo_consent" name="rodo_consent" value="1" required style="width: 22px; height: 22px; margin-top: 2px; accent-color: var(--color-primary);"
                           aria-required="true" @error('rodo_consent') aria-invalid="true" @enderror>
                    <span style="font-size: 0.95rem; line-height: 1.5;">
                        <strong>Oświadczam, że zapoznałem/am się z klauzulą informacyjną i wyrażam zgodę na przetwarzanie moich danych osobowych</strong> w celu przeprowadzenia niniejszego postępowania rekrutacyjnego zgodnie z przepisami RODO.
                        <span style="color: var(--color-danger);" aria-hidden="true">*</span>
                    </span>
                </label>
                @if($consent)
                    <details style="margin-top: 1rem; font-size: 0.85rem; color: var(--color-text-muted);">
                        <summary style="cursor: pointer; font-weight: 700; color: var(--color-primary);">Rozwiń pełną treść klauzuli RODO (wersja {{ $consent->version }})</summary>
                        <div style="margin-top: 0.75rem; line-height: 1.6; padding: 1rem; background: var(--color-surface); border-radius: var(--radius-sm); border: 1px solid var(--color-border-subtle);">
                            {!! nl2br(e($consent->content)) !!}
                        </div>
                    </details>
                @endif
                @error('rodo_consent')
                    <div style="color: var(--color-danger); font-size: 0.85rem; margin-top: 0.5rem; font-weight: 700;">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Opcjonalna zgoda na przyszłe nabory -->
            <div class="form-group" style="margin-bottom: 0;">
                <label style="display: flex; align-items: flex-start; gap: 0.75rem; cursor: pointer;">
                    <input type="checkbox" id="future_consent" name="future_consent" value="1" {{ old('future_consent') ? 'checked' : '' }} style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--color-primary);">
                    <span style="font-size: 0.9rem; color: var(--color-text-muted); line-height: 1.5;">
                        (Opcjonalnie) Wyrażam zgodę na przetwarzanie moich danych osobowych w celach przyszłych naborów prowadzonych przez Urząd Miasta przez okres do 12 miesięcy.
                    </span>
                </label>
            </div>
        </section>

        <!-- Przycisk wysłania -->
        <div style="text-align: center; margin-top: 2rem; margin-bottom: 4rem;">
            <button type="submit" id="submit-btn" class="btn btn-primary btn-lg" style="font-size: 1.15rem; padding: 1rem 3rem; box-shadow: var(--shadow-lg);">
                <span>🔒</span> Wyślij zgłoszenie do naboru &rarr;
            </button>
            <div style="font-size: 0.85rem; color: var(--color-text-light); margin-top: 0.75rem;">
                Poświadczenie złożenia dokumentów z unikalnym kodem referencyjnym zostanie przesłane drogą elektroniczną.
            </div>
        </div>
    </form>
</div>

<script>
    document.getElementById('application-form')?.addEventListener('submit', function() {
        var btn = document.getElementById('submit-btn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Trwa wysyłanie zgłoszenia...';
        }
    });
</script>
@endsection
