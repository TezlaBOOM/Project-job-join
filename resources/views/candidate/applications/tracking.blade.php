<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Aplikacji - {{ $application->announcement->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #003366;
            color: #ffffff;
            padding: 8px;
            z-index: 100;
        }
        .skip-link:focus {
            top: 0;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 font-sans leading-normal">
    <!-- WCAG Skip Link -->
    <a href="#main-content" class="skip-link font-bold underline focus:outline-none">Przejdź do głównej treści</a>

    <!-- Header Navigation -->
    <header class="bg-blue-900 text-white shadow-md" role="banner">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="text-2xl font-extrabold tracking-wide">Portal Naborów JST</span>
                <span class="bg-blue-700 text-xs px-2.5 py-1 rounded font-semibold text-blue-100">BIP</span>
            </div>
            <nav role="navigation" aria-label="Nawigacja główna">
                <a href="{{ route('announcements.index') }}" class="text-sm font-semibold text-blue-200 hover:text-white">
                    Strona główna naborów
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content Area -->
    <main id="main-content" class="max-w-4xl mx-auto px-4 py-8 sm:px-6 lg:px-8" role="main">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 p-4 rounded text-green-900 font-medium" role="alert">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-600 p-4 rounded text-red-900 font-medium" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-600 p-4 rounded text-blue-900 font-medium" role="alert">
                {{ session('info') }}
            </div>
        @endif

        <!-- Application Status Card -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-8 space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center border-b border-gray-100 pb-4">
                <div>
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                        {{ $application->tenant->name }}
                    </div>
                    <h1 class="text-2xl font-extrabold text-blue-950">
                        {{ $application->announcement->title }}
                    </h1>
                </div>

                <!-- Status Badge -->
                <div class="mt-4 md:mt-0">
                    @if($application->is_withdrawn)
                        <span class="bg-gray-200 text-gray-800 text-sm px-4 py-1.5 rounded-full font-extrabold">
                            Wycofana przez kandydata
                        </span>
                    @elseif($application->status === 'submitted')
                        <span class="bg-blue-100 text-blue-900 text-sm px-4 py-1.5 rounded-full font-extrabold">
                            Złozona (Oczekuje na ocenę)
                        </span>
                    @elseif($application->status === 'qualified')
                        <span class="bg-green-100 text-green-900 text-sm px-4 py-1.5 rounded-full font-extrabold">
                            Zakwalifikowana formalnie
                        </span>
                    @elseif($application->status === 'rejected')
                        <span class="bg-red-100 text-red-900 text-sm px-4 py-1.5 rounded-full font-extrabold">
                            Odrzucona formalnie
                        </span>
                    @else
                        <span class="bg-purple-100 text-purple-900 text-sm px-4 py-1.5 rounded-full font-extrabold">
                            {{ ucfirst($application->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Details List -->
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="font-semibold text-gray-500">Kandydat:</dt>
                    <dd class="font-bold text-gray-900">{{ $application->first_name }} {{ $application->last_name }}</dd>
                </div>

                <div>
                    <dt class="font-semibold text-gray-500">Adres e-mail:</dt>
                    <dd class="font-bold text-gray-900">{{ $application->email }}</dd>
                </div>

                <div>
                    <dt class="font-semibold text-gray-500">Data złożenia zgłoszenia:</dt>
                    <dd class="font-bold text-gray-900">{{ $application->created_at->format('d.m.Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="font-semibold text-gray-500">Termin naboru:</dt>
                    <dd class="font-bold text-gray-900">{{ $application->announcement->deadline_at->format('d.m.Y H:i') }}</dd>
                </div>
            </dl>

            <!-- Actions / Withdrawal -->
            <div class="pt-6 border-t border-gray-100 flex justify-between items-center">
                <a href="{{ route('announcements.show', $application->announcement_id) }}" class="text-sm font-semibold text-blue-900 hover:underline">
                    &larr; Podgląd ogłoszenia
                </a>

                @if(!$application->is_withdrawn && now()->lessThanOrEqualTo($application->announcement->deadline_at))
                    <form action="{{ route('tracking.withdraw', $application->tracking_token) }}" method="POST" onsubmit="return confirm('Czy na pewno chcesz wycofać swoje zgłoszenie? Akcji nie można cofnąć.');">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded shadow focus:ring-2 focus:ring-red-500">
                            Wycofaj zgłoszenie
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 mt-12 py-8" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 text-center text-sm space-y-2">
            <p>&copy; {{ date('Y') }} {{ $application->tenant->name }}. Wszelkie prawa zastrzeżone.</p>
        </div>
    </footer>
</body>
</html>
