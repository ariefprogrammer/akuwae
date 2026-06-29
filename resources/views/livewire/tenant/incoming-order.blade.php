<div>
    @if($incomingOrder)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-end"
            style="background:rgba(0,0,0,0.5);z-index:999;">
            <div class="bg-white w-100 rounded-top-4 p-4"
                style="max-width:480px;margin:0 auto;">

                <div class="text-center mb-3">
                    <div style="font-size:36px;">🍱</div>
                    <h6 class="fw-bold mt-2">Pesanan Masuk!</h6>
                    <div class="text-muted small">{{ $incomingOrder['order_number'] }}</div>
                </div>

                {{-- Daftar item --}}
                <div class="app-card mb-3" style="background:#f8f9fa;">
                    <div class="fw-semibold small mb-2">Detail Pesanan</div>
                    @foreach($incomingOrder['items'] as $item)
                        <div class="d-flex justify-content-between py-1 border-top">
                            <div>
                                <span class="small">{{ $item['quantity'] }}× {{ $item['item_name'] }}</span>
                                @if($item['notes'])
                                    <div class="text-muted" style="font-size:11px;">📝 {{ $item['notes'] }}</div>
                                @endif
                            </div>
                            <span class="small fw-medium">
                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                {{-- Info pengiriman --}}
                <div class="app-card mb-3" style="background:#f8f9fa;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Dikirim ke</span>
                        <span class="small fw-medium text-end" style="max-width:65%;">
                            {{ $incomingOrder['destination_address'] }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Jarak</span>
                        <span class="small fw-medium">{{ $incomingOrder['distance_km'] }} km</span>
                    </div>
                    @if($incomingOrder['notes_for_driver'])
                        <div class="d-flex justify-content-between mt-1">
                            <span class="text-muted small">Catatan</span>
                            <span class="small fw-medium text-end" style="max-width:65%;">
                                {{ $incomingOrder['notes_for_driver'] }}
                            </span>
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <div class="text-muted small">Total Pesanan</div>
                        <div class="fw-bold text-danger fs-5">
                            Rp {{ number_format($incomingOrder['total_fare'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                @if($error)
                    <div class="alert alert-danger py-2 small mb-3">{{ $error }}</div>
                @endif

                <div class="d-flex gap-2">
                    <button wire:click="rejectOrder"
                        class="btn btn-outline-danger rounded-3 flex-grow-1 py-2">
                        Tolak
                    </button>
                    <button wire:click="acceptOrder"
                        wire:loading.attr="disabled"
                        class="btn btn-danger rounded-3 flex-grow-1 py-2 fw-semibold">
                        <span wire:loading.remove wire:target="acceptOrder">Terima Pesanan</span>
                        <span wire:loading wire:target="acceptOrder">Memproses...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif
</div>