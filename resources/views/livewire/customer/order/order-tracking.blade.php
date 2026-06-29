<div>

    {{-- Status Card --}}
    <div class="app-card text-center py-4 mb-3"
        @if(!in_array($order->status, ['completed', 'cancelled', 'item_mismatch']))
            wire:poll.10s="refreshOrder"
        @endif
    >
        @if($order->status === 'waiting_tenant')
            <div style="font-size:48px;">🏪</div>
            <h6 class="fw-bold mt-3">Menunggu Konfirmasi Toko</h6>
            <p class="text-muted small mb-2">Pemilik toko sedang mengkonfirmasi pesananmu.</p>
            <div class="spinner-border text-danger mt-1" style="width:24px;height:24px;"></div>

        @elseif($order->status === 'preparing')
            <div style="font-size:48px;">👨‍🍳</div>
            <h6 class="fw-bold mt-3">Pesanan Diterima!</h6>
            <p class="text-muted small mb-2">Toko sedang menyiapkan pesananmu, mencari driver...</p>
            <div class="spinner-border text-danger mt-1" style="width:24px;height:24px;"></div>

        @elseif($order->status === 'finding_driver')
            <div style="font-size:48px;">🔍</div>
            <h6 class="fw-bold mt-3">Mencari Driver...</h6>
            <p class="text-muted small mb-2">Mohon tunggu, kami sedang mencarikan driver untukmu.</p>
            <div class="spinner-border text-danger mt-1" style="width:24px;height:24px;"></div>

        @elseif($order->status === 'processing')
            <div style="font-size:48px;">✅</div>
            <h6 class="fw-bold mt-3">Driver Ditemukan!</h6>
            <p class="text-muted small mb-0">
                @if($order->service_type === 'custom')
                    Driver sedang menuju lokasi pengambilan barang.
                @elseif($order->service_type === 'makan')
                    Driver sedang menuju toko untuk mengambil pesananmu.
                @else
                    Driver sedang menuju lokasi jemputmu.
                @endif
            </p>

        @elseif($order->status === 'ready')
            <div style="font-size:48px;">✅</div>
            <h6 class="fw-bold mt-3">Pesanan Siap!</h6>
            <p class="text-muted small mb-0">Pesananmu sudah siap, driver sedang menuju toko.</p>

        @elseif($order->status === 'pickup')
            <div style="font-size:48px;">📍</div>
            <h6 class="fw-bold mt-3">Driver di Toko</h6>
            <p class="text-muted small mb-0">Driver sedang mengambil pesananmu.</p>

        @elseif($order->status === 'item_mismatch')
            <div style="font-size:48px;">⚠️</div>
            <h6 class="fw-bold mt-3">Ada Perubahan Pesanan</h6>
            <p class="text-muted small mb-0">Driver melaporkan perubahan pada barang pesananmu. Mohon cek detail di bawah.</p>

        @elseif($order->status === 'arrived')
            <div style="font-size:48px;">📍</div>
            <h6 class="fw-bold mt-3">Driver Sudah Tiba</h6>
            <p class="text-muted small mb-0">Driver sudah sampai di lokasimu.</p>

        @elseif($order->status === 'delivering')
            <div style="font-size:48px;">🛵</div>
            <h6 class="fw-bold mt-3">Dalam Perjalanan</h6>
            <p class="text-muted small mb-0">
                @if($order->service_type === 'custom')
                    Driver sedang menuju lokasimu untuk mengantar barang.
                @else
                    Pesananmu sedang diantar.
                @endif
            </p>

        @elseif($order->status === 'completed')
            <div style="font-size:48px;">🎉</div>
            <h6 class="fw-bold mt-3">Pesanan Selesai!</h6>
            <p class="text-muted small mb-0">Terima kasih telah menggunakan ToLong.</p>

        @elseif($order->status === 'cancelled')
            <div style="font-size:48px;">❌</div>
            <h6 class="fw-bold mt-3">Pesanan Dibatalkan</h6>
            <p class="text-muted small mb-0">Maaf, pesananmu tidak dapat diproses.</p>
        @endif
    </div>

    {{-- Card khusus item mismatch — perlu keputusan customer --}}
    @if($order->status === 'item_mismatch' && $order->customDetail?->mismatch_reason)
        <div class="app-card mb-3">
            <div class="fw-semibold small mb-2">⚠️ Penjelasan dari Driver</div>
            <p class="small text-muted mb-3">{{ $order->customDetail->mismatch_reason }}</p>

            <div class="d-flex gap-2">
                <button wire:click="cancelMismatch"
                    wire:confirm="Yakin ingin membatalkan pesanan ini?"
                    wire:loading.attr="disabled"
                    class="btn btn-outline-danger rounded-3 flex-grow-1 py-2">
                    Batalkan Pesanan
                </button>
                <button wire:click="acceptMismatch"
                    wire:loading.attr="disabled"
                    class="btn btn-success rounded-3 flex-grow-1 py-2 fw-semibold">
                    Terima
                </button>
            </div>
        </div>
    @endif

    @livewire('order-chat', ['order' => $order])

</div>