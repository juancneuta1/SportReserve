<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Mensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComunicacionesController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();

        $inbox = Mensaje::with('remitente')
            ->where('destinatario_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $sent = Mensaje::with('destinatario')
            ->where('remitente_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        return view('admin.comunicaciones.dashboard', compact('inbox', 'sent'));
    }

    public function notifications(): JsonResponse
    {
        $notifications = AdminNotification::orderByDesc('created_at')
            ->take(10)
            ->get();

        return response()->json([
            'ok' => true,
            'count' => $notifications->where('status', 'new')->count(),
            'items' => $notifications,
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        AdminNotification::where('status', 'new')->update(['status' => 'read']);

        return response()->json([
            'ok' => true,
            'message' => 'Todas las notificaciones fueron marcadas como leídas.',
        ]);
    }
}
