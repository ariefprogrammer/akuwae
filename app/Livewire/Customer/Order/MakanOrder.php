<?php

namespace App\Livewire\Customer\Order;

use App\Models\FareConfig;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderLocation;
use App\Models\OrderMakanDetail;
use App\Models\OrderMakanItem;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Livewire\Component;

class MakanOrder extends Component
{
    public int    $step        = 1; // 1=browse, 2=lokasi, 3=konfirmasi
    public string $activeTab   = 'toko'; // 'toko' atau 'menu'
    public string $search      = '';

    // Keranjang: ['tenant_id' => ['tenant' => [...], 'items' => [menu_id => ['menu' => [...], 'qty' => int, 'notes' => '']]]]
    public array $cart = [];

    // Lokasi pengiriman
    public string $delivery_address   = '';
    public string $delivery_latitude  = '';
    public string $delivery_longitude = '';
    public string $notes_for_driver   = '';

    // Tarif
    public float  $distance_km  = 0;
    public float  $food_subtotal = 0;
    public float  $delivery_fee  = 0;
    public float  $total_fare    = 0;

    public string $error = '';

    public ?string $expandedTenantId = null;

    public function toggleTenant(string $tenantId): void
    {
        $this->expandedTenantId = $this->expandedTenantId === $tenantId ? null : $tenantId;
    }

    // ── Browse ───────────────────────────────────────────────

