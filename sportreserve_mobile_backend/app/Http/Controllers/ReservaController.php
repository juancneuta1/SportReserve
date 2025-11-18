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
     * =====================================================================
     * LISTAR RESERVAS DEL USUARIO AUTENTICADO
     * =====================================================================
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
     * =====================================================================
     * CREAR RESERVA (VERSIÓN COMPLETA + CORREGIDA)
     * =====================================================================
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            // -----------------------------
            // VALIDACIÓN INICIAL
            // -----------------------------
            $validator = Validator::make($request->all(), [
                'cancha_id' => 'required|exists:canchas,id',
                'deporte' => 'required|string',
                'fecha' => 'required|date',
                'hora' => 'required|date_format:H:i',
                'cantidad_horas' => 'required|integer|min:1|max:5',
            ]);

            if ($validator->fails()) {
                Log::warning('❌ Validación fallida en reserva', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $cancha = Cancha::find($data['cancha_id']);

            if (!$cancha) {
                return response()->json(['success' => false, 'message' => 'La cancha no existe.'], 404);
            }

            // Validar que el deporte esté disponible en la cancha
            $tiposCancha = collect($cancha->tipo ?? [])
                ->map(fn ($v) => trim((string) $v))
                ->filter()
                ->values();

            if ($tiposCancha->isNotEmpty() && !$tiposCancha->contains($data['deporte'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'El deporte seleccionado no está disponible para esta cancha.',
                    'disponibles' => $tiposCancha,
                ], 422);
            }

            // =====================================================================
            // tomar precio SIEMPRE del backend
            // =====================================================================
            $precioPorHora = (float) $cancha->precio_por_hora;
            $precioTotal = $precioPorHora * (int) $data['cantidad_horas'];

            // -----------------------------------------------------------------
            // MONTO MÍNIMO PASARELA (Mercado Pago CO) → evita errores sandbox
            // -----------------------------------------------------------------
            $valorMinimo = 10000; // $10.000 COP recomendado para pagos electrónicos

            if ($precioTotal < $valorMinimo && $precioTotal > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'El valor mínimo para procesar pagos electrónicos es de $' . number_format($valorMinimo, 0, ',', '.') . ' COP.',
                    'detail' => 'Aumenta la tarifa por hora o la cantidad de horas para continuar con el pago.',
                ], 422);
            }

            // -----------------------------
            // PARSEAR HORAS
            // -----------------------------

            $horaInicio = Carbon::parse("{$data['fecha']} {$data['hora']}")->setSeconds(0);
            $horaFin = $horaInicio->copy()->addHours($data['cantidad_horas']);

            $horaApertura = Carbon::createFromTime(6, 0);
            $horaCierre = Carbon::createFromTime(22, 0);

            if ($horaInicio->lt($horaApertura) || $horaInicio->gte($horaCierre->copy()->addHour())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se permiten reservas entre 6:00 AM y 10:00 PM.',
                ], 403);
            }

            if ($horaFin->gt($horaCierre) && $horaFin->diffInHours($horaInicio) > 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Después de las 10:00 PM solo se permiten reservas de máximo 2 horas.',
                ], 403);
            }

            // -----------------------------
            // EVITAR SPAM (últimos 2 minutos)
            // -----------------------------

            $reciente = Reserva::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subMinutes(2))
                ->exists();

            if ($reciente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya hiciste una reserva hace menos de 2 minutos.',
                ]);
            }

            // -----------------------------
            // VERIFICAR SI LA CANCHA ESTÁ OCUPADA
            // -----------------------------

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
                ->whereIn('estado', ['pendiente', 'pendiente_pago', 'confirmada'])
                ->exists();

            if ($ocupada) {
                return response()->json([
                    'success' => false,
                    'message' => 'La cancha ya está ocupada en ese horario.',
                ], 409);
            }

            // -----------------------------
            // CREAR RESERVA EN ESTADO PENDIENTE
            // -----------------------------

            $reserva = Reserva::create([
                'user_id' => $user->id,
                'cancha_id' => $data['cancha_id'],
                'deporte' => $data['deporte'],
                'fecha' => $data['fecha'],
                'hora' => $horaInicio->format('H:i'),
                'hora_fin' => $horaFin->format('H:i'),
                'cantidad_horas' => $data['cantidad_horas'],
                'precio_por_cancha' => $precioPorHora,

                // Estado compatible con CHECK
                'estado' => $precioTotal > 0 ? 'pendiente' : 'confirmada',

                // Estado del pago separado
                'payment_status' => $precioTotal > 0 ? 'pendiente_pago' : 'not_required',
            ]);


            // =====================================================================
            // CANCHA GRATIS → CONFIRMAR DIRECTO
            // =====================================================================

            if ($precioTotal <= 0) {
                Log::info("Reserva gratuita confirmada automáticamente", ['reserva_id' => $reserva->id]);

                return response()->json([
                    'success' => true,
                    'message' => 'Reserva confirmada automáticamente (sin pago).',
                    'payment_link' => null,
                    'reserva' => $reserva,
                ], 201);
            }

            // =====================================================================
            // SI MERCADOPAGO FALLA, NO SE CONFIRMA NADA
            // =====================================================================

            $checkout = $this->mercadoPago->createPreference($reserva);

            if (!$checkout || empty($checkout['payment_link'])) {

                // ❗ ELIMINAR LA RESERVA SI FALLA MERCADOPAGO
                $reserva->delete();

                Log::warning("❌ No se generó payment_link, reserva eliminada automáticamente.");

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo iniciar la pasarela de pago. Intenta nuevamente.',
                ], 422);
            }

            // ----------------------------------------------------------
            // GUARDAR DATOS DE LA PREFERENCIA
            // ----------------------------------------------------------
            
            $reserva->update([
                'payment_link' => $checkout['payment_link'],
                'payment_reference' => $checkout['payment_reference'] ?? null,
                'payment_status' => 'pendiente_pago',
                'estado' => 'pendiente_verificacion',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada, completa el pago.',
                'payment_link' => $reserva->payment_link,
                'payment_status' => $reserva->payment_status,
                'reserva' => $reserva,
                'back_urls' => $checkout['back_urls'] ?? [],
            ], 201);

        } catch (\Throwable $e) {

            Log::error("❌ Error al crear reserva", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => "Error interno: {$e->getMessage()}",
            ], 500);
        }
    }


    /**
     * =====================================================================
     * ACTUALIZAR RESERVA
     * =====================================================================
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $reserva = Reserva::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $reserva->update($request->only(['fecha', 'hora', 'hora_fin', 'estado']));

        return response()->json([
            'message' => 'Reserva actualizada correctamente',
            'reserva' => $reserva
        ]);
    }

    /**
     * =====================================================================
     * ELIMINAR RESERVA
     * =====================================================================
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
     * =====================================================================
     * CANCELAR RESERVA
     * =====================================================================
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
                'message' => 'Reserva no encontrada.'
            ], 404);
        }

        if ($reserva->estado === 'cancelada') {
            return response()->json([
                'success' => false,
                'message' => 'Esta reserva ya está cancelada.'
            ]);
        }

        $reserva->estado = 'cancelada';
        $reserva->save();

        if ($reserva->cancha) {
            $reserva->cancha->estado_id = 1;
            $reserva->cancha->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Reserva cancelada correctamente.',
            'reserva' => $reserva
        ]);
    }

    /**
     * =====================================================================
     * MIS RESERVAS
     * =====================================================================
     */
    public function misReservas(Request $request)
    {
        $user = $request->user();

        $reservas = Reserva::with('cancha')
            ->where('user_id', $user->id)
            ->orderByDesc('fecha')
            ->get();

        return response()->json([
            'success' => true,
            'reservas' => $reservas
        ]);
    }

    /**
     * =====================================================================
     * DISPONIBILIDAD HORARIA
     * =====================================================================
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
     * =====================================================================
     * SUBIR COMPROBANTE
     * =====================================================================
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
     * =====================================================================
     * ADMINS — RESERVAS PENDIENTES DE VALIDACIÓN
     * =====================================================================
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
     * =====================================================================
     * VALIDAR PAGO MANUAL (ADMIN)
     * =====================================================================
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
     * =====================================================================
     * CONSULTAR ESTADO DE PAGO
     * =====================================================================
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
