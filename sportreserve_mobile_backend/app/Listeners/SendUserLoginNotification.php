<?php

namespace App\Listeners;

use App\Events\UserLoginSucceeded;
use App\Mail\UserLoginNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendUserLoginNotification
{
    public function handle(UserLoginSucceeded $event): void
    {
        $user = $event->user;
        $request = $event->request;

        Mail::to($user->email)->send(
            new UserLoginNotification(
                user: $user,
                ip: $request->ip(),
                userAgent: (string) $request->userAgent(),
                loginTime: Carbon::now()
            )
        );
    }
}
