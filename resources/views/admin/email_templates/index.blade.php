@extends('layouts.admin')

@section('title', 'Szablony wiadomości e-mail')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Szablony wiadomości e-mail</h1>
    <p style="color: var(--color-text-muted); font-size: 0.95rem;">Zarządzaj treścią powiadomień e-mail wysyłanych automatycznie do kandydatów</p>
</div>

<div style="display: grid; gap: 1.5rem;">
    @forelse($templates as $tmpl)
        <div class="card">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--color-primary);">
                Klauza szablonu: <code>{{ $tmpl->key }}</code>
            </h2>

            <form action="{{ route('admin.email-templates.update', $tmpl->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="subject_{{ $tmpl->id }}" class="form-label">Domyślny temat wiadomości:</label>
                    <input type="text" id="subject_{{ $tmpl->id }}" name="subject" value="{{ old('subject', $tmpl->subject) }}" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="body_{{ $tmpl->id }}" class="form-label">Treść wiadomości (wersja tekstowa):</label>
                    <textarea id="body_{{ $tmpl->id }}" name="body_text" rows="5" required class="form-control">{{ old('body_text', $tmpl->body_text) }}</textarea>
                </div>

                <div style="text-align: right;">
                    <button type="submit" class="btn btn-primary" style="font-size: 0.875rem;">
                        Zapisz szablon
                    </button>
                </div>
            </form>
        </div>
    @empty
        <div class="card" style="text-align: center; padding: 2rem;">
            <p style="color: var(--color-text-muted);">Brak szablonów e-mail w bazie.</p>
        </div>
    @endforelse
</div>
@endsection
