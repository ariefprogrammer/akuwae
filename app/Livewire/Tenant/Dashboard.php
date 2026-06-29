<?php

namespace App\Livewire\Tenant;

use App\Models\Order;
use App\Models\OrderMakanItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    // Statistik pendapatan
    public string $earningsToday     = '0';
    public string $earningsThisMonth = '0';
    public string $earningsAllTime   = '0';

    // Data chart
    public array $chartLabels = [];
    public array $chartData   = [];

    // Menu terlaris
    public $topMenus = [];

    public function mount()
    {
        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return redirect()->route('tenant.onboarding');
        }

        $this->loadEarnings();
        $this->loadChartData();
        $this->loadTopMenus();
    }

    private function baseCompletedOrdersQuery()
    {
        $tenant = auth()->user()->tenant;

        return Order::whereHas('makanDetails', fn($q) =>
                $q->where('tenant_id', $tenant->id)
            )
            ->where('status', 'completed');
    }

    public function loadEarnings(): void
    {
        $today = (clone $this->baseCompletedOrdersQuery())
            ->whereDate('updated_at', today())
            ->sum('total_fare');

        $thisMonth = (clone $this->baseCompletedOrdersQuery())
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('total_fare');

        $allTime = (clone $this->baseCompletedOrdersQuery())
            ->sum('total_fare');

        $this->earningsToday     = number_format($today, 0, ',', '.');
        $this->earningsThisMonth = number_format($thisMonth, 0, ',', '.');
        $this->earningsAllTime   = number_format($allTime, 0, ',', '.');
    }

    public function loadChartData(): void
    {
        $tenant = auth()->user()->tenant;

        $rows = Order::whereHas('makanDetails', fn($q) =>
                $q->where('tenant_id', $tenant->id)
            )
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays(29)->startOfDay())
            ->select(
                DB::raw('DATE(updated_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = [];
        $data   = [];

        for ($i = 29; $i >= 0; $i--) {
            $date     = now()->subDays($i)->format('Y-m-d');
            $label    = now()->subDays($i)->format('d/m');
            $labels[] = $label;
            $data[]   = $rows[$date] ?? 0;
        }

        $this->chartLabels = $labels;
        $this->chartData   = $data;
    }

    public function loadTopMenus(): void
    {
        $tenant = auth()->user()->tenant;

        $this->topMenus = OrderMakanItem::query()
            ->select(
                'order_makan_items.menu_id',
                DB::raw('SUM(order_makan_items.quantity) as total_qty'),
                DB::raw('SUM(order_makan_items.quantity * order_makan_items.price_snapshot) as total_revenue')
            )
            ->join('order_makan_details', 'order_makan_details.id', '=', 'order_makan_items.order_makan_detail_id')
            ->join('orders', 'orders.id', '=', 'order_makan_details.order_id')
            ->where('order_makan_details.tenant_id', $tenant->id)
            ->where('orders.status', 'completed')
            ->groupBy('order_makan_items.menu_id')
            ->orderByDesc('total_qty')
            ->with('menu.photos')
            ->take(5)
            ->get();
    }

    public function render()
    {
        $tenant = auth()->user()->tenant;

        return view('livewire.tenant.dashboard', compact('tenant'))
            ->layout('layouts.app', ['title' => 'Dashboard Toko']);
    }
}