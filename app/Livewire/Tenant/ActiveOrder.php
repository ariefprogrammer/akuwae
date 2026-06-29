<?php

namespace App\Livewire\Tenant;

use App\Events\OrderReady;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Tenant;
use Livewire\Component;

class ActiveOrder extends Component
{
    public ?Tenant $tenant       = null;
    public string  $success      = '';

    protected $listeners = [
    'order-accepted'       => 'loadActiveOrders',
    'order-status-updated' => 'loadActiveOrders',
    'refresh-active-orders' => '$refresh',
];

    public function mount()
    {
        $this->tenant = auth()->user()->tenant;
    }

    public function loadActiveOrders(): void
    {
        // Trigger re-render dengan query terbaru
        $this->success = '';
    }

    public function markReady(int $orderId): void
    {
        $order = Order::with(['location'])->findOrFail($orderId);

        if ($order->status !== 'preparing' && $order->status !== 'processing') {
            return;
        }

        $order->update(['status' => 'ready']);

        // Broadcast ke customer
        broadcast(new OrderStatusUpdated($order));

        // Broadcast notif ke driver
        if ($order->driver_id) {
            broadcast(new OrderReady($order));
        }

        $this->success = 'Pesanan ' . $order->order_number . ' ditandai siap diambil!';
    }

    public function render()
    {
        $activeOrders = Order::with(['location', 'makanDetails.items.menu', 'driver',])
            ->whereHas('makanDetails', fn($q) =>
                $q->where('tenant_id', $this->tenant->id)
            )
            ->whereIn('status', ['preparing', 'processing', 'ready', 'delivering'])
            ->latest()
            ->get();

        return view('livewire.tenant.active-order', compact('activeOrders'));
    }
}