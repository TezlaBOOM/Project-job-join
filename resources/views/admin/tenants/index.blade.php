<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin - Zarządzanie Instancjami JST</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 font-sans">
    <main class="max-w-7xl mx-auto px-4 py-8 space-y-8">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded text-green-900 font-medium">
                {{ session('success') }}
            </div>
        @endif

        <div>
            <h1 class="text-2xl font-bold text-slate-900">Zarządzanie Instancjami JST (Multi-Tenant)</h1>
            <p class="text-sm text-gray-600">Twórz i zarządzaj odseparowanymi instancjami urzędów miast, gmin i starostw.</p>
        </div>

        <!-- Add Tenant Form -->
        <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-sm">
            <h2 class="text-lg font-bold text-slate-900 mb-4">Dodaj Nową Instancję JST</h2>
            <form action="{{ route('admin.tenants.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-800 mb-1">Nazwa Urzędu / JST *</label>
                    <input type="text" id="name" name="name" placeholder="np. Urząd Gminy Zabierzow" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>

                <div>
                    <label for="slug" class="block text-sm font-semibold text-gray-800 mb-1">Identyfikator / Slug *</label>
                    <input type="text" id="slug" name="slug" placeholder="np. zabierzow" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-mono">
                </div>

                <div>
                    <label for="retention_days" class="block text-sm font-semibold text-gray-800 mb-1">Retencja RODO (dni) *</label>
                    <input type="number" id="retention_days" name="retention_days" value="90" required class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                </div>

                <div class="md:col-span-3 flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-900 hover:bg-blue-950 text-white font-bold text-sm rounded shadow">
                        Utwórz Instancję JST
                    </button>
                </div>
            </form>
        </div>

        <!-- Tenants List -->
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-left text-sm text-gray-700 border-collapse">
                <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-600 border-b border-gray-200">
                    <tr>
                        <th class="py-3 px-4">Nazwa JST</th>
                        <th class="py-3 px-4">Slug</th>
                        <th class="py-3 px-4">Ogłoszenia</th>
                        <th class="py-3 px-4">Aplikacje</th>
                        <th class="py-3 px-4">Retencja RODO</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Akcje</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($tenants as $t)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900">{{ $t->name }}</td>
                            <td class="py-3.5 px-4 font-mono text-xs">{{ $t->slug }}</td>
                            <td class="py-3.5 px-4 font-bold">{{ $t->announcements_count }}</td>
                            <td class="py-3.5 px-4 font-bold">{{ $t->candidate_applications_count }}</td>
                            <td class="py-3.5 px-4">{{ $t->retention_days }} dni</td>
                            <td class="py-3.5 px-4">
                                @if($t->status === 'active')
                                    <span class="bg-green-100 text-green-800 text-xs px-2.5 py-1 rounded font-bold">Aktywna</span>
                                @else
                                    <span class="bg-red-100 text-red-800 text-xs px-2.5 py-1 rounded font-bold">Zawieszona</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <form action="{{ route('admin.tenants.toggle-status', $t->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold {{ $t->status === 'active' ? 'text-red-700' : 'text-green-700' }} hover:underline">
                                        {{ $t->status === 'active' ? 'Zawieś' : 'Aktywuj' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="p-4 border-t border-gray-100">
                {{ $tenants->links() }}
            </div>
        </div>
    </main>
</body>
</html>
