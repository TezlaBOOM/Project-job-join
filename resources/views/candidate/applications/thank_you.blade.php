@extends('layouts.app')

@section('title', 'Zgłoszenie zostało przyjęte')

@section('content')
<div style="max-width: 600px; margin: 3rem auto; text-align: center;">
    <div class="card" style="padding: 3rem 2rem;">
        <div style="width: 72px; height: 72px; background-color: var(--color-success-bg); border: 2px solid var(--color-success-border); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto; font-size: 2.5rem; color: var(--color-success);" aria-hidden="true">
            ✓
        </div>

        <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--color-primary); margin-bottom: 0.75rem;">
            Dziękujemy! Zgłoszenie zostało przyjęte
        </h1>

        <p style="font-size: 1.05rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 1.5rem;">
            Twoja aplikacja została pomyślnie zarejestrowana w systemie rekrutacyjnym Urzędu Miasta.
        </p>

        <div style="background-color: var(--color-surface-subtle); border: 1px dashed var(--color-border); border-radius: 0.5rem; padding: 1.25rem; margin-bottom: 2rem;">
            <div style="font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">Twój numer referencyjny:</div>
            <div style="font-size: 1.5rem; font-weight: 800; letter-spacing: 1px; color: var(--color-primary); font-family: monospace;">
                {{ $ref }}
            </div>
        </div>

        <div style="text-align: left; background-color: var(--color-surface-subtle); border-radius: 0.5rem; padding: 1.25rem; margin-bottom: 2rem; font-size: 0.9375rem; line-height: 1.6;">
            <h2 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem;">Co dalej?</h2>
            <ul style="padding-left: 1.25rem; margin: 0;">
                <li>Wysłaliśmy na Twój adres e-mail potwierdzenie przyjęcia dokumentów.</li>
                <li>W e-mailu znajduje się bezpieczny link umożliwiający wycofanie zgłoszenia, jeśli zdecydujesz się zrezygnować.</li>
                <li>Po zakończeniu naboru komisja dokona oceny formalnej i merytorycznej złożonych dokumentów.</li>
            </ul>
        </div>

        <div>
            <a href="{{ route('public.offers.index') }}" class="btn btn-primary" style="font-size: 1rem; padding: 0.625rem 1.75rem;">
                Wróć do listy ofert pracy
            </a>
        </div>
    </div>
</div>
@endsection
