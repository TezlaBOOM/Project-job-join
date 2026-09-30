@extends('layouts.app')

@section('title', 'Potwierdzenie anulowania zgłoszenia')

@section('content')
<div style="max-width: 580px; margin: 3rem auto;">
    <div class="card" style="padding: 2.5rem 2rem; border-top: 4px solid var(--color-danger);">
        <div style="text-align: center; margin-bottom: 1.5rem;">
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;" aria-hidden="true">⚠️</div>
            <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--color-text); margin-bottom: 0.5rem;">
                Czy na pewno chcesz anulować zgłoszenie?
            </h1>
            <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                Ta operacja spowoduje wycofanie Twojej aplikacji z trwającego naboru.
            </p>
        </div>

        <div style="background-color: var(--color-surface-subtle); border: 1px solid var(--color-border); border-radius: 0.5rem; padding: 1.25rem; margin-bottom: 2rem;">
            <div style="margin-bottom: 0.75rem;">
                <span style="font-size: 0.8125rem; color: var(--color-text-muted); display: block;">Stanowisko:</span>
                <strong style="font-size: 1.1rem; color: var(--color-primary);">{{ $application->jobOffer->title }}</strong>
            </div>
            <div style="margin-bottom: 0.75rem;">
                <span style="font-size: 0.8125rem; color: var(--color-text-muted); display: block;">Numer referencyjny:</span>
                <strong style="font-family: monospace; font-size: 1.05rem;">{{ $application->reference_code }}</strong>
            </div>
            <div>
                <span style="font-size: 0.8125rem; color: var(--color-text-muted); display: block;">Kandydat:</span>
                <span>{{ $application->full_name }} ({{ $application->email }})</span>
            </div>
        </div>

        <form action="{{ route('applications.cancel.process', ['token' => $token]) }}" method="POST">
            @csrf
            
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <button type="submit" class="btn btn-danger" style="width: 100%; font-size: 1.05rem; padding: 0.75rem 1.25rem;">
                    Tak, anuluj moje zgłoszenie
                </button>

                <a href="{{ route('public.offers.show', $application->jobOffer->slug) }}" class="btn btn-secondary" style="width: 100%; text-align: center;">
                    Nie, wróć do ogłoszenia
                </a>
            </div>
        </form>

        <p style="font-size: 0.8125rem; color: var(--color-text-muted); text-align: center; margin-top: 1.5rem; margin-bottom: 0;">
            Po anulowaniu zgłoszenia wyślemy potwierdzenie na Twój adres e-mail.
        </p>
    </div>
</div>
@endsection
