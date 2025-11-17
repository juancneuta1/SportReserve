<?php

namespace App\Services;

use App\Mail\AdminActionNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AdminActionNotifier
{
    public static function send(?User $actor, string $action, string $message, array $details = []): void
    {
        $configured = collect(explode(',', (string) config('services.audit_recipient')))
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values();

        if ($configured->isEmpty() && $actor?->email) {
            $configured->push($actor->email);
        }

        if ($configured->isEmpty() && ($fallback = config('mail.from.address'))) {
            $configured->push($fallback);
        }

        if ($configured->isEmpty()) {
            return;
        }

        try {
            Mail::to($configured->all())->send(
                new AdminActionNotification(
                    $actor,
                    $action,
                    $message,
                    $details,
                )
            );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
