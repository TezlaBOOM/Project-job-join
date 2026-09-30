<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RecruiterInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $resetUrl
    ) {}

    public function build(): self
    {
        $officeName = Setting::get('office_name', 'Urząd Miasta');

        return $this->subject("Zaproszenie do panelu rekrutacyjnego - {$officeName}")
            ->view('emails.recruiter_invite')
            ->text('emails.recruiter_invite_text');
    }
}
