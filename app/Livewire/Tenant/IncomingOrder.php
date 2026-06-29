<?php

namespace App\Livewire\Tenant;

use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Models\Order;
use App\Models\Tenant;
use Livewire\Component;

class IncomingOrder extends Component
{
    public ?array  $incomingOrder = null;
    public ?Tenant $tenant        = null;
    public string  $error         = '';

    protected $listeners = ['incoming-order-tenant' => 'setIncomingOrder'];

    public function mount()
    {
        $this->tenant = auth()->user()->tenant;
    }

    public function setIncomingOrder(array $orderData): void
    {
        $this->incomingOrder = $orderData;
    }

    public function acceptOrder(): void
    {
        if (!$this->incomingOrder) return;

        $order = Order::find($this->incomingOrder['order_id']);

        if (!$order || $order->status !== 'waiting_tenant') {
            $this->error        = 'Order sudah tidak tersedia.';
            $this->incomingOrder = null;
            return;
        }

        $order->update(['status' => 'preparing']);

        broadcast(new OrderStatusUpdated($order));
        broadcast(new OrderCreated($order));

        // Kirim Web Push ke semua driver online dengan vehicle_type sesuai
        $vehicleType = $order->antarDetail?->requested_vehicle_type ?? 'motor';

        $onlineDrivers = \App\Models\Driver::with('user')
            ->where('is_online', true)
            ->where('verification_status', 'approved')
            ->where('vehicle_type', $vehicleType)
            ->get();

        foreach ($onlineDrivers as $driver) {
            if ($driver->user) {
                $driver->user->notify(new \App\Notifications\NewOrderNotification(
                    title: 'Order Baru Tersedia!',
                    body: "Order {$order->order_number} menunggu driver.",
                    url: '/driver/dashboard'
                ));
            }
        }

        $this->incomingOrder = null;

        // Trigger ActiveOrder component untuk refresh
        $this->dispatch('order-accepted');
    }

    public function rejectOrder(): void
    {
        if (!$this->incomingOrder) return;

        $order = Order::find($this->incomingOrder['order_id']);

        if ($order && $order->status === 'waiting_tenant') {
            $order->update(['status' => 'cancelled']);
            broadcast(new OrderStatusUpdated($order));
        }

        $this->incomingOrder = null;
    }

    public function render()
    {
        return view('livewire.tenant.incoming-order');
    }
}