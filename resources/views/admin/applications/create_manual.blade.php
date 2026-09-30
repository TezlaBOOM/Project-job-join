@extends('layouts.admin')

@section('title', 'Ręczne wprowadzenie zgłoszenia')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('admin.applications.index') }}" style="font-weight: 600; text-decoration: none;">
        &larr; Wróć do listy zgłoszeń
    </a>
</div>

<div style="max-width: 700px; margin: 0 auto;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 1.5rem;">
        Ręczne wprowadzenie zgłoszenia kandydata
    </h1>

    <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 1.5rem;">
        Formularz przeznaczony do wprowadzania aplikacji dostarczonych w formie papierowej na dziennik podawczy lub drogą tradycyjną.
    </p>

    <form action="{{ route('admin.applications.store-manual') }}" method="POST" enctype="multipart/form-data" class="card" style="padding: 2rem;">
        @csrf

        <div class="form-group">
            <label for="job_offer_id" class="form-label">Wybierz nabór <span style="color: var(--color-danger);">*</span></label>
            <select id="job_offer_id" name="job_offer_id" class="form-control" required>
                <option value="">-- Wybierz ogłoszenie --</option>
                @foreach($offers as $o)
                    <option value="{{ $o->id }}" {{ old('job_offer_id') == $o->id ? 'selected' : '' }}>
                        {{ $o->title }} (termin: {{ $o->deadline_at->format('d.m.Y') }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="first_name" class="form-label">Imię <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="form-control">
            </div>

            <div class="form-group">
                <label for="last_name" class="form-label">Nazwisko <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="email" class="form-label">Adres e-mail <span style="color: var(--color-danger);">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required class="form-control">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Telefon</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="form-label">Adres zamieszkania / korespondencyjny</label>
            <input type="text" id="address" name="address" value="{{ old('address') }}" class="form-control">
        </div>

        <div class="form-group">
            <label for="cv_file" class="form-label">Zeskanowane CV / dokumenty (PDF)</label>
            <input type="file" id="cv_file" name="cv_file" accept=".pdf,application/pdf" class="form-control">
            <div class="form-help">Wymagany format: PDF (max 5 MB).</div>
        </div>

        <div class="form-group">
            <label for="internal_notes" class="form-label">Notatki wewnętrzne / opis dokumentów</label>
            <textarea id="internal_notes" name="internal_notes" rows="3" class="form-control" placeholder="np. Złożono osobiście na Dzienniku Podawczym dnia... W komplecie oryginały oświadczeń.">{{ old('internal_notes') }}</textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
            <a href="{{ route('admin.applications.index') }}" class="btn btn-secondary">Anuluj</a>
            <button type="submit" class="btn btn-primary">Zarejestruj zgłoszenie</button>
        </div>
    </form>
</div>
@endsection
