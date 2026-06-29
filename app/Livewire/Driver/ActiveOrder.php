<?php

namespace App\Livewire\Driver;

use App\Models\Order;
use Livewire\Component;

class ActiveOrder extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        $this->order = $order->load(['location', 'customer.user']);
    }

    public function render()
    {
        return view('livewire.driver.active-order')
            ->layout('layouts.app', ['title' => 'Order Aktif']);
    }
}