@extends('layouts.app')

@section('title', 'Polityka prywatności i ochrona danych osobowych (RODO)')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <article class="card" style="padding: 2.5rem 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--color-border); padding-bottom: 0.75rem;">
            Polityka prywatności i ochrona danych (RODO)
        </h1>

        <div style="line-height: 1.8; font-size: 1.05rem;">
            <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 0.75rem;">
                Administrator Danych Osobowych
            </h2>
            <p>
                Administratorem Państwa danych osobowych jest <strong>{{ $officeName }}</strong> z siedzibą przy {{ \App\Models\Setting::get('office_address', 'ul. Urzędowej 1 w Mieście') }}.
            </p>

            <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                Inspektor Ochrony Danych (IOD)
            </h2>
            <p>
                W sprawach związanych z ochroną danych osobowych mogą Państwo kontaktować się z Inspektorem Ochrony Danych pod adresem e-mail: <strong>{{ \App\Models\Setting::get('iod_email', 'iod@miasto.gov.pl') }}</strong>.
            </p>

            <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                Cele i podstawy prawne przetwarzania
            </h2>
            <p>
                Państwa dane osobowe przetwarzane są w celu przeprowadzenia procedury naboru na wolne stanowisko pracy w urzędzie na podstawie:
            </p>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li>Art. 6 ust. 1 lit. c RODO w zw. z art. 22¹ Kodeksu pracy oraz przepisami ustawy z dnia 21 listopada 2008 r. o pracownikach samorządowych – w zakresie danych wymaganych przepisami prawa.</li>
                <li>Art. 6 ust. 1 lit. a RODO – na podstawie Państwa dobrowolnej zgody w zakresie danych podanych ponad wymóg ustawowy (np. numer telefonu, zdjęcie w CV).</li>
            </ul>

            <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                Okres retencji (przechowywania) danych
            </h2>
            <p>
                Dane kandydatów przechowywane są przez okres trwania naboru, a po jego zakończeniu przez okres przewidziany przepisami archiwalnymi (standardowo {{ \App\Models\Setting::get('retention_months', '3') }} miesiące po zakończeniu naboru).
                Po upływie okresu retencji dane oraz pliki PDF są automatycznie usuwane i anonimizowane z systemu.
            </p>

            <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                Prawa osób, których dane dotyczą
            </h2>
            <p>
                Przysługuje Państwu prawo do:
            </p>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li>Dostępu do swoich danych oraz otrzymania ich kopii.</li>
                <li>Sprostowania (poprawiania) swoich danych.</li>
                <li>Usunięcia danych lub ograniczenia przetwarzania.</li>
                <li>Cofnięcia zgody w dowolnym momencie (m.in. za pomocą jednorazowego linku wycofania zgłoszenia przesłanego w e-mailu).</li>
                <li>Wniesienia skargi do organu nadzorczego – Prezesa Urzędu Ochrony Danych Osobowych (UODO).</li>
            </ul>

            @if($consent)
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                    Aktualna klauzula informacyjna (wersja {{ $consent->version }})
                </h2>
                <div style="background-color: var(--color-surface-subtle); padding: 1.25rem; border-radius: 0.5rem; border: 1px solid var(--color-border); font-size: 0.95rem; line-height: 1.6;">
                    {!! nl2br(e($consent->content)) !!}
                </div>
            @endif
        </div>
    </article>
</div>
@endsection
