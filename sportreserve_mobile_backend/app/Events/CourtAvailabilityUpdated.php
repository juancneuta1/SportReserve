<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CourtAvailabilityUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $canchaId,
        public string $date,
        public array $slots
    ) {
    }

    public function broadcastOn(): Channel
    {
        return new Channel("courts.{$this->canchaId}");
    }

    public function broadcastAs(): string
    {
        return 'availability.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'cancha_id' => $this->canchaId,
            'date' => $this->date,
            'slots' => $this->slots,
        ];
    }
}

