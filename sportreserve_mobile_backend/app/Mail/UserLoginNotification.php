<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class UserLoginNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $ip,
        public ?string $userAgent,
        public Carbon $loginTime
    ) {
    }

    public function build(): self
    {
        return $this->subject('Nuevo inicio de sesión en tu cuenta SportReserve')
            ->view('emails.users.login-notification', [
                'user' => $this->user,
                'ipAddress' => $this->ip,
                'userAgent' => $this->userAgent,
                'loginTime' => $this->loginTime,
            ]);
    }
}

