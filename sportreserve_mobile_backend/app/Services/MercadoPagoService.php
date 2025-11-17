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
     * Crear preferencia de Mercado Pago.
     */
    public function createPreference(Reserva $reserva): ?array
    {
        $token = config('services.mercadopago.access_token');
        if (!$token) {
            Log::error('MercadoPago: token de acceso no configurado');
            return null;
        }

        try {
            // Cargar relaciones si no existen
            $reserva->loadMissing(['cancha', 'user']);

            $amount = (float) $reserva->cantidad_horas * (float) $reserva->precio_por_cancha;

            //
            // ===============================================================
            // URLs IMPORTANTES DEL .env (actualizadas con Cloudflare Tunnel)
            // ===============================================================
            //
            $successUrl = config('services.mercadopago.success_url');
            $failureUrl = config('services.mercadopago.failure_url');
            $pendingUrl = config('services.mercadopago.pending_url');
            $notificationUrl = config('services.mercadopago.notification_url');

            if (!$successUrl || !$failureUrl || !$pendingUrl) {
                Log::warning('MercadoPago: faltan URLs de callbacks en .env');
            }

            // ===============================================================
            // Configurar preferencia
            // ===============================================================
            $preference = new Preference();

            // Item
            $preference->items = [
                [
                    'title' => 'Reserva de cancha ' . ($reserva->cancha->nombre ?? 'SportReserve'),
                    'quantity' => 1,
                    'unit_price' => round($amount, 2),
                    'currency_id' => 'COP',
                ],
            ];

            // Cliente
            $preference->payer = (object) [
                'name' => $reserva->user->name ?? 'Invitado',
                'email' => $reserva->user->email ?? 'invitado@sportreserve.com',
                'identification' => (object) [
                    'type' => 'CC',
                    'number' => $reserva->user->id ?? '000000',
                ],
            ];

            // ===============================================================
            // URLs de retorno (Flutter WebView las usa para cerrar correctamente)
            // ===============================================================
            $preference->back_urls = [
                'success' => $successUrl,
                'failure' => $failureUrl,
                'pending' => $pendingUrl,
            ];

            // Retorno automático al éxito
            $preference->auto_return = 'approved';

            // Webhook
            $preference->notification_url = $notificationUrl;

            // Modo seguro de aprobación inmediata
            $preference->binary_mode = true;

            // Para relacionar el pago con la reserva
            $preference->external_reference = (string) $reserva->id;

            // Guardar preferencia en MercadoPago
            $preference->save();

            // ===============================================================
            // Modo Sandbox / Producción
            // ===============================================================
            $forceSandbox = filter_var(config('services.mercadopago.force_sandbox'), FILTER_VALIDATE_BOOLEAN);

            $checkoutUrl = $forceSandbox
                ? ($preference->sandbox_init_point ?? $preference->init_point)
                : ($preference->init_point ?? $preference->sandbox_init_point);

            // Log bonito para depurar
            Log::info('MercadoPago: preferencia creada correctamente', [
                'reserva_id' => $reserva->id,
                'preference_id' => $preference->id,
                'monto' => $amount,
                'sandbox' => $forceSandbox,
                'init_point' => $preference->init_point,
                'sandbox_init_point' => $preference->sandbox_init_point,
                'checkout_url' => $checkoutUrl,
            ]);

            return [
                'payment_link' => $checkoutUrl,
                'payment_reference' => $preference->id,
                'preference' => $preference,
                'back_urls' => $preference->back_urls,
            ];
        } catch (\Throwable $e) {
            Log::error('MercadoPago: error al crear preferencia', [
                'exception' => $e->getMessage(),
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
