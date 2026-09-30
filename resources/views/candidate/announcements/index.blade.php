<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Naborów - Urząd Miasta i Jednostki Podległe (BIP)</title>
    <meta name="description" content="Oficjalny portal naborów na wolne stanowiska urzędnicze w Urzędzie Miasta oraz miejskich jednostkach organizacyjnych (MOPS, MZDiT, MOSiR, Oświata).">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">
    <!-- WCAG Skip Link -->
    <a href="#main-content" class="skip-link">Przejdź do głównej treści</a>

    <!-- Top City Hall Header -->
    <header class="gov-gradient-header text-white shadow-xl relative z-10" role="banner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Utility Bar -->
            <div class="py-2 border-b border-blue-900/60 flex justify-between items-center text-xs text-blue-200">
                <div class="flex items-center space-x-3">
                    <span class="inline-flex items-center space-x-1.5 font-semibold">
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm1 11H9v-2h2v2zm0-4H9V5h2v4z"/></svg>
                        <span>Oficjalny Biuletyn Informacji Publicznej (BIP) Miasta</span>
                    </span>
                </div>
                <div class="flex items-center space-x-4 font-semibold">
                    <a href="{{ route('results.index') }}" class="hover:text-white underline focus:outline-none focus:ring-2 focus:ring-amber-400 rounded">
                        Wyniki Naborów (BIP)
                    </a>
                    <span>•</span>
                    <a href="{{ route('login') }}" class="text-amber-300 hover:text-white font-extrabold flex items-center space-x-1 bg-blue-900/60 px-2.5 py-0.5 rounded-lg border border-amber-400/30">
                        <span>Panel Urzędnika / Admin</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </a>
                </div>
            </div>

            <!-- Main Navigation Bar -->
            <div class="py-5 flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center space-x-3.5">
                    <!-- City Emblem Icon -->
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-amber-500 to-amber-700 text-white flex items-center justify-center shadow-lg border border-amber-300/40">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1m-1-4h.01M9 16h.01M9 12h.01M13 16h.01M13 12h.01M14 8h-4"/></svg>
                    </div>
                    <div>
                        <span class="text-2xl font-extrabold tracking-tight text-white block leading-tight">
                            Portal Naborów Miejskich
                        </span>
                        <span class="text-xs text-blue-200 font-medium tracking-wide">
                            Urząd Miasta & Miejskie Jednostki Organizacyjne
                        </span>
                    </div>
                </div>

                <nav role="navigation" aria-label="Nawigacja główna" class="flex items-center space-x-2">
                    <a href="{{ route('announcements.index') }}" aria-current="page" class="px-4 py-2.5 rounded-xl text-xs font-extrabold bg-blue-800/90 text-white border border-blue-600 shadow-sm">
                        Nabory Miejskie
                    </a>
                    <a href="{{ route('results.index') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold text-blue-100 hover:text-white hover:bg-blue-800/40 transition">
                        Wyniki i Protokoły (BIP)
                    </a>
                    <a href="{{ route('login') }}" class="px-4 py-2.5 rounded-xl text-xs font-extrabold bg-amber-600 hover:bg-amber-700 text-white shadow-md transition">
                        Zaloguj jako Urzędnik
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <!-- City Hero Search Banner -->
    <section class="gov-gradient-header pt-6 pb-16 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-3xl mb-8 space-y-2">
                <span class="bg-amber-500/20 text-amber-300 border border-amber-400/30 text-xs font-extrabold px-3 py-1 rounded-full inline-block uppercase tracking-wider">
                    Kariera w Służbie Miejskiej
                </span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    Praca w Urzędzie Miasta i Jednostkach Podległych
                </h1>
                <p class="text-blue-100 text-sm sm:text-base leading-relaxed">
                    Aplikuj na wolne stanowiska urzędnicze w Urzędzie Miasta oraz w podległych jednostkach: MOPS, Miejskim Zarządzie Dróg, MOSiR, Straży Miejskiej oraz jednostkach oświatowych.
                </p>
            </div>

            <!-- Multi-Filter Search Form -->
            <form action="{{ route('announcements.index') }}" method="GET" role="search" class="bg-white/95 p-3.5 rounded-2xl shadow-2xl border border-white/20 backdrop-blur-md grid grid-cols-1 sm:grid-cols-12 gap-3 text-slate-800">
                <!-- Search Input -->
                <div class="sm:col-span-5 relative">
                    <label for="search-input" class="sr-only">Wyszukaj stanowisko lub słowa kluczowe</label>
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="search" id="search-input" name="search" value="{{ request('search') }}" placeholder="Stanowisko, dziedzina (np. Inspektor, Księgowy)..." class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 placeholder-slate-400 focus:bg-white focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                </div>

                <!-- Unit / Tenant Filter -->
                <div class="sm:col-span-4">
                    <label for="category-select" class="sr-only">Kategoria lub dziedzina</label>
                    <select id="category-select" name="category" class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold text-slate-900 focus:bg-white focus:ring-2 focus:ring-blue-600 transition">
                        <option value="">Wszystkie dziedziny miejskie</option>
                        <option value="Administracja" {{ request('category') === 'Administracja' ? 'selected' : '' }}>Urząd Miasta - Administracja</option>
                        <option value="Informatyka" {{ request('category') === 'Informatyka' ? 'selected' : '' }}>Informatyka / IT</option>
                        <option value="Finanse" {{ request('category') === 'Finanse' ? 'selected' : '' }}>Finanse i Księgowość Budżetowa</option>
                        <option value="Srodowisko" {{ request('category') === 'Srodowisko' ? 'selected' : '' }}>Ochrona Środowiska & Zielona Miejskie</option>
                        <option value="Infrastruktura" {{ request('category') === 'Infrastruktura' ? 'selected' : '' }}>Zarząd Dróg & Infrastruktura</option>
                        <option value="Rolnictwo" {{ request('category') === 'Rolnictwo' ? 'selected' : '' }}>Rolnictwo & Audyty</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="sm:col-span-3">
                    <button type="submit" class="w-full py-3 px-6 bg-blue-900 hover:bg-blue-950 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl shadow-md focus:ring-4 focus:ring-blue-400 transition flex items-center justify-center space-x-2">
                        <span>Szukaj Oferty</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </form>
        </div>
    </section>

    <!-- Main Content Area -->
    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex-grow w-full" role="main">
        <!-- Subordinate Entities Selector Pills -->
        <div class="mb-10 space-y-3">
            <h2 class="text-xs font-extrabold text-slate-400 uppercase tracking-wider">
                Miejskie Jednostki Organizacyjne biorące udział w naborach:
            </h2>
            <div class="flex flex-wrap gap-2 text-xs font-bold">
                <span class="px-3.5 py-1.5 rounded-full bg-blue-900 text-white shadow-sm border border-blue-800">
                    Urząd Miasta (Centrala)
                </span>
                <span class="px-3.5 py-1.5 rounded-full bg-white text-slate-700 border border-slate-200 shadow-sm hover:border-slate-300 transition">
                    Miejski Ośrodek Pomocy Społecznej (MOPS)
                </span>
                <span class="px-3.5 py-1.5 rounded-full bg-white text-slate-700 border border-slate-200 shadow-sm hover:border-slate-300 transition">
                    Miejski Zarząd Dróg i Komunikacji
                </span>
                <span class="px-3.5 py-1.5 rounded-full bg-white text-slate-700 border border-slate-200 shadow-sm hover:border-slate-300 transition">
                    Miejski Ośrodek Sportu i Rekreacji (MOSiR)
                </span>
                <span class="px-3.5 py-1.5 rounded-full bg-white text-slate-700 border border-slate-200 shadow-sm hover:border-slate-300 transition">
                    Straż Miejska
                </span>
            </div>
        </div>

        <!-- Section Title & Filter Counter -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 pb-4 border-b border-slate-200">
            <div>
                <h3 class="text-xl font-extrabold text-slate-950 tracking-tight">
                    Aktualnie Otwarte Nabory Miejskie
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    Liczba dostępnych ofert: <span class="font-bold text-blue-900">{{ $announcements->total() }}</span>
                </p>
            </div>

            @if(request('search') || request('category'))
                <a href="{{ route('announcements.index') }}" class="text-xs font-bold text-blue-800 hover:text-blue-950 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200">
                    &times; Wyczyść filtry
                </a>
            @endif
        </div>

        <!-- Announcements Grid -->
        @if($announcements->isEmpty())
            <div class="bg-white rounded-2xl p-12 text-center border border-slate-200 shadow-sm max-w-2xl mx-auto space-y-4">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
                    !
                </div>
                <h4 class="text-lg font-bold text-slate-900">Brak ogłoszeń spełniających kryteria</h4>
                <p class="text-sm text-slate-600">Nie znaleziono aktywnych naborów dla wybranych parametrów. Zmień frazę wyszukiwania lub zobacz wszystkie oferty.</p>
                <a href="{{ route('announcements.index') }}" class="inline-block px-5 py-2.5 bg-blue-900 text-white font-bold text-xs rounded-xl shadow hover:bg-blue-950 transition">
                    Pokaż wszystkie ogłoszenia miejskie
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($announcements as $a)
                    <article class="glass-card rounded-2xl p-6 flex flex-col justify-between relative group border border-slate-200/80 hover:border-slate-300">
                        <div class="space-y-4">
                            <!-- Unit & Category Badges -->
                            <div class="flex justify-between items-start gap-2">
                                <span class="bg-slate-900 text-white text-[11px] font-extrabold px-3 py-1 rounded-full shadow-sm truncate">
                                    {{ $a->tenant->name ?? 'Urząd Miasta' }}
                                </span>
                                <span class="bg-blue-50 text-blue-900 text-[11px] font-extrabold px-2.5 py-1 rounded-full border border-blue-100 shrink-0">
                                    {{ $a->category }}
                                </span>
                            </div>

                            <!-- Title -->
                            <div>
                                <h4 class="text-lg font-extrabold text-slate-950 group-hover:text-blue-800 transition leading-snug">
                                    <a href="{{ route('announcements.show', $a->id) }}" class="focus:outline-none focus:underline">
                                        {{ $a->title }}
                                    </a>
                                </h4>
                                @if($a->position_code)
                                    <span class="inline-block text-[11px] font-mono text-slate-400 mt-1 font-semibold">
                                        Nr naboru: {{ $a->position_code }}
                                    </span>
                                @endif
                            </div>

                            <!-- Description Snippet -->
                            <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
                                {{ Str::limit($a->description, 130) }}
                            </p>

                            <!-- Meta Info Grid -->
                            <dl class="grid grid-cols-2 gap-2 py-3 border-y border-slate-100 text-xs">
                                <div>
                                    <dt class="text-slate-400 font-semibold">Lokalizacja:</dt>
                                    <dd class="font-bold text-slate-800 flex items-center mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ $a->location }}</span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-slate-400 font-semibold">Forma zatrudnienia:</dt>
                                    <dd class="font-bold text-slate-800 flex items-center mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <span>{{ $a->contract_type }}</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Card Footer CTA -->
                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between gap-2">
                            <div class="text-[11px]">
                                <span class="text-slate-400 block font-semibold">Termin ofert:</span>
                                <span class="font-extrabold text-amber-700">
                                    {{ $a->deadline_at->format('d.m.Y') }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-2">
                                <a href="{{ route('announcements.show', $a->id) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition">
                                    Ogłoszenie
                                </a>
                                <a href="{{ route('applications.create', $a->id) }}" class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition">
                                    Aplikuj &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-10">
                {{ $announcements->links() }}
            </div>
        @endif
    </main>

    <!-- City Footer -->
    <footer class="bg-slate-950 text-slate-400 py-12 mt-auto border-t border-slate-800 text-xs" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center md:text-left">
                <div class="space-y-2">
                    <span class="text-sm font-extrabold text-white block">Oficjalny Portal Naborów Miejskich</span>
                    <p class="text-slate-400 leading-relaxed">
                        Serwis publikacji naborów urzędniczych dla Urzędu Miasta oraz miejskich jednostek organizacyjnych zgodnie z ustawą o pracownikach samorządowych.
                    </p>
                </div>

                <div class="space-y-2">
                    <span class="text-sm font-extrabold text-white block">Miejskie Jednostki Organizacyjne</span>
                    <ul class="space-y-1 text-slate-400">
                        <li>• Urząd Miasta (Wydziały Centralne)</li>
                        <li>• Miejski Ośrodek Pomocy Społecznej</li>
                        <li>• Miejski Zarząd Dróg i Komunikacji</li>
                        <li>• Miejski Ośrodek Sportu i Rekreacji</li>
                    </ul>
                </div>

                <div class="space-y-2">
                    <span class="text-sm font-extrabold text-white block">Informacje i Logowanie</span>
                    <ul class="space-y-1 text-slate-400">
                        <li><a href="{{ route('results.index') }}" class="underline hover:text-white">Archiwum Wyników Naborów (BIP)</a></li>
                        <li><a href="{{ route('login') }}" class="text-amber-400 font-bold underline hover:text-white">Logowanie do Panelu Urzędnika / Admina</a></li>
                        <li>Standard dostępności cyfrowej WCAG 2.1 AA</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-900 pt-6 text-center text-slate-500 text-[11px]">
                &copy; {{ date('Y') }} Biuletyn Informacji Publicznej — Urząd Miasta. Wszelkie prawa zastrzeżone.
            </div>
        </div>
    </footer>
</body>
</html>
