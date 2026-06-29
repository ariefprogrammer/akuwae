<?php

namespace App\Livewire\Tenant\Order;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    private array $activeStatuses = [
        'waiting_tenant', 'preparing', 'processing', 'ready', 'delivering',
    ];

    public function render()
    {
        $tenant = auth()->user()->tenant;

        $activeOrders = Order::with(['location', 'driver', 'customer', 'makanDetails.items.menu'])
            ->whereHas('makanDetails', fn($q) => $q->where('tenant_id', $tenant->id))
            ->whereIn('status', $this->activeStatuses)
            ->latest()
            ->paginate(10, ['*'], 'activePage');

        $historyOrders = Order::with(['location', 'driver', 'customer', 'makanDetails.items.menu'])
            ->whereHas('makanDetails', fn($q) => $q->where('tenant_id', $tenant->id))
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest()
            ->paginate(10, ['*'], 'historyPage');

        return view('livewire.tenant.order.order-list', [
            'activeOrders'  => $activeOrders,
            'historyOrders' => $historyOrders,
        ])->layout('layouts.app', ['title' => 'Pesanan']);
    }
}