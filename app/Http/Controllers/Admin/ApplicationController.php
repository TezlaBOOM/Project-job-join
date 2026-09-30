<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\CandidateCustomMail;
use App\Models\Application;
use App\Models\ApplicationFile;
use App\Models\ApplicationStatusHistory;
use App\Models\EmailTemplate;
use App\Models\JobOffer;
use App\Models\MailLog;
use App\Services\AuditLogger;
use App\Services\PdfValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Application::with(['jobOffer', 'files']);

        if ($request->filled('offer_id')) {
            $query->where('job_offer_id', $request->input('offer_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('q')) {
            $term = trim($request->input('q'));
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('reference_code', 'like', "%{$term}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $offers = JobOffer::orderBy('title')->get();

        return view('admin.applications.index', compact('applications', 'offers'));
    }

    public function show(string $publicId): View
    {
        $application = Application::where('public_id', $publicId)
            ->with(['jobOffer', 'files', 'statusHistory.user', 'mailLogs'])
            ->firstOrFail();

        AuditLogger::log('application_viewed', $application);
        $templates = EmailTemplate::all();

        return view('admin.applications.show', compact('application', 'templates'));
    }

    public function create(): View
    {
        $offers = JobOffer::where('status', 'published')->get();

        return view('admin.applications.create_manual', compact('offers'));
    }

    public function storeManual(Request $request): RedirectResponse
    {
        $request->validate([
            'job_offer_id' => ['required', 'exists:job_offers,id'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
            'cv_file' => ['nullable', 'file'],
        ]);

        $offer = JobOffer::findOrFail($request->input('job_offer_id'));

        $application = Application::create([
            'job_offer_id' => $offer->id,
            'first_name' => trim($request->input('first_name')),
            'last_name' => trim($request->input('last_name')),
            'email' => trim($request->input('email')),
            'phone' => trim($request->input('phone')),
            'address' => trim($request->input('address')),
            'status' => Application::STATUS_NEW,
            'internal_notes' => $request->input('internal_notes'),
            'consent_at' => now(),
            'source' => 'manual',
            'created_by_user_id' => auth()->id(),
        ]);

        if ($request->hasFile('cv_file')) {
            $file = $request->file('cv_file');
            [$isValid, $error] = PdfValidator::validate($file);
            if (! $isValid) {
                return back()->withInput()->withErrors(['cv_file' => $error]);
            }

            $storedName = (string) Str::uuid().'.pdf';
            $path = $file->storeAs('applications/'.$application->id, $storedName, 'local');
            $checksum = hash_file('sha256', $file->getRealPath());

            ApplicationFile::create([
                'application_id' => $application->id,
                'type' => 'cv',
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime' => 'application/pdf',
                'size' => $file->getSize(),
                'checksum' => $checksum,
            ]);
        }

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status' => 'none',
            'to_status' => Application::STATUS_NEW,
            'user_id' => auth()->id(),
            'note' => 'Wprowadzono ręcznie przez pracownika urzędu.',
            'created_at' => now(),
        ]);

        AuditLogger::log('application_created_manually', $application, null, $application->toArray());

        return redirect()->route('admin.applications.show', $application->public_id)
            ->with('status', "Zgłoszenie {$application->reference_code} zostało zarejestrowane.");
    }

    public function edit(string $publicId): View
    {
        $application = Application::where('public_id', $publicId)->with('files')->firstOrFail();

        return view('admin.applications.edit', compact('application'));
    }

    public function update(Request $request, string $publicId): RedirectResponse
    {
        $application = Application::where('public_id', $publicId)->firstOrFail();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
        ]);

        $old = $application->toArray();
        $application->update($validated);

        AuditLogger::log('application_updated', $application, $old, $application->toArray());

        return redirect()->route('admin.applications.show', $application->public_id)
            ->with('status', 'Dane zgłoszenia zostały zaktualizowane.');
    }

    public function updateStatus(Request $request, string $publicId): RedirectResponse
    {
        $application = Application::where('public_id', $publicId)->with('jobOffer')->firstOrFail();

        $request->validate([
            'status' => ['required', 'in:new,under_review,interview,rejected,qualified,cancelled'],
            'note' => ['nullable', 'string', 'max:1000'],
            'notify_candidate' => ['nullable', 'boolean'],
            'custom_message' => ['nullable', 'string'],
        ]);

        $oldStatus = $application->status;
        $newStatus = $request->input('status');

        $application->update([
            'status' => $newStatus,
            'cancelled_at' => ($newStatus === Application::STATUS_CANCELLED) ? now() : $application->cancelled_at,
        ]);

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'user_id' => auth()->id(),
            'note' => $request->input('note'),
            'created_at' => now(),
        ]);

        AuditLogger::log('application_status_changed', $application, ['status' => $oldStatus], ['status' => $newStatus, 'note' => $request->input('note')]);

        // Notify candidate if requested
        if ($request->boolean('notify_candidate')) {
            $subject = "Zmiana statusu zgłoszenia w naborze: {$application->jobOffer->title}";
            $body = $request->input('custom_message') ?: "Informujemy, że status Twojego zgłoszenia ({$application->reference_code}) zmienił się na: {$application->status_label}.".($request->input('note') ? "\n\nNotatka: ".$request->input('note') : '');

            try {
                Mail::to($application->email)->send(new CandidateCustomMail($application, $subject, $body));

                MailLog::create([
                    'application_id' => $application->id,
                    'template' => 'status_changed',
                    'to_email' => $application->email,
                    'subject' => $subject,
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $e) {
                MailLog::create([
                    'application_id' => $application->id,
                    'template' => 'status_changed',
                    'to_email' => $application->email,
                    'subject' => $subject,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'sent_at' => now(),
                ]);
            }
        }

        return back()->with('status', "Status zgłoszenia został zmieniony na: {$application->status_label}.");
    }

    public function sendMail(Request $request, string $publicId): RedirectResponse
    {
        $application = Application::where('public_id', $publicId)->with('jobOffer')->firstOrFail();

        $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $subject = $request->input('subject');
        $body = $request->input('body');

        try {
            Mail::to($application->email)->send(new CandidateCustomMail($application, $subject, $body));

            MailLog::create([
                'application_id' => $application->id,
                'template' => 'custom_recruiter_message',
                'to_email' => $application->email,
                'subject' => $subject,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            AuditLogger::log('mail_sent_to_candidate', $application, null, ['subject' => $subject]);

            return back()->with('status', 'Wiadomość została wysłana do kandydata.');
        } catch (\Throwable $e) {
            MailLog::create([
                'application_id' => $application->id,
                'template' => 'custom_recruiter_message',
                'to_email' => $application->email,
                'subject' => $subject,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            return back()->withErrors(['subject' => 'Nie udało się wysłać wiadomości: '.$e->getMessage()]);
        }
    }

    public function downloadFile(string $publicId, int $fileId)
    {
        $application = Application::where('public_id', $publicId)->firstOrFail();
        $file = ApplicationFile::where('id', $fileId)->where('application_id', $application->id)->firstOrFail();

        if (! Storage::disk('local')->exists($file->stored_path)) {
            abort(404, 'Plik nie istnieje na serwerze.');
        }

        AuditLogger::log('application_file_downloaded', $application, null, [
            'file_id' => $file->id,
            'file_name' => $file->original_name,
        ]);

        return Storage::disk('local')->download(
            $file->stored_path,
            $file->original_name,
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'application_ids' => ['required', 'array'],
            'status' => ['required', 'in:new,under_review,interview,rejected,qualified,cancelled'],
        ]);

        $ids = $request->input('application_ids');
        $status = $request->input('status');

        $apps = Application::whereIn('id', $ids)->get();
        foreach ($apps as $app) {
            $oldStatus = $app->status;
            $app->update([
                'status' => $status,
                'cancelled_at' => ($status === Application::STATUS_CANCELLED) ? now() : $app->cancelled_at,
            ]);

            ApplicationStatusHistory::create([
                'application_id' => $app->id,
                'from_status' => $oldStatus,
                'to_status' => $status,
                'user_id' => auth()->id(),
                'note' => 'Masowa zmiana statusu.',
                'created_at' => now(),
            ]);
        }

        AuditLogger::log('applications_bulk_status_update', null, null, ['count' => count($apps), 'status' => $status]);

        return back()->with('status', 'Zaktualizowano status dla '.count($apps).' zgłoszeń.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = Application::with('jobOffer');

        if ($request->filled('offer_id')) {
            $query->where('job_offer_id', $request->input('offer_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->orderBy('created_at', 'desc')->get();

        AuditLogger::log('applications_exported_csv', null, null, ['count' => $applications->count()]);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="kandydaci_'.date('Y-m-d_His').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($applications) {
            $output = fopen('php://output', 'w');
            // UTF-8 BOM for Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, ['Kod referencyjny', 'Imię', 'Nazwisko', 'E-mail', 'Telefon', 'Stanowisko', 'Status', 'Data złożenia', 'Źródło'], ';');

            foreach ($applications as $app) {
                fputcsv($output, [
                    $app->reference_code,
                    $app->first_name,
                    $app->last_name,
                    $app->email,
                    $app->phone,
                    $app->jobOffer->title,
                    $app->status_label,
                    $app->created_at->format('d.m.Y H:i'),
                    $app->source === 'manual' ? 'Ręczne' : 'Portal WWW',
                ], ';');
            }

            fclose($output);
        }, 200, $headers);
    }
}
