@extends('layouts.admin')

@section('title', 'Logi audytu systemu')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Rejestr zdarzeń i logi audytu</h1>
        <p style="color: var(--color-text-muted); font-size: 0.95rem;">Niezmienny rejestr operacji wykonywanych w systemie rekrutacyjnym (tylko do odczytu)</p>
    </div>

    <div>
        <a href="{{ route('admin.logs.export-csv', request()->query()) }}" class="btn btn-secondary">
            📥 Eksportuj CSV
        </a>
    </div>
</div>

<!-- Filtry logów -->
<div class="card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
    <form action="{{ route('admin.logs.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 2; min-width: 180px;">
            <label for="action" class="form-label">Akcja / Zdarzenie:</label>
            <input type="text" id="action" name="action" value="{{ request('action') }}" class="form-control" placeholder="np. login, offer, status...">
        </div>

        @if(auth()->user()->isAdmin())
            <div style="flex: 2; min-width: 200px;">
                <label for="user_id" class="form-label">Pracownik:</label>
                <select id="user_id" name="user_id" class="form-control">
                    <option value="">Wszyscy pracownicy</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div style="flex: 1; min-width: 140px;">
            <label for="date_from" class="form-label">Od daty:</label>
            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>

        <div style="flex: 1; min-width: 140px;">
            <label for="date_to" class="form-label">Do daty:</label>
            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>

        <div>
            <button type="submit" class="btn btn-secondary">Filtruj</button>
        </div>
    </form>
</div>

<!-- Tabela logów (tylko do odczytu) -->
<div class="card" style="padding: 0;">
    <div class="table-responsive">
        <table class="table" style="font-size: 0.875rem;">
            <thead>
                <tr>
                    <th style="width: 150px;">Data i godzina</th>
                    <th>Użytkownik</th>
                    <th>Akcja</th>
                    <th>Obiekt</th>
                    <th>Szczegóły</th>
                    <th>IP / Przeglądarka</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td style="white-space: nowrap;">
                            <strong>{{ $log->created_at->format('d.m.Y') }}</strong>
                            <div style="color: var(--color-text-muted); font-size: 0.8125rem;">{{ $log->created_at->format('H:i:s') }}</div>
                        </td>
                        <td>
                            @if($log->user)
                                <strong>{{ $log->user->name }}</strong>
                                <div style="color: var(--color-text-muted); font-size: 0.75rem;">{{ $log->user->role }}</div>
                            @else
                                <span style="color: var(--color-text-muted);">Gość / System</span>
                            @endif
                        </td>
                        <td>
                            <code style="font-weight: bold; background: var(--color-surface-subtle); padding: 0.2rem 0.4rem; border-radius: 4px;">
                                {{ $log->action }}
                            </code>
                        </td>
                        <td>
                            @if($log->auditable_type)
                                <div style="font-size: 0.8125rem;">{{ class_basename($log->auditable_type) }} #{{ $log->auditable_id }}</div>
                            @else
                                <span style="color: var(--color-text-muted);">—</span>
                            @endif
                        </td>
                        <td style="max-width: 320px; word-break: break-all;">
                            @if($log->new_values)
                                <details style="font-size: 0.8rem;">
                                    <summary style="cursor: pointer; color: var(--color-primary);">Pokaż wartości</summary>
                                    <pre style="margin-top: 0.25rem; background: var(--color-surface-subtle); padding: 0.5rem; border-radius: 4px; overflow-x: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @else
                                <span style="color: var(--color-text-muted);">—</span>
                            @endif
                        </td>
                        <td>
                            <div><code>{{ $log->ip }}</code></div>
                            <div style="font-size: 0.75rem; color: var(--color-text-muted);" title="{{ $log->user_agent }}">
                                {{ Str::limit($log->user_agent, 25) }}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                            Brak zarejestrowanych wpisów audytowych.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 1.5rem;">
    {{ $logs->links() }}
</div>
@endsection
