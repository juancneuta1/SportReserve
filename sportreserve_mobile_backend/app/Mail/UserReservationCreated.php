<?php

namespace App\Mail;

use App\Models\Reserva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserReservationCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reserva $reserva)
    {
        $this->reserva->loadMissing(['user', 'cancha']);
    }

    public function build(): self
    {
        return $this->subject('Tu reserva en SportReserve')
            ->view('emails.users.reservation-created', [
                'reserva' => $this->reserva,
                'user' => $this->reserva->user,
                'cancha' => $this->reserva->cancha,
            ]);
    }
}

