<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\WorkingBalance;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Hanya proses sekali saat status berubah jadi completed
        if (!$order->wasChanged('status') || $order->status !== 'completed') {
            return;
        }

        // Potong saldo driver
        if ($order->driver_id) {
            $driver = $order->driver;
            if ($driver && $driver->user_id) {
                $commission = $order->platform_commission;

                if ($commission > 0) {
                    $balance = WorkingBalance::getOrCreateFor($driver->user_id);
                    $balance->deduct(
                        $commission,
                        $order->id,
                        "Komisi order {$order->order_number}"
                    );
                }
            }
        }

        // Potong saldo tenant (untuk Tolong Makan, saat ini commission = 0, tapi siap untuk masa depan)
        if ($order->service_type === 'makan') {
            foreach ($order->makanDetails as $detail) {
                $tenant = $detail->tenant;
                if ($tenant && $tenant->user_id) {
                    $tenantCommission = 0; // hardcode 0 sesuai keputusan sebelumnya

                    if ($tenantCommission > 0) {
                        $balance = WorkingBalance::getOrCreateFor($tenant->user_id);
                        $balance->deduct(
                            $tenantCommission,
                            $order->id,
                            "Komisi order {$order->order_number}"
                        );
                    }
                }
            }
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
