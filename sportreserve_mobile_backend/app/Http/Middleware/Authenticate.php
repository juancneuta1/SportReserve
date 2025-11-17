<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
    {
        // Si la solicitud NO acepta JSON (es web), redirige al login
        // pero en API, devuelve 401 (sin redirección)
        if (! $request->expectsJson()) {
            return null;
        }
    }
}
