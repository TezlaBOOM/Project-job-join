<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public string $ipAddress,
        public int $validMinutes = 10
    ) {}

    public function build(): self
    {
        $officeName = Setting::get('office_name', 'Urząd Miasta');

        return $this->subject("Kod logowania do panelu (2FA) - {$officeName}")
            ->view('emails.two_factor_code')
            ->text('emails.two_factor_code_text');
    }
}
