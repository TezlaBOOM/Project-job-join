@extends('layouts.admin')

@section('title', "Pola formularza: {$form->name}")

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('admin.forms.index') }}" style="font-weight: 600; text-decoration: none;">
        &larr; Wróć do listy szablonów
    </a>
</div>

<div style="margin-bottom: 2rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">
        Pola formularza: {{ $form->name }}
    </h1>
    <p style="color: var(--color-text-muted); font-size: 0.95rem;">
        Konfiguracja pól widocznych w formularzu aplikacyjnym. Pola systemowe (oznaczone gwiazdką) są chronione.
    </p>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Lista pól formularza -->
    <div class="card" style="padding: 0;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">Zdefiniowane pola ({{ $form->fields->count() }})</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Kol.</th>
                        <th>Etykieta</th>
                        <th>Klucz / Typ</th>
                        <th>Wymagane</th>
                        <th>Status</th>
                        <th style="text-align: right;">Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($form->fields as $field)
                        <tr>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <form action="{{ route('admin.forms.move-field', ['id' => $field->id, 'direction' => 'up']) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="min-height: 28px; padding: 0 0.35rem; font-size: 0.75rem;">▲</button>
                                    </form>
                                    <form action="{{ route('admin.forms.move-field', ['id' => $field->id, 'direction' => 'down']) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="min-height: 28px; padding: 0 0.35rem; font-size: 0.75rem;">▼</button>
                                    </form>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $field->label }}</strong>
                                @if($field->is_system)
                                    <span class="badge" style="font-size: 0.7rem; background: #e0e7ff; color: #3730a3;">Systemowe</span>
                                @endif
                                @if($field->help_text)
                                    <div style="font-size: 0.8rem; color: var(--color-text-muted);">{{ $field->help_text }}</div>
                                @endif
                            </td>
                            <td>
                                <code>{{ $field->key }}</code>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">{{ $field->type }}</div>
                            </td>
                            <td>
                                {{ $field->is_required ? 'Tak' : 'Nie' }}
                            </td>
                            <td>
                                @if($field->is_active)
                                    <span class="badge" style="background: #dcfce7; color: #166534;">Aktywne</span>
                                @else
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">Ukryte</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if(! $field->is_system)
                                    <form action="{{ route('admin.forms.toggle-field', $field->id) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="min-height: 30px; padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                            {{ $field->is_active ? 'Ukryj' : 'Aktywuj' }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                                Brak zdefiniowanych pól.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dodaj nowe pole do formularza -->
    <div class="card">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            + Dodaj pole do formularza
        </h2>

        <form action="{{ route('admin.forms.store-field', $form->id) }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="field_label" class="form-label">Etykieta pola <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="field_label" name="label" required class="form-control" placeholder="np. Poziom wykształcenia">
            </div>

            <div class="form-group">
                <label for="field_key" class="form-label">Unikalny klucz pola <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="field_key" name="key" required class="form-control" placeholder="np. education_level">
            </div>

            <div class="form-group">
                <label for="field_type" class="form-label">Typ pola <span style="color: var(--color-danger);">*</span></label>
                <select id="field_type" name="type" class="form-control" required>
                    <option value="text">Krótki tekst (input text)</option>
                    <option value="textarea">Długi tekst (textarea)</option>
                    <option value="email">Adres e-mail</option>
                    <option value="tel">Telefon</option>
                    <option value="number">Liczba</option>
                    <option value="date">Data</option>
                    <option value="select">Lista rozwijana (select)</option>
                    <option value="checkbox">Pojedynczy checkbox</option>
                </select>
            </div>

            <div class="form-group">
                <label for="field_options" class="form-label">Opcje wyboru (dla select / checkbox)</label>
                <input type="text" id="field_options" name="options" class="form-control" placeholder="Wyższe, Średnie, Zawodowe (rozdzielone przecinkami)">
            </div>

            <div class="form-group">
                <label for="field_help" class="form-label">Podpowiedź / pomoc</label>
                <input type="text" id="field_help" name="help_text" class="form-control" placeholder="np. Wpisz kierunek i rok ukończenia studiów">
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="is_required" value="1" style="width: 18px; height: 18px;">
                    <span style="font-weight: 600;">Pole wymagane (obowiązkowe)</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Dodaj pole
            </button>
        </form>
    </div>
</div>
@endsection
