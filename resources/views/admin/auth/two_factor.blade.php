@extends('layouts.app')

@section('title', 'Weryfikacja dwuskładnikowa (2FA)')

@section('content')
<div style="max-width: 440px; margin: 2rem auto;">
    <div class="card" style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;" aria-hidden="true">✉️</div>
            <h1 style="font-size: 1.5rem; margin-bottom: 0.25rem;">Weryfikacja dwuskładnikowa</h1>
            <p style="font-size: 0.875rem; color: var(--color-text-muted);">
                Wprowadź 6-cyfrowy kod bezpieczeństwa przesłany na Twój adres e-mail.
            </p>
        </div>

        <form action="{{ route('admin.2fa.verify') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label for="code" class="form-label" style="text-align: center; display: block;">
                    Jednorazowy kod 2FA <span aria-hidden="true" style="color: var(--color-danger);">*</span>
                </label>
                <input type="text" id="code" name="code" required autofocus maxlength="6"
                       class="form-control"
                       style="font-size: 1.75rem; letter-spacing: 0.4rem; text-align: center; font-family: monospace; font-weight: bold;"
                       autocomplete="one-time-code"
                       inputmode="numeric"
                       pattern="[0-9]{6}"
                       placeholder="000000"
                       aria-required="true"
                       aria-describedby="code-help @error('code') code-error @enderror"
                       @error('code') aria-invalid="true" @enderror>
                <div id="code-help" class="form-help" style="text-align: center; margin-top: 0.375rem;">
                    Kod jest ważny przez 10 minut. Maksymalnie 5 prób weryfikacji.
                </div>
                @error('code')
                    <div id="code-error" style="color: var(--color-danger); font-size: 0.875rem; margin-top: 0.25rem; text-align: center;">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Zaloguj się do panelu &rarr;
                </button>
            </div>
        </form>

        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
            <form action="{{ route('admin.2fa.resend') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-secondary" style="font-size: 0.8125rem; min-height: 38px; padding: 0.375rem 0.75rem;">
                    Wyślij kod ponownie
                </button>
            </form>

            <a href="{{ route('admin.login') }}" style="font-size: 0.8125rem;">
                Wróć do logowania
            </a>
        </div>
    </div>
</div>
@endsection
