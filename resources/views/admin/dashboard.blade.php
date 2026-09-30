@extends('layouts.admin')

@section('title', 'Pulpit główny')

@section('content')
<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">
        Pulpit rekrutacyjny
    </h1>
    <p style="color: var(--color-text-muted);">
        Przegląd aktywnych naborów i zgłoszeń kandydatów
    </p>
</div>

<!-- Statystyki -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="border-left: 4px solid var(--color-primary);">
        <div style="font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">Aktywne nabory</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--color-primary);">{{ $activeOffersCount }}</div>
    </div>

    <div class="card" style="border-left: 4px solid var(--color-accent);">
        <div style="font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">Nowe zgłoszenia</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--color-accent);">{{ $newApplicationsCount }}</div>
    </div>

    <div class="card" style="border-left: 4px solid var(--color-danger);">
        <div style="font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">Kończące się &le; 7 dni</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--color-danger);">{{ $endingSoonOffersCount }}</div>
    </div>

    <div class="card" style="border-left: 4px solid var(--color-success);">
        <div style="font-size: 0.875rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">Wszystkie zgłoszenia</div>
        <div style="font-size: 2rem; font-weight: 800; color: var(--color-success);">{{ $totalApplicationsCount }}</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Ostatnie zgłoszenia -->
    <section class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            <h2 style="font-size: 1.15rem; font-weight: 700;">Najnowsze zgłoszenia</h2>
            <a href="{{ route('admin.applications.index') }}" style="font-size: 0.875rem; font-weight: 600;">Wszystkie &rarr;</a>
        </div>

        @if($recentApplications->isEmpty())
            <p style="color: var(--color-text-muted); padding: 1rem 0;">Brak zarejestrowanych zgłoszeń.</p>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kod</th>
                            <th>Kandydat</th>
                            <th>Stanowisko</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentApplications as $app)
                            <tr>
                                <td><code style="font-weight: bold;">{{ $app->reference_code }}</code></td>
                                <td>{{ $app->full_name }}</td>
                                <td>{{ Str::limit($app->jobOffer->title, 25) }}</td>
                                <td>
                                    <span class="badge" style="font-size: 0.75rem;">{{ $app->status_label }}</span>
                                </td>
                                <td style="font-size: 0.8125rem;">{{ $app->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.applications.show', $app->public_id) }}" class="btn btn-secondary" style="min-height: 32px; padding: 0.2rem 0.5rem; font-size: 0.8rem;">
                                        Szczegóły
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <!-- Oferty kończące się wkrótce -->
    <section class="card">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            ⏰ Kończące się nabory
        </h2>

        @if($endingSoonOffers->isEmpty())
            <p style="color: var(--color-text-muted); font-size: 0.9rem;">Brak naborów z bliskim terminem zakończenia.</p>
        @else
            <ul style="list-style: none; padding: 0;">
                @foreach($endingSoonOffers as $offer)
                    <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--color-border-subtle);">
                        <a href="{{ route('admin.offers.edit', $offer->public_id) }}" style="font-weight: 600; text-decoration: none; color: var(--color-text);">
                            {{ $offer->title }}
                        </a>
                        <div style="font-size: 0.8125rem; color: var(--color-danger); font-weight: bold; margin-top: 0.25rem;">
                            Termin: {{ $offer->deadline_at->format('d.m.Y H:i') }} (za {{ $offer->deadline_at->diffForHumans() }})
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</div>
@endsection
