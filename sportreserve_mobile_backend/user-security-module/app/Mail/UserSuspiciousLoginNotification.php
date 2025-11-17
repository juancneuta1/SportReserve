<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserSuspiciousLoginNotification extends Mailable
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
        return $this->subject('Alerta de acceso inusual en tu cuenta SportReserve')
            ->view('emails.users.suspicious-login', [
                'user' => $this->user,
                'ip' => $this->ip,
                'location' => $this->location,
                'device' => $this->device,
            ]);
    }
}
