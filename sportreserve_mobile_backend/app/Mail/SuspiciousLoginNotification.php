<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SuspiciousLoginNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $ip,
        public ?string $location,
        public ?string $device
    ) {
    }

    public function build(): self
    {
        return $this->subject('Nuevo inicio de sesión detectado')
            ->view('emails.admin.suspicious-login');
    }
}
