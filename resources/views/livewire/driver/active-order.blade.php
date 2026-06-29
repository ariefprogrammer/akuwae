<div class="px-3 py-3">
    <div class="app-card text-center py-4">
        <div style="font-size:48px;">✅</div>
        <h6 class="fw-bold mt-3">Order Diterima!</h6>
        <p class="text-muted small">Order #{{ $order->order_number }}</p>
        <p class="text-muted small mb-0">
            Dari: {{ $order->location->origin_address }}<br>
            Ke: {{ $order->location->destination_address }}
        </p>
    </div>

    <a href="{{ route('driver.dashboard') }}"
        class="btn btn-outline-secondary w-100 rounded-3 py-2 mt-3">
        ← Kembali ke Dashboard
    </a>
</div>