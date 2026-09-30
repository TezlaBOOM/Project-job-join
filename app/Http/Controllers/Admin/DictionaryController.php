<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\JobCategory;
use App\Models\Location;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DictionaryController extends Controller
{
    protected array $types = [
        'departments' => ['model' => Department::class, 'title' => 'Wydziały i Jednostki'],
        'contract_types' => ['model' => ContractType::class, 'title' => 'Rodzaje umów'],
        'job_categories' => ['model' => JobCategory::class, 'title' => 'Kategorie stanowisk'],
        'locations' => ['model' => Location::class, 'title' => 'Lokalizacje / Miejsca pracy'],
    ];

    public function index(string $type = 'departments'): View
    {
        if (! array_key_exists($type, $this->types)) {
            abort(404);
        }

        $config = $this->types[$type];
        $modelClass = $config['model'];
        $items = $modelClass::withCount('jobOffers')->orderBy('name')->get();

        return view('admin.dictionaries.index', [
            'currentType' => $type,
            'title' => $config['title'],
            'types' => $this->types,
            'items' => $items,
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        if (! array_key_exists($type, $this->types)) {
            abort(404);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $modelClass = $this->types[$type]['model'];
        $item = $modelClass::create([
            'name' => trim($request->input('name')),
            'is_active' => true,
        ]);

        AuditLogger::log("dictionary_{$type}_created", $item, null, $item->toArray());

        return back()->with('status', "Pozycja „{$item->name}” została dodana do słownika.");
    }

    public function toggle(string $type, int $id): RedirectResponse
    {
        if (! array_key_exists($type, $this->types)) {
            abort(404);
        }

        $modelClass = $this->types[$type]['model'];
        $item = $modelClass::findOrFail($id);
        $old = $item->toArray();

        $item->is_active = ! $item->is_active;
        $item->save();

        AuditLogger::log("dictionary_{$type}_toggled", $item, $old, $item->toArray());

        return back()->with('status', "Zmieniono status pozycji „{$item->name}”.");
    }
}
