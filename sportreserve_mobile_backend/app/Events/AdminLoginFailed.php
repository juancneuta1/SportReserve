<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class AdminLoginFailed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $email,
        public Request $request,
        public bool $locked = false
    ) {
    }
}
