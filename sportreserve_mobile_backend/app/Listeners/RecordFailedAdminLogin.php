<?php

namespace App\Listeners;

use App\Events\AdminLoginFailed;
use App\Models\FailedLogin;

class RecordFailedAdminLogin
{
    public function handle(AdminLoginFailed $event): void
    {
        FailedLogin::create([
            'email' => $event->email,
            'ip' => $event->request->ip(),
            'user_agent' => $event->request->userAgent(),
            'attempted_at' => now(),
        ]);
    }
}
