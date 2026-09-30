<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CandidateCustomMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Application $application,
        public string $customSubject,
        public string $customBody
    ) {}

    public function build(): self
    {
        $officeName = Setting::get('office_name', 'Urząd Miasta');
        $replyToEmail = Setting::get('office_email', config('mail.from.address'));

        return $this->subject("{$this->customSubject} - {$this->application->reference_code} - {$officeName}")
            ->replyTo($replyToEmail, $officeName)
            ->view('emails.candidate_custom')
            ->text('emails.candidate_custom_text');
    }
}
