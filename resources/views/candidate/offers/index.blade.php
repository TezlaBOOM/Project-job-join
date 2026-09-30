@extends('layouts.app')

@section('title', 'Aktualne nabory i oferty pracy')
@section('meta_description', 'Przeglądaj aktualne oferty pracy i nabory na stanowiska urzędnicze w Urzędzie Miasta. Złóż aplikację online bez logowania.')

@section('content')
<!-- Hero Section -->
<header class="hero-banner">
    <div class="hero-pill">
        <span>🏛️</span> Nabory Samorządowe
    </div>
    <h1 class="hero-title">
        Rozwijaj karierę w służbie mieszkańcom
    </h1>
    <p class="hero-subtitle">
        {{ \App\Models\Setting::get('home_intro', 'Oficjalny portal naborów Urzędu Miasta. Oferujemy stabilne warunki pracy, przejrzyste kryteria naboru oraz wygodną aplikację online bez konieczności zakładania konta.') }}
    </p>

    <!-- Pasek atrybutów jakości i dostępności -->
    <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; margin-top: 1.75rem; font-size: 0.875rem; color: var(--color-text-muted); font-weight: 600;">
        <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
            <span style="color: var(--color-success);" aria-hidden="true">✓</span> Bezpieczna aplikacja PDF
        </div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
            <span style="color: var(--color-primary);" aria-hidden="true">✓</span> Standard dostępności WCAG 2.1 AA
        </div>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
            <span style="color: var(--color-accent);" aria-hidden="true">✓</span> Bez zakładania konta
        </div>
    </div>
</header>

