@extends('layouts.admin')

@section('title', 'Zarządzanie użytkownikami')

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Użytkownicy i uprawnienia</h1>
    <p style="color: var(--color-text-muted); font-size: 0.95rem;">Zarządzaj kontami pracowników urzędu, rekruterów i administratorów panelu</p>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Lista użytkowników -->
    <div class="card" style="padding: 0;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border);">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">Zarejestrowani pracownicy ({{ $users->count() }})</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Imię i nazwisko / E-mail</th>
                        <th>Rola</th>
                        <th>Status</th>
                        <th>Ostatnie logowanie</th>
                        <th style="text-align: right;">Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>
                                <div><strong>{{ $user->name }}</strong></div>
                                <div style="font-size: 0.8125rem; color: var(--color-text-muted);">{{ $user->email }}</div>
                            </td>
                            <td>
                                <form action="{{ route('admin.users.update-role', $user->id) }}" method="POST" style="margin: 0; display: inline-flex; align-items: center; gap: 0.35rem;">
                                    @csrf
                                    <select name="role" class="form-control" style="width: auto; min-height: 32px; padding: 0.2rem 0.5rem; font-size: 0.8125rem;" onchange="this.form.submit()">
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                                        <option value="recruiter" {{ $user->role === 'recruiter' ? 'selected' : '' }}>Rekruter</option>
                                        <option value="viewer" {{ $user->role === 'viewer' ? 'selected' : '' }}>Przeglądający</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge" style="background: #dcfce7; color: #166534;">Aktywny</span>
                                @else
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">Zablokowany</span>
                                @endif
                            </td>
                            <td style="font-size: 0.8125rem;">
                                {{ $user->last_login_at ? $user->last_login_at->format('d.m.Y H:i') : 'Brak danych' }}
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 0.35rem;">
                                    <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary" style="min-height: 30px; padding: 0.15rem 0.5rem; font-size: 0.75rem;" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            {{ $user->is_active ? 'Zablokuj' : 'Odblokuj' }}
                                        </button>
                                    </form>

                                    <!-- Reset hasła -->
                                    <button type="button" class="btn btn-secondary" style="min-height: 30px; padding: 0.15rem 0.5rem; font-size: 0.75rem;" onclick="var p = prompt('Wprowadź nowe hasło dla {{ $user->name }}:'); if(p) { document.getElementById('reset-p-{{ $user->id }}').value = p; document.getElementById('reset-form-{{ $user->id }}').submit(); }">
                                        Reset hasła
                                    </button>

                                    <form id="reset-form-{{ $user->id }}" action="{{ route('admin.users.reset-password', $user->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        <input type="hidden" name="new_password" id="reset-p-{{ $user->id }}">
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dodaj nowego pracownika / rekrutera -->
    <div class="card">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            + Dodaj użytkownika
        </h2>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Imię i nazwisko <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="name" name="name" required class="form-control" placeholder="np. Anna Nowak">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Służbowy adres e-mail <span style="color: var(--color-danger);">*</span></label>
                <input type="email" id="email" name="email" required class="form-control" placeholder="np. a.nowak@miasto.gov.pl">
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Rola systemowa <span style="color: var(--color-danger);">*</span></label>
                <select id="role" name="role" class="form-control" required>
                    <option value="recruiter">Rekruter (tworzenie ofert, obsługa zgłoszeń)</option>
                    <option value="viewer">Przeglądający (tylko wgląd)</option>
                    <option value="admin">Administrator (pełne uprawnienia)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Hasło początkowe (opcjonalne)</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Pozostaw puste, aby wygenerować losowe">
                <div class="form-help">Użytkownik otrzyma e-mail z linkiem do ustawienia hasła.</div>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Utwórz konto
            </button>
        </form>
    </div>
</div>
@endsection
