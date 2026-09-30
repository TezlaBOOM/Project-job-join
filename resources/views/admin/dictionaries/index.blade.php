@extends('layouts.admin')

@section('title', "Słowniki: {$title}")

@section('content')
<div style="margin-bottom: 1.5rem;">
    <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">Słowniki systemowe</h1>
    <p style="color: var(--color-text-muted); font-size: 0.95rem;">Zarządzaj słownikami wykorzystywanymi w ogłoszeniach i filtrach naborów</p>
</div>

<!-- Zakładki słowników -->
<div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem; overflow-x: auto;">
    @foreach($types as $key => $conf)
        <a href="{{ route('admin.dictionaries.index', ['type' => $key]) }}" class="btn {{ $currentType === $key ? 'btn-primary' : 'btn-secondary' }}" style="font-size: 0.875rem; min-height: 38px; padding: 0.35rem 0.875rem;">
            {{ $conf['title'] }}
        </a>
    @endforeach
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Lista pozycji słownika -->
    <div class="card" style="padding: 0;">
        <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center;">
            <h2 style="font-size: 1.15rem; font-weight: 700; margin: 0;">{{ $title }} ({{ $items->count() }})</h2>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nazwa pozycji</th>
                        <th>Powiązane nabory</th>
                        <th>Status</th>
                        <th style="text-align: right;">Akcja</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td><strong>{{ $item->name }}</strong></td>
                            <td>
                                <span class="badge">{{ $item->job_offers_count }}</span>
                            </td>
                            <td>
                                @if($item->is_active)
                                    <span class="badge" style="background: #dcfce7; color: #166534;">Aktywna</span>
                                @else
                                    <span class="badge" style="background: #fee2e2; color: #991b1b;">Nieaktywna</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <form action="{{ route('admin.dictionaries.toggle', ['type' => $currentType, 'id' => $item->id]) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-secondary" style="min-height: 30px; padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                        {{ $item->is_active ? 'Dezaktywuj' : 'Aktywuj' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem; color: var(--color-text-muted);">
                                Brak pozycji w tym słowniku.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Dodawanie nowej pozycji -->
    <div class="card">
        <h2 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem;">
            + Dodaj pozycję do: {{ $title }}
        </h2>

        <form action="{{ route('admin.dictionaries.store', ['type' => $currentType]) }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Nazwa pozycji: <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="name" name="name" required class="form-control" placeholder="Wpisz nazwę...">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Zapisz nową pozycję
            </button>
        </form>
    </div>
</div>
@endsection
