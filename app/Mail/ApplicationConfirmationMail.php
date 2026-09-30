<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public string $cancelToken,
        public array $attachedFileNames = []
    ) {}

    public function build(): self
    {
        $officeName = Setting::get('office_name', 'Urząd Miasta');
        $replyToEmail = Setting::get('office_email', config('mail.from.address'));

        return $this->subject("Potwierdzenie złożenia zgłoszenia - {$this->application->reference_code} - {$officeName}")
            ->replyTo($replyToEmail, $officeName)
            ->view('emails.application_confirmation')
            ->text('emails.application_confirmation_text');
    }
}
