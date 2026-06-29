<div class="px-3 py-3">

    {{-- Order Aktif --}}
    @if($activeOrders->isNotEmpty())
        <div class="text-muted small fw-medium mb-2">Sedang Berlangsung</div>

        @foreach($activeOrders as $order)
            <a href="{{ route('customer.order.tracking', $order->public_id) }}" class="text-decoration-none text-dark">
                <div class="app-card mb-3" style="border-left:4px solid #dc3545;">

                    <div class="d-flex align-items-center justify-content-between mb-2">
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
                        <span class="badge rounded-pill
                            {{ match($order->status) {
                                'waiting_tenant', 'preparing', 'finding_driver' => 'bg-warning-subtle text-warning',
                                'item_mismatch' => 'bg-danger-subtle text-danger',
                                default => 'bg-info-subtle text-info'
                            } }}"
                            style="font-size:10px;">
                            {{ match($order->status) {
                                'waiting_tenant'  => 'Menunggu Toko',
                                'preparing'       => 'Disiapkan',
                                'finding_driver'  => 'Cari Driver',
                                'processing'      => 'Driver Menuju',
                                'ready'           => 'Siap Diambil',
                                'pickup'          => 'Diambil Driver',
                                'item_mismatch'   => 'Perlu Konfirmasi',
                                'arrived'         => 'Driver Tiba',
                                'delivering'      => 'Diantar',
                                default           => $order->status
                            } }}
                        </span>
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
                        <span class="fw-semibold small text-danger">
                            Rp {{ number_format($order->total_fare, 0, ',', '.') }}
                        </span>
                    </div>

                </div>
            </a>
        @endforeach
        <div class="px-1">
            {{ $activeOrders->links() }}
        </div>
    @endif

    {{-- Riwayat --}}
    @if($historyOrders->isNotEmpty())
        <div class="text-muted small fw-medium mb-2 {{ $activeOrders->isNotEmpty() ? 'mt-4' : '' }}">
            Riwayat
        </div>

        @foreach($historyOrders as $order)
            <a href="{{ route('customer.order.tracking', $order->id) }}" class="text-decoration-none text-dark">
                <div class="app-card mb-3">

                    <div class="d-flex align-items-center justify-content-between mb-2">
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
                        <span class="badge rounded-pill
                            {{ $order->status === 'completed' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}"
                            style="font-size:10px;">
                            {{ $order->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                        </span>
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
                        <span class="fw-semibold small {{ $order->status === 'completed' ? 'text-dark' : 'text-muted' }}">
                            Rp {{ number_format($order->total_fare, 0, ',', '.') }}
                        </span>
                    </div>

                </div>
            </a>
        @endforeach
        <div class="px-1">
            {{ $historyOrders->links() }}
        </div>
    @endif

    {{-- Kosong --}}
    @if($activeOrders->isEmpty() && $historyOrders->isEmpty())
        <div class="text-center py-5">
            <div style="font-size:48px;">📭</div>
            <p class="fw-semibold mt-3 mb-1">Belum Ada Pesanan</p>
            <p class="text-muted small">Yuk, mulai pesan layanan ToLong!</p>
            <a href="{{ route('customer.dashboard') }}" class="btn btn-danger rounded-3 px-4 mt-2">
                Mulai Pesan
            </a>
        </div>
    @endif

</div>