@extends('layouts.admin')

@section('title', "Edycja naboru: {$offer->title}")

@section('content')
<div style="margin-bottom: 1.5rem;">
    <a href="{{ route('admin.offers.index') }}" style="font-weight: 600; text-decoration: none;">
        &larr; Wróć do listy naborów
    </a>
</div>

<div style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.25rem;">
                Edycja ogłoszenia
            </h1>
            <p style="color: var(--color-text-muted); font-size: 0.875rem;">
                Publiczne ID: <code>{{ $offer->public_id }}</code> | Slug: <code>{{ $offer->slug }}</code>
            </p>
        </div>

        <a href="{{ route('public.offers.show', $offer->slug) }}" target="_blank" class="btn btn-secondary">
            Podgląd strony publicznej ↗
        </a>
    </div>

    <form action="{{ route('admin.offers.update', $offer->public_id) }}" method="POST" class="card" style="padding: 2rem;">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="title" class="form-label">Tytuł stanowiska <span style="color: var(--color-danger);">*</span></label>
            <input type="text" id="title" name="title" value="{{ old('title', $offer->title) }}" required class="form-control">
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem;">
            <div class="form-group">
                <label for="department_id" class="form-label">Wydział / Jednostka</label>
                <select id="department_id" name="department_id" class="form-control">
                    <option value="">-- Wybierz wydział --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $offer->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="contract_type_id" class="form-label">Rodzaj umowy</label>
                <select id="contract_type_id" name="contract_type_id" class="form-control">
                    <option value="">-- Wybierz rodzaj umowy --</option>
                    @foreach($contractTypes as $ct)
                        <option value="{{ $ct->id }}" {{ old('contract_type_id', $offer->contract_type_id) == $ct->id ? 'selected' : '' }}>{{ $ct->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="category_id" class="form-label">Kategoria stanowiska</label>
                <select id="category_id" name="category_id" class="form-control">
                    <option value="">-- Wybierz kategorię --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $offer->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="location_id" class="form-label">Miejsce pracy</label>
                <select id="location_id" name="location_id" class="form-control">
                    <option value="">-- Wybierz lokalizację --</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ old('location_id', $offer->location_id) == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="working_time" class="form-label">Wymiar etatu <span style="color: var(--color-danger);">*</span></label>
                <input type="text" id="working_time" name="working_time" value="{{ old('working_time', $offer->working_time) }}" required class="form-control">
            </div>

            <div class="form-group">
                <label for="deadline_at" class="form-label">Termin składania dokumentów <span style="color: var(--color-danger);">*</span></label>
                <input type="datetime-local" id="deadline_at" name="deadline_at" value="{{ old('deadline_at', $offer->deadline_at->format('Y-m-d\TH:i')) }}" required class="form-control">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="form_id" class="form-label">Szablon formularza aplikacyjnego</label>
                <select id="form_id" name="form_id" class="form-control">
                    <option value="">Domyślny formularz</option>
                    @foreach($forms as $f)
                        <option value="{{ $f->id }}" {{ old('form_id', $offer->form_id) == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status ogłoszenia <span style="color: var(--color-danger);">*</span></label>
                <select id="status" name="status" class="form-control" required onchange="togglePurgeOption(this.value)">
                    <option value="draft" {{ old('status', $offer->status) === 'draft' ? 'selected' : '' }}>Szkic (niewidoczne publicznie)</option>
                    <option value="published" {{ old('status', $offer->status) === 'published' ? 'selected' : '' }}>Opublikowana (widoczna na portalu)</option>
                    <option value="completed" {{ old('status', $offer->status) === 'completed' ? 'selected' : '' }}>Zakończona</option>
                    <option value="archived" {{ old('status', $offer->status) === 'archived' ? 'selected' : '' }}>Zarchiwizowana</option>
                </select>

                <div id="purge_rodo_box" style="margin-top: 0.5rem; padding: 0.75rem; background: var(--color-surface-subtle); border-radius: 0.375rem; border: 1px solid var(--color-border); font-size: 0.875rem; {{ old('status', $offer->status) === 'completed' ? '' : 'display: none;' }}">
                    <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="purge_unqualified" value="1" id="purge_unqualified" {{ old('purge_unqualified', '1') ? 'checked' : '' }} style="margin-top: 0.2rem;">
                        <span>
                            <strong>Zgodność z RODO:</strong> Usuń trwale dane osobowe i pliki CV kandydatów, którzy się nie zakwalifikowali (oferta pracy i dane wygranego zostaną zachowane).
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Zakres zadań / Opis stanowiska <span style="color: var(--color-danger);">*</span></label>
            <div class="form-help">Dozwolone bezpieczne formatowanie HTML: &lt;p&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;h3&gt;, &lt;h4&gt;, &lt;a&gt;, &lt;br&gt;.</div>
            <textarea id="description" name="description" rows="6" class="form-control" required>{{ old('description', $offer->description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="requirements" class="form-label">Wymagania niezbędne (formalne)</label>
            <textarea id="requirements" name="requirements" rows="4" class="form-control">{{ old('requirements', $offer->requirements) }}</textarea>
        </div>

        <div class="form-group">
            <label for="nice_to_have" class="form-label">Wymagania dodatkowe</label>
            <textarea id="nice_to_have" name="nice_to_have" rows="3" class="form-control">{{ old('nice_to_have', $offer->nice_to_have) }}</textarea>
        </div>

        <div class="form-group">
            <label for="offer_text" class="form-label">Warunki pracy i oferowane świadczenia</label>
            <textarea id="offer_text" name="offer_text" rows="3" class="form-control">{{ old('offer_text', $offer->offer_text) }}</textarea>
        </div>

        <div class="form-group">
            <label for="required_documents" class="form-label">Wymagane dokumenty</label>
            <textarea id="required_documents" name="required_documents" rows="3" class="form-control">{{ old('required_documents', $offer->required_documents) }}</textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem;">
            <a href="{{ route('admin.offers.index') }}" class="btn btn-secondary">Anuluj</a>
            <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
        </div>
    </form>
</div>

@if($offer->applications()->count() > 0)
    <div class="card" style="margin-top: 1.5rem; border-left: 4px solid var(--color-primary); background: var(--color-surface-subtle); padding: 1.25rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div>
                <h3 style="font-size: 1.05rem; font-weight: 700; margin-bottom: 0.25rem;">
                    🏁 Zakończenie procesu rekrutacji
                </h3>
                <p style="font-size: 0.875rem; color: var(--color-text-muted); margin: 0;">
                    Łącznie zgłoszeń: <strong>{{ $offer->applications()->count() }}</strong> &bull;
                    Zakwalifikowani / wygrani: <strong style="color: var(--color-success);">{{ $offer->qualifiedApplications()->count() }}</strong> &bull;
                    Niezakwalifikowani: <strong style="color: var(--color-danger);">{{ $offer->unqualifiedApplications()->count() }}</strong>
                </p>
                <div style="font-size: 0.8125rem; color: var(--color-text-muted); margin-top: 0.25rem;">
                    Zgodnie z wymogami RODO, po zakończeniu naboru oferta pracy oraz dane wybranego kandydata pozostają w systemie. Dane osobowe i pliki CV pozostałych kandydatów są trwale niszczone.
                </div>
            </div>

            <form action="{{ route('admin.offers.complete', $offer->public_id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Czy na pewno chcesz zakończyć proces rekrutacji dla tego naboru?\n\n- Oferta pozostanie w systemie (status: Zakończona).\n- Dane wygranego kandydata ({{ $offer->qualifiedApplications()->count() }}) i jego CV zostaną zachowane.\n- Dane osobowe i pliki CV pozostałych kandydatów ({{ $offer->unqualifiedApplications()->count() }}) zostaną trwale usunięte.{{ $offer->qualifiedApplications()->count() === 0 ? '\n\nUWAGA: Żaden kandydat nie jest oznaczony jako zakwalifikowany!' : '' }}');">
                @csrf
                <button type="submit" class="btn btn-secondary" style="background: var(--color-surface); border: 1px solid var(--color-border); font-weight: 600;">
                    Zakończ proces i oczyść dane
                </button>
            </form>
        </div>
    </div>
@endif

<script>
function togglePurgeOption(val) {
    var box = document.getElementById('purge_rodo_box');
    if (box) {
        box.style.display = (val === 'completed') ? 'block' : 'none';
    }
}
</script>
@endsection
