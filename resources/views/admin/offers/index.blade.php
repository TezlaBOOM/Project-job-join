@extends('layouts.admin')

@section('title', 'Zarządzanie naborami')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Nabory i oferty pracy</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">Zarządzaj ogłoszeniami rekrutacyjnymi Urzędu Miasta</p>
    </div>

    @if(auth()->user()->isRecruiter())
        <div>
            <a href="{{ route('admin.offers.create') }}" class="btn btn-primary">
                + Dodaj nowe ogłoszenie
            </a>
        </div>
    @endif
</div>

<!-- Filtry listy ofert -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('admin.offers.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 2; min-width: 200px;">
            <label for="search" class="form-label">Szukaj po tytule:</label>
            <input type="text" id="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="Tytuł stanowiska...">
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label for="status" class="form-label">Status:</label>
            <select id="status" name="status" class="form-control">
                <option value="">Wszystkie statusy</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Szkic</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Opublikowana</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Zakończona</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Zarchiwizowana</option>
            </select>
        </div>

        <div style="flex: 1; min-width: 180px;">
            <label for="department" class="form-label">Wydział:</label>
            <select id="department" name="department" class="form-control">
                <option value="">Wszystkie wydziały</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-secondary">Filtruj</button>
        </div>
    </form>
</div>

<!-- Tabela ofert -->
<div class="card" style="padding: 0;">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Stanowisko</th>
                    <th>Wydział</th>
                    <th>Termin składania</th>
                    <th>Status</th>
                    <th>Zgłoszenia</th>
                    <th style="text-align: right;">Akcje</th>
                </tr>
            </thead>
            <tbody>
                @forelse($offers as $offer)
                    <tr>
                        <td>
                            <strong>{{ $offer->title }}</strong>
                            <div style="font-size: 0.8125rem; color: var(--color-text-muted);">
                                ID: <code>{{ $offer->public_id }}</code> | Slug: {{ $offer->slug }}
                            </div>
                        </td>
                        <td>{{ $offer->department?->name ?? '—' }}</td>
                        <td>
                            <div>{{ $offer->deadline_at->format('d.m.Y H:i') }}</div>
                            @if($offer->isOpen())
                                <span style="font-size: 0.75rem; color: var(--color-success); font-weight: bold;">Aktywny</span>
                            @else
                                <span style="font-size: 0.75rem; color: var(--color-danger);">Zakończony</span>
                            @endif
                        </td>
                        <td>
                            @if($offer->status === 'published')
                                <span class="badge" style="background: #dcfce7; color: #166534;">Opublikowana</span>
                            @elseif($offer->status === 'draft')
                                <span class="badge" style="background: #f3f4f6; color: #4b5563;">Szkic</span>
                            @elseif($offer->status === 'completed')
                                <span class="badge" style="background: #fef3c7; color: #92400e;">Zakończona</span>
                            @else
                                <span class="badge" style="background: #fee2e2; color: #991b1b;">Zarchiwizowana</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.applications.index', ['offer_id' => $offer->id]) }}" style="font-weight: bold; text-decoration: none;">
                                👥 {{ $offer->applications_count }}
                            </a>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 0.35rem; align-items: center;">
                                <a href="{{ route('public.offers.show', $offer->slug) }}" target="_blank" class="btn btn-secondary" style="min-height: 32px; padding: 0.2rem 0.5rem; font-size: 0.8rem;" title="Podgląd publiczny">
                                    Podgląd
                                </a>

                                @if(auth()->user()->isRecruiter())
                                    <a href="{{ route('admin.offers.edit', $offer->public_id) }}" class="btn btn-secondary" style="min-height: 32px; padding: 0.2rem 0.5rem; font-size: 0.8rem;">
                                        Edytuj
                                    </a>

                                    <form action="{{ route('admin.offers.duplicate', $offer->public_id) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="min-height: 32px; padding: 0.2rem 0.5rem; font-size: 0.8rem;" title="Utwórz kopię">
                                            Kopiuj
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.offers.destroy', $offer->public_id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Czy na pewno chcesz usunąć ten nabór?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="min-height: 32px; padding: 0.2rem 0.5rem; font-size: 0.8rem;">
                                            Usuń
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                            Nie znaleziono żadnych ogłoszeń w systemie.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1.5rem;">
    {{ $offers->links() }}
</div>
@endsection
