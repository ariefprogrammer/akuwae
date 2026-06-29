<?php

namespace App\Livewire\Customer;

use App\Models\Order;
use App\Models\Wallet;
use Livewire\Component;

class Dashboard extends Component
{
    public string $customerName = '';
    public string $balance      = '0';

    public function mount()
    {
        $user = auth()->user();

        if (!$user->customer) {
            abort(403);
        }

        $this->customerName = $user->customer->name;

        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $this->balance = number_format($wallet->balance, 0, ',', '.');
    }

    public function render()
    {
        $customer = auth()->user()->customer;

        $activeOrder = Order::where('customer_id', $customer->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['location', 'driver'])
            ->latest()
            ->first();

        $recentOrders = Order::where('customer_id', $customer->id)
            ->with(['location', 'makanDetails.tenant', 'customDetail'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.customer.dashboard', [
            'activeOrder'  => $activeOrder,
            'recentOrders' => $recentOrders,
        ])->layout('layouts.app', ['title' => 'Dashboard']);
    }
}