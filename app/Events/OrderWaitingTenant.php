<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;

class OrderWaitingTenant implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->load(['location', 'makanDetails.tenant', 'makanDetails.items.menu']);
    }

    public function broadcastOn(): array
    {
        // Broadcast ke channel per tenant yang ada di order
        $channels = [];
        foreach ($this->order->makanDetails as $detail) {
            $channels[] = new PrivateChannel('tenant.' . $detail->tenant_id);
        }
        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'order.waiting';
    }

    public function broadcastWith(): array
    {
        $items = [];
        foreach ($this->order->makanDetails as $detail) {
            foreach ($detail->items as $item) {
                $items[] = [
                    'item_name' => $item->menu->item_name,
                    'quantity'  => $item->quantity,
                    'notes'     => $item->notes,
                    'price'     => $item->price_snapshot,
                ];
            }
        }

        return [
            'order_id'           => $this->order->id,
            'order_number'       => $this->order->order_number,
            'total_fare'         => $this->order->total_fare,
            'destination_address'=> $this->order->location->destination_address,
            'distance_km'        => $this->order->location->distance_km,
            'notes_for_driver'   => $this->order->location->notes_for_driver,
            'items'              => $items,
        ];
    }
}