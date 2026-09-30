<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ContractType;
use App\Models\Department;
use App\Models\Form;
use App\Models\JobCategory;
use App\Models\JobOffer;
use App\Models\Location;
use App\Services\AuditLogger;
use App\Services\HtmlSanitizer;
use App\Services\RecruitmentClosureService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobOfferController extends Controller
{
    public function index(Request $request): View
    {
        $query = JobOffer::with(['department', 'contractType', 'category', 'location', 'creator'])
            ->withCount([
                'applications',
                'applications as qualified_applications_count' => function ($q) {
                    $q->where('status', Application::STATUS_QUALIFIED);
                },
            ]);

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where('title', 'like', "%{$term}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->input('department'));
        }

        $offers = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $departments = Department::where('is_active', true)->get();

        return view('admin.offers.index', compact('offers', 'departments'));
    }

    public function create(): View
    {
        $departments = Department::where('is_active', true)->get();
        $contractTypes = ContractType::where('is_active', true)->get();
        $categories = JobCategory::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->get();
        $forms = Form::all();

        return view('admin.offers.create', compact('departments', 'contractTypes', 'categories', 'locations', 'forms'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'contract_type_id' => ['nullable', 'exists:contract_types,id'],
            'category_id' => ['nullable', 'exists:job_categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'working_time' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'nice_to_have' => ['nullable', 'string'],
            'offer_text' => ['nullable', 'string'],
            'required_documents' => ['nullable', 'string'],
            'form_id' => ['nullable', 'exists:forms,id'],
            'statements' => ['nullable', 'array'],
            'deadline_at' => ['required', 'date'],
            'status' => ['required', 'in:draft,published,completed,archived'],
        ]);

        // Clean HTML description according to specification
        $validated['description'] = HtmlSanitizer::clean($validated['description']);
        $validated['created_by'] = auth()->id();

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $offer = JobOffer::create($validated);
        AuditLogger::log('offer_created', $offer, null, $offer->toArray());

        return redirect()->route('admin.offers.index')->with('status', "Nabór „{$offer->title}” został utworzony.");
    }

    public function edit(string $publicId): View
    {
        $offer = JobOffer::where('public_id', $publicId)->firstOrFail();
        $departments = Department::all();
        $contractTypes = ContractType::all();
        $categories = JobCategory::all();
        $locations = Location::all();
        $forms = Form::all();

        return view('admin.offers.edit', compact('offer', 'departments', 'contractTypes', 'categories', 'locations', 'forms'));
    }

    public function update(Request $request, string $publicId): RedirectResponse
    {
        $offer = JobOffer::where('public_id', $publicId)->firstOrFail();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'contract_type_id' => ['nullable', 'exists:contract_types,id'],
            'category_id' => ['nullable', 'exists:job_categories,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'working_time' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'requirements' => ['nullable', 'string'],
            'nice_to_have' => ['nullable', 'string'],
            'offer_text' => ['nullable', 'string'],
            'required_documents' => ['nullable', 'string'],
            'form_id' => ['nullable', 'exists:forms,id'],
            'statements' => ['nullable', 'array'],
            'deadline_at' => ['required', 'date'],
            'status' => ['required', 'in:draft,published,completed,archived'],
        ]);

        $validated['description'] = HtmlSanitizer::clean($validated['description']);

        if ($validated['status'] === 'published' && ! $offer->published_at) {
            $validated['published_at'] = now();
        }

        $isCompleting = $validated['status'] === JobOffer::STATUS_COMPLETED;
        $purgeRequested = $request->boolean('purge_unqualified');

        $oldValues = $offer->toArray();
        $offer->update($validated);

        AuditLogger::log('offer_updated', $offer, $oldValues, $offer->toArray());

        if ($isCompleting && $purgeRequested) {
            $closure = app(RecruitmentClosureService::class)->completeRecruitment($offer, auth()->id());
            $msg = "Nabór „{$offer->title}” został zaktualizowany i zakończony. Zachowano ofertę oraz dane wybranego kandydata ({$closure['qualified_candidates_count']}), a trwale usunięto dane i pliki CV {$closure['purged_candidates_count']} niezakwalifikowanych kandydatów.";

            return redirect()->route('admin.offers.index')->with('status', $msg);
        }

        return redirect()->route('admin.offers.index')->with('status', "Nabór „{$offer->title}” został zaktualizowany.");
    }

    public function complete(Request $request, string $publicId, RecruitmentClosureService $service): RedirectResponse
    {
        $offer = JobOffer::where('public_id', $publicId)->firstOrFail();

        $result = $service->completeRecruitment($offer, auth()->id());

        $msg = "Proces rekrutacji dla naboru „{$offer->title}” został zakończony. ";
        if ($result['qualified_candidates_count'] > 0) {
            $msg .= "Zachowano ofertę pracy oraz dane i dokumenty wygranego kandydata ({$result['qualified_candidates_count']}). ";
        } else {
            $msg .= 'Zachowano ofertę pracy (żaden kandydat nie był oznaczony jako zakwalifikowany). ';
        }
        $msg .= "Trwale usunięto dane osobowe i pliki CV {$result['purged_candidates_count']} niezakwalifikowanych kandydatów.";

        return redirect()->back()->with('status', $msg);
    }

    public function duplicate(string $publicId): RedirectResponse
    {
        $original = JobOffer::where('public_id', $publicId)->firstOrFail();

        $duplicate = $original->replicate([
            'public_id',
            'slug',
            'created_at',
            'updated_at',
        ]);

        $duplicate->title = "Kopia - {$original->title}";
        $duplicate->status = 'draft';
        $duplicate->published_at = null;
        $duplicate->created_by = auth()->id();
        $duplicate->save();

        AuditLogger::log('offer_duplicated', $duplicate, ['original_id' => $original->id], $duplicate->toArray());

        return redirect()->route('admin.offers.edit', $duplicate->public_id)
            ->with('status', 'Utworzono kopię naboru jako wersję roboczą.');
    }

    public function destroy(string $publicId): RedirectResponse
    {
        $offer = JobOffer::where('public_id', $publicId)->firstOrFail();
        $old = $offer->toArray();
        $offer->delete();

        AuditLogger::log('offer_deleted', $offer, $old, null);

        return redirect()->route('admin.offers.index')->with('status', 'Nabór został przeniesiony do kosza.');
    }
}
