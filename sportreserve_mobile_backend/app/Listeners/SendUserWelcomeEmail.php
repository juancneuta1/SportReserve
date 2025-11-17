<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use App\Mail\UserWelcomeEmail;
use Illuminate\Support\Facades\Mail;

class SendUserWelcomeEmail
{
    public function handle(UserRegistered $event): void
    {
        Mail::to($event->user->email)->send(new UserWelcomeEmail($event->user));
    }
}
