<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApplicationCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application
    ) {}

    public function build(): self
    {
        $officeName = Setting::get('office_name', 'Urząd Miasta');

        return $this->subject("Potwierdzenie anulowania zgłoszenia - {$this->application->reference_code} - {$officeName}")
            ->view('emails.application_cancelled')
            ->text('emails.application_cancelled_text');
    }
}
