<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminActionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public ?User $actor;
    public string $action;
    public string $body;
    public array $details;
    public \DateTime $timestamp;

    /**
     * Crea una nueva instancia del correo de auditoría.
     */
    public function __construct(?User $actor, string $action, string $body, array $details = [])
    {
        $this->actor = $actor;
        $this->action = $action;
        $this->body = $body;
        $this->details = $details;
        $this->timestamp = now(); // Fecha y hora actual del servidor
    }

    /**
     * Construye el mensaje del correo.
     */
    public function build()
    {
        return $this->subject('[AUDITORÍA] ' . $this->action)
                    ->view('emails.admin.action-notification')
                    ->with([
                        'actor' => $this->actor,
                        'action' => $this->action,
                        'body' => $this->body,
                        'details' => $this->details,
                        'timestamp' => $this->timestamp,
                    ]);
    }
}
