<?php

namespace App\Services;

use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CourtAvailabilityService
{
    public function availabilityFor(int $canchaId, string $date): array
    {
        $opening = Carbon::parse("{$date} " . config('sportreserve.availability_opening_hour'));
        $closing = Carbon::parse("{$date} " . config('sportreserve.availability_closing_hour'));
        $step = max(15, config('sportreserve.availability_step_minutes'));

        $reservas = Reserva::query()
            ->select(['hora', 'hora_fin', 'estado'])
            ->where('cancha_id', $canchaId)
            ->whereDate('fecha', $date)
            ->whereIn('estado', ['pendiente', 'pendiente_verificacion', 'confirmada'])
            ->get()
            ->map(function (Reserva $reserva) use ($date, $opening) {
                return [
                    'start' => $this->normalizeTime($reserva->hora, $date, $opening),
                    'end' => $this->normalizeTime($reserva->hora_fin ?? $reserva->hora, $date, $opening),
                ];
            });

        $slots = [];
        $cursor = $opening->copy();

        while ($cursor < $closing) {
            $slotStart = $cursor->copy();
            $slotEnd = $cursor->copy()->addMinutes($step);

            $hasOverlap = $this->hasOverlap($reservas, $slotStart, $slotEnd);

            $slots[] = [
                'start' => $slotStart->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'available' => ! $hasOverlap,
            ];

            $cursor->addMinutes($step);
        }

        return [
            'date' => $date,
            'step_minutes' => $step,
            'slots' => $slots,
            'next_available' => collect($slots)->firstWhere('available', true),
        ];
    }

    protected function hasOverlap(Collection $reservas, Carbon $slotStart, Carbon $slotEnd): bool
    {
        return $reservas->contains(function (array $reserva) use ($slotStart, $slotEnd) {
            return $slotStart < $reserva['end'] && $slotEnd > $reserva['start'];
        });
    }

    private function normalizeTime(?string $time, string $date, Carbon $reference): Carbon
    {
        $base = trim((string) $time) !== ''
            ? Carbon::parse("{$date} {$time}", $reference->timezone)
            : $reference->copy();

        return $base->setSeconds(0);
    }
}
