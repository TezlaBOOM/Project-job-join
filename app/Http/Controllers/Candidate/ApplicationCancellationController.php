<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Mail\ApplicationCancelledMail;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\MailLog;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class ApplicationCancellationController extends Controller
{
    public function showConfirm(string $token): View
    {
        $hash = hash('sha256', $token);
        $application = Application::where('cancel_token_hash', $hash)->with('jobOffer')->first();

        if (! $application) {
            return view('candidate.applications.cancel_status', [
                'type' => 'invalid',
                'title' => 'Nieprawidłowy link',
                'message' => 'Link do anulowania zgłoszenia jest nieprawidłowy lub wygasł. W razie potrzeby prosimy o kontakt z urzędem.',
            ]);
        }

        if ($application->isCancelled()) {
            return view('candidate.applications.cancel_status', [
                'type' => 'already_cancelled',
                'title' => 'Zgłoszenie zostało już anulowane',
                'message' => "Zgłoszenie o numerze referencyjnym {$application->reference_code} zostało już wcześniej anulowane.",
            ]);
        }

        if ($application->cancel_token_expires_at && $application->cancel_token_expires_at->isPast()) {
            return view('candidate.applications.cancel_status', [
                'type' => 'expired',
                'title' => 'Link wygasł',
                'message' => 'Termin na samodzielne anulowanie tego zgłoszenia upłynął. Skontaktuj się z działem kadr.',
            ]);
        }

        return view('candidate.applications.cancel_confirm', compact('application', 'token'));
    }

    public function processCancel(Request $request, string $token): View
    {
        $hash = hash('sha256', $token);
        $application = Application::where('cancel_token_hash', $hash)->with('jobOffer')->first();

        if (! $application) {
            return view('candidate.applications.cancel_status', [
                'type' => 'invalid',
                'title' => 'Nieprawidłowy link',
                'message' => 'Nie znaleziono zgłoszenia powiązanego z tym linkiem.',
            ]);
        }

        if ($application->isCancelled()) {
            return view('candidate.applications.cancel_status', [
                'type' => 'already_cancelled',
                'title' => 'Zgłoszenie zostało już anulowane',
                'message' => "Zgłoszenie o numerze referencyjnym {$application->reference_code} zostało już anulowane.",
            ]);
        }

        $oldStatus = $application->status;

        // Perform cancellation
        $application->update([
            'status' => Application::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        ApplicationStatusHistory::create([
            'application_id' => $application->id,
            'from_status' => $oldStatus,
            'to_status' => Application::STATUS_CANCELLED,
            'note' => 'Zgłoszenie anulowane samodzielnie przez kandydata linkiem z wiadomości e-mail.',
            'created_at' => now(),
        ]);

        AuditLogger::log('application_cancelled_by_candidate', $application, ['status' => $oldStatus], ['status' => Application::STATUS_CANCELLED]);

        // Send confirmation email
        try {
            Mail::to($application->email)->send(new ApplicationCancelledMail($application));

            MailLog::create([
                'application_id' => $application->id,
                'template' => 'application_cancelled',
                'to_email' => $application->email,
                'subject' => "Potwierdzenie anulowania zgłoszenia - {$application->reference_code}",
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            MailLog::create([
                'application_id' => $application->id,
                'template' => 'application_cancelled',
                'to_email' => $application->email,
                'subject' => "Potwierdzenie anulowania zgłoszenia - {$application->reference_code}",
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }

        return view('candidate.applications.cancel_status', [
            'type' => 'success',
            'title' => 'Zgłoszenie zostało anulowane',
            'message' => "Zgłoszenie {$application->reference_code} na stanowisko {$application->jobOffer->title} zostało pomyślnie anulowane. Wysłaliśmy potwierdzenie na Twój adres e-mail.",
        ]);
    }
}
