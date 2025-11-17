<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($request->routeIs('admin.2fa.challenge', 'admin.2fa.verify')) {
            return $next($request);
        }

        if (
            $user &&
            $user->role === 'admin' &&
            $user->two_factor_verified &&
            ! $request->session()->get('two_factor_passed')
        ) {
            return redirect()->route('admin.2fa.challenge');
        }

        return $next($request);
    }
}
