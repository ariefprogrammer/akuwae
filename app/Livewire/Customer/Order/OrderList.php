<?php

namespace App\Livewire\Customer\Order;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    private array $activeStatuses = [
        'waiting_tenant', 'preparing', 'finding_driver',
        'processing', 'ready', 'pickup',
        'item_mismatch', 'arrived', 'delivering',
    ];

    public function render()
    {
        $customer = auth()->user()->customer;

        $activeOrders = Order::with(['location', 'driver', 'makanDetails.tenant', 'customDetail'])
            ->where('customer_id', $customer->id)
            ->whereIn('status', $this->activeStatuses)
            ->latest()
            ->paginate(10, ['*'], 'activePage');

        $historyOrders = Order::with(['location', 'driver', 'makanDetails.tenant', 'customDetail'])
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest()
            ->paginate(10, ['*'], 'historyPage');

        return view('livewire.customer.order.order-list', [
            'activeOrders'  => $activeOrders,
            'historyOrders' => $historyOrders,
        ])->layout('layouts.app', ['title' => 'Pesanan Saya']);
    }
}