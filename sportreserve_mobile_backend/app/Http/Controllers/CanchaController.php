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
     * Sanitiza y normaliza cualquier estructura antes de convertirla a JSON
     */
    private function cleanForJson(mixed $data): mixed
    {
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);

        if ($encoded === false) {
            return $data;
        }

        return json_decode($encoded, true);
    }

    /**
     * Mostrar todas las canchas registradas en el sistema.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Cancha::query();

        if ($request->filled('tipo')) {
            $query->whereRaw("unaccent(lower(tipo::text)) LIKE unaccent(?)", ['%' . strtolower($request->tipo) . '%']);
        }

        if ($request->filled('ubicacion')) {
            $query->whereRaw("lower(unaccent(ubicacion::text)) LIKE unaccent(?)", ['%' . strtolower($request->ubicacion) . '%']);
        }

        if ($request->has('disponibilidad')) {
            $val = filter_var($request->disponibilidad, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($val)) {
                $query->where('disponibilidad', $val);
            }
        }

        $date = $request->input('fecha', now()->toDateString());
        $canchas = $query->get();

        // Filtrar las canchas que no tengan coordenadas válidas
        $canchas = $canchas
            ->filter(fn($c) => !is_null($c->latitud) && !is_null($c->longitud))
            ->values()
            ->map(fn(Cancha $c) => $this->appendAvailability($c, $date));

        return response()->json(
            $this->cleanForJson([
                'success' => true,
                'count' => $canchas->count(),
                'fecha' => $date,
                'canchas' => $canchas,
            ]),
            Response::HTTP_OK
        );
    }

    /**
     * Registrar una nueva cancha.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user || !in_array($user->rol, ['Administrador', 'admin', 'staff'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'unique:canchas,nombre'],
            'tipo' => ['required', 'string'],
            'ubicacion' => ['required', 'string'],
            'latitud' => ['required', 'numeric'],
            'longitud' => ['required', 'numeric'],
            'precio_por_hora' => ['required', 'numeric'],
            'descripcion' => ['nullable', 'string'],
            'servicios' => ['nullable', 'string'],
            'imagen' => ['nullable', 'string'],
        ]);

        $cancha = Cancha::create($validated);

        return response()->json(
            $this->cleanForJson([
                'success' => true,
                'message' => 'Cancha registrada correctamente',
                'cancha' => $cancha,
            ]),
            Response::HTTP_CREATED
        );
    }

    /**
     * Mostrar información de una cancha
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cancha = Cancha::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cancha no encontrada'], 404);
        }

        $date = request()->input('fecha', now()->toDateString());

        return response()->json(
            $this->cleanForJson([
                'success' => true,
                'cancha' => $this->appendAvailability($cancha, $date),
                'fecha' => $date,
            ]),
            200
        );
    }

    /**
     * Actualizar cancha
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $cancha = Cancha::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cancha no encontrada'], 404);
        }

        $validated = $request->validate([
            'nombre' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('canchas', 'nombre')->ignore($id),
            ],
            'tipo' => ['sometimes', 'string'],
            'ubicacion' => ['sometimes', 'string'],
            'latitud' => ['sometimes', 'numeric'],
            'longitud' => ['sometimes', 'numeric'],
            'precio_por_hora' => ['sometimes', 'numeric'],
            'descripcion' => ['nullable', 'string'],
            'servicios' => ['nullable', 'string'],
            'imagen' => ['nullable', 'string'],
        ]);

        $cancha->update($validated);

        return response()->json(
            $this->cleanForJson([
                'success' => true,
                'message' => 'Cancha actualizada correctamente',
                'cancha' => $cancha,
            ]),
            200
        );
    }

    /**
     * Eliminar una cancha
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $cancha = Cancha::findOrFail($id);
        } catch (ModelNotFoundException) {
            return response()->json(['message' => 'Cancha no encontrada'], 404);
        }

        $cancha->delete();

        return response()->json(['success' => true, 'message' => 'Cancha eliminada'], 200);
    }

    private function appendAvailability(Cancha $cancha, string $date): Cancha
    {
        $availability = $this->availabilityService->availabilityFor($cancha->id, $date);

        $cancha->setAttribute('availability', $availability);
        $cancha->setAttribute('is_available_now', (bool) $availability['next_available']);

        return $cancha;
    }
}
