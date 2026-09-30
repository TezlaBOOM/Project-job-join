@extends('layouts.app')

@section('title', 'Deklaracja dostępności cyfrowej')

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <article class="card" style="padding: 2.5rem 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800; color: var(--color-primary); margin-bottom: 1.5rem; border-bottom: 2px solid var(--color-border); padding-bottom: 0.75rem;">
            Deklaracja dostępności cyfrowej
        </h1>

        <div style="line-height: 1.8; font-size: 1.05rem;">
            <p>
                <strong>{{ $officeName }}</strong> zobowiązuje się zapewnić dostępność swojego portalu rekrutacyjnego zgodnie z przepisami ustawy z dnia 4 kwietnia 2019 r. o dostępności cyfrowej stron internetowych i aplikacji mobilnych podmiotów publicznych (Dz.U. 2019 poz. 848) oraz normą europejską EN 301 549.
            </p>

            <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                Stan dostępności cyfrowej
            </h2>
            <p>
                Portal rekrutacyjny jest <strong>zgodny</strong> z wytycznymi WCAG 2.1 na poziomie AA (Web Content Accessibility Guidelines 2.1).
            </p>
            <p>
                Serwis został wyposażony m.in. w:
            </p>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li>Obsługę nawigacji wyłącznie za pomocą klawiatury bez pułapek klawiaturowych.</li>
                <li>Wyraźny, widoczny wskaźnik fokusu spełniający wymóg minimalnego kontrastu 3:1.</li>
                <li>Link "Przejdź do treści głównej" (skip link) umożliwiający ominięcie nagłówka.</li>
                <li>Przełącznik trybu ciemnego, jasnego i automatycznego zapamiętywany w przeglądarce.</li>
                <li>Poprawną hierarchię semantyczną nagłówków (pojedynczy nagłówek H1 na każdej podstronie).</li>
                <li>Wsparcie dla czytników ekranu (NVDA, JAWS, VoiceOver) oraz atrybuty ARIA przy komunikatach walidacji.</li>
                <li>Dostępność wszystkich formularzy i filtrów również przy wyłączonym JavaScript.</li>
            </ul>

            <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                Data sporządzenia deklaracji
            </h2>
            <p>
                Deklarację sporządzono dnia: <strong>29 września 2026 r.</strong>
                Deklaracja została sporządzona na podstawie samooceny przeprowadzonej przez podmiot publiczny oraz testów automatycznych axe-core i pa11y.
            </p>

            <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                Informacje zwrotne i dane kontaktowe
            </h2>
            <p>
                W przypadku problemów z dostępnością cyfrową portalu rekrutacyjnego prosimy o kontakt. Osobą kontaktową jest Koordynator ds. Dostępności:
            </p>
            <ul style="padding-left: 1.5rem; margin-bottom: 1rem;">
                <li>E-mail: <strong>{{ \App\Models\Setting::get('office_email', 'dostepnosc@miasto.gov.pl') }}</strong></li>
                <li>Telefon: <strong>{{ \App\Models\Setting::get('office_phone', '12 345 67 89') }}</strong></li>
                <li>Adres korespondencyjny: <strong>{{ \App\Models\Setting::get('office_address', 'ul. Urzędowa 1, 00-001 Miasto') }}</strong></li>
            </ul>

            <h2 style="font-size: 1.35rem; font-weight: 700; margin-top: 1.75rem; margin-bottom: 0.75rem;">
                Procedura wnioskowo-skargowa
            </h2>
            <p>
                Każdy ma prawo do wystąpienia z żądaniem zapewnienia dostępności cyfrowej strony internetowej lub jej elementu. Można także zażądać udostępnienia informacji za pomocą alternatywnego sposobu dostępu.
                Żądanie powinno zawierać dane osoby zgłaszającej, wskazanie strony oraz sposób kontaktu. Urząd powinien zrealizować żądanie niezwłocznie, nie później niż w ciągu 7 dni od dnia wystąpienia z żądaniem.
            </p>
            <p>
                W przypadku odmowy lub braku realizacji żądania, przysługuje prawo złożenia skargi do Rzecznika Praw Obywatelskich: <a href="https://bip.brpo.pl/" target="_blank" rel="noopener noreferrer">https://bip.brpo.pl/</a>.
            </p>
        </div>
    </article>
</div>
@endsection
