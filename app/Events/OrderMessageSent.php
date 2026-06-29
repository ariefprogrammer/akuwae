<?php

namespace App\Events;

use App\Models\OrderMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public OrderMessage $message)
    {
        $this->message->load('sender');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('order.' . $this->message->order_id . '.chat'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'id'             => $this->message->id,
            'order_id'       => $this->message->order_id,
            'sender_type'    => $this->message->sender_type,
            'sender_user_id' => $this->message->sender_user_id,
            'message'        => $this->message->message,
            'photo'          => $this->message->photo ? \Storage::url($this->message->photo) : null,
            'created_at'     => $this->message->created_at->format('H:i'),
        ];
    }
}