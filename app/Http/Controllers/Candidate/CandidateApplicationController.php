<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationConfirmationMail;
use App\Models\Application;
use App\Models\ApplicationFile;
use App\Models\ApplicationStatusHistory;
use App\Models\ConsentText;
use App\Models\Form;
use App\Models\JobOffer;
use App\Models\MailLog;
use App\Services\PdfValidator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CandidateApplicationController extends Controller
{
    public function create(string $slug): View|RedirectResponse
    {
        $offer = JobOffer::where('slug', $slug)
            ->orWhere('public_id', $slug)
            ->with(['department', 'contractType', 'form.activeFields'])
            ->firstOrFail();

        if (! $offer->isOpen()) {
            return redirect()->route('public.offers.show', $offer->slug)
                ->with('error', 'Termin składania dokumentów w tym naborze minął.');
        }

        // Determine form fields: either from assigned form or default template
        $form = $offer->form;
        if (! $form) {
            $form = Form::where('is_template', true)->first();
        }

        $fields = $form ? $form->activeFields : collect();
        $consent = ConsentText::orderBy('active_from', 'desc')->first();

        return view('candidate.applications.create', compact('offer', 'fields', 'consent'));
    }

    public function store(Request $request, string $slug): RedirectResponse
    {
        $offer = JobOffer::where('slug', $slug)
            ->orWhere('public_id', $slug)
            ->firstOrFail();

        if (! $offer->isOpen()) {
            return redirect()->route('public.offers.show', $offer->slug)
                ->with('error', 'Termin składania dokumentów w tym naborze minął.');
        }

        // Honeypot spam check: hidden field must remain empty
        if ($request->filled('_hp_name')) {
            // Fake success to mislead spam bots
            return redirect()->route('public.applications.thank_you', ['ref' => 'REK-'.date('Y').'-SPAM00']);
        }

        // Time-trap check: form cannot be submitted in under 3 seconds
        $renderedAt = (int) $request->input('_rendered_at', 0);
        if ($renderedAt > 0 && (time() - $renderedAt < 3)) {
            return back()->withInput()->withErrors(['first_name' => 'Formularz został wysłany zbyt szybko. Odczekaj chwilę i spróbuj ponownie.']);
        }

        // Base validation rules
        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'rodo_consent' => ['accepted'],
            'future_consent' => ['nullable', 'boolean'],
            'cv_file' => ['required', 'file'],
            'additional_files.*' => ['nullable', 'file'],
        ];

        $request->validate($rules, [
            'rodo_consent.accepted' => 'Zgoda na przetwarzanie danych osobowych jest obowiązkowa.',
            'cv_file.required' => 'Załączenie pliku CV w formacie PDF jest wymagane.',
        ]);

        // Duplicate check: max 1 active application per email + job_offer
        $existing = Application::where('job_offer_id', $offer->id)
            ->where('email', trim($request->input('email')))
            ->where('status', '!=', Application::STATUS_CANCELLED)
            ->exists();

        if ($existing) {
            return back()->withInput()->withErrors([
                'email' => 'Zgłoszenie z tego adresu e-mail zostało już zarejestrowane dla tego naboru.',
            ]);
        }

        // Strict PDF validation for CV file
        $cvFile = $request->file('cv_file');
        [$isCvValid, $cvError] = PdfValidator::validate($cvFile);
        if (! $isCvValid) {
            return back()->withInput()->withErrors(['cv_file' => $cvError]);
        }

        // Validate any additional files
        $additionalFiles = $request->file('additional_files', []);
        $totalSize = $cvFile->getSize();

        if (! empty($additionalFiles) && is_array($additionalFiles)) {
            foreach ($additionalFiles as $index => $file) {
                if ($file) {
                    [$isFileValid, $fileError] = PdfValidator::validate($file);
                    if (! $isFileValid) {
                        return back()->withInput()->withErrors(["additional_files.{$index}" => $fileError]);
                    }
                    $totalSize += $file->getSize();
                }
            }
        }

        // Total attachment size limit: 20 MB
        if ($totalSize > 20 * 1024 * 1024) {
            return back()->withInput()->withErrors(['cv_file' => 'Łączny rozmiar wszystkich załączników nie może przekraczać 20 MB.']);
        }

        // Generate cancellation token (plain for mail, SHA-256 in DB)
        $plainCancelToken = Str::random(64);
        $cancelTokenHash = hash('sha256', $plainCancelToken);
        $activeConsent = ConsentText::orderBy('active_from', 'desc')->first();

        // Extract answers to dynamic form fields
        $answers = $request->input('answers', []);

        $attachedFileNames = [];

        DB::beginTransaction();
        try {
            $application = Application::create([
                'job_offer_id' => $offer->id,
                'first_name' => trim($request->input('first_name')),
                'last_name' => trim($request->input('last_name')),
                'email' => trim($request->input('email')),
                'phone' => trim($request->input('phone')),
                'address' => trim($request->input('address')),
                'answers' => $answers,
                'status' => Application::STATUS_NEW,
                'consent_version_id' => $activeConsent?->id,
                'consent_at' => now(),
                'future_consent' => $request->boolean('future_consent'),
                'cancel_token_hash' => $cancelTokenHash,
                'cancel_token_expires_at' => $offer->deadline_at->copy()->addDays(30),
                'source' => 'web',
            ]);

            // Save CV file
            $cvStoredName = (string) Str::uuid().'.pdf';
            $cvPath = $cvFile->storeAs('applications/'.$application->id, $cvStoredName, 'local');
            $cvChecksum = hash_file('sha256', $cvFile->getRealPath());

            ApplicationFile::create([
                'application_id' => $application->id,
                'type' => 'cv',
                'original_name' => $cvFile->getClientOriginalName(),
                'stored_path' => $cvPath,
                'mime' => 'application/pdf',
                'size' => $cvFile->getSize(),
                'checksum' => $cvChecksum,
                'scan_status' => 'clean',
            ]);
            $attachedFileNames[] = $cvFile->getClientOriginalName();

            // Save additional files
            if (! empty($additionalFiles) && is_array($additionalFiles)) {
                foreach ($additionalFiles as $file) {
                    if ($file) {
                        $storedName = (string) Str::uuid().'.pdf';
                        $path = $file->storeAs('applications/'.$application->id, $storedName, 'local');
                        $checksum = hash_file('sha256', $file->getRealPath());

                        ApplicationFile::create([
                            'application_id' => $application->id,
                            'type' => 'other',
                            'original_name' => $file->getClientOriginalName(),
                            'stored_path' => $path,
                            'mime' => 'application/pdf',
                            'size' => $file->getSize(),
                            'checksum' => $checksum,
                            'scan_status' => 'clean',
                        ]);
                        $attachedFileNames[] = $file->getClientOriginalName();
                    }
                }
            }

            // Initial status history
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => 'none',
                'to_status' => Application::STATUS_NEW,
                'note' => 'Aplikacja złożona online przez kandydata.',
                'created_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors(['first_name' => 'Wystąpił błąd podczas zapisywania zgłoszenia: '.$e->getMessage()]);
        }

        // Send confirmation email
        try {
            Mail::to($application->email)->send(
                new ApplicationConfirmationMail($application, $plainCancelToken, $attachedFileNames)
            );

            MailLog::create([
                'application_id' => $application->id,
                'template' => 'application_confirmation',
                'to_email' => $application->email,
                'subject' => "Potwierdzenie zgłoszenia - {$application->reference_code}",
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            MailLog::create([
                'application_id' => $application->id,
                'template' => 'application_confirmation',
                'to_email' => $application->email,
                'subject' => "Potwierdzenie zgłoszenia - {$application->reference_code}",
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }

        return redirect()->route('public.applications.thank_you', ['ref' => $application->reference_code]);
    }

    public function thankYou(Request $request): View
    {
        $ref = $request->query('ref', 'REK-XXXX');

        return view('candidate.applications.thank_you', compact('ref'));
    }
}
