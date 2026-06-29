<div>

    {{-- Header --}}
    <div class="px-3 pt-3 pb-2"
        style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <div class="text-white-50 small">Halo,</div>
                <div class="text-white fw-bold fs-6">{{ $customerName }} 👋</div>
            </div>
            <a href="#" class="text-white text-decoration-none">
                <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="fas fa-bell"></i>
                </div>
            </a>
        </div>

        {{-- Saldo TolongPay --}}
        <div class="rounded-3 p-3 mb-3"
            style="background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);">
            <div class="text-white-50 small mb-1">Saldo TolongPay</div>
            <div class="d-flex align-items-center justify-content-between">
                <div class="text-white fw-bold fs-5">Rp {{ $balance }}</div>
                <a href="#"
                    class="btn btn-sm btn-light rounded-3 fw-semibold text-danger px-3"
                    style="font-size:12px;">
                    + Top Up
                </a>
            </div>
        </div>

    </div>

    {{-- Layanan --}}
    <div class="px-3 pt-3">
        <div class="fw-semibold small mb-3 text-muted">Mau dibantu apa hari ini?</div>

        <div class="row g-2 mb-4">

            {{-- Aku Custom --}}
            <div class="col-4">
                <a href="{{ route('customer.order.custom') }}" class="text-decoration-none">
                    <div class="app-card text-center py-3 h-100">
                        <div style="font-size:32px;">
                            <i class="fas fa-box-open text-danger"></i>
                        </div>
                        <div class="text-danger fw-bold" style="font-size:12px;">Aku Bantu</div>
                        <div class="text-muted mt-1" style="font-size:10px;">Bantu apa saja</div>
                    </div>
                </a>
            </div>

            {{-- Aku Makan --}}
            <div class="col-4">
                <a href="#" class="text-decoration-none">
                    <div class="app-card text-center py-3 h-100 position-relative">
                        <span class="badge bg-secondary position-absolute top-0 end-0 m-1" style="font-size:8px;">Segera</span>
                        <div style="font-size:32px;">
                            <i class="fas fa-utensils text-muted"></i>
                        </div>
                        <div class="text-muted fw-bold" style="font-size:12px;">Aku Makan</div>
                        <div class="text-muted mt-1" style="font-size:10px;">Pesan makanan</div>
                    </div>
                </a>
            </div>

            {{-- Aku Antar --}}
            <div class="col-4">
                <a href="#" class="text-decoration-none">
                    <div class="app-card text-center py-3 h-100 position-relative">
                        <span class="badge bg-secondary position-absolute top-0 end-0 m-1" style="font-size:8px;">Segera</span>
                        <div style="font-size:32px;">
                            <i class="fas fa-motorcycle text-muted"></i>
                        </div>
                        <div class="text-muted fw-bold" style="font-size:12px;">Aku Antar</div>
                        <div class="text-muted mt-1" style="font-size:10px;">Kirim barang</div>
                    </div>
                </a>
            </div>

        </div>

        {{-- Active Order --}}
        @if($activeOrder)
        <div class="fw-semibold small mb-2 text-muted">Order Aktif</div>
        <a href="{{ route('customer.order.tracking', $activeOrder->public_id) }}" class="text-decoration-none">
            <div class="app-card mb-3 p-3">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="fw-bold small">{{ $activeOrder->order_number }}</span>
                    <span class="badge rounded-pill"
                        style="font-size:10px; background-color:#fff3cd; color:#856404;">
                        {{ match($activeOrder->status) {
                            'finding_driver' => '🔍 Mencari Driver',
                            'processing'     => '✅ Driver Ditemukan',
                            'preparing'      => '👨‍🍳 Disiapkan',
                            'ready'          => '✅ Siap Diambil',
                            'pickup'         => '📍 Driver di Toko',
                            'delivering'     => '🛵 Dalam Perjalanan',
                            'arrived'        => '📍 Driver Tiba',
                            'waiting_tenant' => '🏪 Menunggu Toko',
                            'item_mismatch'  => '⚠️ Cek Perubahan',
                            default          => $activeOrder->status
                        } }}
                    </span>
                </div>
                <div class="small text-muted">
                    📍 {{ $activeOrder->location->destination_address }}
                </div>
                <div class="small text-danger fw-semibold mt-1">
                    Rp {{ number_format($activeOrder->total_fare, 0, ',', '.') }}
                </div>
            </div>
        </a>
        @endif

        {{-- Riwayat order --}}
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="fw-semibold small text-muted">Aktivitas Terakhir</div>
            @if($recentOrders->isNotEmpty())
                <a href="{{ route('customer.order.index') }}" class="text-danger small text-decoration-none fw-medium">
                    Lihat Semua
                </a>
            @endif
        </div>

        @if($recentOrders->isEmpty())
            <div class="app-card text-center py-4">
                <div style="font-size:36px;">📭</div>
                <p class="text-muted small mt-2 mb-0">Belum ada riwayat order.</p>
                <p class="text-muted small">Yuk, coba layanan ToLong sekarang!</p>
            </div>
        @else
            @foreach($recentOrders as $order)
                <a href="{{ route('customer.order.tracking', $order->public_id) }}" class="text-decoration-none text-dark">
                    <div class="app-card mb-2 p-3">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <div style="font-size:20px;">
                                    <i class="fas {{ match($order->service_type) {
                                        'antar'  => 'fa-motorcycle',
                                        'makan'  => 'fa-utensils',
                                        'custom' => 'fa-box-open',
                                        default  => 'fa-motorcycle'
                                    } }}"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold small">{{ $order->order_number }}</div>
                                    <div class="text-muted" style="font-size:11px;">
                                        {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                                    </div>
                                </div>
                            </div>

                            @if(in_array($order->status, ['completed', 'cancelled']))
                                <span class="badge rounded-pill
                                    {{ $order->status === 'completed' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}"
                                    style="font-size:10px;">
                                    {{ $order->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                                </span>
                            @else
                                <span class="badge rounded-pill bg-warning-subtle text-warning" style="font-size:10px;">
                                    {{ match($order->status) {
                                        'finding_driver' => 'Cari Driver',
                                        'processing'     => 'Driver Menuju',
                                        'preparing'      => 'Disiapkan',
                                        'ready'          => 'Siap Diambil',
                                        'pickup'         => 'Diambil Driver',
                                        'delivering'     => 'Diantar',
                                        'arrived'        => 'Driver Tiba',
                                        'waiting_tenant' => 'Menunggu Toko',
                                        'item_mismatch'  => 'Cek Perubahan',
                                        default          => $order->status
                                    } }}
                                </span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small text-truncate" style="max-width:65%;">
                                @if($order->service_type === 'makan' && $order->makanDetails->isNotEmpty())
                                    {{ $order->makanDetails->pluck('tenant.store_name')->join(', ') }}
                                @elseif($order->service_type === 'custom' && $order->customDetail)
                                    {{ Str::limit($order->customDetail->item_description, 35) }}
                                @else
                                    {{ $order->location->destination_address ?? '-' }}
                                @endif
                            </span>
                            <span class="fw-semibold small">
                                Rp {{ number_format($order->total_fare, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        @endif

    </div>

</div>