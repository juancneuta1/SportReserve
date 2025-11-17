<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Cancha;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalificacionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'cancha_id' => ['required', 'exists:canchas,id'],
            'estrellas' => ['required', 'integer', 'between:1,5'],
            'comentario' => ['nullable', 'string'],
        ]);

        $calificacion = Calificacion::updateOrCreate(
            ['user_id' => $user->id, 'cancha_id' => $validated['cancha_id']],
            [
                'estrellas' => $validated['estrellas'],
                'comentario' => $validated['comentario'] ?? null,
            ]
        );

        $this->recalcularPromedio($calificacion->cancha_id);

        return response()->json([
            'success' => true,
            'message' => 'Calificación guardada exitosamente.',
            'calificacion' => $calificacion->load('user:id,name', 'cancha:id,nombre'),
        ]);
    }

    public function promedio(int $cancha_id): JsonResponse
    {
        $promedio = Calificacion::where('cancha_id', $cancha_id)->avg('estrellas') ?? 0;
        $total = Calificacion::where('cancha_id', $cancha_id)->count();

        $response = [
            'success' => true,
            'cancha_id' => $cancha_id,
            'promedio' => round((float) $promedio, 2),
            'total' => $total,
        ];

        if ($response['promedio'] >= 4.5 && $total > 20) {
            $response['insignia'] = '⭐ Cancha Top';
        }

        return response()->json($response);
    }

    public function listar(int $cancha_id): JsonResponse
    {
        $calificaciones = Calificacion::with('user:id,name')
            ->where('cancha_id', $cancha_id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'calificaciones' => $calificaciones,
        ]);
    }

    protected function recalcularPromedio(int $canchaId): void
    {
        $promedio = Calificacion::where('cancha_id', $canchaId)->avg('estrellas') ?? 0;

        Cancha::where('id', $canchaId)->update([
            'rating_promedio' => round($promedio, 2),
        ]);
    }
}
