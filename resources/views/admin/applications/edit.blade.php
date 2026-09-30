@extends('layouts.admin')

@section('title', "Edycja zgłoszenia: {$application->reference_code}")

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('admin.applications.show', $application->public_id) }}" style="font-weight: 600; text-decoration: none;">
        &larr; Wróć do podglądu zgłoszenia
    </a>
</div>

<div style="max-width: 700px; margin: 0 auto;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 1.5rem;">
        Edycja danych kandydata ({{ $application->reference_code }})
    </h1>

    <form action="{{ route('admin.applications.update', $application->public_id) }}" method="POST" class="card" style="padding: 2rem;">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="first_name" class="form-label">Imię <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $application->first_name) }}" required class="form-control">
            </div>

            <div class="form-group">
                <label for="last_name" class="form-label">Nazwisko <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $application->last_name) }}" required class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="email" class="form-label">Adres e-mail <span style="color: var(--color-danger);">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $application->email) }}" required class="form-control">
            </div>

            <div class="form-group">
                <label for="phone" class="form-label">Telefon</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $application->phone) }}" class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="form-label">Adres zamieszkania / korespondencyjny</label>
            <input type="text" id="address" name="address" value="{{ old('address', $application->address) }}" class="form-control">
        </div>

        <div class="form-group">
            <label for="internal_notes" class="form-label">Notatki wewnętrzne</label>
            <textarea id="internal_notes" name="internal_notes" rows="4" class="form-control">{{ old('internal_notes', $application->internal_notes) }}</textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
            <a href="{{ route('admin.applications.show', $application->public_id) }}" class="btn btn-secondary">Anuluj</a>
            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
        </div>
    </form>
</div>
@endsection
