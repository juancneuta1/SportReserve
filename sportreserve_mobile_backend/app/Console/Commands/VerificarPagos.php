<?php

namespace App\Console\Commands;

use App\Models\Reserva;
use App\Services\MercadoPagoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerificarPagos extends Command
{
    protected $signature = 'pagos:verificar';

    protected $description = 'Verifica y sincroniza los pagos pendientes con Mercado Pago';

    public function __construct(private MercadoPagoService $mercadoPago)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $pendientes = Reserva::whereIn('payment_status', ['pending', 'in_process', 'in_mediation'])
            ->whereNotNull('payment_id')
            ->get();

        if ($pendientes->isEmpty()) {
            $this->info('No hay reservas pendientes de verificación.');
            return Command::SUCCESS;
        }

        $actualizadas = 0;

        foreach ($pendientes as $reserva) {
            $payment = $this->mercadoPago->fetchPayment($reserva->payment_id);

            if (! $payment) {
                Log::warning('MP: no se pudo obtener el pago', ['reserva_id' => $reserva->id, 'payment_id' => $reserva->payment_id]);
                continue;
            }

            $estadoAnterior = $reserva->payment_status;
            $nuevoEstadoPago = $payment->status ?? $estadoAnterior;

            $reserva->payment_status = $nuevoEstadoPago;
            $reserva->payment_detail = json_encode($payment);

            if ($nuevoEstadoPago === 'approved') {
                $reserva->estado = 'confirmada';
            } elseif (in_array($nuevoEstadoPago, ['rejected', 'cancelled'])) {
                $reserva->estado = 'cancelada';
            } else {
                $reserva->estado = 'pendiente_verificacion';
            }

            $reserva->save();
            $actualizadas++;

            Log::info('MP: reserva actualizada', [
                'reserva_id' => $reserva->id,
                'estado_anterior' => $estadoAnterior,
                'estado_nuevo' => $reserva->payment_status,
                'fecha' => now()->toDateTimeString(),
            ]);
        }

        $this->info("Reservas verificadas: {$pendientes->count()}. Actualizadas: {$actualizadas}.");

        return Command::SUCCESS;
    }
}
