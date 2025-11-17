<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use App\Services\CourtAvailabilityService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\Rule;

class CanchaController extends Controller
{
    public function __construct(private CourtAvailabilityService $availabilityService)
    {
    }

    /**
     * Mostrar todas las canchas registradas en el sistema.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cancha::query();

        if ($request->has('tipo') && $request->tipo !== null) {
            $query->whereRaw("unaccent(LOWER(tipo::text)) LIKE unaccent(?)", ['%' . strtolower($request->tipo) . '%']);
        }

        if ($request->has('ubicacion') && $request->ubicacion !== null) {
            $query->whereRaw("LOWER(unaccent(ubicacion::text)) LIKE ?", ['%' . strtolower($request->ubicacion) . '%']);
        }

        if ($request->has('disponibilidad')) {
            $value = filter_var($request->disponibilidad, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($value)) {
                $query->where('disponibilidad', $value);
            }
        }

        $date = $request->input('fecha', now()->toDateString());
        $canchas = $query->get();

        // 🧩 Validación extra: que las canchas tengan coordenadas válidas
        $canchas = $canchas->filter(function ($cancha) {
            return !is_null($cancha->latitud) && !is_null($cancha->longitud);
        })->values()->map(fn (Cancha $cancha) => $this->appendAvailability($cancha, $date));

        // ✅ Estructura uniforme para la API
        return response()->json([
            'success' => true,
            'count' => $canchas->count(),
            'fecha' => $date,
            'canchas' => $canchas,
        ], Response::HTTP_OK);
    }




    /**
     * Registrar una nueva cancha con la información proporcionada.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // 🔒 Verificar que el usuario tenga rol administrador
        if (!$user || !in_array($user->rol, ['Administrador', 'admin', 'staff'])) {
            return response()->json([
                'message' => 'No autorizado. Solo administradores pueden crear canchas.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'unique:canchas,nombre'],
            'tipo' => ['required', 'string'],
            'ubicacion' => ['required', 'string'],
            'latitud' => ['required', 'numeric'],
            'longitud' => ['required', 'numeric'],
            'precio_por_hora' => ['required', 'numeric', 'min:0'],
            'disponibilidad' => ['sometimes', 'boolean'],
            'imagen' => ['nullable', 'url'],
        ]);

        // ✅ Crear la cancha en la base de datos
        $cancha = Cancha::create($validated);

        // ✅ Notificar respuesta para Flutter
        return response()->json([
            'success' => true,
            'message' => 'Cancha registrada correctamente',
            'cancha' => $cancha,
        ], Response::HTTP_CREATED);
    }



    /**
     * Mostrar la informacion de una cancha especifica.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cancha = Cancha::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'Cancha no encontrada',
            ], Response::HTTP_NOT_FOUND);
        }

        $date = request()->input('fecha', now()->toDateString());

        return response()->json([
            'cancha' => $this->appendAvailability($cancha, $date),
            'fecha' => $date,
        ], Response::HTTP_OK);
    }

    /**
     * Actualizar los datos de una cancha existente.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $cancha = Cancha::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'Cancha no encontrada',
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'nombre' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('canchas', 'nombre')->ignore($id),
            ],
            'tipo' => ['sometimes', 'required', 'string'],
            'ubicacion' => ['sometimes', 'required', 'string'],
            'latitud' => ['sometimes', 'required', 'numeric'],
            'longitud' => ['sometimes', 'required', 'numeric'],
            'precio_por_hora' => ['sometimes', 'required', 'numeric', 'min:0'],
            'disponibilidad' => ['sometimes', 'boolean'],
            'descripcion' => ['nullable', 'string'],
            'servicios' => ['nullable', 'string'],
            'imagen' => ['nullable', 'url'],
        ]);

        $cancha->update($validated);

        return response()->json([
            'message' => 'Cancha actualizada correctamente',
            'cancha' => $cancha,
        ], Response::HTTP_OK);
    }


    /**
     * Eliminar una cancha del catalogo disponible.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $cancha = Cancha::findOrFail($id);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'Cancha no encontrada',
            ], Response::HTTP_NOT_FOUND);
        }

        $cancha->delete();

        return response()->json([
            'message' => 'Cancha eliminada correctamente',
        ], Response::HTTP_OK);
    }

    private function appendAvailability(Cancha $cancha, string $date): Cancha
    {
        $availability = $this->availabilityService->availabilityFor($cancha->id, $date);

        $cancha->setAttribute('availability', $availability);
        $cancha->setAttribute('is_available_now', (bool) $availability['next_available']);

        return $cancha;
    }
}