<div style="display: grid; grid-template-columns: 1fr; gap: 2.25rem; align-items: start;">
    <!-- Formularz filtrów (działa całkowicie w oparciu o GET bez wymogu JavaScriptu) -->
    <section aria-labelledby="filters-heading" class="card" style="padding: 1.75rem;">
        <div class="card-header-styled">
            <h2 id="filters-heading" style="font-size: 1.25rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                <span>🔎</span> Filtruj ogłoszenia o pracę
            </h2>
            @if(request()->hasAny(['q', 'department', 'contract_type', 'category', 'location', 'working_time', 'ending_soon', 'sort']))
                <a href="{{ route('public.offers.index') }}" class="btn btn-secondary btn-sm">
                    ✕ Wyczyść filtry
                </a>
            @endif
        </div>

        <form action="{{ route('public.offers.index') }}" method="GET" role="search">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
                <!-- Szukaj tekstu -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-q" class="form-label">Słowo kluczowe / Stanowisko</label>
                    <input type="text" id="filter-q" name="q" value="{{ request('q') }}" class="form-control" placeholder="np. Informatyk, Inspektor, Referent...">
                </div>

                <!-- Wydział -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-department" class="form-label">Wydział / Referat</label>
                    <select id="filter-department" name="department" class="form-control">
                        <option value="">Wszystkie wydziały</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Rodzaj umowy -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-contract" class="form-label">Forma zatrudnienia</label>
                    <select id="filter-contract" name="contract_type" class="form-control">
                        <option value="">Wszystkie formy</option>
                        @foreach($contractTypes as $ct)
                            <option value="{{ $ct->id }}" {{ request('contract_type') == $ct->id ? 'selected' : '' }}>
                                {{ $ct->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Kategoria -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-category" class="form-label">Kategoria stanowiska</label>
                    <select id="filter-category" name="category" class="form-control">
                        <option value="">Wszystkie kategorie</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Lokalizacja -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-location" class="form-label">Miejsce pracy</label>
                    <select id="filter-location" name="location" class="form-control">
                        <option value="">Wszystkie lokalizacje</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Wymiar etatu -->
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="filter-working-time" class="form-label">Wymiar etatu</label>
                    <select id="filter-working-time" name="working_time" class="form-control">
                        <option value="">Dowolny wymiar</option>
                        @foreach($workingTimes as $wt)
                            <option value="{{ $wt }}" {{ request('working_time') == $wt ? 'selected' : '' }}>
                                {{ $wt }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.25rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-subtle);">
                <div style="display: flex; align-items: center; gap: 1.75rem; flex-wrap: wrap;">
                    <label style="display: inline-flex; align-items: center; gap: 0.6rem; cursor: pointer; font-weight: 600; font-size: 0.9375rem;">
                        <input type="checkbox" name="ending_soon" value="1" {{ request('ending_soon') ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: var(--color-primary); cursor: pointer;">
                        <span>🔥 Tylko kończące się w ciągu 7 dni</span>
                    </label>

                    <div style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <label for="filter-sort" style="font-size: 0.875rem; font-weight: 600; color: var(--color-text-muted);">Sortowanie:</label>
                        <select id="filter-sort" name="sort" class="form-control" style="width: auto; min-height: 40px; padding: 0.35rem 2rem 0.35rem 0.85rem; font-size: 0.875rem;">
                            <option value="newest" {{ request('sort', 'newest') == 'newest' ? 'selected' : '' }}>Najnowsze ogłoszenia</option>
                            <option value="ending_soonest" {{ request('sort') == 'ending_soonest' ? 'selected' : '' }}>Termin kończący się najszybciej</option>
                        </select>
                    </div>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.75rem;">
                        <span>🔍</span> Zastosuj filtry
                    </button>
                </div>
            </div>
        </form>
    </section>

    <!-- Wyniki wyszukiwania i lista ofert -->
    <section aria-labelledby="offers-list-heading">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="offers-list-heading" style="font-size: 1.5rem; font-weight: 800; color: var(--color-text);">
                Aktualne nabory
            </h2>
            <div class="badge badge-primary" style="font-size: 0.875rem; padding: 0.4rem 0.85rem;">
                Dostępnych ogłoszeń: <strong style="margin-left: 0.25rem;">{{ $offers->total() }}</strong>
            </div>
        </div>

        @if($offers->isEmpty())
            <div class="card" style="text-align: center; padding: 4rem 2rem;">
                <div style="font-size: 3.5rem; margin-bottom: 1.25rem;" aria-hidden="true">📂</div>
                <h3 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-text);">
                    Brak ofert spełniających podane kryteria
                </h3>
                <p style="color: var(--color-text-muted); max-width: 540px; margin: 0 auto 2rem auto; line-height: 1.6;">
                    Nie znaleźliśmy ogłoszeń odpowiadających wybranym parametrom wyszukiwania. Spróbuj zmienić słowo kluczowe lub zresetować filtry.
                </p>
                <a href="{{ route('public.offers.index') }}" class="btn btn-secondary">
                    Wyczyść wszystkie filtry
                </a>
            </div>
        @else
            <div class="offers-grid">
                @foreach($offers as $offer)
                    @php
                        $daysLeft = now()->diffInDays($offer->deadline_at, false);
                        $isEndingSoon = $daysLeft >= 0 && $daysLeft <= 7;
                    @endphp
                    <article class="offer-card {{ $isEndingSoon ? 'is-ending-soon' : '' }}">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
                            <div style="flex: 1; min-width: 280px;">
                                <h3 class="offer-card-title">
                                    <a href="{{ route('public.offers.show', $offer->slug) }}">
                                        {{ $offer->title }}
                                    </a>
                                </h3>

                                <div class="offer-meta-list">
                                    @if($offer->department)
                                        <span class="badge" title="Wydział">
                                            🏢 {{ $offer->department->name }}
                                        </span>
                                    @endif
                                    @if($offer->location)
                                        <span class="badge" title="Lokalizacja">
                                            📍 {{ $offer->location->name }}
                                        </span>
                                    @endif
                                    @if($offer->contractType)
                                        <span class="badge" title="Rodzaj umowy">
                                            📄 {{ $offer->contractType->name }}
                                        </span>
                                    @endif
                                    <span class="badge" title="Wymiar etatu">
                                        ⏱️ {{ $offer->working_time }}
                                    </span>
                                </div>
                            </div>

                            <div style="text-align: right; min-width: 200px;">
                                @if($isEndingSoon)
                                    <div style="margin-bottom: 0.5rem;">
                                        <span class="badge badge-urgent">
                                            🔥 Kończy się wkrótce!
                                        </span>
                                    </div>
                                @endif
                                <div style="font-size: 0.8125rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">
                                    Termin składania ofert:
                                </div>
                                <div style="font-size: 1.15rem; font-weight: 800; color: {{ $isEndingSoon ? 'var(--color-warning)' : 'var(--color-primary)' }};">
                                    {{ $offer->deadline_at->format('d.m.Y') }}
                                </div>
                                <div style="font-size: 0.8125rem; color: var(--color-text-muted);">
                                    do godz. {{ $offer->deadline_at->format('H:i') }}
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--color-border-subtle); padding-top: 1.125rem; margin-top: 0.75rem; flex-wrap: wrap; gap: 0.75rem;">
                            <span style="font-size: 0.85rem; color: var(--color-text-light);">
                                Data publikacji: <strong>{{ $offer->published_at ? $offer->published_at->format('d.m.Y') : $offer->created_at->format('d.m.Y') }}</strong>
                            </span>

                            <a href="{{ route('public.offers.show', $offer->slug) }}" class="btn btn-primary btn-sm" style="padding: 0.5rem 1.25rem; font-size: 0.9375rem;">
                                Zobacz szczegóły naboru &rarr;
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Paginacja -->
            <div style="margin-top: 2.5rem;" aria-label="Nawigacja po stronach wyników">
                {{ $offers->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
