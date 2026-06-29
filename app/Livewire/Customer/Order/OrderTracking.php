<?php

namespace App\Livewire\Customer\Order;

use App\Events\OrderMismatchResolved;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use Livewire\Component;

class OrderTracking extends Component
{
    public Order $order;

    // Tambahkan ini
    public function getListeners(): array
    {
        return [
            "echo-private:order.{$this->order->id},.status.updated" => 'handleStatusUpdated',
        ];
    }

    public function mount(Order $order)
    {
        if ($order->customer_id !== auth()->user()->customer->id) {
            abort(403);
        }

        $this->order = $order->load(['location', 'driver', 'antarDetail', 'customDetail']);
    }

    // Ganti refreshOrder agar bisa terima payload dari WebSocket
    public function handleStatusUpdated(array $data): void
    {
        $this->order->refresh()->load(['location', 'driver', 'antarDetail', 'customDetail']);

        // Redirect ke dashboard 3 detik setelah driver ditemukan
        if ($this->order->status === 'processing') {
            $this->dispatch('driver-found');
        }
    }

    public function refreshOrder(): void
    {
        $this->order->refresh()->load(['location', 'driver', 'antarDetail', 'customDetail']);
    }

    public function acceptMismatch(): void
    {
        $this->order->update(['status' => 'delivering']);
        $this->order->refresh()->load(['location', 'driver', 'antarDetail', 'customDetail']);

        broadcast(new OrderStatusUpdated($this->order));
        broadcast(new OrderMismatchResolved($this->order, true));
    }

    public function cancelMismatch(): void
    {
        $this->order->update(['status' => 'cancelled']);
        $this->order->refresh()->load(['location', 'driver', 'antarDetail', 'customDetail']);

        broadcast(new OrderStatusUpdated($this->order));
        broadcast(new OrderMismatchResolved($this->order, false));
    }

    public function render()
    {
        return view('livewire.customer.order.order-tracking')
            ->layout('layouts.app', ['title' => 'Tracking Order']);
    }
    
}