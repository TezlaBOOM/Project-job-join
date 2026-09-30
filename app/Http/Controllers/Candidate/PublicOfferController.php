<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Filter;
use App\Models\JobCategory;
use App\Models\JobOffer;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicOfferController extends Controller
{
    public function index(Request $request): View
    {
        $query = JobOffer::query()->active()->with(['department', 'contractType', 'category', 'location']);

        // Full-text / LIKE Search
        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('requirements', 'like', "%{$term}%");
            });
        }

        // Department filter
        if ($request->filled('department')) {
            $query->where('department_id', $request->input('department'));
        }

        // Contract type filter
        if ($request->filled('contract_type')) {
            $query->where('contract_type_id', $request->input('contract_type'));
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->input('category'));
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location_id', $request->input('location'));
        }

        // Working time filter
        if ($request->filled('working_time')) {
            $query->where('working_time', $request->input('working_time'));
        }

        // Ending soon (within 7 days)
        if ($request->boolean('ending_soon')) {
            $query->where('deadline_at', '<=', now()->addDays(7));
        }

        // Sorting
        $sort = $request->input('sort', 'newest');
        if ($sort === 'ending_soonest') {
            $query->orderBy('deadline_at', 'asc');
        } else {
            $query->orderBy('published_at', 'desc')->orderBy('created_at', 'desc');
        }

        $offers = $query->paginate(12)->withQueryString();

        // Load active configured filters
        $configuredFilters = Filter::where('is_active', true)->orderBy('position')->get();

        // Dictionary values for filter selects
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $contractTypes = ContractType::where('is_active', true)->orderBy('name')->get();
        $categories = JobCategory::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $workingTimes = ['Pełny etat', '3/4 etatu', '1/2 etatu', 'Inny'];

        return view('candidate.offers.index', compact(
            'offers',
            'configuredFilters',
            'departments',
            'contractTypes',
            'categories',
            'locations',
            'workingTimes'
        ));
    }

    public function show(string $slug): View
    {
        $offer = JobOffer::where('slug', $slug)
            ->orWhere('public_id', $slug)
            ->with(['department', 'contractType', 'category', 'location', 'form.activeFields'])
            ->firstOrFail();

        // Allow previewing draft only for logged in recruiters/admins
        if ($offer->status !== 'published' && (! auth()->check() || ! auth()->user()->isViewer())) {
            abort(404);
        }

        return view('candidate.offers.show', compact('offer'));
    }
}
