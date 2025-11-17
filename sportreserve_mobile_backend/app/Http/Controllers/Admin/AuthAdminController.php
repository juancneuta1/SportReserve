<?php

namespace App\Http\Controllers\Admin;

use App\Events\AdminLoginFailed;
use App\Events\AdminLoginSucceeded;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthAdminController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        /** @var User|null $user */
        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->locked_until && now()->lessThan($user->locked_until)) {
            return back()
                ->withErrors(['email' => 'Cuenta bloqueada por múltiples intentos fallidos.'])
                ->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            if ($user) {
                $user->failed_login_count = $user->failed_login_count + 1;

                if ($user->failed_login_count >= 5) {
                    $user->locked_until = now()->addMinutes(15);
                }

                $user->save();
            }

            AdminLoginFailed::dispatch($credentials['email'], $request, (bool) ($user?->locked_until));

            return back()->withErrors([
                'email' => $user?->locked_until ? 'Cuenta bloqueada por múltiples intentos fallidos.' : 'Credenciales incorrectas.',
            ])->onlyInput('email');
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->role !== 'admin') {
            Auth::logout();
            return back()->withErrors(['email' => 'Acceso restringido. Solo los administradores pueden ingresar.']);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'failed_login_count' => 0,
            'locked_until' => null,
        ])->save();

        $request->session()->regenerate();

        if ($user->two_factor_verified) {
            $request->session()->put('2fa:user:id', $user->id);
            $request->session()->put('two_factor_passed', false);

            return redirect()
                ->route('admin.2fa.challenge')
                ->with('status', 'Ingresa el código generado para completar el acceso.');
        }

        $request->session()->put('two_factor_passed', true);
        AdminLoginSucceeded::dispatch($user, $request);

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget(['two_factor_passed', '2fa:user:id']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('status', 'Sesión cerrada correctamente.');
    }
}
