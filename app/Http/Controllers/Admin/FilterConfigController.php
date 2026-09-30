<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Filter;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FilterConfigController extends Controller
{
    public function index(): View
    {
        $filters = Filter::orderBy('position')->get();

        return view('admin.filters.index', compact('filters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'key' => ['required', 'string', 'max:50', 'unique:filters,key'],
            'source' => ['required', 'in:dictionary,field'],
            'source_ref' => ['required', 'string', 'max:50'],
            'control_type' => ['required', 'in:select,checkbox,text,date_range'],
        ]);

        $maxPos = Filter::max('position') ?? 0;
        $validated['position'] = $maxPos + 1;
        $validated['is_active'] = true;

        $filter = Filter::create($validated);
        AuditLogger::log('filter_created', $filter, null, $filter->toArray());

        return back()->with('status', "Filtr „{$filter->label}” został dodany.");
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $filter = Filter::findOrFail($id);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:100'],
        ]);

        $old = $filter->toArray();
        $filter->update($validated);

        AuditLogger::log('filter_updated', $filter, $old, $filter->toArray());

        return back()->with('status', 'Etykieta filtra została zmieniona.');
    }

    public function toggle(int $id): RedirectResponse
    {
        $filter = Filter::findOrFail($id);
        $old = $filter->toArray();

        $filter->is_active = ! $filter->is_active;
        $filter->save();

        AuditLogger::log('filter_toggled', $filter, $old, $filter->toArray());

        return back()->with('status', "Status filtra „{$filter->label}” został zmieniony.");
    }

    public function move(int $id, string $direction): RedirectResponse
    {
        $filter = Filter::findOrFail($id);
        $currentPos = $filter->position;

        if ($direction === 'up') {
            $prev = Filter::where('position', '<', $currentPos)->orderBy('position', 'desc')->first();
            if ($prev) {
                $filter->position = $prev->position;
                $prev->position = $currentPos;
                $filter->save();
                $prev->save();
            }
        } elseif ($direction === 'down') {
            $next = Filter::where('position', '>', $currentPos)->orderBy('position', 'asc')->first();
            if ($next) {
                $filter->position = $next->position;
                $next->position = $currentPos;
                $filter->save();
                $next->save();
            }
        }

        return back()->with('status', 'Kolejność filtrów została zaktualizowana.');
    }
}
