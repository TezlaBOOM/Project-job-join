<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Panel administracyjny') - Portal Rekrutacyjny</title>

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
    <style>
        .role-badge {
            font-size: 0.72rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-block;
        }
        .role-admin { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .role-recruiter { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .role-viewer { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        [data-theme="dark"] .role-admin { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border-color: rgba(239, 68, 68, 0.3); }
        [data-theme="dark"] .role-recruiter { background: rgba(59, 130, 246, 0.2); color: #93c5fd; border-color: rgba(59, 130, 246, 0.3); }
        [data-theme="dark"] .role-viewer { background: #1e293b; color: #cbd5e1; border-color: #334155; }
    </style>
    @yield('styles')
</head>
<body>
    <a href="#admin-main" class="skip-link">Przejdź do treści głównej</a>

    @auth
        <div class="admin-layout">
            <!-- PIONOWY SIDEBAR (Vertical Menu) -->
            <aside class="admin-sidebar" id="admin-sidebar" role="navigation" aria-label="Menu boczne panelu">
                <!-- Nagłówek Sidebara -->
                <div class="admin-sidebar-header">
                    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
                        <span class="admin-brand-icon">🏛️</span>
                        <div>
                            <div class="admin-brand-title">Rekrutacja UM</div>
                            <div class="admin-brand-sub">Panel Zarządzania</div>
                        </div>
                    </a>
                    <span class="badge" style="font-size: 0.7rem; padding: 0.15rem 0.5rem;">Intranet</span>
                </div>

                <!-- Profil zalogowanego użytkownika -->
                <div class="admin-sidebar-profile">
                    <div class="admin-user-avatar" aria-hidden="true">
                        {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="admin-user-info">
                        <div class="admin-user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</div>
                        <div>
                            @if(auth()->user()->isAdmin())
                                <span class="role-badge role-admin">Administrator</span>
                            @elseif(auth()->user()->isRecruiter())
                                <span class="role-badge role-recruiter">Rekruter</span>
                            @else
                                <span class="role-badge role-viewer">Przeglądający</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Pionowa lista linków nawigacyjnych -->
                <nav class="admin-sidebar-nav">
                    <div class="admin-nav-group-title">Główne moduły</div>
                    <ul class="admin-vertical-menu">
                        <li>
                            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                                <span class="menu-icon" aria-hidden="true">📊</span>
                                <span>Pulpit</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.offers.index') }}" class="{{ request()->routeIs('admin.offers.*') ? 'active' : '' }}">
                                <span class="menu-icon" aria-hidden="true">💼</span>
                                <span>Oferty pracy</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.applications.index') }}" class="{{ request()->routeIs('admin.applications.*') ? 'active' : '' }}">
                                <span class="menu-icon" aria-hidden="true">👥</span>
                                <span>Kandydaci i zgłoszenia</span>
                            </a>
                        </li>
                    </ul>

                    <div class="admin-nav-group-title">Konfiguracja naborów</div>
                    <ul class="admin-vertical-menu">
                        <li>
                            <a href="{{ route('admin.dictionaries.index', ['type' => 'departments']) }}" class="{{ request()->routeIs('admin.dictionaries.*') ? 'active' : '' }}">
                                <span class="menu-icon" aria-hidden="true">📚</span>
                                <span>Słowniki</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.filters.index') }}" class="{{ request()->routeIs('admin.filters.*') ? 'active' : '' }}">
                                <span class="menu-icon" aria-hidden="true">🔍</span>
                                <span>Filtry naborów</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.forms.index') }}" class="{{ request()->routeIs('admin.forms.*') ? 'active' : '' }}">
                                <span class="menu-icon" aria-hidden="true">📝</span>
                                <span>Szablony formularzy</span>
                            </a>
                        </li>
                    </ul>

                    @if(auth()->user()->isAdmin())
                        <div class="admin-nav-group-title">Administracja</div>
                        <ul class="admin-vertical-menu">
                            <li>
                                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                    <span class="menu-icon" aria-hidden="true">👤</span>
                                    <span>Użytkownicy i role</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.logs.index') }}" class="{{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                                    <span class="menu-icon" aria-hidden="true">📜</span>
                                    <span>Logi audytu</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                                    <span class="menu-icon" aria-hidden="true">⚙️</span>
                                    <span>Ustawienia urzędu</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.email-templates.index') }}" class="{{ request()->routeIs('admin.email-templates.*') ? 'active' : '' }}">
                                    <span class="menu-icon" aria-hidden="true">✉️</span>
                                    <span>Szablony e-mail</span>
                                </a>
                            </li>
                        </ul>
                    @endif
                </nav>

                <!-- Dolna część sidebara: Podgląd, Motyw, Wylogowanie -->
                <div class="admin-sidebar-footer">
                    <a href="{{ route('public.offers.index') }}" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: flex-start; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <span aria-hidden="true">🌐</span> Podgląd portalu ↗
                    </a>

                    <div style="display: flex; gap: 0.5rem; align-items: center; justify-content: space-between;">
                        <button type="button" id="admin-theme-btn" class="theme-toggle-btn" style="flex: 1; min-height: 38px; padding: 0.35rem 0.5rem; font-size: 0.8125rem;" aria-label="Zmień motyw kolorystyczny">
                            <span id="admin-theme-icon" aria-hidden="true">🌓</span> Motyw
                        </button>
                        <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0; flex: 1;">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; min-height: 38px; padding: 0.35rem 0.5rem;">
                                Wyloguj
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Główna część treści -->
            <div class="admin-content-wrapper">
                <header class="admin-topbar">
                    <button type="button" id="sidebar-toggle-btn" class="btn btn-secondary btn-sm admin-mobile-toggle" aria-label="Przełącz menu boczne">
                        ☰ Menu
                    </button>
                    <div style="display: flex; align-items: center; gap: 1rem; margin-left: auto;">
                        <span style="font-size: 0.85rem; color: var(--color-text-muted);">
                            Zalogowano jako: <strong>{{ auth()->user()->email }}</strong>
                        </span>
                    </div>
                </header>

                <main id="admin-main" role="main" class="admin-main-content">
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

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert" tabindex="-1" id="error-summary">
                            <h3 style="font-size: 1rem; margin-bottom: 0.5rem; font-weight: 700;">Wystąpiły błędy formularza:</h3>
                            <ul style="padding-left: 1.25rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>

                <footer style="margin-top: auto; padding: 1.25rem 2rem; border-top: 1px solid var(--color-border); font-size: 0.8125rem; color: var(--color-text-muted); text-align: center;">
                    Panel Administracyjny Rekrutacji &bull; Dostęp autoryzowany wyłącznie z sieci lokalnej (Intranet) &bull; WCAG 2.1 AA
                </footer>
            </div>
        </div>
    @else
        <!-- Widok dla gościa (np. logowanie) -->
        <main id="admin-main" role="main" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem;">
            <div style="width: 100%;">
                @if(session('status'))
                    <div class="container" style="max-width: 440px;">
                        <div class="alert alert-success" role="status">
                            {{ session('status') }}
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="container" style="max-width: 440px;">
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    @endauth

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var themeBtn = document.getElementById('admin-theme-btn');
            if (themeBtn) {
                themeBtn.addEventListener('click', function() {
                    var current = localStorage.getItem('theme') || 'auto';
                    var next = current === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('theme', next);
                    document.documentElement.setAttribute('data-theme', next);
                });
            }

            var toggleBtn = document.getElementById('sidebar-toggle-btn');
            var sidebar = document.getElementById('admin-sidebar');
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('open');
                });
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
