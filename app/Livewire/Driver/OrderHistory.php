<?php

namespace App\Livewire\Driver;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class OrderHistory extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public function render()
    {
        $driver = auth()->user()->driver;

        $orders = Order::with(['location', 'customer.user', 'makanDetails.tenant', 'customDetail'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'cancelled'])
            ->latest()
            ->paginate(15);

        return view('livewire.driver.order-history', [
            'orders' => $orders,
        ])->layout('layouts.app', ['title' => 'Riwayat Order']);
    }
}