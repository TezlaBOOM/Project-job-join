<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie do Systemu Naborów JST</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 text-slate-100 font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4">
    <!-- Login Container -->
    <div class="w-full max-w-md space-y-6">
        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-900 text-white flex items-center justify-center font-extrabold text-2xl shadow-xl mx-auto border border-blue-400/30">
                JST
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">
                Panel Urzędnika JST
            </h1>
            <p class="text-xs text-slate-400">Portal Logowania Rekruterów, Moderatorów i Administratorów</p>
        </div>

        <!-- Card Form -->
        <div class="bg-slate-800/90 rounded-2xl p-8 border border-slate-700/80 shadow-2xl space-y-6 backdrop-blur-md">
            @if(session('success'))
                <div class="p-3.5 bg-emerald-950/80 border border-emerald-500/50 rounded-xl text-emerald-200 text-xs font-bold text-center">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="p-3.5 bg-rose-950/80 border border-rose-500/50 rounded-xl text-rose-200 text-xs font-bold text-center">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Adres E-mail *</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-sm font-semibold text-white placeholder-slate-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    @error('email')
                        <p class="mt-1 text-xs text-rose-400 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5">Hasło *</label>
                    <input type="password" id="password" name="password" required class="w-full px-4 py-3 bg-slate-900 border border-slate-700 rounded-xl text-sm font-semibold text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                    @error('password')
                        <p class="mt-1 text-xs text-rose-400 font-bold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400 pt-1">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-blue-600 focus:ring-blue-500">
                        <span>Zapamiętaj sesję</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 px-6 bg-blue-600 hover:bg-blue-500 text-white font-extrabold text-sm rounded-xl shadow-lg focus:ring-4 focus:ring-blue-400 transition">
                    Zaloguj się do Panelu &rarr;
                </button>
            </form>

            <!-- Quick Auto-Fill Demo Accounts (Enabled strictly in local environment only) -->
            @if(app()->environment('local'))
                <div class="pt-4 border-t border-slate-700/60 space-y-2">
                    <span class="text-[11px] text-slate-400 font-semibold block text-center uppercase tracking-wider">Tryb deweloperski (Konta testowe):</span>
                    <div class="grid grid-cols-3 gap-2 text-[11px] font-bold">
                        <button type="button" onclick="fillForm('admin@portal.pl')" class="py-2 px-1 bg-slate-700 hover:bg-slate-600 text-blue-300 rounded-lg text-center truncate border border-slate-600">
                            Superadmin
                        </button>
                        <button type="button" onclick="fillForm('rekruter@zabierzow.pl')" class="py-2 px-1 bg-slate-700 hover:bg-slate-600 text-amber-300 rounded-lg text-center truncate border border-slate-600">
                            Rekruter
                        </button>
                        <button type="button" onclick="fillForm('moderator@krakow.pl')" class="py-2 px-1 bg-slate-700 hover:bg-slate-600 text-purple-300 rounded-lg text-center truncate border border-slate-600">
                            Moderator
                        </button>
                    </div>
                </div>

                <script>
                    function fillForm(email) {
                        document.getElementById('email').value = email;
                        document.getElementById('password').value = 'password';
                    }
                </script>
            @endif
        </div>

        <div class="text-center">
            <a href="{{ route('announcements.index') }}" class="text-xs text-slate-400 hover:text-white underline">
                &larr; Wróć do portalu ogłoszeń publicznych
            </a>
        </div>
    </div>
</body>
</html>
