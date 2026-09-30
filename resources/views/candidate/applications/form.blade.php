<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formularz Aplikacji - {{ $announcement->title }}</title>
    <meta name="description" content="Formularz online składania aplikacji na stanowisko urzędnicze: {{ $announcement->title }}.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased min-h-screen flex flex-col">
    <!-- WCAG Skip Link -->
    <a href="#main-content" class="skip-link">Przejdź do głównej treści</a>

    <!-- Header Navigation -->
    <header class="gov-gradient-header text-white shadow-md" role="banner">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="text-xl font-extrabold tracking-tight">Portal Naborów JST</span>
                <span class="bg-blue-800 text-xs px-2.5 py-0.5 rounded-full text-blue-200 font-bold border border-blue-600">BIP</span>
            </div>
            <nav role="navigation" aria-label="Nawigacja">
                <a href="{{ route('announcements.show', $announcement->id) }}" class="text-xs font-bold text-blue-200 hover:text-white flex items-center space-x-1">
                    <span>&larr; Wróć do ogłoszenia</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-grow w-full" role="main">
        <!-- Application Title Card -->
        <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm mb-8 space-y-2">
            <span class="text-xs font-extrabold text-blue-900 uppercase tracking-wider block">
                {{ $announcement->tenant->name ?? 'Jednostka JST' }}
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-950">
                Formularz Aplikacyjny: {{ $announcement->title }}
            </h1>
            <p class="text-xs text-slate-500 font-medium">Termin składania wniosków upływa: <span class="font-bold text-amber-700">{{ $announcement->deadline_at->format('d.m.Y H:i') }}</span></p>
        </div>

        <!-- Form Component -->
        <form action="{{ route('applications.store', $announcement->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Section 1: Candidate Personal Data -->
            <section class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3">
                    1. Dane Osobowe Kandydata
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-bold text-slate-800 mb-1.5">Imię *</label>
                        <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                        @error('first_name')
                            <p class="mt-1 text-xs text-red-600 font-bold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="last_name" class="block text-sm font-bold text-slate-800 mb-1.5">Nazwisko *</label>
                        <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                        @error('last_name')
                            <p class="mt-1 text-xs text-red-600 font-bold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold text-slate-800 mb-1.5">Adres e-mail (do powiadomień) *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600 font-bold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-bold text-slate-800 mb-1.5">Numer telefonu kontaktowego</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+48 600 000 000" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:bg-white focus:ring-2 focus:ring-blue-600 focus:border-transparent transition">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600 font-bold" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <!-- Section 2: Document Attachments Upload -->
            <section class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3">
                    2. Załączniki i Dokumentacja (PDF, DOCX, do 10MB)
                </h2>

                <div class="space-y-4">
                    @foreach($announcement->required_documents ?? ['CV', 'List motywacyjny'] as $index => $docType)
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                            <label for="attachment_{{ $index }}" class="block text-sm font-bold text-slate-900">
                                {{ $docType }} *
                            </label>
                            <input type="file" id="attachment_{{ $index }}" name="attachments[{{ Str::slug($docType) }}]" accept=".pdf,.doc,.docx" required class="block w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-extrabold file:bg-blue-900 file:text-white hover:file:bg-blue-950 cursor-pointer">
                        </div>
                    @endforeach
                </div>
            </section>

            <!-- Section 3: RODO Consent -->
            <section class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-200 shadow-sm space-y-6">
                <h2 class="text-lg font-extrabold text-slate-900 border-b border-slate-100 pb-3">
                    3. Zgoda na Przetwarzanie Danych Osobowych (RODO)
                </h2>

                <div class="p-4 bg-blue-50/60 rounded-xl border border-blue-200/60 space-y-3 text-xs text-slate-700 leading-relaxed">
                    <p class="font-medium">
                        Wyrażam zgodę na przetwarzanie moich danych osobowych dla potrzeb niezbędnych do realizacji procesu naboru zgodnie z ustawą o ochronie danych osobowych oraz Rozporządzeniem Parlamentu Europejskiego i Rady (UE) 2016/679 (RODO).
                    </p>
                </div>

                <div class="flex items-start space-x-3 pt-2">
                    <input type="checkbox" id="rodo_consent" name="rodo_consent" value="1" required class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="rodo_consent" class="text-sm font-bold text-slate-900 cursor-pointer">
                        Oświadczam, że zapoznałem/am się z klauzulą informacyjną RODO i wyrażam zgodę na przetwarzanie danych. *
                    </label>
                </div>
                @error('rodo_consent')
                    <p class="text-xs text-red-600 font-bold" role="alert">{{ $message }}</p>
                @enderror
            </section>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end space-x-4 pt-4">
                <a href="{{ route('announcements.show', $announcement->id) }}" class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-sm rounded-xl transition">
                    Anuluj
                </a>
                <button type="submit" class="px-8 py-3.5 bg-blue-900 hover:bg-blue-950 text-white font-extrabold text-sm rounded-xl shadow-lg focus:ring-4 focus:ring-blue-400 transition">
                    Wyślij Wniosek Aplikacyjny &rarr;
                </button>
            </div>
        </form>
    </main>
</body>
</html>
