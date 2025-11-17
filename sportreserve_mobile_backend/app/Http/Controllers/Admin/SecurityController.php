<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use App\Models\UserSession;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $sessions = $user->sessions()
            ->latest('last_activity_at')
            ->get();

        $logs = AccessLog::where('user_id', $user->id)
            ->latest('logged_in_at')
            ->paginate(10);

        return view('admin.security.index', compact('user', 'sessions', 'logs'));
    }

    public function destroyOtherSessions(Request $request): RedirectResponse
    {
        $user = $request->user();

        UserSession::where('user_id', $user->id)
            ->where('session_id', '!=', session()->getId())
            ->delete();

        return back()->with('status', 'Se cerraron todas las demás sesiones activas.');
    }
}
