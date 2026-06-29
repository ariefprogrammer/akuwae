<?php

namespace App\Livewire\Customer\Order;

use App\Models\FareConfig;
use App\Models\Order;
use App\Models\OrderAntarDetail;
use App\Models\OrderLocation;
use Illuminate\Support\Str;
use Livewire\Component;

class AntarOrder extends Component
{
    public int    $step = 1;

    // Lokasi
    public string $origin_address      = '';
    public string $origin_latitude     = '';
    public string $origin_longitude    = '';
    public string $destination_address = '';
    public string $dest_latitude       = '';
    public string $dest_longitude      = '';
    public string $notes_for_driver    = '';

    // Kendaraan & tarif
    public string $vehicle_type  = 'motor';
    public float  $distance_km   = 0;
    public float  $total_fare    = 0;
    public float  $base_fare     = 0;
    public float  $per_km_fare   = 0;

    public string $error = '';

    public function updatedVehicleType()
    {
        if ($this->distance_km > 0) {
            $this->calculateFare();
        }
    }

    public function updatedDistanceKm(): void
    {
        $this->calculateFare();
    }

    public function calculateFare()
    {
        $config = FareConfig::getFor('antar', $this->vehicle_type);
        if (!$config) return;

        $this->base_fare   = $config->base_fare;
        $this->per_km_fare = $config->per_km_fare;
        $this->total_fare  = $config->base_fare + ($this->distance_km * $config->per_km_fare);
    }

    // Dipanggil dari JS saat jarak dihitung Haversine
    public function setRouteData(
        float $distanceKm,
        string $originLat,
        string $originLng,
        string $destLat,
        string $destLng
    ) {
        $this->distance_km      = round($distanceKm * 1.3, 2); // koreksi jalan
        $this->origin_latitude  = $originLat;
        $this->origin_longitude = $originLng;
        $this->dest_latitude    = $destLat;
        $this->dest_longitude   = $destLng;
        $this->calculateFare();
    }

    public function nextStep()
    {
        $this->error = '';

        $this->validate([
            'origin_address'      => 'required|string',
            'destination_address' => 'required|string',
            'origin_latitude'     => 'required|numeric',
            'origin_longitude'    => 'required|numeric',
            'dest_latitude'       => 'required|numeric',
            'dest_longitude'      => 'required|numeric',
        ], [
            'origin_latitude.required'  => 'Pin titik jemput di peta.',
            'dest_latitude.required'    => 'Pin titik tujuan di peta.',
        ]);

        if ($this->distance_km <= 0) {
            $this->error = 'Gagal menghitung jarak. Pastikan kedua titik sudah dipin.';
            return;
        }

        // Hitung tarif sebelum pindah step
        $this->calculateFare();

        $this->step = 2;
    }

    public function placeOrder()
    {
        $this->error = '';

        $customer = auth()->user()->customer;
        $orderNumber = 'TLG-' . strtoupper(Str::random(8));
        $config      = FareConfig::getFor('antar', $this->vehicle_type);
        $commission  = $this->total_fare * ($config->platform_commission_pct / 100);
        $earnings    = $this->total_fare - $commission;

        $order = Order::create([
            'order_number'        => $orderNumber,
            'customer_id'         => $customer->id,
            'driver_id'           => null,
            'service_type'        => 'antar',
            'status'              => 'finding_driver',
            'payment_method'      => 'tunai',
            'payment_status'      => 'unpaid',
            'total_fare'          => $this->total_fare,
            'driver_earnings'     => $earnings,
            'platform_commission' => $commission,
        ]);

        OrderLocation::create([
            'order_id'              => $order->id,
            'origin_address'        => $this->origin_address,
            'origin_latitude'       => $this->origin_latitude,
            'origin_longitude'      => $this->origin_longitude,
            'destination_address'   => $this->destination_address,
            'destination_latitude'  => $this->dest_latitude,
            'destination_longitude' => $this->dest_longitude,
            'distance_km'           => $this->distance_km,
            'notes_for_driver'      => $this->notes_for_driver,
        ]);

        OrderAntarDetail::create([
            'order_id'               => $order->id,
            'requested_vehicle_type' => $this->vehicle_type,
        ]);

        // Broadcast ke driver
        \Log::info('Broadcasting OrderCreated for order: ' . $order->id);
        broadcast(new \App\Events\OrderCreated($order))->toOthers();
        \Log::info('Broadcast done');

        return redirect()->route('customer.order.tracking', $order->public_id);
    }

    public function render()
    {
        return view('livewire.customer.order.antar-order')
            ->layout('layouts.app', ['title' => 'Tolong Antar']);
    }
}