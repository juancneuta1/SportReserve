<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mensaje;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MensajeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $mensajes = Mensaje::with('remitente')
            ->where('destinatario_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'ok' => true,
            'items' => $mensajes,
        ]);
    }

    public function enviados(Request $request): JsonResponse
    {
        $user = $request->user();

        $mensajes = Mensaje::with('destinatario')
            ->where('remitente_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'ok' => true,
            'items' => $mensajes,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'destinatario_id' => ['required', 'exists:users,id'],
            'asunto' => ['required', 'string', 'max:150'],
            'contenido' => ['required', 'string', 'max:5000'],
        ]);

        abort_unless($request->user()->id !== (int) $validated['destinatario_id'], 400, 'No puedes enviarte un mensaje a ti mismo.');

        $mensaje = Mensaje::create([
            'remitente_id' => $request->user()->id,
            'destinatario_id' => $validated['destinatario_id'],
            'asunto' => $validated['asunto'],
            'contenido' => $validated['contenido'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Mensaje enviado correctamente.',
            'data' => $mensaje->load('destinatario'),
        ], 201);
    }

    public function marcarLeido(Request $request, Mensaje $mensaje): JsonResponse
    {
        abort_unless($mensaje->destinatario_id === $request->user()->id, 403);

        $mensaje->update(['leido' => true]);

        return response()->json([
            'ok' => true,
            'message' => 'Mensaje marcado como leído.',
        ]);
    }

    public function destroy(Request $request, Mensaje $mensaje): JsonResponse
    {
        abort_unless(
            $mensaje->destinatario_id === $request->user()->id || $mensaje->remitente_id === $request->user()->id,
            403
        );

        $mensaje->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Mensaje eliminado correctamente.',
        ]);
    }
}
