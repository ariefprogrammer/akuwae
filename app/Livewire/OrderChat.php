<?php

namespace App\Livewire;

use App\Events\OrderMessageSent;
use App\Models\Order;
use App\Models\OrderMessage;
use Livewire\Component;
use Livewire\WithFileUploads;

class OrderChat extends Component
{
    use WithFileUploads;

    public Order $order;
    public bool  $showChat = false;
    public string $newMessage = '';
    public      $newPhoto = null;
    public string $error = '';

    protected $listeners = ['refresh-chat' => '$refresh'];

    public function mount(Order $order)
    {
        $this->order = $order;
    }

    public function getMessagesProperty()
    {
        return $this->order->messages()->with('sender')->get();
    }

    public function getMyTypeProperty(): string
    {
        $user = auth()->user();
        return $user->role === 'driver' ? 'driver' : 'customer';
    }

    public function getCanChatProperty(): bool
    {
        return in_array($this->order->status, [
            'processing', 'ready', 'pickup', 'item_mismatch', 'arrived', 'delivering',
        ]) && $this->order->driver_id !== null;
    }

    public function openChat()
    {
        $this->showChat = true;
        $this->markAsRead();
    }

    public function closeChat()
    {
        $this->showChat = false;
    }

    private function markAsRead(): void
    {
        $this->order->messages()
            ->where('sender_type', '!=', $this->myType)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function sendMessage()
    {
        $this->error = '';

        if (!$this->canChat) {
            $this->error = 'Chat tidak tersedia untuk order ini.';
            return;
        }

        if (empty(trim($this->newMessage)) && !$this->newPhoto) {
            return;
        }

        $this->validate([
            'newMessage' => 'nullable|string|max:1000',
            'newPhoto'   => 'nullable|image|max:2048',
        ]);

        $photoPath = null;
        if ($this->newPhoto) {
            $photoPath = $this->newPhoto->store('orders/chat', 'public');
        }

        $message = OrderMessage::create([
            'order_id'       => $this->order->id,
            'sender_type'    => $this->myType,
            'sender_user_id' => auth()->id(),
            'message'        => trim($this->newMessage) ?: null,
            'photo'          => $photoPath,
            'is_read'        => false,
        ]);

        broadcast(new OrderMessageSent($message))->toOthers();

        $this->newMessage = '';
        $this->newPhoto   = null;
    }

    public function getUnreadCountProperty(): int
    {
        return $this->order->messages()
            ->where('sender_type', '!=', $this->myType)
            ->where('is_read', false)
            ->count();
    }

    public function render()
    {
        return view('livewire.order-chat');
    }
}