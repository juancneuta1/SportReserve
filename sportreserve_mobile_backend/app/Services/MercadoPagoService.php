<?php

namespace App\Services;

use App\Models\Reserva;
use Illuminate\Support\Facades\Log;
use MercadoPago\Payment;
use MercadoPago\Preference;
use MercadoPago\SDK;

class MercadoPagoService
{
    public function __construct()
    {
        $token = config('services.mercadopago.access_token');

        if ($token) {
            SDK::setAccessToken($token);
        } else {
            Log::error('MercadoPago: falta MERCADOPAGO_ACCESS_TOKEN');
        }
    }

    /**
     * Crea una preferencia de pago y devuelve datos relevantes.
     */
    public function createPreference(Reserva $reserva): ?array
    {
        $token = config('services.mercadopago.access_token');
        if (!$token) {
            Log::error('MercadoPago: token de acceso no configurado');
            return null;
        }

        try {
            $reserva->loadMissing('cancha', 'user');
            $amount = (float) $reserva->cantidad_horas * (float) $reserva->precio_por_cancha;

            $successUrl = config('services.mercadopago.success_url');
            $failureUrl = config('services.mercadopago.failure_url');
            $pendingUrl = config('services.mercadopago.pending_url');
            $notificationUrl = config('services.mercadopago.notification_url');

            $preference = new Preference();
            $preference->items = [
                [
                    'title' => 'Reserva de cancha ' . ($reserva->cancha->nombre ?? 'SportReserve'),
                    'quantity' => 1,
                    'unit_price' => round($amount, 2),
                    'currency_id' => 'COP',
                ],
            ];

            $preference->payer = (object) [
                'name' => $reserva->user->name ?? 'Invitado',
                'email' => $reserva->user->email ?? 'invitado@sportreserve.com',
                'identification' => (object) [
                    'type' => 'CC',
                    'number' => $reserva->user->id ?? '000000',
                ],
            ];

            $preference->back_urls = [
                'success' => $successUrl,
                'failure' => $failureUrl,
                'pending' => $pendingUrl,
            ];
            $preference->notification_url = $notificationUrl;
            $preference->binary_mode = true;
            $preference->auto_return = 'approved';
            $preference->external_reference = (string) $reserva->id;

            $preference->save();


            $forceSandbox = filter_var(config('services.mercadopago.force_sandbox'), FILTER_VALIDATE_BOOLEAN);
            $checkoutUrl = $forceSandbox
                ? ($preference->sandbox_init_point ?? $preference->init_point)
                : ($preference->init_point ?? $preference->sandbox_init_point);

            Log::info('MercadoPago: preferencia creada', [
                'reserva_id' => $reserva->id,
                'preference_id' => $preference->id,
                'init_point' => $preference->init_point ?? null,
                'sandbox_init_point' => $preference->sandbox_init_point ?? null,
            ]);

            return [
                'payment_link' => $checkoutUrl,
                'payment_reference' => $preference->id,
                'preference' => $preference,
            ];
        } catch (\Throwable $e) {
            Log::error('MercadoPago: error al crear preferencia', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Consulta un pago por ID en Mercado Pago.
     */
    public function fetchPayment(string $paymentId)
    {
        try {
            return Payment::find_by_id($paymentId);
        } catch (\Throwable $e) {
            Log::error('MercadoPago: error consultando pago', [
                'payment_id' => $paymentId,
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
