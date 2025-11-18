<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Cancha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalificacionController extends Controller
{
    /**
     * Resumen: promedio + total + últimas reseñas
     */
    public function resumen($cancha_id)
    {
        $query = Calificacion::with('user')
            ->where('cancha_id', $cancha_id);

        $promedio = round($query->avg('estrellas') ?? 0, 1);
        $count = $query->count();

        $reviews = $query
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'user_name' => $r->user->name ?? 'Usuario',
                    'rating' => (float) $r->estrellas,
                    'comentario' => $r->comentario ?? '',
                    'created_at' => $r->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'average' => $promedio,
            'count' => $count,
            'reviews' => $reviews,
        ]);
    }


    /**
     * Listar todas las calificaciones de una cancha
     */
    public function listar($cancha_id)
    {
        $calificaciones = Calificacion::with('user')
            ->where('cancha_id', $cancha_id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'user_name' => $r->user->name ?? 'Usuario',
                    'rating' => (float) $r->estrellas,
                    'comentario' => $r->comentario ?? '',
                    'created_at' => $r->created_at->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'calificaciones' => $calificaciones,
        ]);
    }


    /**
     * Crear / actualizar calificación de usuario autenticado
     */
    public function store(Request $request, $cancha_id)
    {
        $validator = Validator::make($request->all(), [
            'estrellas' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        $calificacion = Calificacion::updateOrCreate(
            [
                'user_id' => $user->id,
                'cancha_id' => $cancha_id,
            ],
            [
                'estrellas' => $request->estrellas,
                'comentario' => $request->comentario,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Calificación guardada correctamente',
            'calificacion' => $calificacion,
        ]);
    }


    /**
     * Promedio simple (opcional)
     */
    public function promedio($cancha_id)
    {
        $promedio = Calificacion::where('cancha_id', $cancha_id)->avg('estrellas');

        return response()->json([
            'success' => true,
            'promedio' => round($promedio ?? 0, 1),
        ]);
    }
}
