<?php

namespace App\Livewire\Driver;

use App\Events\OrderMismatchResolved;
use App\Events\OrderStatusUpdated;
use App\Models\Driver;
use App\Models\Order;
use App\Models\WorkingBalance as WorkingBalanceModel;
use App\Models\OrderCustomPhoto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Dashboard extends Component
{
    use WithFileUploads;

    public ?Driver $driver       = null;
    public ?Order  $activeOrder  = null;
    public string  $success      = '';

    // Flow Tolong Custom
    public bool   $showPickupConfirm = false;
    public bool   $showMismatchForm  = false;
    public string $mismatchReason    = '';
    public bool   $showCompleteForm  = false;
    public        $deliveryProofPhoto = null;


    public ?int $activeOrderId = null;

    // Statistik pendapatan
    public string $earningsToday     = '0';
    public string $earningsThisMonth = '0';
    public string $earningsAllTime   = '0';
 
    // Data chart
    public array $chartLabels = [];
    public array $chartData   = [];

    // balance
    public $balance;

    public function getListeners(): array
    {
        $listeners = [
            'refresh-driver-active-order' => 'handleRefreshActiveOrder',
        ];

        if ($this->activeOrderId) {
            $listeners["echo-private:order.{$this->activeOrderId},.status.updated"] = 'handleOrderStatusUpdated';
        }

        return $listeners;
    }

    public function handleRefreshActiveOrder(): void
    {
        $this->loadActiveOrder();

        if (!$this->activeOrder) {
            $this->checkPendingOrders();
        }
    }


    public function handleOrderStatusUpdated(array $data): void
    {
        $this->loadActiveOrder();
    }

    public function mount()
    {
        $driver = auth()->user()->driver;
        if (!$driver) {
            return redirect()->route('driver.onboarding');
        }
        $this->driver = $driver;
        $this->loadActiveOrder(); 
        $this->loadEarnings();
        $this->loadChartData();
        $this->loadSaldo();
    }

    public function loadSaldo()
    {
        $wb = WorkingBalanceModel::getOrCreateFor(auth()->id());
        $this->balance       = $wb->balance;
    }

    public function loadActiveOrder(): void
    {
        $this->activeOrder = Order::with(['location', 'makanDetails.items.menu', 'customDetail'])
            ->where('driver_id', $this->driver->id)
            ->whereIn('status', ['processing', 'ready', 'pickup', 'item_mismatch', 'arrived', 'delivering'])
            ->latest()
            ->first();

        $this->activeOrderId = $this->activeOrder?->id;
    }

    public function loadEarnings(): void
    {
        $base = Order::where('driver_id', $this->driver->id)
            ->where('status', 'completed');
 
        $today = (clone $base)
            ->whereDate('updated_at', today())
            ->sum('driver_earnings');
 
        $thisMonth = (clone $base)
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->sum('driver_earnings');
 
        $allTime = (clone $base)
            ->sum('driver_earnings');
 
        $this->earningsToday     = number_format($today, 0, ',', '.');
        $this->earningsThisMonth = number_format($thisMonth, 0, ',', '.');
        $this->earningsAllTime   = number_format($allTime, 0, ',', '.');
    }
 
    public function loadChartData(): void
    {
        $rows = Order::where('driver_id', $this->driver->id)
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

    public function toggleOnline()
    {
        if ($this->driver->verification_status !== 'approved') return;

        $this->driver->update([
            'is_online'        => !$this->driver->is_online,
            'last_activity_at' => now(),
        ]);

        $this->driver->refresh();

        if ($this->driver->is_online) {
            $this->checkPendingOrders();
        }
    }

    public function checkPendingOrders(): void
    {
        if (!$this->driver->is_online) return;

        $hasActive = Order::where('driver_id', $this->driver->id)
            ->whereIn('status', ['processing', 'ready', 'pickup', 'item_mismatch', 'arrived', 'delivering'])
            ->exists();

        if ($hasActive) return;

        $balance = \App\Models\WorkingBalance::getOrCreateFor($this->driver->user_id);

        $pendingOrder = Order::with(['location', 'antarDetail', 'customDetail'])
            ->whereIn('status', ['finding_driver', 'preparing'])
            ->whereDoesntHave('driver')
            ->latest()
            ->get()
            ->first(function ($order) use ($balance) {
                $vehicleType = $order->antarDetail?->requested_vehicle_type
                    ?? $order->customDetail?->vehicle_type
                    ?? 'motor';

                if ($vehicleType !== $this->driver->vehicle_type) return false;

                $estimatedCommission = $order->total_fare - $order->driver_earnings;
                return $balance->balance >= $estimatedCommission;
            });

        $pendingOrder = Order::with(['location', 'antarDetail'])
            ->where('status', 'finding_driver')
            ->whereDoesntHave('driver')
            ->latest()
            ->get()
            ->first(function ($order) {
                $vehicleType = $order->antarDetail?->requested_vehicle_type ?? 'motor';
                return $vehicleType === $this->driver->vehicle_type;
            });

        if ($pendingOrder) {
            $this->dispatch('queue-pending-order', [
                'order_id'            => $pendingOrder->id,
                'order_number'        => $pendingOrder->order_number,
                'service_type'        => $pendingOrder->service_type,
                'total_fare'          => $pendingOrder->total_fare,
                'driver_earnings'     => $pendingOrder->driver_earnings,
                'payment_method'      => $pendingOrder->payment_method,
                'vehicle_type'        => $pendingOrder->antarDetail?->requested_vehicle_type ?? 'motor',
                'origin_address'      => $pendingOrder->location->origin_address,
                'destination_address' => $pendingOrder->location->destination_address,
                'distance_km'         => $pendingOrder->location->distance_km,
                'notes_for_driver'    => $pendingOrder->location->notes_for_driver,
            ]);
        }
    }


    // ── Tolong Makan / Antar: ambil & selesai ────────────────

    public function pickupOrder(): void
    {
        if (!$this->activeOrder) return;

        $this->activeOrder->update(['status' => 'delivering']);
        $this->activeOrder->refresh()->load(['location', 'makanDetails.items.menu']);

        broadcast(new OrderStatusUpdated($this->activeOrder))->toOthers();

        $this->loadActiveOrder();
    }

    public function completeOrder(): void
    {
        if (!$this->activeOrder) return;

        $this->activeOrder->update([
            'status'         => 'completed',
            'payment_status' => 'paid',
        ]);

        broadcast(new OrderStatusUpdated($this->activeOrder))->toOthers();

        $this->success = 'Pesanan selesai! Terima kasih.';
        $this->loadActiveOrder();
        $this->checkPendingOrders();
    }

    // ── Tolong Custom: barang diambil ────────────────────────

    public function markPickedUp(): void
    {
        $this->showPickupConfirm = true;
    }

    public function confirmItemMatch(bool $matches): void
    {
        if (!$this->activeOrder) return;

        $this->showPickupConfirm = false;

        if ($matches) {
            $this->activeOrder->update(['status' => 'delivering']);
            broadcast(new OrderStatusUpdated($this->activeOrder))->toOthers();
            $this->loadActiveOrder();
        } else {
            $this->showMismatchForm = true;
        }
    }

    public function submitMismatch(): void
    {
        $this->validate([
            'mismatchReason' => 'required|string|min:5',
        ], [
            'mismatchReason.required' => 'Jelaskan perbedaannya kepada customer.',
            'mismatchReason.min'      => 'Penjelasan minimal 5 karakter.',
        ]);

        $this->activeOrder->customDetail?->update([
            'mismatch_reason' => $this->mismatchReason,
        ]);

        $this->activeOrder->update(['status' => 'item_mismatch']);
        $this->activeOrder->refresh()->load('customDetail');

        broadcast(new OrderStatusUpdated($this->activeOrder))->toOthers();

        $this->showMismatchForm = false;
        $this->mismatchReason   = '';
        $this->loadActiveOrder();
    }

    // ── Tolong Custom: sampai di tujuan ──────────────────────

    public function markArrived(): void
    {
        if (!$this->activeOrder) return;

        $this->activeOrder->update(['status' => 'arrived']);
        broadcast(new OrderStatusUpdated($this->activeOrder))->toOthers();

        $this->loadActiveOrder();
    }

    // ── Tolong Custom: barang diterima ───────────────────────

    public function openCompleteForm(): void
    {
        $this->showCompleteForm = true;
    }

    public function completeCustomOrder(): void
    {
        if (!$this->activeOrder) return;

        $this->validate([
            'deliveryProofPhoto' => 'nullable|image|max:2048',
        ]);

        if ($this->deliveryProofPhoto) {
            $path = $this->deliveryProofPhoto->store('orders/custom/proofs', 'public');

            OrderCustomPhoto::create([
                'order_id'   => $this->activeOrder->id,
                'photo_type' => 'driver_delivery_proof',
                'photo_url'  => $path,
            ]);
        }

        $this->activeOrder->update([
            'status'         => 'completed',
            'payment_status' => 'paid',
        ]);

        broadcast(new OrderStatusUpdated($this->activeOrder))->toOthers();

        $this->showCompleteForm   = false;
        $this->deliveryProofPhoto = null;
        $this->success = 'Pesanan selesai! Terima kasih.';

        $this->loadActiveOrder();
        $this->checkPendingOrders();
    }

    public function render()
    {
        return view('livewire.driver.dashboard')
            ->layout('layouts.app', ['title' => 'Dashboard Driver']);
    }
}