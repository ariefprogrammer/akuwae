<div class="px-3 py-3">

    {{-- Order Aktif --}}
    @if($activeOrders->isNotEmpty())
        <div class="text-muted small fw-medium mb-2">Sedang Berlangsung</div>

        @foreach($activeOrders as $order)
            <div class="app-card mb-3" style="border-left:4px solid #dc3545;">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div style="font-size:24px;">🍱</div>
                        <div>
                            <div class="fw-semibold small">{{ $order->order_number }}</div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                            </div>
                        </div>
                    </div>
                    <span class="badge rounded-pill
                        {{ match($order->status) {
                            'waiting_tenant' => 'bg-warning-subtle text-warning',
                            'preparing'      => 'bg-warning-subtle text-warning',
                            'processing'     => 'bg-info-subtle text-info',
                            'ready'          => 'bg-success-subtle text-success',
                            'delivering'     => 'bg-primary-subtle text-primary',
                            default          => 'bg-secondary-subtle text-secondary'
                        } }}"
                        style="font-size:10px;">
                        {{ match($order->status) {
                            'waiting_tenant' => 'Menunggu Konfirmasi',
                            'preparing'      => 'Sedang Dimasak',
                            'processing'     => 'Driver Menuju Toko',
                            'ready'          => 'Siap Diambil',
                            'delivering'     => 'Sudah Diambil',
                            default          => $order->status
                        } }}
                    </span>
                </div>

                {{-- Daftar item --}}
                @foreach($order->makanDetails as $detail)
                    @if($detail->tenant_id === auth()->user()->tenant->id)
                        @foreach($detail->items as $item)
                            <div class="d-flex justify-content-between align-items-start py-1 border-top">
                                <div>
                                    <span class="small">{{ $item->quantity }}× {{ $item->menu->item_name }}</span>
                                    @if($item->notes)
                                        <div class="text-muted" style="font-size:11px;">📝 {{ $item->notes }}</div>
                                    @endif
                                </div>
                                <span class="small text-muted">
                                    Rp {{ number_format($item->price_snapshot * $item->quantity, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    @endif
                @endforeach

                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <span class="text-muted small text-truncate" style="max-width:65%;">
                        📍 {{ $order->location->destination_address ?? '-' }}
                    </span>
                    <span class="fw-semibold small text-danger">
                        Rp {{ number_format($order->total_fare, 0, ',', '.') }}
                    </span>
                </div>

            </div>
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
            <div class="app-card mb-3">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="d-flex align-items-center gap-2">
                        <div style="font-size:24px;opacity:0.6;">🍱</div>
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

                @foreach($order->makanDetails as $detail)
                    @if($detail->tenant_id === auth()->user()->tenant->id)
                        @foreach($detail->items as $item)
                            <div class="d-flex justify-content-between align-items-start py-1 border-top">
                                <span class="small">{{ $item->quantity }}× {{ $item->menu->item_name }}</span>
                                <span class="small text-muted">
                                    Rp {{ number_format($item->price_snapshot * $item->quantity, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    @endif
                @endforeach

                <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                    <span class="text-muted small text-truncate" style="max-width:65%;">
                        📍 {{ $order->location->destination_address ?? '-' }}
                    </span>
                    <span class="fw-semibold small {{ $order->status === 'completed' ? 'text-dark' : 'text-muted' }}">
                        Rp {{ number_format($order->total_fare, 0, ',', '.') }}
                    </span>
                </div>

            </div>
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
            <p class="text-muted small">Pesanan dari customer akan muncul di sini.</p>
        </div>
    @endif

</div>