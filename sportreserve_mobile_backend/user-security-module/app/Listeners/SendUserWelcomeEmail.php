<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\UserWelcomeEmail;
use Illuminate\Support\Facades\Mail;

class SendUserWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        $user = $event->user;

        Mail::to($user->email)->send(new UserWelcomeEmail($user));
    }
}
