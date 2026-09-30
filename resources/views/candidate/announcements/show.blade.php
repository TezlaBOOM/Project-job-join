<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $announcement->title }} - {{ $announcement->tenant->name ?? 'Nabór JST' }}</title>
    <meta name="description" content="Szczegóły ogłoszenia o naborze na stanowisko: {{ $announcement->title }}. Wymagania, zakres obowiązków i składanie wniosku.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">
    <!-- WCAG Skip Link -->
    <a href="#main-content" class="skip-link">Przejdź do głównej treści</a>

    <!-- Top Navigation -->
    <header class="gov-gradient-header text-white shadow-md" role="banner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="text-xl font-extrabold tracking-tight">Portal Naborów JST</span>
                <span class="bg-blue-800 text-xs px-2.5 py-0.5 rounded-full text-blue-200 font-bold border border-blue-600">BIP</span>
            </div>
            <nav role="navigation" aria-label="Nawigacja">
                <a href="{{ route('announcements.index') }}" class="text-xs font-bold text-blue-200 hover:text-white flex items-center space-x-1">
                    <span>&larr; Powrót do listy naborów</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full" role="main">
        <!-- Breadcrumbs -->
        <nav aria-label="Ścieżka nawigacji" class="mb-6 text-xs text-slate-500 font-medium">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('announcements.index') }}" class="hover:underline text-slate-700">Ogłoszenia</a></li>
                <li><span>/</span></li>
                <li class="text-slate-900 font-bold truncate max-w-xs">{{ $announcement->title }}</li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Left Main Column (Announcement Details) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Header Banner Card -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="bg-blue-50 text-blue-900 font-extrabold text-xs px-3 py-1 rounded-full border border-blue-100">
                            {{ $announcement->tenant->name ?? 'Jednostka JST' }}
                        </span>
                        <span class="bg-slate-100 text-slate-700 text-xs font-bold px-3 py-1 rounded-full border border-slate-200">
                            {{ $announcement->category }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-950 leading-tight">
                        {{ $announcement->title }}
                    </h1>

                    <dl class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-100 text-xs">
                        <div>
                            <dt class="text-slate-400 font-semibold">Kod stanowiska:</dt>
                            <dd class="font-bold text-slate-800 font-mono mt-0.5">{{ $announcement->position_code ?? 'Brak' }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Miejscowość:</dt>
                            <dd class="font-bold text-slate-800 mt-0.5">{{ $announcement->location }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-400 font-semibold">Rodzaj umowy:</dt>
                            <dd class="font-bold text-slate-800 mt-0.5">{{ $announcement->contract_type }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Scope of Duties Card -->
                <section aria-labelledby="duties-heading" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4">
                    <h2 id="duties-heading" class="text-lg font-extrabold text-slate-900 flex items-center space-x-2 border-b border-slate-100 pb-3">
                        <svg class="w-5 h-5 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span>Główne Obowiązki na Stanowisku</span>
                    </h2>
                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $announcement->scope_of_duties }}
                    </div>
                </section>

                <!-- Requirements Card (Formal vs Merit) -->
                <section aria-labelledby="requirements-heading" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                    <h2 id="requirements-heading" class="text-lg font-extrabold text-slate-900 flex items-center space-x-2 border-b border-slate-100 pb-3">
                        <svg class="w-5 h-5 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Wymagania dla Kandydatów</span>
                    </h2>

                    <!-- Formal Requirements -->
                    <div class="space-y-2 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <h3 class="text-xs font-extrabold text-blue-900 uppercase tracking-wider">
                            Wymagania Niezbędne (Formalne)
                        </h3>
                        <p class="text-sm text-slate-800 leading-relaxed whitespace-pre-line font-medium">
                            {{ $announcement->requirements_formal }}
                        </p>
                    </div>

                    <!-- Merit Requirements -->
                    @if($announcement->requirements_merit)
                        <div class="space-y-2 bg-amber-50/50 p-4 rounded-xl border border-amber-200/60">
                            <h3 class="text-xs font-extrabold text-amber-900 uppercase tracking-wider">
                                Wymagania Dodatkowe (Merytoryczne)
                            </h3>
                            <p class="text-sm text-slate-800 leading-relaxed whitespace-pre-line font-medium">
                                {{ $announcement->requirements_merit }}
                            </p>
                        </div>
                    @endif
                </section>

                <!-- Full Description Card -->
                <section aria-labelledby="description-heading" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4">
                    <h2 id="description-heading" class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3">
                        Informacje Dodatkowe o Naborze
                    </h2>
                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                        {{ $announcement->description }}
                    </div>
                </section>

                <!-- Required Documents Checklist Card -->
                <section aria-labelledby="documents-heading" class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-4">
                    <h2 id="documents-heading" class="text-lg font-extrabold text-slate-900 flex items-center space-x-2 border-b border-slate-100 pb-3">
                        <svg class="w-5 h-5 text-blue-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>Wymagane Dokumenty i Oświadczenia</span>
                    </h2>

                    <ul class="space-y-2 text-sm text-slate-800 font-medium">
                        @foreach($announcement->required_documents ?? [] as $doc)
                            <li class="flex items-center space-x-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <svg class="w-5 h-5 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span>{{ $doc }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </div>

            <!-- Right Sidebar Column (CTA & Deadline Box) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Apply Sticky Box -->
                <div class="glass-card rounded-2xl p-6 border border-slate-200 shadow-lg space-y-6 sticky top-6">
                    <!-- Deadline Box -->
                    <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-center space-y-1">
                        <span class="text-xs font-bold text-amber-900 uppercase tracking-wider block">Termin składania ofert</span>
                        <span class="text-xl font-extrabold text-amber-950 block">
                            {{ $announcement->deadline_at->format('d.m.Y H:i') }}
                        </span>
                        <span class="text-xs text-amber-800 font-medium block">
                            Pozostało: {{ now()->diffInDays($announcement->deadline_at, false) }} dni
                        </span>
                    </div>

                    <!-- Apply CTA Button -->
                    <a href="{{ route('applications.create', $announcement->id) }}" class="block w-full text-center py-4 px-6 bg-blue-900 hover:bg-blue-950 text-white font-extrabold text-sm rounded-xl shadow-lg focus:ring-4 focus:ring-blue-400 transition">
                        Złóż Wniosek Online &rarr;
                    </a>

                    <div class="text-xs text-slate-500 text-center space-y-2 border-t border-slate-100 pt-4">
                        <p>Aplikacja przez portal nie wymaga zakłada nia konta. Otrzymasz unikalny kod do śledzenia statusu.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 py-8 mt-12 text-xs border-t border-slate-800" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-2">
            <p>&copy; {{ date('Y') }} {{ $announcement->tenant->name ?? 'JST' }}. Wszelkie prawa zastrzeżone.</p>
        </div>
    </footer>
</body>
</html>
