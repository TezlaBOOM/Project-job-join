@extends('layouts.admin')

@section('title', 'Konfigurator filtrów strony głównej')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Konfigurator filtrów naborów</h1>
    <p style="color: var(--color-text-muted); font-size: 0.95rem;">Zarządzaj zestawem filtrów widocznych dla kandydatów na stronie głównej portalu</p>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Lista filtrów z możliwością zmiany kolejności i edycji -->
    <div class="card" style="padding: 0;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">Filtry na stronie głównej</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Kol.</th>
                        <th>Etykieta</th>
                        <th>Klucz / Źródło</th>
                        <th>Kontrolka</th>
                        <th>Widoczny</th>
                        <th style="text-align: right;">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($filters as $filter)
                        <tr>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <form action="{{ route('admin.filters.move', ['id' => $filter->id, 'direction' => 'up']) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="min-height: 28px; padding: 0 0.35rem; font-size: 0.75rem;" title="Przesuń w górę">▲</button>
                                    </form>
                                    <form action="{{ route('admin.filters.move', ['id' => $filter->id, 'direction' => 'down']) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="min-height: 28px; padding: 0 0.35rem; font-size: 0.75rem;" title="Przesuń w dół">▼</button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $filter->label }}</strong>
                            </td>
                            <td>
                                <code>{{ $filter->key }}</code>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ $filter->source }}: {{ $filter->source_ref }}</div>
                            </td>
                            <td>{{ $filter->control_type }}</td>
                            <td>
                                @if($filter->is_active)
                                    <span class="badge" style="background: #dcfce7; color: #166534;">Aktywny</span>
                                @else
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">Ukryty</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <form action="{{ route('admin.filters.toggle', $filter->id) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary" style="min-height: 30px; padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                        {{ $filter->is_active ? 'Ukryj' : 'Włącz' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                                Brak zdefiniowanych filtrów.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dodaj nowy filtr -->
    <div class="card">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            + Dodaj nowy filtr
        </h2>

        <form action="{{ route('admin.filters.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="filter_label" class="form-label">Etykieta widoczna dla kandydata <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="filter_label" name="label" required class="form-control" placeholder="np. Jednostka organizacyjna">
            </div>

            <div class="form-group">
                <label for="filter_key" class="form-label">Klucz w adresie URL <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="filter_key" name="key" required class="form-control" placeholder="np. department">
            </div>

            <div class="form-group">
                <label for="filter_source" class="form-label">Typ źródła danych <span style="color: var(--color-danger);">*</span></label>
                <select id="filter_source" name="source" class="form-control" required>
                    <option value="dictionary">Słownik systemowy</option>
                    <option value="field">Pole ogłoszenia</option>
                </select>
            </div>

            <div class="form-group">
                <label for="filter_source_ref" class="form-label">Źródło referencyjne <span style="color: var(--color-danger);">*</span></label>
                <select id="filter_source_ref" name="source_ref" class="form-control" required>
                    <option value="department">Wydział (departments)</option>
                    <option value="contract_type">Rodzaj umowy (contract_types)</option>
                    <option value="job_category">Kategoria (job_categories)</option>
                    <option value="location">Lokalizacja (locations)</option>
                    <option value="working_time">Wymiar etatu (working_time)</option>
                    <option value="search">Wyszukiwanie tekstowe</option>
                    <option value="deadline_at">Termin składania</option>
                </select>
            </div>

            <div class="form-group">
                <label for="filter_control_type" class="form-label">Typ kontrolki <span style="color: var(--color-danger);">*</span></label>
                <select id="filter_control_type" name="control_type" class="form-control" required>
                    <option value="select">Lista rozwijana (Select)</option>
                    <option value="checkbox">Pole wyboru (Checkbox)</option>
                    <option value="text">Wyszukiwarka tekstowa</option>
                    <option value="date_range">Zakres dat</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Dodaj filtr
            </button>
        </form>
    </div>
</div>
@endsection
