<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class UserLoginSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Request $request
    ) {
    }
}

