<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class UserLoginFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ?User $user,
        public Request $request,
        public string $email,
        public bool $locked
    ) {
    }
}
