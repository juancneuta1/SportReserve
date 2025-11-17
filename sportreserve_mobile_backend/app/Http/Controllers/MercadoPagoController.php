<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Services\MercadoPagoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    public function __construct(private MercadoPagoService $mercadoPago)
    {
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            $type = $request->input('type') ?? $request->input('topic');
            $paymentId = $request->input('data.id') ?? $request->input('id');

            Log::info('MercadoPago webhook recibido', [
                'type' => $type,
                'paymentId' => $paymentId,
                'payload' => $request->all(),
            ]);

            if ($type === 'payment' && $paymentId) {
                $payment = $this->mercadoPago->fetchPayment($paymentId);

                if ($payment && isset($payment->external_reference)) {
                    $reserva = Reserva::find($payment->external_reference);

                    if ($reserva) {
                        $status = $payment->status ?? 'pending';
                        $reserva->payment_status = $status;
                        $reserva->payment_id = $payment->id ?? null;
                        $reserva->payment_detail = json_encode($payment);

                        switch ($status) {
                            case 'approved':
                                $reserva->estado = 'confirmada';
                                break;
                            case 'rejected':
                            case 'cancelled':
                                $reserva->estado = 'cancelada';
                                break;
                            default:
                                $reserva->estado = 'pendiente_verificacion';
                                break;
                        }

                        $reserva->save();

                        Log::info('Reserva actualizada por webhook MercadoPago', [
                            'reserva_id' => $reserva->id,
                            'estado' => $reserva->estado,
                            'payment_status' => $status,
                        ]);
                    } else {
                        Log::warning('Reserva no encontrada para external_reference', ['external_reference' => $payment->external_reference]);
                    }
                }
            } else {
                Log::info('Webhook recibido sin tipo payment válido', ['payload' => $request->all()]);
            }
        } catch (\Throwable $e) {
            Log::error('Error procesando webhook de MercadoPago', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json(['received' => true], 200);
    }
}
