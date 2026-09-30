@extends('layouts.admin')

@section('title', "Zgłoszenie: {$application->reference_code} - {$application->full_name}")

@section('content')
<div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <a href="{{ route('admin.applications.index') }}" style="font-weight: 600; text-decoration: none;">
        &larr; Wróć do listy zgłoszeń
    </a>

    <div style="display: flex; gap: 0.5rem;">
        @if(auth()->user()->isRecruiter())
            <a href="{{ route('admin.applications.edit', $application->public_id) }}" class="btn btn-secondary">
                ✏️ Edytuj dane zgłoszenia
            </a>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Kolumna główna: Dane kandydata, odpowiedzi, pliki -->
    <div style="display: grid; gap: 1.5rem;">
        <!-- Karta nagłówkowa -->
        <div class="card" style="padding: 1.75rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                <div>
                    <span class="badge" style="font-size: 0.8125rem; margin-bottom: 0.5rem;">{{ $application->reference_code }}</span>
                    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">
                        {{ $application->full_name }}
                    </h1>
                    <div style="font-size: 1rem; color: var(--color-primary); font-weight: 600;">
                        Stanowisko: {{ $application->jobOffer->title }}
                    </div>
                </div>

                <div>
                    @if($application->status === 'new')
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.95rem; padding: 0.35rem 0.75rem;">Nowe</span>
                    @elseif($application->status === 'under_review')
                        <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.95rem; padding: 0.35rem 0.75rem;">W trakcie oceny</span>
                    @elseif($application->status === 'interview')
                        <span class="badge" style="background: #f3e8ff; color: #6b21a8; font-size: 0.95rem; padding: 0.35rem 0.75rem;">Zaproszony na rozmowę</span>
                    @elseif($application->status === 'qualified')
                        <span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.95rem; padding: 0.35rem 0.75rem;">Zakwalifikowany</span>
                    @elseif($application->status === 'rejected')
                        <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.95rem; padding: 0.35rem 0.75rem;">Odrzucone</span>
                    @else
                        <span class="badge" style="background: #f3f4f6; color: #4b5563; font-size: 0.95rem; padding: 0.35rem 0.75rem;">Anulowane</span>
                    @endif
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-subtle); font-size: 0.9375rem;">
                <div>
                    <span style="color: var(--color-text-muted); display: block; font-size: 0.8125rem;">E-mail:</span>
                    <strong><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></strong>
                </div>
                <div>
                    <span style="color: var(--color-text-muted); display: block; font-size: 0.8125rem;">Telefon:</span>
                    <strong>{{ $application->phone ?: '—' }}</strong>
                </div>
                <div>
                    <span style="color: var(--color-text-muted); display: block; font-size: 0.8125rem;">Adres:</span>
                    <span>{{ $application->address ?: '—' }}</span>
                </div>
                <div>
                    <span style="color: var(--color-text-muted); display: block; font-size: 0.8125rem;">Data wpłynięcia:</span>
                    <span>{{ $application->created_at->format('d.m.Y H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Załączone dokumenty (pliki PDF) -->
        <section class="card">
            <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
                📎 Załączone dokumenty PDF
            </h2>

            @if($application->files->isEmpty())
                <p style="color: var(--color-text-muted);">Brak załączonych plików.</p>
            @else
                <ul style="list-style: none; padding: 0;">
                    @foreach($application->files as $file)
                        <li style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid var(--color-border-subtle);">
                            <div>
                                <span style="font-weight: 600;">📄 {{ $file->original_name }}</span>
                                <div style="font-size: 0.8125rem; color: var(--color-text-muted);">
                                    Typ: <strong>{{ strtoupper($file->type) }}</strong> &bull; Rozmiar: {{ $file->formatted_size }} &bull; Suma SHA256: <code>{{ substr($file->checksum, 0, 10) }}...</code>
                                </div>
                            </div>

                            <a href="{{ route('admin.applications.download-file', ['public_id' => $application->public_id, 'file_id' => $file->id]) }}" class="btn btn-secondary" style="font-size: 0.8125rem; min-height: 36px; padding: 0.25rem 0.75rem;">
                                Pobierz plik PDF ⬇
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <!-- Odpowiedzi na pola formularza -->
        @if(!empty($application->answers) && is_array($application->answers))
            <section class="card">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
                    📝 Odpowiedzi w formularzu aplikacyjnym
                </h2>

                <div style="display: grid; gap: 0.75rem;">
                    @foreach($application->answers as $key => $val)
                        <div style="padding: 0.75rem; background: var(--color-surface-subtle); border-radius: 0.375rem;">
                            <span style="font-weight: 600; font-size: 0.875rem; color: var(--color-text-muted); display: block;">
                                {{ ucfirst(str_replace('_', ' ', $key)) }}:
                            </span>
                            <div style="font-size: 0.95rem; margin-top: 0.25rem; white-space: pre-wrap;">
                                {{ is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Wysłanie wiadomości e-mail do kandydata -->
        @if(auth()->user()->isRecruiter())
            <section class="card">
                <h2 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
                    ✉️ Wyślij wiadomość do kandydata
                </h2>

                <form action="{{ route('admin.applications.send-mail', $application->public_id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="mail_subject" class="form-label">Temat wiadomości <span style="color: var(--color-danger);">*</span></label>
                        <input type="text" id="mail_subject" name="subject" required class="form-control" value="Dotyczy naboru na stanowisko: {{ $application->jobOffer->title }}">
                    </div>

                    <div class="form-group">
                        <label for="mail_body" class="form-label">Treść wiadomości <span style="color: var(--color-danger);">*</span></label>
                        <textarea id="mail_body" name="body" rows="5" required class="form-control" placeholder="Wprowadź treść wiadomości, np. zaproszenie na rozmowę kwalifikacyjną z terminem i godziną..."></textarea>
                    </div>

                    <div style="text-align: right;">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Wysłać wiadomość e-mail do kandydata?');">
                            Wyślij wiadomość e-mail &rarr;
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <!-- Log wysłanych wiadomości -->
        <section class="card">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
                📬 Historia wysłanych wiadomości e-mail
            </h2>

            @if($application->mailLogs->isEmpty())
                <p style="color: var(--color-text-muted); font-size: 0.9rem;">Brak zarejestrowanych wysyłek wiadomości.</p>
            @else
                <ul style="list-style: none; padding: 0;">
                    @foreach($application->mailLogs as $mail)
                        <li style="padding: 0.5rem 0; border-bottom: 1px solid var(--color-border-subtle); font-size: 0.875rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <strong>{{ $mail->subject }}</strong>
                                <span style="color: {{ $mail->status === 'sent' ? 'var(--color-success)' : 'var(--color-danger)' }}; font-weight: bold;">
                                    {{ $mail->status === 'sent' ? 'Wysłano' : 'Błąd' }}
                                </span>
                            </div>
                            <div style="color: var(--color-text-muted); font-size: 0.8125rem;">
                                Do: {{ $mail->to_email }} &bull; Data: {{ $mail->sent_at->format('d.m.Y H:i:s') }}
                            </div>
                            @if($mail->error)
                                <div style="color: var(--color-danger); font-size: 0.8rem; margin-top: 0.25rem;">Błąd: {{ $mail->error }}</div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <!-- Prawa kolumna: Zarządzanie statusem, notatki, historia zmian -->
    <div style="display: grid; gap: 1.5rem;">
        <!-- Zmiana statusu -->
        @if(auth()->user()->isRecruiter())
            <div class="card" style="border-top: 4px solid var(--color-primary);">
                <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">
                    Zmień status zgłoszenia
                </h2>

                <form action="{{ route('admin.applications.update-status', $application->public_id) }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label for="new_status" class="form-label">Nowy status:</label>
                        <select id="new_status" name="status" class="form-control" required>
                            <option value="new" {{ $application->status === 'new' ? 'selected' : '' }}>Nowe</option>
                            <option value="under_review" {{ $application->status === 'under_review' ? 'selected' : '' }}>W trakcie oceny</option>
                            <option value="interview" {{ $application->status === 'interview' ? 'selected' : '' }}>Zaproszony na rozmowę</option>
                            <option value="qualified" {{ $application->status === 'qualified' ? 'selected' : '' }}>Zakwalifikowany / Zatrudniony</option>
                            <option value="rejected" {{ $application->status === 'rejected' ? 'selected' : '' }}>Odrzucone</option>
                            <option value="cancelled" {{ $application->status === 'cancelled' ? 'selected' : '' }}>Anulowane</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status_note" class="form-label">Notatka do historii:</label>
                        <textarea id="status_note" name="note" rows="2" class="form-control" placeholder="np. Komplet dokumentów zweryfikowany pozytywnie..."></textarea>
                    </div>

                    <div class="form-group">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem;">
                            <input type="checkbox" name="notify_candidate" value="1" style="width: 16px; height: 16px;">
                            <span>Powiadom kandydata e-mailem o zmianie</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Aktualizuj status
                    </button>
                </form>
            </div>
        @endif

        <!-- Notatki wewnętrzne -->
        <div class="card">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 0.75rem;">
                📌 Notatki wewnętrzne
            </h2>
            <div style="background: var(--color-surface-subtle); padding: 0.75rem; border-radius: 0.375rem; font-size: 0.9rem; line-height: 1.5; min-height: 60px;">
                {{ $application->internal_notes ?: 'Brak notatek do tego zgłoszenia.' }}
            </div>
        </div>

        <!-- Historia statusów i audyt zmian -->
        <div class="card">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
                ⏱️ Historia statusów
            </h2>

            <ul style="list-style: none; padding: 0;">
                @forelse($application->statusHistory as $hist)
                    <li style="padding: 0.625rem 0; border-bottom: 1px solid var(--color-border-subtle); font-size: 0.85rem;">
                        <div style="font-weight: 700; color: var(--color-primary);">
                            {{ $hist->from_status }} &rarr; {{ $hist->to_status }}
                        </div>
                        @if($hist->note)
                            <div style="margin: 0.2rem 0; color: var(--color-text);">{{ $hist->note }}</div>
                        @endif
                        <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                            {{ $hist->created_at->format('d.m.Y H:i:s') }}
                            @if($hist->user)
                                &bull; {{ $hist->user->name }}
                            @endif
                        </div>
                    </li>
                @empty
                    <li style="color: var(--color-text-muted); font-size: 0.85rem;">Brak historii zmian.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
