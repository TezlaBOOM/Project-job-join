@extends('layouts.admin')

@section('title', 'Szablony formularzy aplikacyjnych')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Szablony formularzy zgłoszeniowych</h1>
    <p style="color: var(--color-text-muted); font-size: 0.95rem;">Twórz i konfiguruj szablony formularzy używane przy składaniu aplikacji online</p>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Lista szablonów -->
    <div class="card" style="padding: 0;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">Dostępne szablony ({{ $forms->count() }})</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nazwa szablonu</th>
                        <th>Liczba pól</th>
                        <th>Data utworzenia</th>
                        <th style="text-align: right;">Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $form)
                        <tr>
                            <td>
                                <strong>{{ $form->name }}</strong>
                            </td>
                            <td>
                                <span class="badge">{{ $form->fields->count() }} pól</span>
                            </td>
                            <td style="font-size: 0.8125rem;">{{ $form->created_at->format('d.m.Y') }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.forms.show', $form->id) }}" class="btn btn-secondary" style="min-height: 32px; padding: 0.2rem 0.6rem; font-size: 0.8125rem;">
                                    Konfiguruj pola &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                                Brak utworzonych szablonów formularzy.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Utwórz nowy szablon -->
    <div class="card">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            + Nowy szablon formularza
        </h2>

        <form action="{{ route('admin.forms.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="form_name" class="form-label">Nazwa szablonu: <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="form_name" name="name" required class="form-control" placeholder="np. Stanowisko urzędnicze / Staż">
                <div class="form-help">Szablon zostanie automatycznie zainicjalizowany podstawowymi polami systemowymi.</div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Utwórz szablon
            </button>
        </form>
    </div>
</div>
@endsection
