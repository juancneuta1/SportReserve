<?php

namespace App\Providers;

use App\Events\CourtAvailabilityUpdated;
use App\Mail\UserReservationCreated;
use App\Models\AdminNotification;
use App\Models\Reserva;
use App\Services\CourtAvailabilityService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Reserva::created(function (Reserva $reserva) {
            AdminNotification::create([
                'type' => 'reserva',
                'title' => 'Nueva reserva creada',
                'body' => "Reserva #{$reserva->id} en {$reserva->fecha} a las {$reserva->hora}",
            ]);

            $reserva->loadMissing(['user', 'cancha']);

            if ($reserva->user && $reserva->user->email) {
                Mail::to($reserva->user->email)->send(new UserReservationCreated($reserva));
            }

            $this->broadcastAvailability($reserva);
        });

        Reserva::updated(function (Reserva $reserva) {
            $this->broadcastAvailability($reserva);
        });

        Reserva::deleted(function (Reserva $reserva) {
            $this->broadcastAvailability($reserva);
        });
    }

    private function broadcastAvailability(Reserva $reserva): void
    {
        if (! $reserva->cancha_id || ! $reserva->fecha) {
            return;
        }

        $availability = app(CourtAvailabilityService::class)
            ->availabilityFor($reserva->cancha_id, $reserva->fecha);

        CourtAvailabilityUpdated::dispatch(
            $reserva->cancha_id,
            $availability['date'],
            $availability['slots']
        );
    }
}
