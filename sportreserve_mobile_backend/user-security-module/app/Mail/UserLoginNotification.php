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
        public ?string $location,
        public ?string $device,
        public ?string $userAgent,
        public Carbon $loginTime
    ) {
    }

    public function build(): self
    {
        return $this->subject('Nuevo inicio de sesión en tu cuenta SportReserve')
            ->view('emails.users.login-notification', [
                'user' => $this->user,
                'ip' => $this->ip,
                'location' => $this->location,
                'device' => $this->device,
                'userAgent' => $this->userAgent,
                'loginTime' => $this->loginTime,
            ]);
    }
}
