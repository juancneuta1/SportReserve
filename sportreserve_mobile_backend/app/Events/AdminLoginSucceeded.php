<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class AdminLoginSucceeded
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public User $user,
        public Request $request
    ) {
    }
}
