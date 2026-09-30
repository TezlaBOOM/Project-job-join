@extends('layouts.app')

@section('title', $offer->title)
@section('meta_description', "Nabór na stanowisko: {$offer->title} w Urzędzie Miasta. Sprawdź wymagania i złóż aplikację online.")

@section('content')
<div style="margin-bottom: 1.75rem;">
    <a href="{{ route('public.offers.index') }}" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
        <span>&larr;</span> Wróć do listy ogłoszeń
    </a>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; align-items: start;">
    <!-- Główna kolumna: Szczegóły oferty -->
    <div style="display: grid; gap: 1.75rem;">
        <!-- Nagłówek ogłoszenia -->
        <article class="card" style="padding: 2.25rem;">
            <div style="display: flex; gap: 0.625rem; flex-wrap: wrap; margin-bottom: 1rem;">
                @if($offer->category)
                    <span class="badge badge-primary">🏷️ {{ $offer->category->name }}</span>
                @endif
                @if($offer->isOpen())
                    <span class="badge badge-success">● Rekrutacja otwarta</span>
                @else
                    <span class="badge badge-danger">● Nabór zakończony</span>
                @endif
            </div>

            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--color-text); margin-bottom: 1.25rem; line-height: 1.2;">
                {{ $offer->title }}
            </h1>

            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                @if($offer->department)
                    <span class="badge" style="font-size: 0.875rem;">🏢 {{ $offer->department->name }}</span>
                @endif
                @if($offer->location)
                    <span class="badge" style="font-size: 0.875rem;">📍 {{ $offer->location->name }}</span>
                @endif
                @if($offer->contractType)
                    <span class="badge" style="font-size: 0.875rem;">📄 {{ $offer->contractType->name }}</span>
                @endif
                <span class="badge" style="font-size: 0.875rem;">⏱️ {{ $offer->working_time }}</span>
            </div>
        </article>

        <!-- Zakres zadań -->
        <section class="card" style="padding: 2rem;">
            <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-text); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem;">
                <span>📋</span> Zakres wykonywanych zadań
            </h2>
            <div style="line-height: 1.75; font-size: 1.05rem; color: var(--color-text);">
                {!! $offer->description !!}
            </div>
        </section>

        <!-- Wymagania niezbędne -->
        @if($offer->requirements)
            <section class="card" style="padding: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-text); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem;">
                    <span>🎓</span> Wymagania niezbędne (formalne)
                </h2>
                <div style="line-height: 1.75; font-size: 1.05rem; white-space: pre-line; color: var(--color-text);">
                    {{ $offer->requirements }}
                </div>
            </section>
        @endif

        <!-- Wymagania dodatkowe -->
        @if($offer->nice_to_have)
            <section class="card" style="padding: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-text); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem;">
                    <span>⭐</span> Wymagania dodatkowe
                </h2>
                <div style="line-height: 1.75; font-size: 1.05rem; white-space: pre-line; color: var(--color-text);">
                    {{ $offer->nice_to_have }}
                </div>
            </section>
        @endif

        <!-- Warunki pracy i oferowane świadczenia -->
        @if($offer->offer_text)
            <section class="card" style="padding: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-text); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem;">
                    <span>💼</span> Warunki pracy i oferowane świadczenia
                </h2>
                <div style="line-height: 1.75; font-size: 1.05rem; white-space: pre-line; color: var(--color-text);">
                    {{ $offer->offer_text }}
                </div>
            </section>
        @endif

        <!-- Wymagane dokumenty -->
        @if($offer->required_documents)
            <section class="card" style="padding: 2rem;">
                <h2 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-text); display: flex; align-items: center; gap: 0.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem;">
                    <span>📎</span> Wymagane dokumenty i oświadczenia
                </h2>
                <div style="line-height: 1.75; font-size: 1.05rem; white-space: pre-line; color: var(--color-text);">
                    {{ $offer->required_documents }}
                </div>
                <div class="alert alert-info" style="margin-top: 1.5rem; margin-bottom: 0;">
                    ℹ️ Do formularza online należy dołączyć dokumenty <strong>wyłącznie w formacie PDF</strong> (maksymalny rozmiar pojedynczego pliku to 10 MB).
                </div>
            </section>
        @endif

        <!-- Informacja RODO -->
        <section class="card" style="background-color: var(--color-surface-subtle); padding: 1.75rem;">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.625rem; color: var(--color-text);">
                Informacja o przetwarzaniu danych osobowych (RODO)
            </h2>
            <p style="font-size: 0.9rem; color: var(--color-text-muted); line-height: 1.6;">
                Administratorem danych osobowych przetwarzanych w procesie naboru jest {{ \App\Models\Setting::get('office_name', 'Urząd Miasta') }}.
                Dane przetwarzane są w celu przeprowadzenia postępowania rekrutacyjnego zgodnie z przepisami ustawy o pracownikach samorządowych oraz Kodeksu pracy.
                Szczegółowe informacje dotyczące przetwarzania danych dostępne są w <a href="{{ route('public.privacy') }}">Polityce prywatności</a>.
            </p>
        </section>
    </div>

    <!-- Prawa kolumna (Boczny panel z terminem i przyciskiem CTA) -->
    <aside style="position: sticky; top: 100px;">
        <div class="card" style="padding: 2rem; border-top: 4px solid var(--color-primary); box-shadow: var(--shadow-lg);">
            <div style="font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                Termin składania dokumentów
            </div>
            
            <div style="font-size: 1.75rem; font-weight: 800; color: {{ $offer->isOpen() ? 'var(--color-primary)' : 'var(--color-danger)' }}; margin-bottom: 0.25rem;">
                {{ $offer->deadline_at->format('d.m.Y') }}
            </div>
            <div style="font-size: 0.95rem; color: var(--color-text-muted); margin-bottom: 1.5rem;">
                do godz. <strong>{{ $offer->deadline_at->format('H:i') }}</strong>
            </div>

            @if($offer->isOpen())
                <a href="{{ route('public.applications.create', $offer->slug) }}" class="btn btn-primary btn-lg" style="width: 100%; font-size: 1.1rem; text-decoration: none; margin-bottom: 1.25rem;">
                    Złóż aplikację teraz &rarr;
                </a>
            @else
                <div class="alert alert-danger" style="margin-bottom: 1.25rem; font-weight: 700;">
                    Termin składania ofert minął
                </div>
            @endif

            <div style="border-top: 1px solid var(--color-border-subtle); padding-top: 1.25rem; font-size: 0.875rem; color: var(--color-text-muted); display: grid; gap: 0.75rem;">
                <div style="display: flex; justify-content: space-between;">
                    <span>Data publikacji:</span>
                    <strong>{{ $offer->published_at ? $offer->published_at->format('d.m.Y') : $offer->created_at->format('d.m.Y') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Forma aplikacji:</span>
                    <strong>Formularz online (PDF)</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>Wymóg logowania:</span>
                    <strong>Brak</strong>
                </div>
            </div>
        </div>
    </aside>
</div>

<!-- Dane strukturalne Schema.org JobPosting -->
<script type="application/ld+json">
{
  "@@context": "https://schema.org/",
  "@type": "JobPosting",
  "title": "{{ addslashes($offer->title) }}",
  "description": "{{ addslashes(strip_tags($offer->description)) }}",
  "datePosted": "{{ $offer->published_at ? $offer->published_at->toIso8601String() : $offer->created_at->toIso8601String() }}",
  "validThrough": "{{ $offer->deadline_at->toIso8601String() }}",
  "employmentType": "FULL_TIME",
  "hiringOrganization": {
    "@type": "GovernmentOrganization",
    "name": "{{ addslashes(\App\Models\Setting::get('office_name', 'Urząd Miasta')) }}"
  },
  "jobLocation": {
    "@type": "Place",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "{{ addslashes($offer->location ? $offer->location->name : 'Polska') }}"
    }
  }
}
</script>
@endsection
