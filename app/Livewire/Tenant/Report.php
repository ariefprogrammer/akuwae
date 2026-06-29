<?php

namespace App\Livewire\Tenant;

use App\Models\Order;
use App\Models\OrderMakanItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Report extends Component
{
    public string $startDate;
    public string $endDate;

    // Ringkasan pendapatan
    public string $grossRevenue   = '0'; 
    public string $platformFee    = '0'; 
    public string $netRevenue     = '0'; 
    public int    $totalOrders    = 0;
    public $orderDetails = [];

    // Menu terlaris
    public $topMenus = [];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate   = now()->format('Y-m-d');

        $this->loadReport();
    }

    public function updatedStartDate()
    {
        $this->loadReport();
    }

    public function updatedEndDate()
    {
        $this->loadReport();
    }

    public function setQuickRange(string $range): void
    {
        match ($range) {
            'today'     => [
                $this->startDate = now()->format('Y-m-d'),
                $this->endDate   = now()->format('Y-m-d'),
            ],
            'week'      => [
                $this->startDate = now()->startOfWeek()->format('Y-m-d'),
                $this->endDate   = now()->format('Y-m-d'),
            ],
            'month'     => [
                $this->startDate = now()->startOfMonth()->format('Y-m-d'),
                $this->endDate   = now()->format('Y-m-d'),
            ],
            'last_month' => [
                $this->startDate = now()->subMonth()->startOfMonth()->format('Y-m-d'),
                $this->endDate   = now()->subMonth()->endOfMonth()->format('Y-m-d'),
            ],
            default => null,
        };

        $this->loadReport();
    }

    private function ordersInRange()
    {
        $tenant = auth()->user()->tenant;

        return Order::whereHas('makanDetails', fn($q) =>
                $q->where('tenant_id', $tenant->id)
            )
            ->where('status', 'completed')
            ->whereDate('updated_at', '>=', $this->startDate)
            ->whereDate('updated_at', '<=', $this->endDate);
    }

    public function loadReport(): void
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate'   => 'required|date|after_or_equal:startDate',
        ], [
            'endDate.after_or_equal' => 'Tanggal akhir harus setelah atau sama dengan tanggal mulai.',
        ]);

        $tenant = auth()->user()->tenant;

        $itemsQuery = OrderMakanItem::query()
            ->join('order_makan_details', 'order_makan_details.id', '=', 'order_makan_items.order_makan_detail_id')
            ->join('orders', 'orders.id', '=', 'order_makan_details.order_id')
            ->where('order_makan_details.tenant_id', $tenant->id)
            ->where('orders.status', 'completed')
            ->whereDate('orders.updated_at', '>=', $this->startDate)
            ->whereDate('orders.updated_at', '<=', $this->endDate);

        $gross = (clone $itemsQuery)
            ->selectRaw('SUM(order_makan_items.quantity * order_makan_items.price_snapshot) as total')
            ->value('total') ?? 0;

        $fee = 0; // Hardcode — ToLong belum memotong dari tenant
        $net = $gross - $fee;

        $totalOrders = (clone $itemsQuery)
            ->distinct('orders.id')
            ->count('orders.id');

        $this->grossRevenue = number_format($gross, 0, ',', '.');
        $this->platformFee  = number_format($fee, 0, ',', '.');
        $this->netRevenue   = number_format($net, 0, ',', '.');
        $this->totalOrders  = $totalOrders;

        $this->loadTopMenus();
        $this->loadOrderDetails();
    }

    private function loadOrderDetails(): void
    {
        $tenant = auth()->user()->tenant;

        $this->orderDetails = OrderMakanItem::query()
            ->select(
                'order_makan_items.menu_id',
                'order_makan_items.price_snapshot',
                DB::raw('SUM(order_makan_items.quantity) as total_qty'),
                DB::raw('SUM(order_makan_items.quantity * order_makan_items.price_snapshot) as total_revenue')
            )
            ->join('order_makan_details', 'order_makan_details.id', '=', 'order_makan_items.order_makan_detail_id')
            ->join('orders', 'orders.id', '=', 'order_makan_details.order_id')
            ->where('order_makan_details.tenant_id', $tenant->id)
            ->where('orders.status', 'completed')
            ->whereDate('orders.updated_at', '>=', $this->startDate)
            ->whereDate('orders.updated_at', '<=', $this->endDate)
            ->groupBy('order_makan_items.menu_id', 'order_makan_items.price_snapshot')
            ->with('menu')
            ->orderByDesc('total_revenue')
            ->get();
    }

    private function loadTopMenus(): void
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
            ->whereDate('orders.updated_at', '>=', $this->startDate)
            ->whereDate('orders.updated_at', '<=', $this->endDate)
            ->groupBy('order_makan_items.menu_id')
            ->orderByDesc('total_qty')
            ->with('menu.photos')
            ->take(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.tenant.report')
            ->layout('layouts.app', ['title' => 'Laporan']);
    }
}