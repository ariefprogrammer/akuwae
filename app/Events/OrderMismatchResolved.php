<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class OrderMismatchResolved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order, public bool $accepted) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('driver.' . $this->order->driver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'order.mismatch-resolved';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'     => $this->order->id,
            'order_number' => $this->order->order_number,
            'accepted'     => $this->accepted,
        ];
    }
}