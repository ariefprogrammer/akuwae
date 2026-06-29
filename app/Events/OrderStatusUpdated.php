<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->load(['makanDetails', 'customDetail']);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('order.' . $this->order->id), // broadcast ke PrivateChannel
        ];

        if ($this->order->service_type === 'makan') {
            foreach ($this->order->makanDetails as $detail) {
                $channels[] = new PrivateChannel('tenant.' . $detail->tenant_id);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id'        => $this->order->id,
            'status'          => $this->order->status,
            'driver_id'       => $this->order->driver_id,
            'mismatch_reason' => $this->order->customDetail?->mismatch_reason,
        ];
    }
}