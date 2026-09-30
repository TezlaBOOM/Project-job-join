@extends('layouts.admin')

@section('title', 'Zgłoszenia kandydatów')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Zgłoszenia kandydatów</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">Przeglądaj, oceniaj i zarządzaj aplikacjami złożonymi w naborach</p>
    </div>

    <div style="display: flex; gap: 0.5rem;">
        <a href="{{ route('admin.applications.export-csv', request()->query()) }}" class="btn btn-secondary">
            📥 Eksportuj CSV
        </a>
        @if(auth()->user()->isRecruiter())
            <a href="{{ route('admin.applications.create-manual') }}" class="btn btn-primary">
                + Wprowadź zgłoszenie ręcznie
            </a>
        @endif
    </div>
</div>

<!-- Filtry -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('admin.applications.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 2; min-width: 220px;">
            <label for="q" class="form-label">Szukaj (kandydat, e-mail, kod):</label>
            <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Imię, nazwisko, kod referencyjny...">
        </div>

        <div style="flex: 2; min-width: 240px;">
            <label for="offer_id" class="form-label">Oferta naboru:</label>
            <select id="offer_id" name="offer_id" class="form-control">
                <option value="">Wszystkie oferty</option>
                @foreach($offers as $o)
                    <option value="{{ $o->id }}" {{ request('offer_id') == $o->id ? 'selected' : '' }}>
                        {{ $o->title }} ({{ $o->reference_code ?? $o->public_id }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="flex: 1; min-width: 160px;">
            <label for="status" class="form-label">Status:</label>
            <select id="status" name="status" class="form-control">
                <option value="">Wszystkie statusy</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Nowe</option>
                <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>W trakcie oceny</option>
                <option value="interview" {{ request('status') === 'interview' ? 'selected' : '' }}>Zaproszony na rozmowę</option>
                <option value="qualified" {{ request('status') === 'qualified' ? 'selected' : '' }}>Zakwalifikowany</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Odrzucone</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Anulowane</option>
            </select>
        </div>

        <div>
            <button type="submit" class="btn btn-secondary">Filtruj</button>
        </div>
    </form>
</div>

<!-- Tabela zgłoszeń z akcjami masowymi -->
<form action="{{ route('admin.applications.bulk-status') }}" method="POST" id="bulk-form">
    @csrf

    <div class="card" style="padding: 0;">
        <div style="padding: 0.75rem 1rem; background-color: var(--color-surface-subtle); border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="font-weight: 600; font-size: 0.9rem;">
                Łącznie zgłoszeń: <strong>{{ $applications->total() }}</strong>
            </div>

            @if(auth()->user()->isRecruiter())
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <label for="bulk-status" style="font-size: 0.8125rem; font-weight: 600;">Zmień status zaznaczonych:</label>
                    <select id="bulk-status" name="status" class="form-control" style="width: auto; min-height: 34px; padding: 0.2rem 0.5rem; font-size: 0.8125rem;">
                        <option value="new">Nowe</option>
                        <option value="under_review">W trakcie oceny</option>
                        <option value="interview">Zaproszony na rozmowę</option>
                        <option value="qualified">Zakwalifikowany</option>
                        <option value="rejected">Odrzucone</option>
                    </select>
                    <button type="submit" class="btn btn-secondary" style="min-height: 34px; padding: 0.2rem 0.6rem; font-size: 0.8125rem;" onclick="return confirm('Czy na pewno chcesz zmienić status zaznaczonych zgłoszeń?');">
                        Zastosuj
                    </button>
                </div>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="select-all" aria-label="Zaznacz wszystkie zgłoszenia" style="width: 18px; height: 18px;">
                        </th>
                        <th>Kod referencyjny</th>
                        <th>Kandydat</th>
                        <th>Stanowisko</th>
                        <th>Status</th>
                        <th>Załączniki</th>
                        <th>Data</th>
                        <th style="text-align: right;">Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" name="application_ids[]" value="{{ $app->id }}" class="app-checkbox" aria-label="Zaznacz zgłoszenie {{ $app->reference_code }}" style="width: 18px; height: 18px;">
                            </td>
                            <td>
                                <a href="{{ route('admin.applications.show', $app->public_id) }}" style="font-weight: bold; font-family: monospace;">
                                    {{ $app->reference_code }}
                                </a>
                                @if($app->source === 'manual')
                                    <span class="badge" style="font-size: 0.7rem; background: #e0e7ff; color: #3730a3;">Ręczne</span>
                                @endif
                            </td>
                            <td>
                                <div><strong>{{ $app->full_name }}</strong></div>
                                <div style="font-size: 0.8125rem; color: var(--color-text-muted);">{{ $app->email }}</div>
                            </td>
                            <td>{{ Str::limit($app->jobOffer->title, 30) }}</td>
                            <td>
                                @if($app->status === 'new')
                                    <span class="badge" style="background: #e0f2fe; color: #0369a1;">Nowe</span>
                                @elseif($app->status === 'under_review')
                                    <span class="badge" style="background: #fef3c7; color: #92400e;">W trakcie oceny</span>
                                @elseif($app->status === 'interview')
                                    <span class="badge" style="background: #f3e8ff; color: #6b21a8;">Rozmowa</span>
                                @elseif($app->status === 'qualified')
                                    <span class="badge" style="background: #dcfce7; color: #166534;">Zakwalifikowany</span>
                                @elseif($app->status === 'rejected')
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">Odrzucone</span>
                                @else
                                    <span class="badge" style="background: #f3f4f6; color: #4b5563;">Anulowane</span>
                                @endif
                            </td>
                            <td>
                                @if($app->files->isNotEmpty())
                                    <span title="{{ $app->files->pluck('original_name')->implode(', ') }}">
                                        📎 {{ $app->files->count() }} PDF
                                    </span>
                                @else
                                    <span style="color: var(--color-text-muted); font-size: 0.8125rem;">Brak</span>
                                @endif
                            </td>
                            <td style="font-size: 0.8125rem;">
                                {{ $app->created_at->format('d.m.Y H:i') }}
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.applications.show', $app->public_id) }}" class="btn btn-secondary" style="min-height: 32px; padding: 0.2rem 0.6rem; font-size: 0.8125rem;">
                                    Szczegóły &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                                Brak zgłoszeń odpowiadających kryteriom.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</form>

<div style="margin-top: 1.5rem;">
    {{ $applications->links() }}
</div>

<script>
    document.getElementById('select-all')?.addEventListener('change', function(e) {
        var checkboxes = document.querySelectorAll('.app-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = e.target.checked;
        });
    });
</script>
@endsection
