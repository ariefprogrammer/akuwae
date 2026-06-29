<div>
    @if($success)
        <div class="alert alert-success py-2 small rounded-3 mx-3 mt-3">✓ {{ $success }}</div>
    @endif

    @forelse($activeOrders as $order)
        <div class="px-3 pt-3">
            <div class="app-card">

                {{-- Header --}}
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="fw-semibold small">{{ $order->order_number }}</div>
                        <span class="badge rounded-pill mt-1
                            {{ match($order->status) {
                                'preparing'  => 'bg-warning-subtle text-warning',
                                'processing' => 'bg-info-subtle text-info',
                                'ready'      => 'bg-success-subtle text-success',
                                'delivering' => 'bg-primary-subtle text-primary',
                                default      => 'bg-secondary-subtle text-secondary'
                            } }}"
                            style="font-size:10px;">
                            {{ match($order->status) {
                                'preparing'  => '👨‍🍳 Sedang Dimasak',
                                'processing' => '🛵 Driver Menuju Toko',
                                'ready'      => '✅ Menunggu Diambil',
                                'delivering' => '🚚 Sudah Diambil Driver',
                                default      => $order->status
                            } }}
                        </span>
                    </div>
                    <div class="text-danger fw-bold small">
                        Rp {{ number_format($order->total_fare, 0, ',', '.') }}
                    </div>
                </div>

                {{-- Info driver (muncul setelah ada driver) --}}
                @if($order->driver)
                    <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-3"
                        style="background:#f8f9fa;">
                        <div style="font-size:20px;">
                            {{ $order->driver->vehicle_type === 'motor' ? '🛵' : '🚗' }}
                        </div>
                        <div>
                            <div class="small fw-medium">{{ $order->driver->name }}</div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ strtoupper($order->driver->vehicle_plate) }}
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Daftar item --}}
                @foreach($order->makanDetails as $detail)
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
                @endforeach

                {{-- Alamat --}}
                <div class="mt-2 pt-2 border-top">
                    <div class="text-muted small">Dikirim ke:</div>
                    <div class="small fw-medium">{{ $order->location->destination_address }}</div>
                </div>

                {{-- Tombol aksi --}}
                @if($order->status === 'processing')
                    {{-- Driver sudah terima, boleh klik Siap Diambil --}}
                    <button
                        wire:click="markReady({{ $order->id }})"
                        wire:loading.attr="disabled"
                        wire:confirm="Tandai pesanan {{ $order->order_number }} sudah siap diambil?"
                        class="btn btn-success w-100 rounded-3 py-2 fw-semibold mt-3">
                        <span wire:loading.remove wire:target="markReady({{ $order->id }})">
                            ✓ Siap Diambil
                        </span>
                        <span wire:loading wire:target="markReady({{ $order->id }})">
                            Memproses...
                        </span>
                    </button>

                @elseif($order->status === 'preparing')
                    {{-- Belum ada driver --}}
                    <div class="text-center text-muted small mt-3 pt-2 border-top">
                        ⏳ Menunggu driver menerima pesanan...
                    </div>

                @elseif($order->status === 'ready')
                    <div class="text-center text-success small fw-medium mt-3 pt-2 border-top">
                        ✅ Menunggu driver mengambil pesanan...
                    </div>

                @elseif($order->status === 'delivering')
                    <div class="text-center text-primary small fw-medium mt-3 pt-2 border-top">
                        🚚 Pesanan sudah diambil, sedang diantar ke customer...
                    </div>
                @endif

            </div>
        </div>
    @empty
        <div class="text-center text-muted small mt-3 pt-2 border-top">
            Tidak ada pesanan aktif.
        </div>
    @endforelse
</div>