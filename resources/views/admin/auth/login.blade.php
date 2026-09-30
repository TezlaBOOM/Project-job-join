@extends('layouts.app')

@section('title', 'Logowanie do panelu administracyjnego')

@section('content')
<div style="max-width: 440px; margin: 2rem auto;">
    <div class="card" style="padding: 2rem;">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;" aria-hidden="true">🏛️</div>
            <h1 style="font-size: 1.5rem; margin-bottom: 0.25rem;">Panel Administracyjny</h1>
            <p style="font-size: 0.875rem; color: var(--color-text-muted);">
                Logowanie dla pracowników Urzędu Miasta
            </p>
        </div>

        <form action="{{ route('admin.login.submit') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Adres e-mail służbowy <span aria-hidden="true" style="color: var(--color-danger);">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="form-control" autocomplete="username"
                       aria-required="true"
                       @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')
                    <div id="email-error" style="color: var(--color-danger); font-size: 0.875rem; margin-top: 0.25rem;">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Hasło <span aria-hidden="true" style="color: var(--color-danger);">*</span></label>
                <input type="password" id="password" name="password" required
                       class="form-control" autocomplete="current-password"
                       aria-required="true"
                       @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                @error('password')
                    <div id="password-error" style="color: var(--color-danger); font-size: 0.875rem; margin-top: 0.25rem;">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    @if(!empty($is2FaEnabled))
                        Dalej (weryfikacja 2FA) &rarr;
                    @else
                        Zaloguj się do panelu &rarr;
                    @endif
                </button>
            </div>
        </form>

        <div style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border); font-size: 0.8125rem; color: var(--color-text-muted); text-align: center;">
            @if(!empty($is2FaEnabled))
                <p>🛡️ Panel zabezpieczony uwierzytelnianiem dwuskładnikowym (2FA).</p>
            @endif
            <p style="margin-top: 0.25rem;">Dostęp dozwolony wyłącznie z autoryzowanej sieci lokalnej (Intranet).</p>
        </div>
    </div>
</div>
@endsection
