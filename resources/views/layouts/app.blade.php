<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Oficjalny portal rekrutacyjny Urzędu Miasta. Aktualne nabory na stanowiska urzędnicze i pomocnicze.')">
    <title>@yield('title', 'Nabory na stanowiska') - {{ \App\Models\Setting::get('office_name', 'Urząd Miasta') }}</title>

    <!-- Skrypt inicjalizacji motywu przed renderowaniem (zapobiega migotaniu) -->
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('theme');
                if (theme === 'dark' || theme === 'light') {
                    document.documentElement.setAttribute('data-theme', theme);
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }
            } catch(e) {}
        })();
    </script>

    @if(file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}">
    @yield('styles')
</head>
<body>
    <a href="#main-content" class="skip-link">Przejdź do treści głównej</a>

    <header class="site-header" role="banner">
        <div class="container header-inner">
            <a href="{{ route('public.offers.index') }}" class="brand-link" aria-label="Strona główna naborów">
                <div class="brand-logo-emblem" aria-hidden="true">🏛️</div>
                <div>
                    <div class="brand-title">{{ \App\Models\Setting::get('office_name', 'Urząd Miasta') }}</div>
                    <div class="brand-sub">Oficjalny Biuletyn Naborów i Ofert Pracy</div>
                </div>
            </a>

            <nav aria-label="Nawigacja główna">
                <ul class="header-nav">
                    <li><a href="{{ route('public.offers.index') }}">Oferty pracy</a></li>
                    @if(\App\Models\Setting::get('bip_url'))
                        <li><a href="{{ \App\Models\Setting::get('bip_url') }}" target="_blank" rel="noopener noreferrer">BIP <span class="sr-only">(otwiera w nowej karcie)</span></a></li>
                    @endif
                    <li><a href="{{ route('public.accessibility') }}">Dostępność</a></li>
                    <li>
                        <button type="button" id="theme-switcher-btn" class="theme-toggle-btn" aria-label="Zmień motyw kolorystyczny (jasny, ciemny, automatyczny)">
                            <span id="theme-btn-icon" aria-hidden="true">🌓</span>
                            <span id="theme-btn-label">Motyw</span>
                        </button>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="main-content" role="main" style="flex: 1 0 auto; padding: 2rem 0;">
        <div class="container">
            @if(session('status'))
                <div class="alert alert-success" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <footer class="site-footer" role="contentinfo">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h4>{{ \App\Models\Setting::get('office_name', 'Urząd Miasta') }}</h4>
                    <p>{{ \App\Models\Setting::get('office_address', 'ul. Urzędowa 1, 00-001 Miasto') }}</p>
                    <p>Tel: {{ \App\Models\Setting::get('office_phone', '12 345 67 89') }}</p>
                    <p>E-mail: {{ \App\Models\Setting::get('office_email', 'kadry@miasto.gov.pl') }}</p>
                </div>
                <div class="footer-col">
                    <h4>Informacje prawne</h4>
                    <ul>
                        <li><a href="{{ route('public.privacy') }}">Polityka prywatności i RODO</a></li>
                        <li><a href="{{ route('public.accessibility') }}">Deklaracja dostępności cyfrowej</a></li>
                        @if(\App\Models\Setting::get('bip_url'))
                            <li><a href="{{ \App\Models\Setting::get('bip_url') }}" target="_blank" rel="noopener noreferrer">Biuletyn Informacji Publicznej</a></li>
                        @endif
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Dostępność cyfrowa</h4>
                    <p>Portal rekrutacyjny został zaprojektowany zgodnie ze standardami WCAG 2.1 na poziomie AA, zapewniając pełną dostępność dla osób ze szczególnymi potrzebami.</p>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('office_name', 'Urząd Miasta') }}. Wszelkie prawa zastrzeżone.</div>
                <div>Zgodność: WCAG 2.1 AA | EN 301 549</div>
            </div>
        </div>
    </footer>

    <!-- Skrypt obsługi przełącznika motywów -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var themeBtn = document.getElementById('theme-switcher-btn');
            var themeLabel = document.getElementById('theme-btn-label');
            var themeIcon = document.getElementById('theme-btn-icon');

            var themes = ['auto', 'light', 'dark'];
            var labels = {
                'auto': 'Auto',
                'light': 'Jasny',
                'dark': 'Ciemny'
            };
            var icons = {
                'auto': '🌓',
                'light': '☀️',
                'dark': '🌙'
            };

            function getSavedTheme() {
                return localStorage.getItem('theme') || 'auto';
            }

            function updateUI(theme) {
                if (theme === 'dark' || theme === 'light') {
                    document.documentElement.setAttribute('data-theme', theme);
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }
                themeLabel.textContent = labels[theme];
                themeIcon.textContent = icons[theme];
            }

            var current = getSavedTheme();
            updateUI(current);

            themeBtn.addEventListener('click', function() {
                var current = getSavedTheme();
                var nextIndex = (themes.indexOf(current) + 1) % themes.length;
                var nextTheme = themes[nextIndex];

                if (nextTheme === 'auto') {
                    localStorage.removeItem('theme');
                } else {
                    localStorage.setItem('theme', nextTheme);
                }
                updateUI(nextTheme);
            });
        });
    </script>
    @yield('scripts')
</body>
</html>
