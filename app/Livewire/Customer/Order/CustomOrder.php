<?php

namespace App\Livewire\Customer\Order;

use App\Models\FareConfig;
use App\Models\Order;
use App\Models\OrderCustomDetail;
use App\Models\OrderLocation;
use Illuminate\Support\Str;
use Livewire\Component;

class CustomOrder extends Component
{
    public int $step = 1;

    // Lokasi
    public string $origin_address      = '';
    public string $origin_latitude     = '';
    public string $origin_longitude    = '';
    public string $destination_address = '';
    public string $dest_latitude       = '';
    public string $dest_longitude      = '';
    public string $notes_for_driver    = '';

    // Detail barang
    public string $item_description = '';
    public string $estimated_weight = '';
    public string $vehicle_type     = 'motor';

    // Tarif
    public float $distance_km     = 0;
    public float $base_fare       = 0;
    public float $per_km_fare     = 0;
    public float $per_kg_fare     = 0;
    public float $distance_fare   = 0;
    public float $weight_fare     = 0;
    public float $total_fare      = 0;

    public string $error = '';

    public function updatedVehicleType()
    {
        $this->calculateFare();
    }

    public function updatedEstimatedWeight()
    {
        $this->calculateFare();
    }

    public function updatedDistanceKm()
    {
        $this->calculateFare();
    }

    public function calculateFare()
    {
        $config = FareConfig::getFor('custom', $this->vehicle_type);
        if (!$config) return;

        $weight = (float) $this->estimated_weight;

        $this->base_fare   = $config->base_fare;
        $this->per_km_fare = $config->per_km_fare;
        $this->per_kg_fare = $config->per_kg_fare;

        // Biaya per-km hanya dihitung untuk jarak di atas 2 km
        $chargeableDistance  = max(0, $this->distance_km - 2);
        $this->distance_fare = $chargeableDistance * $config->per_km_fare;

        $this->weight_fare = $weight * $config->per_kg_fare;
        $this->total_fare  = $this->base_fare + $this->distance_fare + $this->weight_fare;
    }

    // Step 1 -> 2
    public function nextStepFromLocation()
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
            'origin_latitude.required' => 'Pin lokasi penjemputan barang di peta.',
            'dest_latitude.required'   => 'Pin lokasi tujuan di peta.',
        ]);

        if ($this->distance_km <= 0) {
            $this->error = 'Gagal menghitung jarak. Pastikan kedua titik sudah dipin.';
            return;
        }

        $this->step = 2;
    }

    // Step 2 -> 3
    public function nextStepFromDetail()
    {
        $this->error = '';

        $this->validate([
            'item_description' => 'required|string|min:10',
            'estimated_weight' => 'required|numeric|min:0.1|max:50',
        ], [
            'item_description.required' => 'Jelaskan barang yang ingin kamu titip.',
            'item_description.min'      => 'Deskripsi minimal 10 karakter agar driver paham.',
            'estimated_weight.required'  => 'Estimasi berat wajib diisi.',
            'estimated_weight.max'       => 'Maksimal estimasi berat 50 kg.',
        ]);

        $this->calculateFare();
        $this->step = 3;
    }

    public function placeOrder()
    {
        $this->error = '';

        $customer    = auth()->user()->customer;
        $orderNumber = 'TLG-' . strtoupper(Str::random(8));
        $config      = FareConfig::getFor('custom', $this->vehicle_type);
        $commission  = $this->total_fare * ($config->platform_commission_pct / 100);
        $earnings    = $this->total_fare - $commission;

        $order = Order::create([
            'order_number'        => $orderNumber,
            'customer_id'         => $customer->id,
            'driver_id'           => null,
            'service_type'        => 'custom',
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

        OrderCustomDetail::create([
            'order_id'               => $order->id,
            'vehicle_type'           => $this->vehicle_type,
            'item_description'       => $this->item_description,
            'estimated_weight'       => $this->estimated_weight,
            'actual_weight'          => null,
            'base_fare_snapshot'     => $this->base_fare,
            'weight_fare_snapshot'   => $this->weight_fare,
            'distance_fare_snapshot' => $this->distance_fare,
        ]);

        broadcast(new \App\Events\OrderCreated($order))->toOthers();

        return redirect()->route('customer.order.tracking', $order->public_id);
    }

    public function setLocationData(
        string $originAddress,
        string $destinationAddress,
        string $originLat,
        string $originLng,
        string $destLat,
        string $destLng,
        float  $distanceKm
    ): void {
        $this->origin_address      = $originAddress;
        $this->destination_address = $destinationAddress;
        $this->origin_latitude     = $originLat;
        $this->origin_longitude    = $originLng;
        $this->dest_latitude       = $destLat;
        $this->dest_longitude      = $destLng;
        $this->distance_km         = $distanceKm;

        $this->calculateFare();
    }

    public function render()
    {
        return view('livewire.customer.order.custom-order')
            ->layout('layouts.app', ['title' => 'Aku Tolong']);
    }
}