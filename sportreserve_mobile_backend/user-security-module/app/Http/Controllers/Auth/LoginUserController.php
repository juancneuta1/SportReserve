<?php

namespace App\Http\Controllers\Auth;

use App\Events\UserLoginFailed;
use App\Events\UserLoginSucceeded;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMobileSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginUserController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user && $user->locked_until && now()->lessThan($user->locked_until)) {
            $remaining = $user->locked_until->diffInMinutes(now());

            return response()->json([
                'message' => 'La cuenta está bloqueada temporalmente por múltiples intentos fallidos.',
                'locked_until' => $user->locked_until,
                'minutes_remaining' => $remaining,
            ], 423);
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            UserLoginFailed::dispatch($user, $request, $credentials['email'], false);

            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        $user->forceFill([
            'last_login_at' => now(),
            'failed_login_count' => 0,
            'locked_until' => null,
        ])->save();

        $token = $user->createToken('mobile')->plainTextToken;

        UserLoginSucceeded::dispatch($user, $request);

        return response()->json([
            'message' => 'Inicio de sesión exitoso.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function securityLogs(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        $logs = $user->mobileAccessLogs()->latest('logged_in_at')->paginate(10);
        $sessions = $user->mobileSessions()->latest('last_activity_at')->get();

        return response()->json([
            'logs' => $logs,
            'sessions' => $sessions,
        ]);
    }

    public function activeSessions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        $sessions = $user->mobileSessions()->latest('last_activity_at')->get();

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function logoutAllSessions(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'No autenticado.',
            ], 401);
        }

        UserMobileSession::where('user_id', $user->id)->delete();
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Se cerraron todas las sesiones activas.',
        ]);
    }
}
