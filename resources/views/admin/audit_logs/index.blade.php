<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin - Dziennik Audytowy (KRI)</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans">
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Dziennik Zdarzeń i Audytu (Krajowe Ramy Interoperacyjności)</h1>
            <p class="text-sm text-gray-600">Niezmienny rejestr wszystkich operacji zapisujących wykonywanych w systemie.</p>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            @if($auditLogs->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    Brak zarejestrowanych wpisów w dzienniku audytowym.
                </div>
            @else
                <table class="w-full text-left text-sm text-gray-700 border-collapse">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-600 border-b border-gray-200">
                        <tr>
                            <th class="py-3 px-4">Data i czas</th>
                            <th class="py-3 px-4">Instancja JST</th>
                            <th class="py-3 px-4">Użytkownik</th>
                            <th class="py-3 px-4">Akcja / Ścieżka</th>
                            <th class="py-3 px-4">Adres IP</th>
                            <th class="py-3 px-4">Kontekst (Payload)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-mono text-xs">
                        @foreach($auditLogs as $log)
                            <tr class="hover:bg-slate-50">
                                <td class="py-3 px-4 text-gray-600">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="py-3 px-4 font-bold">{{ $log->tenant->name ?? 'System Global' }}</td>
                                <td class="py-3 px-4">{{ $log->user->email ?? 'Anonimowy / System' }}</td>
                                <td class="py-3 px-4 text-blue-900 font-bold">{{ $log->action }}</td>
                                <td class="py-3 px-4">{{ $log->ip_address }}</td>
                                <td class="py-3 px-4 max-w-xs truncate text-gray-500">
                                    {{ json_encode($log->payload, JSON_UNESCAPED_UNICODE) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4 border-t border-gray-100">
                    {{ $auditLogs->links() }}
                </div>
            @endif
        </div>
    </main>
</body>
</html>
