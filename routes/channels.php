<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    if (!$order) return false;

    return $user->customer?->id === $order->customer_id ||
           $user->driver?->id === $order->driver_id;
});

Broadcast::channel('tenant.{tenantId}', function ($user, $tenantId) {
    return $user->tenant?->id === $tenantId;
});

Broadcast::channel('driver.{driverId}', function ($user, $driverId) {
    return $user->driver?->id === $driverId;
});

Broadcast::channel('order.{orderId}.chat', function ($user, $orderId) {
    $order = \App\Models\Order::find($orderId);
    if (!$order) return false;

    return $user->customer?->id === $order->customer_id ||
           $user->driver?->id === $order->driver_id;
});