<?php

namespace App\Livewire\Driver;

use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Models\Driver;
use App\Models\Order;
use Livewire\Component;

class IncomingOrder extends Component
{
    public array   $orderQueue    = []; // antrian order masuk
    public ?array  $incomingOrder = null; // order yang sedang ditampilkan
    public ?Driver $driver        = null;
    public string  $error         = '';

    // Notif makanan siap
    public bool   $showReadyNotif   = false;
    public string $readyOrderNumber = '';

    // Notif hasil keputusan customer atas mismatch
    public bool   $showMismatchResolvedPopup = false;
    public string $mismatchResolvedMsg       = '';

    protected $listeners = [
        'incoming-order'      => 'queueIncomingOrder',
        'queue-pending-order' => 'queueIncomingOrder',
        'order-ready-notify'       => 'showReadyNotifHandler',
        'order-mismatch-resolved'  => 'showMismatchResolvedHandler',
    ];

    public function mount()
    {
        $this->driver = auth()->user()->driver;
        $this->checkPendingOrderOnLoad();
    }

    private function checkPendingOrderOnLoad(): void
    {
        if (!$this->driver || !$this->driver->is_online) return;

        // Driver sedang sibuk dengan order lain
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

        if ($pendingOrder) {
            $this->incomingOrder = [
                'order_id'              => $pendingOrder->id,
                'order_number'          => $pendingOrder->order_number,
                'service_type'          => $pendingOrder->service_type,
                'total_fare'            => $pendingOrder->total_fare,
                'driver_earnings'       => $pendingOrder->driver_earnings,
                'payment_method'        => $pendingOrder->payment_method,
                'vehicle_type'          => $pendingOrder->antarDetail?->requested_vehicle_type
                    ?? $pendingOrder->customDetail?->vehicle_type
                    ?? 'motor',
                'origin_address'        => $pendingOrder->location->origin_address,
                'origin_latitude'       => $pendingOrder->location->origin_latitude,
                'origin_longitude'      => $pendingOrder->location->origin_longitude,
                'destination_address'   => $pendingOrder->location->destination_address,
                'destination_latitude'  => $pendingOrder->location->destination_latitude,
                'destination_longitude' => $pendingOrder->location->destination_longitude,
                'distance_km'           => $pendingOrder->location->distance_km,
                'notes_for_driver'      => $pendingOrder->location->notes_for_driver,
                'item_description'      => $pendingOrder->customDetail?->item_description,
            ];
        }
    }

    // Tambahkan order baru ke antrian
    public function queueIncomingOrder(array $orderData): void
    {
        // Cek driver sedang sibuk
        $activeOrder = Order::where('driver_id', $this->driver->id)
            ->whereIn('status', ['processing', 'ready', 'pickup', 'item_mismatch', 'arrived', 'delivering'])
            ->exists();

        if ($activeOrder) {
            return;
        }

        $vehicleType = $orderData['vehicle_type'] ?? 'motor';

        if ($this->driver->vehicle_type !== $vehicleType) {
            return;
        }

        // Cek saldo kerja cukup untuk estimasi komisi
        $estimatedCommission = $orderData['total_fare'] - $orderData['driver_earnings'];
        $balance = \App\Models\WorkingBalance::getOrCreateFor($this->driver->user_id);

        if ($balance->balance < $estimatedCommission) {
            return; // saldo tidak cukup, abaikan order ini
        }

        // Hindari duplikat order_id di antrian
        $exists = collect($this->orderQueue)
            ->merge([$this->incomingOrder])
            ->filter()
            ->contains('order_id', $orderData['order_id']);

        if ($exists) {
            return;
        }

        // Kalau belum ada popup tampil, langsung tampilkan
        if (!$this->incomingOrder) {
            $this->incomingOrder = $orderData;
        } else {
            // Kalau sedang ada popup, masukkan ke antrian
            $this->orderQueue[] = $orderData;
        }
    }

    // Tampilkan order berikutnya dari antrian
    private function showNextOrder(): void
    {
        if (!empty($this->orderQueue)) {
            $this->incomingOrder = array_shift($this->orderQueue);
        } else {
            $this->incomingOrder = null;
        }
    }

    public function acceptOrder()
    {
        if (!$this->incomingOrder) return;

        $order = Order::find($this->incomingOrder['order_id']);

        if (!$order || !in_array($order->status, ['finding_driver', 'preparing'])) {
            $this->error = 'Order sudah diambil driver lain.';
            $this->showNextOrder();
            return;
        }

        $order->update([
            'driver_id' => $this->driver->id,
            'status'    => 'processing',
        ]);

        $order = $order->fresh(['driver']);

        broadcast(new OrderStatusUpdated($order));

        // Bersihkan antrian — driver sudah dapat order, tidak perlu tawaran lain
        $this->orderQueue    = [];
        $this->incomingOrder = null;

        return redirect()->route('driver.active-order', $order->public_id);
    }

    public function rejectOrder(): void
    {
        $this->error = '';
        $this->showNextOrder();
    }

    public function showReadyNotifHandler(array $data): void
    {
        $this->showReadyNotif   = true;
        $this->readyOrderNumber = $data['order_number'];

        // Refresh dashboard jika sedang dibuka
        $this->dispatch('refresh-driver-active-order');
    }

    public function dismissReadyNotif(): void
    {
        $this->showReadyNotif = false;
    }

    // ── Notif hasil keputusan customer atas mismatch ──────────

    public function showMismatchResolvedHandler(array $data): void
    {
        $this->showMismatchResolvedPopup = true;
        $this->mismatchResolvedMsg = $data['accepted']
            ? 'Customer menerima perubahan. Silakan lanjutkan pengantaran.'
            : 'Customer membatalkan pesanan ini. Pengantaran dihentikan.';

        $this->dispatch('refresh-driver-active-order');
    }

    public function dismissMismatchResolvedPopup(): void
    {
        $this->showMismatchResolvedPopup = false;
        $this->dispatch('refresh-driver-active-order');
    }

    public function render()
    {
        return view('livewire.driver.incoming-order');
    }
}