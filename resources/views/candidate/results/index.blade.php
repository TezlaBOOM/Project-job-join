<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wyniki Naborów - Biuletyn Informacji Publicznej (BIP)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 font-sans leading-normal">
    <!-- Header Navigation -->
    <header class="bg-blue-900 text-white shadow-md" role="banner">
        <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <span class="text-2xl font-extrabold tracking-wide">Portal Naborów JST</span>
                <span class="bg-blue-700 text-xs px-2.5 py-1 rounded font-semibold text-blue-100">BIP</span>
            </div>
            <nav role="navigation" aria-label="Nawigacja główna">
                <a href="{{ route('announcements.index') }}" class="text-sm font-semibold text-blue-200 hover:text-white">
                    Aktualne Ogłoszenia &rarr;
                </a>
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-8" role="main">
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-blue-950 mb-2">Informacja o Wynikach Naborów (BIP)</h1>
            <p class="text-gray-700">Wykaz zakonczonych naborow na stanowiska urzednicze zgodnie z ustawa o pracownikach samorzadowych.</p>
        </div>

        <div class="space-y-6">
            @if($protocols->isEmpty())
                <div class="bg-white p-8 rounded-lg shadow-sm text-center border border-gray-200">
                    Brak opublikowanych protokołów wyników naboru.
                </div>
            @else
                @foreach($protocols as $p)
                    <article class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm space-y-3">
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            {{ $p->announcement->tenant->name ?? 'JST' }}
                        </div>
                        <h2 class="text-xl font-bold text-blue-950">
                            {{ $p->announcement->title }}
                        </h2>
                        <div class="bg-gray-50 p-4 rounded text-xs font-mono whitespace-pre-wrap border border-gray-100 text-gray-800">
                            {{ $p->summary }}
                        </div>
                        <div class="text-xs text-gray-500 text-right">
                            Data publikacji protokołu: {{ $p->created_at->format('d.m.Y H:i') }}
                        </div>
                    </article>
                @endforeach

                <div class="mt-6">
                    {{ $protocols->links() }}
                </div>
            @endif
        </div>
    </main>
</body>
</html>
