<?php

namespace App\Listeners;

use App\Events\UserLoginFailed;
use App\Models\UserFailedLogin;

class RecordFailedUserLogin
{
    public function handle(UserLoginFailed $event): void
    {
        $user = $event->user;
        $request = $event->request;

        $locked = $event->locked;

        if ($user) {
            $user->failed_login_count = ($user->failed_login_count ?? 0) + 1;

            if ($user->failed_login_count >= 5) {
                $user->locked_until = now()->addMinutes(15);
                $locked = true;
            }

            $user->save();
        }

        UserFailedLogin::create([
            'user_id' => $user?->id,
            'email' => $event->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'attempted_at' => now(),
            'locked' => $locked,
        ]);
    }
}
