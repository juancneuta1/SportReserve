<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Events\AdminLoginSucceeded;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $secret = $user->two_factor_secret ? Crypt::decryptString($user->two_factor_secret) : null;
        $qrCode = null;

        if ($secret) {
            /** @var \PragmaRX\Google2FALaravel\Google2FA $google2fa */
            $google2fa = app('pragmarx.google2fa');
            $qrCode = $google2fa->getQRCodeInline(
                config('app.name', 'SportReserve'),
                $user->email,
                $secret
            );
        }

        $recoveryCodes = $user->two_factor_recovery_codes
            ? json_decode($user->two_factor_recovery_codes, true)
            : [];

        return view('admin.security.two-factor', compact('user', 'qrCode', 'recoveryCodes'));
    }

    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();

        /** @var \PragmaRX\Google2FALaravel\Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret'        => Crypt::encryptString($secret),
            'two_factor_verified'      => false,
            'two_factor_enabled_at'    => now(),
            'two_factor_confirmed_at'  => null,
        ])->save();

        return back()->with('status', 'Escanea el código QR y confirma tu 2FA ingresando un token.');
    }

    public function confirm(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            throw ValidationException::withMessages([
                'code' => 'Activa primero el doble factor.',
            ]);
        }

        /** @var \PragmaRX\Google2FALaravel\Google2FA $google2fa */
        $google2fa = app('pragmarx.google2fa');
        $secret = Crypt::decryptString($user->two_factor_secret);

        if (! $google2fa->verifyKey($secret, $request->code)) {
            throw ValidationException::withMessages([
                'code' => 'El código proporcionado no es válido.',
            ]);
        }

        $user->forceFill([
            'two_factor_verified'       => true,
            'two_factor_confirmed_at'   => now(),
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
        ])->save();

        return back()->with('status', 'Doble factor activado correctamente.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->forceFill([
            'two_factor_secret'          => null,
            'two_factor_recovery_codes'  => null,
            'two_factor_enabled_at'      => null,
            'two_factor_confirmed_at'    => null,
            'two_factor_verified'        => false,
        ])->save();

        return back()->with('status', 'Se ha desactivado el doble factor de autenticación.');
    }

    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->two_factor_verified) {
            return back()->withErrors(['code' => 'Activa el doble factor primero.']);
        }

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($this->generateRecoveryCodes()),
        ])->save();

        return back()->with('status', 'Se generaron nuevos códigos de recuperación.');
    }

    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => strtoupper(str()->random(10)))
            ->all();
    }

    public function challenge(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('2fa:user:id')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.two-factor-challenge');
    }

    public function verifyChallenge(Request $request): RedirectResponse
    {
        $request->validate([
            'code'          => ['nullable', 'string', 'size:6'],
            'recovery_code' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login');
        }

        $verified = false;

        if ($request->filled('code') && $user->two_factor_secret) {

            /** @var \PragmaRX\Google2FALaravel\Google2FA $google2fa */
            $google2fa = app('pragmarx.google2fa');
            $secret = Crypt::decryptString($user->two_factor_secret);
            $verified = $google2fa->verifyKey($secret, $request->code);

        } elseif ($request->filled('recovery_code') && $user->two_factor_recovery_codes) {

            $codes = json_decode($user->two_factor_recovery_codes, true) ?? [];

            $entered = strtoupper($request->recovery_code);

            if (in_array($entered, $codes, true)) {
                $verified = true;
                $remaining = array_values(array_diff($codes, [$entered]));
                $user->forceFill(['two_factor_recovery_codes' => json_encode($remaining)])->save();
            }
        }

        if (! $verified) {
            return back()->withErrors(['code' => 'El código proporcionado no es válido.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('2fa:user:id');
        $request->session()->put('two_factor_passed', true);

        AdminLoginSucceeded::dispatch($user, $request);

        return redirect()->route('admin.dashboard');
    }
}
