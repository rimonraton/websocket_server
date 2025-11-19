<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RidePositionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $rideId;
    public $lat;
    public $lng;
    public $bearing;
    public $speed;
    public $driverId;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($rideId, $lat, $lng, $bearing = null, $speed = null, $driverId = null)
    {
        $this->rideId = $rideId;
        $this->lat = $lat;
        $this->lng = $lng;
        $this->bearing = $bearing;
        $this->speed = $speed;
        $this->driverId = $driverId;
        $this->timestamp = now()->toIso8601String();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('ride.' . $this->rideId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'PositionUpdated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'rideId' => $this->rideId,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'bearing' => $this->bearing,
            'speed' => $this->speed,
            'driverId' => $this->driverId,
            'ts' => $this->timestamp,
        ];
    }
}
