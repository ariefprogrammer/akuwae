<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->load(['location', 'antarDetail', 'customDetail']);
    }

    // Broadcast ke channel driver yang online & sesuai vehicle type
    public function broadcastOn(): array
    {
        return [
            new Channel('drivers.orders'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        $vehicleType = $this->order->antarDetail?->requested_vehicle_type
            ?? $this->order->customDetail?->vehicle_type
            ?? 'motor';

        return [
            'order_id'            => $this->order->id,
            'order_number'        => $this->order->order_number,
            'service_type'        => $this->order->service_type,
            'total_fare'          => $this->order->total_fare,
            'driver_earnings'     => $this->order->driver_earnings,
            'payment_method'      => $this->order->payment_method,
            'vehicle_type'        => $vehicleType,
            'origin_address'      => $this->order->location->origin_address,
            'origin_latitude'       => $this->order->location->origin_latitude,
            'origin_longitude'      => $this->order->location->origin_longitude,
            'destination_address' => $this->order->location->destination_address,
            'destination_latitude'  => $this->order->location->destination_latitude,
            'destination_longitude' => $this->order->location->destination_longitude,
            'distance_km'         => $this->order->location->distance_km,
            'notes_for_driver'    => $this->order->location->notes_for_driver,
            'item_description'      => $this->order->customDetail?->item_description,
        ];
    }
}