<div>
    @if($incomingOrder)
        {{-- Overlay order masuk --}}
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-end"
            style="background:rgba(0,0,0,0.5);z-index:999;">
            <div class="bg-white w-100 rounded-top-4 p-4"
                style="max-width:480px;margin:0 auto;">

                <div class="text-center mb-3">
                    <div style="font-size:36px;">🛵</div>
                    <h6 class="fw-bold mt-2">Order Masuk!</h6>
                    <span class="badge bg-danger-subtle text-danger rounded-pill">
                        {{ strtoupper($incomingOrder['service_type']) }}
                    </span> * {{ $incomingOrder['distance_km'] }} km

                    {{-- Info antrian --}}
                    @if(count($orderQueue) > 0)
                        <div class="text-muted small mt-1">
                            +{{ count($orderQueue) }} order menunggu setelah ini
                        </div>
                    @endif
                </div>

                <div class="app-card mb-3" style="background:#f8f9fa;">
                    <div class="d-flex justify-content-between mb-2 row">
                        <div class="col-4 text-center small">Dari</div>
                        <div class="col-4 text-center small">Ke</div>
                        <div class="col-4 text-center small">{{ $incomingOrder['payment_method'] === 'tunai' ? 'Tunai' : 'TolongPay' }}</div>
                        <div class="col-4 text-center small">{{ $incomingOrder['origin_address'] }}</div>
                        <div class="col-4 text-center small">{{ $incomingOrder['destination_address'] }}</div>
                        <div class="col-4 text-center small text-danger fw-bold">Rp {{ number_format($incomingOrder['total_fare'], 0, ',', '.') }}</div>
                    </div>

                    @if($incomingOrder['service_type'] === 'custom' && !empty($incomingOrder['item_description']))
                        <div class="app-card" style="background:#fff8f0;">
                            <div class="text-muted small mb-1">📝 Deskripsi Barang dari Customer</div>
                            <div class="small">{{ $incomingOrder['item_description'] }}</div>
                        </div>
                    @endif

                    @if($incomingOrder['notes_for_driver'])
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Catatan</span>
                            <span class="small fw-medium text-end" style="max-width:65%;">
                                {{ $incomingOrder['notes_for_driver'] }}
                            </span>
                        </div>
                    @endif
                </div>

                @if($error)
                    <div class="alert alert-danger py-2 small mb-3">{{ $error }}</div>
                @endif

                @if(!empty($incomingOrder['origin_latitude']) && !empty($incomingOrder['destination_latitude']))
                    <div class="rounded-3 overflow-hidden mb-3" style="width:100%;">
                        <div id="map-incoming-order"
                            data-origin-lat="{{ $incomingOrder['origin_latitude'] }}"
                            data-origin-lng="{{ $incomingOrder['origin_longitude'] }}"
                            data-dest-lat="{{ $incomingOrder['destination_latitude'] }}"
                            data-dest-lng="{{ $incomingOrder['destination_longitude'] }}"
                            style="height:200px;width:100%;z-index:0;"></div>
                    </div>
                @endif

                <div class="d-flex gap-2">
                    <button wire:click="rejectOrder"
                        class="btn btn-outline-secondary rounded-3 flex-grow-1 py-2">
                        Tolak
                    </button>
                    <button wire:click="acceptOrder"
                        wire:loading.attr="disabled"
                        class="btn btn-danger rounded-3 flex-grow-1 py-2 fw-semibold">
                        <span wire:loading.remove wire:target="acceptOrder">Terima Order</span>
                        <span wire:loading wire:target="acceptOrder">Memproses...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- Popup notif makanan siap --}}
    @if($showReadyNotif)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-end"
            style="background:rgba(0,0,0,0.5);z-index:999;">
            <div class="bg-white w-100 rounded-top-4 p-4" style="max-width:480px;margin:0 auto;">
                <div class="text-center mb-4">
                    <div style="font-size:48px;">✅</div>
                    <h6 class="fw-bold mt-3">Makanan Siap Diambil!</h6>
                    <p class="text-muted small mb-0">
                        Pesanan <strong>{{ $readyOrderNumber }}</strong> sudah siap.<br>
                        Segera menuju toko untuk mengambil pesanan.
                    </p>
                </div>
                <button wire:click="dismissReadyNotif"
                    class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
                    OKE, Menuju Toko
                </button>
            </div>
        </div>
    @endif

    {{-- Popup hasil keputusan customer --}}
    @if($showMismatchResolvedPopup)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-end"
            style="background:rgba(0,0,0,0.5);z-index:999;">
            <div class="bg-white w-100 rounded-top-4 p-4" style="max-width:480px;margin:0 auto;">
                <div class="text-center mb-4">
                    <div style="font-size:40px;">📩</div>
                    <h6 class="fw-bold mt-2">Respon dari Customer</h6>
                    <p class="text-muted small mb-0">{{ $mismatchResolvedMsg }}</p>
                </div>
                <button wire:click="dismissMismatchResolvedPopup"
                    class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
                    OKE
                </button>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function () {
    function initIncomingOrderMap() {
        const mapEl = document.getElementById('map-incoming-order');
        console.log('[Map Debug] mapEl:', mapEl);

        if (!mapEl) {
            console.log('[Map Debug] Container tidak ditemukan');
            return;
        }
        if (mapEl._leaflet_id) {
            console.log('[Map Debug] Sudah ada peta di container ini');
            return;
        }

        const originLat = parseFloat(mapEl.dataset.originLat);
        const originLng = parseFloat(mapEl.dataset.originLng);
        const destLat    = parseFloat(mapEl.dataset.destLat);
        const destLng    = parseFloat(mapEl.dataset.destLng);

        console.log('[Map Debug] Koordinat:', originLat, originLng, destLat, destLng);

        if (!originLat || !destLat) {
            console.log('[Map Debug] Koordinat tidak valid, batal render peta');
            return;
        }

        console.log('[Map Debug] Leaflet L tersedia?', typeof L !== 'undefined');

        const map = L.map('map-incoming-order', {
            zoomControl: false,
            dragging: true,
            scrollWheelZoom: false,
        }).setView([originLat, originLng], 13);

        console.log('[Map Debug] Map berhasil dibuat:', map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Tolong App'
        }).addTo(map);

        const greenIcon = L.divIcon({
            html: '<div style="width:14px;height:14px;background:#198754;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
            className: '', iconAnchor: [7, 7]
        });
        const redIcon = L.divIcon({
            html: '<div style="width:14px;height:14px;background:#dc3545;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
            className: '', iconAnchor: [7, 7]
        });

        L.marker([originLat, originLng], { icon: greenIcon }).addTo(map);
        L.marker([destLat, destLng], { icon: redIcon }).addTo(map);

        const routeLine = L.polyline([[originLat, originLng], [destLat, destLng]], {
            color: '#dc3545', weight: 3, dashArray: '6,6'
        }).addTo(map);

        map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });

        setTimeout(() => map.invalidateSize(), 100);
        setTimeout(() => map.invalidateSize(), 400);
        setTimeout(() => map.invalidateSize(), 800);

        console.log('[Map Debug] Selesai render peta');
    }

    const intervalId = setInterval(function () {
        const mapEl = document.getElementById('map-incoming-order');
        if (mapEl && !mapEl._leaflet_id) {
            initIncomingOrderMap();
        }
    }, 300);

    setTimeout(() => clearInterval(intervalId), 10000);
})();
</script>
@endpush