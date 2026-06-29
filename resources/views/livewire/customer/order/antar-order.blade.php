<div class="px-3 py-3">

    {{-- Step indicator --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        @foreach([1 => 'Lokasi', 2 => 'Konfirmasi'] as $s => $label)
            <div class="d-flex align-items-center gap-2 {{ !$loop->last ? 'flex-grow-1' : '' }}">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                    style="width:28px;height:28px;font-size:12px;
                    background:{{ $step >= $s ? '#dc3545' : '#e9ecef' }};
                    color:{{ $step >= $s ? '#fff' : '#adb5bd' }};">
                    {{ $s }}
                </div>
                <span style="font-size:12px;color:{{ $step >= $s ? '#dc3545' : '#adb5bd' }};">
                    {{ $label }}
                </span>
                @if(!$loop->last)
                    <div class="flex-grow-1" style="height:2px;background:{{ $step > $s ? '#dc3545' : '#e9ecef' }};"></div>
                @endif
            </div>
        @endforeach
    </div>

    @if($error)
        <div class="alert alert-danger py-2 small rounded-3">{{ $error }}</div>
    @endif

    {{-- ══ STEP 1: LOKASI ══ --}}
    @if($step === 1)

        {{-- Peta --}}
        <div class="app-card mb-3 p-0 overflow-hidden">
            <div id="map" style="height:260px;z-index:0;"></div>
        </div>

        {{-- Titik Jemput --}}
        <div class="app-card mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle bg-success d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width:12px;height:12px;"></div>
                <span class="small fw-medium">Titik Jemput</span>
            </div>
            <input wire:model="origin_address" type="text"
                placeholder="Nama atau alamat titik jemput"
                class="form-control form-control-sm rounded-3 @error('origin_address') is-invalid @enderror">
            @error('origin_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if($origin_latitude)
                <div class="text-muted mt-1" style="font-size:11px;">
                    📍 {{ $origin_latitude }}, {{ $origin_longitude }}
                </div>
            @endif
        </div>

        {{-- Titik Tujuan --}}
        <div class="app-card mb-3">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center flex-shrink-0"
                    style="width:12px;height:12px;"></div>
                <span class="small fw-medium">Titik Tujuan</span>
            </div>
            <input wire:model="destination_address" type="text"
                placeholder="Nama atau alamat tujuan"
                class="form-control form-control-sm rounded-3 @error('destination_address') is-invalid @enderror">
            @error('destination_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if($dest_latitude)
                <div class="text-muted mt-1" style="font-size:11px;">
                    📍 {{ $dest_latitude }}, {{ $dest_longitude }}
                </div>
            @endif
        </div>

        {{-- Info jarak — dikontrol JS, bukan Livewire --}}
        <div id="distance-box" class="app-card mb-3 text-center" style="display:none;">
            <div class="text-muted small">Estimasi Jarak</div>
            <div class="fw-bold text-danger fs-5">
                <span id="distance-display">0</span>
            </div>
        </div>

        {{-- Catatan --}}
        <div class="app-card mb-3">
            <label class="form-label small fw-medium">
                Catatan untuk Driver <span class="text-muted">(opsional)</span>
            </label>
            <textarea wire:model="notes_for_driver" rows="2"
                placeholder="Cth: paket warna merah, hubungi dulu sebelum tiba"
                class="form-control rounded-3"></textarea>
        </div>

        {{-- Tombol — id dipakai JS, bukan wire:click --}}
        <button id="btn-next" class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
            Lanjut →
        </button>

    {{-- ══ STEP 2: KONFIRMASI ══ --}}
    @elseif($step === 2)

        {{-- Pilih kendaraan --}}
        <div class="app-card mb-3">
            <div class="fw-semibold small mb-3">Pilih Kendaraan</div>
            <div class="d-flex gap-2">
                <label class="flex-grow-1" wire:click="$set('vehicle_type', 'motor')">
                    <div class="border rounded-3 text-center py-3 {{ $vehicle_type === 'motor' ? 'border-danger bg-danger bg-opacity-10' : '' }}"
                        style="cursor:pointer;">
                        <div style="font-size:28px;">🛵</div>
                        <div class="small fw-medium mt-1">Motor</div>
                    </div>
                </label>
                <label class="flex-grow-1" wire:click="$set('vehicle_type', 'mobil')">
                    <div class="border rounded-3 text-center py-3 {{ $vehicle_type === 'mobil' ? 'border-danger bg-danger bg-opacity-10' : '' }}"
                        style="cursor:pointer;">
                        <div style="font-size:28px;">🚗</div>
                        <div class="small fw-medium mt-1">Mobil</div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Ringkasan --}}
        <div class="app-card mb-3">
            <div class="fw-semibold small mb-3">Ringkasan Order</div>

            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Dari</span>
                <span class="small fw-medium text-end" style="max-width:60%;">{{ $origin_address }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Ke</span>
                <span class="small fw-medium text-end" style="max-width:60%;">{{ $destination_address }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">Jarak</span>
                <span class="small fw-medium">{{ $distance_km }} km</span>
            </div>
            @if($notes_for_driver)
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Catatan</span>
                    <span class="small fw-medium text-end" style="max-width:60%;">{{ $notes_for_driver }}</span>
                </div>
            @endif

            <hr class="my-2">

            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted small">Biaya dasar</span>
                <span class="small">Rp {{ number_format($base_fare, 0, ',', '.') }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted small">{{ $distance_km }} km × Rp {{ number_format($per_km_fare, 0, ',', '.') }}</span>
                <span class="small">Rp {{ number_format($distance_km * $per_km_fare, 0, ',', '.') }}</span>
            </div>

            <hr class="my-2">

            <div class="d-flex justify-content-between">
                <span class="fw-semibold">Total</span>
                <span class="fw-bold text-danger fs-6">Rp {{ number_format($total_fare, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Pembayaran --}}
        <div class="app-card mb-3 d-flex align-items-center gap-3">
            <div style="font-size:24px;">💵</div>
            <div>
                <div class="fw-medium small">Pembayaran</div>
                <div class="text-muted" style="font-size:12px;">Tunai ke driver</div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button wire:click="$set('step', 1)"
                class="btn btn-outline-secondary rounded-3 flex-grow-1">
                ← Kembali
            </button>
            <button wire:click="placeOrder" wire:loading.attr="disabled"
                class="btn btn-danger rounded-3 flex-grow-1 fw-semibold">
                <span wire:loading.remove wire:target="placeOrder">Pesan Sekarang</span>
                <span wire:loading wire:target="placeOrder">Memproses...</span>
            </button>
        </div>

    @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = -4.5564;
    const defaultLng = 105.4057;

    const map = L.map('map').setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let originMarker = null;
    let destMarker   = null;
    let routeLine    = null;
    let pickingMode  = 'origin';

    // Simpan data lokal — tidak kirim ke Livewire dulu
    let routeData = {
        originLat: null, originLng: null,
        destLat: null,   destLng: null,
        distanceKm: 0,
    };

    // ── Tombol mode picker ───────────────────────────────────
    const ModeControl = L.Control.extend({
        options: { position: 'topright' },
        onAdd: function () {
            const div = L.DomUtil.create('div', '');
            div.style.cssText = 'display:flex;flex-direction:column;gap:4px;';

            const btnOrigin = createBtn('🟢 Jemput', 'origin', '#198754');
            const btnDest   = createBtn('🔴 Tujuan', 'dest',   '#dee2e6');
            div.appendChild(btnOrigin);
            div.appendChild(btnDest);

            function createBtn(label, mode, borderColor) {
                const btn = L.DomUtil.create('button', '');
                btn.innerHTML    = label;
                btn.style.cssText = `padding:4px 8px;font-size:11px;border-radius:6px;border:2px solid ${borderColor};background:#fff;cursor:pointer;white-space:nowrap;`;
                L.DomEvent.on(btn, 'click', function (e) {
                    L.DomEvent.stopPropagation(e);
                    pickingMode = mode;
                    btnOrigin.style.borderColor = mode === 'origin' ? '#198754' : '#dee2e6';
                    btnDest.style.borderColor   = mode === 'dest'   ? '#dc3545' : '#dee2e6';
                });
                return btn;
            }
            return div;
        }
    });
    map.addControl(new ModeControl());

    // ── Tombol posisi saya ───────────────────────────────────
    const LocateControl = L.Control.extend({
        options: { position: 'topleft' },
        onAdd: function () {
            const btn = L.DomUtil.create('button', '');
            btn.innerHTML = '📍';
            btn.style.cssText = 'width:34px;height:34px;font-size:16px;cursor:pointer;border-radius:6px;border:1px solid #ccc;background:#fff;';
            L.DomEvent.on(btn, 'click', function (e) {
                L.DomEvent.stopPropagation(e);
                navigator.geolocation.getCurrentPosition(function (pos) {
                    map.setView([pos.coords.latitude, pos.coords.longitude], 16);
                });
            });
            return btn;
        }
    });
    map.addControl(new LocateControl());

    // ── Klik peta ────────────────────────────────────────────
    map.on('click', function (e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        if (pickingMode === 'origin') {
            if (originMarker) {
                originMarker.setLatLng([lat, lng]);
            } else {
                originMarker = L.marker([lat, lng], { draggable: true, icon: greenIcon() }).addTo(map);
                originMarker.on('dragend', function (e) {
                    const p = e.target.getLatLng();
                    routeData.originLat = p.lat;
                    routeData.originLng = p.lng;
                    drawRoute();
                });
            }
            routeData.originLat = lat;
            routeData.originLng = lng;
        } else {
            if (destMarker) {
                destMarker.setLatLng([lat, lng]);
            } else {
                destMarker = L.marker([lat, lng], { draggable: true, icon: redIcon() }).addTo(map);
                destMarker.on('dragend', function (e) {
                    const p = e.target.getLatLng();
                    routeData.destLat = p.lat;
                    routeData.destLng = p.lng;
                    drawRoute();
                });
            }
            routeData.destLat = lat;
            routeData.destLng = lng;
        }

        drawRoute();
    });

    // ── Garis rute & hitung jarak ────────────────────────────
    function drawRoute() {
        if (!routeData.originLat || !routeData.destLat) return;

        const o = [routeData.originLat, routeData.originLng];
        const d = [routeData.destLat,   routeData.destLng];

        if (routeLine) map.removeLayer(routeLine);
        routeLine = L.polyline([o, d], {
            color: '#dc3545', weight: 3, dashArray: '6,6'
        }).addTo(map);
        map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });

        // Hitung jarak dengan koreksi 1.3x
        routeData.distanceKm = Math.round(haversine(...o, ...d) * 1.3 * 100) / 100;

        // Update UI jarak tanpa menyentuh Livewire
        document.getElementById('distance-display').textContent = routeData.distanceKm + ' km';
        document.getElementById('distance-box').style.display   = 'block';
    }

    // ── Haversine ────────────────────────────────────────────
    function haversine(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) ** 2 +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    // ── Icon ─────────────────────────────────────────────────
    function greenIcon() {
        return L.divIcon({
            html: '<div style="width:14px;height:14px;background:#198754;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
            className: '', iconAnchor: [7, 7]
        });
    }
    function redIcon() {
        return L.divIcon({
            html: '<div style="width:14px;height:14px;background:#dc3545;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
            className: '', iconAnchor: [7, 7]
        });
    }

    // ── Inject data ke Livewire HANYA saat tombol Lanjut diklik ──
    document.getElementById('btn-next').addEventListener('click', function () {
        if (!routeData.originLat || !routeData.destLat) return;

        // Set semua sekaligus lalu panggil nextStep yang sudah include calculateFare
        @this.set('origin_latitude',  routeData.originLat.toFixed(8));
        @this.set('origin_longitude', routeData.originLng.toFixed(8));
        @this.set('dest_latitude',    routeData.destLat.toFixed(8));
        @this.set('dest_longitude',   routeData.destLng.toFixed(8));
        @this.set('distance_km',      routeData.distanceKm);

        setTimeout(() => @this.call('nextStep'), 300);
    });
});
</script>
@endpush