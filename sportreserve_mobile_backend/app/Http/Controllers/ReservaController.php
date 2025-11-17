<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Cancha;
use App\Models\Reserva;
use App\Services\CourtAvailabilityService;
use App\Services\MercadoPagoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReservaController extends Controller
{
    protected MercadoPagoService $mercadoPago;

    public function __construct(
        MercadoPagoService $mercadoPago,
        protected CourtAvailabilityService $availabilityService
    ) {
        $this->mercadoPago = $mercadoPago;
    }

    /**
     * Listar todas las reservas del usuario autenticado
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $reservas = Reserva::with('cancha')
            ->where('user_id', $user->id)
            ->orderByDesc('fecha')
            ->get();

        return response()->json($reservas);
    }

    /**
     * Crear una nueva reserva
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'cancha_id' => 'required|exists:canchas,id',
                'fecha' => 'required|date',
                'hora' => 'required|date_format:H:i',
                'cantidad_horas' => 'required|integer|min:1|max:5',
                'precio_por_cancha' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                Log::warning('Validación fallida en reserva', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();

            $horaInicio = Carbon::parse("{$data['fecha']} {$data['hora']}")->setSeconds(0);
            $horaFin = $horaInicio->copy()->addHours($data['cantidad_horas']);

            $horaApertura = Carbon::createFromTime(6, 0, 0);
            $horaCierre = Carbon::createFromTime(22, 0, 0);

            if ($horaInicio->lt($horaApertura) || $horaInicio->gte($horaCierre->copy()->addHour())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se permiten reservas entre las 6:00 a.m. y las 10:00 p.m.',
                ], 403);
            }

            if ($horaFin->gt($horaCierre)) {
                $duracion = $horaFin->diffInHours($horaInicio);
                if ($duracion > 2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Después de las 10:00 p.m. solo se permiten reservas de máximo 2 horas.',
                    ], 403);
                }
            }

            $reciente = Reserva::where('user_id', $user->id)
                ->where('created_at', '>=', Carbon::now()->subMinutes(2))
                ->exists();

            if ($reciente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya hiciste una reserva hace menos de 2 minutos. Espera antes de intentar nuevamente.',
                ]);
            }

            $ocupada = Reserva::where('cancha_id', $data['cancha_id'])
                ->where('fecha', $data['fecha'])
                ->where(function ($q) use ($horaInicio, $horaFin) {
                    $q->whereBetween('hora', [$horaInicio->format('H:i'), $horaFin->format('H:i')])
                        ->orWhereBetween('hora_fin', [$horaInicio->format('H:i'), $horaFin->format('H:i')])
                        ->orWhere(function ($q2) use ($horaInicio, $horaFin) {
                            $q2->where('hora', '<', $horaInicio->format('H:i'))
                                ->where('hora_fin', '>', $horaFin->format('H:i'));
                        });
                })
                ->whereIn('estado', ['pendiente', 'confirmada'])
                ->exists();

            if ($ocupada) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cancha ya está ocupada en ese horario. Elige otro horario disponible.',
                ]);
            }

            $reserva = new Reserva([
                'user_id' => $user->id,
                'cancha_id' => $data['cancha_id'],
                'fecha' => $data['fecha'],
                'hora' => $horaInicio->format('H:i'),
                'hora_fin' => $horaFin->format('H:i'),
                'cantidad_horas' => $data['cantidad_horas'],
                'precio_por_cancha' => $data['precio_por_cancha'],
            ]);

            $reserva->save();

            if ($data['precio_por_cancha'] <= 0) {
                $reserva->estado = 'confirmada';
                $reserva->payment_status = 'confirmado';
                $reserva->save();

                Log::info('Reserva confirmada automáticamente sin pago', ['reserva_id' => $reserva->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Reserva confirmada automáticamente (sin pago requerido).',
                    'reserva' => $reserva,
                ], 201);
            }

            $checkout = $this->mercadoPago->createPreference($reserva);

            if ($checkout) {
                $reserva->payment_link = $checkout['payment_link'] ?? ($checkout['preference']->init_point ?? null);
                $reserva->payment_reference = $checkout['payment_reference'] ?? $checkout['preference']->id ?? null;
                $reserva->payment_status = 'pendiente_pago';
                $reserva->payment_id = null;
                $reserva->payment_detail = null;
                $reserva->estado = 'pendiente_verificacion';
                $reserva->save();

                Log::info('Preferencia MercadoPago creada', [
                    'reserva_id' => $reserva->id,
                    'payment_link' => $reserva->payment_link,
                    'reference' => $reserva->payment_reference,
                ]);
            } else {
                Log::warning('No se pudo crear la preferencia en MercadoPago', ['reserva_id' => $reserva->id]);
            }

            $cancha = Cancha::find($data['cancha_id']);
            if ($cancha) {
                if (Schema::hasColumn('canchas', 'disponibilidad')) {
                    $cancha->disponibilidad = false;
                } elseif (Schema::hasColumn('canchas', 'estado_id')) {
                    $cancha->estado_id = 2;
                }
                $cancha->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada. Completa el pago.',
                'reserva_id' => $reserva->id,
                'payment_link' => $reserva->payment_link,
                'payment_status' => $reserva->payment_status,
                'back_urls' => [
                    'success' => config('services.mercadopago.success_url'),
                    'failure' => config('services.mercadopago.failure_url'),
                    'pending' => config('services.mercadopago.pending_url'),
                ],
                'reserva' => $reserva->fresh()->toArray(),
            ], 201);


        } catch (\Throwable $e) {
            Log::error('Error al crear reserva', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al crear la reserva: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar reserva
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $reserva = Reserva::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $reserva->update($request->only(['fecha', 'hora', 'hora_fin', 'estado']));
        return response()->json(['message' => 'Reserva actualizada correctamente', 'reserva' => $reserva]);
    }

    /**
     * Eliminar reserva
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $reserva = Reserva::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $reserva->delete();

        return response()->json(['message' => 'Reserva eliminada correctamente']);
    }

    /**
     * Cancelar reserva
     */
    public function cancelar(Request $request, $id)
    {
        $user = $request->user();

        $reserva = Reserva::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$reserva) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada o no pertenece a tu cuenta.'
            ], 404);
        }

        if ($reserva->estado === 'cancelada') {
            return response()->json([
                'success' => false,
                'message' => 'Esta reserva ya fue cancelada anteriormente.'
            ]);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        $cancha = $reserva->cancha;
        if ($cancha) {
            $cancha->estado_id = 1;
            $cancha->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Reserva cancelada y cancha liberada correctamente.',
            'reserva' => $reserva
        ]);
    }

    /**
     * Mis reservas
     */
    public function misReservas(Request $request)
    {
        $user = $request->user();

        $reservas = Reserva::with('cancha')
            ->where('user_id', $user->id)
            ->orderByDesc('fecha')
            ->get();

        if ($reservas->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No tienes reservas registradas aún.',
                'reservas' => []
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Reservas obtenidas correctamente.',
            'reservas' => $reservas
        ]);
    }

    /**
     * Disponibilidad horaria
     */
    public function disponibilidad($cancha_id, Request $request)
    {
        $fecha = $request->query('fecha', now()->toDateString());

        $availability = $this->availabilityService->availabilityFor((int) $cancha_id, $fecha);

        $legacyBlocks = collect($availability['slots'])->map(fn(array $slot) => [
            'inicio' => $slot['start'],
            'fin' => $slot['end'],
            'disponible' => $slot['available'],
        ]);

        return response()->json([
            'success' => true,
            'cancha_id' => (int) $cancha_id,
            'fecha' => $availability['date'],
            'disponibilidad' => $legacyBlocks,
            'meta' => [
                'step_minutes' => $availability['step_minutes'],
                'next_available' => $availability['next_available'],
            ],
        ]);
    }

    /**
     * Subir comprobante
     */
    public function subirComprobante(Request $request, $id)
    {
        $user = $request->user();
        $reserva = Reserva::findOrFail($id);

        if ($reserva->user_id !== $user->id) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'comprobante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($reserva->comprobante && Storage::disk('public')->exists($reserva->comprobante)) {
            Storage::disk('public')->delete($reserva->comprobante);
        }

        $path = $request->file('comprobante')->store('comprobantes', 'public');

        $reserva->update([
            'comprobante' => $path,
            'estado' => 'pendiente_validacion',
        ]);

        return response()->json([
            'message' => 'Comprobante subido correctamente',
            'comprobante_url' => asset('storage/' . $path),
            'estado' => $reserva->estado,
        ], 200);
    }

    /**
     * Reservas pendientes de validación
     */
    public function pendientes()
    {
        $reservas = Reserva::with('cancha', 'user')
            ->where('estado', 'pendiente_validacion')
            ->orderByDesc('created_at')
            ->get();

        return response()->json($reservas);
    }

    /**
     * Validar pago
     */
    public function validarPago(Request $request, $id)
    {
        $request->validate([
            'accion' => 'required|in:aprobar,rechazar',
        ]);

        $reserva = Reserva::findOrFail($id);

        if ($request->accion === 'aprobar') {
            $reserva->estado = 'confirmada';
        } else {
            $reserva->estado = 'cancelada';
        }

        $reserva->save();

        return response()->json([
            'message' => $request->accion === 'aprobar'
                ? 'Reserva confirmada correctamente.'
                : 'Reserva rechazada correctamente.',
            'estado' => $reserva->estado,
        ]);
    }

    /**
     * Estado de pago
     */
    public function estadoPago($id)
    {
        $reserva = Reserva::with('cancha', 'user')->find($id);

        if (!$reserva) {
            return response()->json([
                'success' => false,
                'message' => 'Reserva no encontrada.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'reserva_id' => $reserva->id,
            'estado' => $reserva->estado,
            'payment_status' => $reserva->payment_status,
            'payment_reference' => $reserva->payment_reference,
            'payment_link' => $reserva->payment_link,
        ]);
    }
}