    public function getTenants()
    {
        return Tenant::where('verification_status', 'approved')
            ->where('is_open', true)
            ->whereHas('user.workingBalance', fn($q) => $q->where('balance', '>=', 20000))
            ->when($this->search, fn($q) =>
                $q->where('store_name', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%')
            )
            ->get();
    }

    public function getMenus()
    {
        return Menu::with('menuCategory.tenant')
            ->where('is_available', true)
            ->whereHas('menuCategory.tenant', fn($q) =>
                $q->where('verification_status', 'approved')
                ->where('is_open', true)
                ->whereHas('user.workingBalance', fn($q2) => $q2->where('balance', '>=', 20000))
            )
            ->when($this->search, fn($q) =>
                $q->where('item_name', 'like', '%' . $this->search . '%')
            )
            ->get();
    }

    // ── Keranjang ────────────────────────────────────────────

    public function addToCart(int $menuId)
    {
        $menu   = Menu::with('menuCategory.tenant')->findOrFail($menuId);
        $tenant = $menu->menuCategory->tenant;

        if (!isset($this->cart[$tenant->id])) {
            $this->cart[$tenant->id] = [
                'tenant' => [
                    'id'         => $tenant->id,
                    'store_name' => $tenant->store_name,
                    'category'   => $tenant->category,
                ],
                'items' => [],
            ];
        }

        if (isset($this->cart[$tenant->id]['items'][$menuId])) {
            $this->cart[$tenant->id]['items'][$menuId]['qty']++;
        } else {
            $this->cart[$tenant->id]['items'][$menuId] = [
                'menu_id'    => $menuId,
                'item_name'  => $menu->item_name,
                'price'      => (float) $menu->price,
                'qty'        => 1,
                'notes'      => '',
            ];
        }

        $this->recalculateFoodSubtotal();
    }

    public function incrementItem(string $tenantId, int $menuId)
    {
        $this->cart[$tenantId]['items'][$menuId]['qty']++;
        $this->recalculateFoodSubtotal();
    }

    public function decrementItem(string $tenantId, int $menuId)
    {
        $this->cart[$tenantId]['items'][$menuId]['qty']--;

        if ($this->cart[$tenantId]['items'][$menuId]['qty'] <= 0) {
            unset($this->cart[$tenantId]['items'][$menuId]);

            if (empty($this->cart[$tenantId]['items'])) {
                unset($this->cart[$tenantId]);
            }
        }

        $this->recalculateFoodSubtotal();
    }

    public function updateNotes(string $tenantId, int $menuId, string $notes)
    {
        $this->cart[$tenantId]['items'][$menuId]['notes'] = $notes;
    }

    public function recalculateFoodSubtotal()
    {
        $subtotal = 0;
        foreach ($this->cart as $tenantCart) {
            foreach ($tenantCart['items'] as $item) {
                $subtotal += $item['price'] * $item['qty'];
            }
        }
        $this->food_subtotal = $subtotal;
        $this->calculateTotal();
    }

    public function getTotalItems(): int
    {
        $total = 0;
        foreach ($this->cart as $tenantCart) {
            foreach ($tenantCart['items'] as $item) {
                $total += $item['qty'];
            }
        }
        return $total;
    }

    // ── Navigasi step ────────────────────────────────────────

    public function goToStep2()
    {
        if (empty($this->cart)) {
            $this->error = 'Pilih minimal satu item menu terlebih dahulu.';
            return;
        }
        $this->error = '';
        $this->step  = 2;
    }

    public function goToStep3()
    {
        $this->error = '';

        $this->validate([
            'delivery_address'   => 'required|string',
            'delivery_latitude'  => 'required|numeric',
            'delivery_longitude' => 'required|numeric',
        ], [
            'delivery_latitude.required' => 'Pin lokasi pengiriman di peta.',
        ]);

        if ($this->distance_km <= 0) {
            $this->error = 'Gagal menghitung jarak. Pastikan pin lokasi sudah diset.';
            return;
        }

        $this->calculateTotal();
        $this->step = 3;
    }

    public function setDeliveryLocation(
        float $lat, float $lng,
        float $distanceKm
    ) {
        $this->delivery_latitude  = (string) $lat;
        $this->delivery_longitude = (string) $lng;
        $this->distance_km        = round($distanceKm * 1.3, 2);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $config = FareConfig::getFor('makan', 'motor');
        if (!$config) return;

        $this->delivery_fee = $config->base_fare + ($this->distance_km * $config->per_km_fare);
        $this->total_fare   = $this->food_subtotal + $this->delivery_fee;
    }

    // ── Place order ──────────────────────────────────────────

    public function placeOrder()
    {
        $this->error = '';

        if (empty($this->cart)) {
            $this->error = 'Keranjang kosong.';
            return;
        }

        $customer    = auth()->user()->customer;
        $orderNumber = 'TLG-' . strtoupper(Str::random(8));
        $config      = FareConfig::getFor('makan', 'motor');
        $commission  = $this->total_fare * ($config->platform_commission_pct / 100);
        $earnings    = $this->total_fare - $commission;

        $order = Order::create([
            'order_number'        => $orderNumber,
            'customer_id'         => $customer->id,
            'driver_id'           => null,
            'service_type'        => 'makan',
            'status'              => 'waiting_tenant', // status awal untuk makan
            'payment_method'      => 'tunai',
            'payment_status'      => 'unpaid',
            'total_fare'          => $this->total_fare,
            'driver_earnings'     => $earnings,
            'platform_commission' => $commission,
        ]);

        $firstTenantId = array_key_first($this->cart);
        $firstTenant   = Tenant::find($firstTenantId);

        OrderLocation::create([
            'order_id'              => $order->id,
            'origin_address'        => $firstTenant->store_name . ' - ' . $firstTenant->address,
            'origin_latitude'       => $firstTenant->latitude,
            'origin_longitude'      => $firstTenant->longitude,
            'destination_address'   => $this->delivery_address,
            'destination_latitude'  => $this->delivery_latitude,
            'destination_longitude' => $this->delivery_longitude,
            'distance_km'           => $this->distance_km,
            'notes_for_driver'      => $this->notes_for_driver,
        ]);

        foreach ($this->cart as $tenantId => $tenantCart) {
            $detail = OrderMakanDetail::create([
                'order_id'                   => $order->id,
                'tenant_id'                  => $tenantId,
                'estimated_preparation_time' => 15,
            ]);

            foreach ($tenantCart['items'] as $item) {
                OrderMakanItem::create([
                    'order_makan_detail_id' => $detail->id,
                    'menu_id'               => $item['menu_id'],
                    'quantity'              => $item['qty'],
                    'notes'                 => $item['notes'],
                    'price_snapshot'        => $item['price'],
                ]);
            }
        }

        broadcast(new \App\Events\OrderWaitingTenant($order))->toOthers();
        
        // Kirim Web Push ke tenant
        foreach ($this->cart as $tenantId => $tenantCart) {
            $tenant = \App\Models\Tenant::with('user')->find($tenantId);
            if ($tenant?->user) {
                $tenant->user->notify(new \App\Notifications\NewOrderNotification(
                    title: 'Pesanan Baru!',
                    body: "Order {$order->order_number} menunggu konfirmasimu.",
                    url: '/tenant/dashboard'
                ));
            }
        }

        return redirect()->route('customer.order.tracking', $order->public_id);
    }

    public function render()
    {
        return view('livewire.customer.order.makan-order', [
            'tenants'    => $this->activeTab === 'toko' ? $this->getTenants() : collect(),
            'menus'      => $this->activeTab === 'menu' ? $this->getMenus() : collect(),
            'totalItems' => $this->getTotalItems(),
        ])->layout('layouts.app', ['title' => 'Tolong Makan']);
    }
}