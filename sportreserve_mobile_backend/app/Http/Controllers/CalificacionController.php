<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CalificacionController extends Controller
{
    /**
     * Listar calificaciones de una cancha
     */
    public function listar($cancha_id)
    {
        $calificaciones = Calificacion::with('user')
            ->where('cancha_id', $cancha_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'calificaciones' => $calificaciones
        ]);
    }

    /**
     * Guardar una calificación (solo usuarios autenticados)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cancha_id' => 'required|exists:canchas,id',
            'estrellas' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $calificacion = Calificacion::create([
            'user_id' => $request->user()->id,
            'cancha_id' => $request->cancha_id,
            'estrellas' => $request->estrellas,
            'comentario' => $request->comentario,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Calificación registrada',
            'calificacion' => $calificacion
        ]);
    }

    /**
     * Obtener el promedio de estrellas de una cancha
     */
    public function promedio($cancha_id)
    {
        // CAMBIO IMPORTANTE: antes decía ->avg('puntuacion')
        // Ahora usa la columna correcta ->avg('estrellas')
        $promedio = Calificacion::where('cancha_id', $cancha_id)->avg('estrellas');


        return response()->json([
            'success' => true,
            'promedio' => round($promedio ?? 0, 1)
        ]);
    }
}
