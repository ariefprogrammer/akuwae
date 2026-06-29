<div class="px-3 py-3">

    {{-- Step indicator --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        @foreach([1 => 'Lokasi', 2 => 'Detail Barang', 3 => 'Konfirmasi'] as $s => $label)
            <div class="d-flex align-items-center gap-1 {{ !$loop->last ? 'flex-grow-1' : '' }}">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                    style="width:26px;height:26px;font-size:11px;
                    background:{{ $step >= $s ? '#dc3545' : '#e9ecef' }};
                    color:{{ $step >= $s ? '#fff' : '#adb5bd' }};">
                    {{ $s }}
                </div>
                <span style="font-size:10px;color:{{ $step >= $s ? '#dc3545' : '#adb5bd' }};">
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
    
    <div class="app-card mb-3 p-0 overflow-hidden {{ $step !== 1 ? 'd-none' : '' }}">
        <div id="map-custom" wire:ignore style="height:260px;z-index:0;"></div>
    </div>
    
    {{-- ══ STEP 1: LOKASI ══ --}}
    @if($step === 1)
        <div wire:key="step-1-content">
            
            <div class="app-card mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-circle bg-success flex-shrink-0" style="width:12px;height:12px;"></div>
                    <span class="small fw-medium">Lokasi Pengambilan Barang</span>
                </div>
                <input wire:model.live.debounce.500ms="origin_address" type="text"
                    placeholder="Cth: Apotek Sehat Jl. Merdeka"
                    class="form-control form-control-sm rounded-3 @error('origin_address') is-invalid @enderror">
                @error('origin_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if($origin_latitude)
                    <div class="text-muted mt-1" style="font-size:11px;">📍 {{ $origin_latitude }}, {{ $origin_longitude }}</div>
                @endif
            </div>
    
            <div class="app-card mb-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-circle bg-danger flex-shrink-0" style="width:12px;height:12px;"></div>
                    <span class="small fw-medium">Lokasi Tujuan (Kamu)</span>
                </div>
                <input wire:model.live.debounce.500ms="destination_address" type="text"
                    placeholder="Alamat tujuan pengantaran"
                    class="form-control form-control-sm rounded-3 @error('destination_address') is-invalid @enderror">
                @error('destination_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                @if($dest_latitude)
                    <div class="text-muted mt-1" style="font-size:11px;">📍 {{ $dest_latitude }}, {{ $dest_longitude }}</div>
                @endif
            </div>
    
            @if($distance_km > 0)
                <div class="app-card mb-3 text-center">
                    <div class="text-muted small">Estimasi Jarak</div>
                    <div class="fw-bold text-danger fs-5">{{ $distance_km }} km</div>
                </div>
            @endif
    
            <button id="btn-next-custom-1" class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
                Lanjut →
            </button>
        </div>


    {{-- ══ STEP 2: DETAIL BARANG ══ --}}
    @elseif($step === 2)
        <div wire:key="step-2-content">

            <div class="app-card mb-3">
                <label class="form-label small fw-medium">Apa yang ingin kamu titip?</label>
                <textarea wire:model="item_description" rows="4"
                    placeholder="Jelaskan barang secara detail. Cth: Tolong belikan obat batuk merk X di Apotek, kalau tidak ada bisa diganti merk Y. Budget maks Rp50.000."
                    class="form-control rounded-3 @error('item_description') is-invalid @enderror"></textarea>
                @error('item_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="text-muted mt-1" style="font-size:11px;">
                    Semakin detail, semakin mudah driver memahami permintaanmu.
                </div>
            </div>
    
            <div class="app-card mb-3">
                <label class="form-label small fw-medium">Estimasi Berat (Minimal 1 kg)</label>
                <input wire:model.live="estimated_weight" type="number" step="0.1" min="0.1" max="50"
                    placeholder="Contoh : 1"
                    class="form-control rounded-3 @error('estimated_weight') is-invalid @enderror">
                @error('estimated_weight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="text-muted mt-1" style="font-size:11px;">
                    Tidak yakin? Beri estimasi kasar — tarif akan disesuaikan setelah driver memeriksa barang.
                </div>
            </div>
    
            <div class="app-card mb-3">
                <div class="fw-semibold small mb-3">Pilih Kendaraan</div>
                <div class="d-flex gap-2">
                    <label class="flex-grow-1" wire:click="$set('vehicle_type', 'motor')">
                        <div class="border rounded-3 text-center py-3 {{ $vehicle_type === 'motor' ? 'border-danger bg-danger bg-opacity-10' : '' }}"
                            style="cursor:pointer;">
                            <div style="font-size:28px;">🛵</div>
                            <div class="small fw-medium mt-1">Motor</div>
                            <div class="text-muted" style="font-size:10px;">Maks ~5 kg</div>
                        </div>
                    </label>
                    <label class="flex-grow-1" wire:click="$set('vehicle_type', 'mobil')">
                        <div class="border rounded-3 text-center py-3 {{ $vehicle_type === 'mobil' ? 'border-danger bg-danger bg-opacity-10' : '' }}"
                            style="cursor:pointer;">
                            <div style="font-size:28px;">🚗</div>
                            <div class="small fw-medium mt-1">Mobil</div>
                            <div class="text-muted" style="font-size:10px;">Barang besar/berat</div>
                        </div>
                    </label>
                </div>
            </div>
    
            <div class="app-card mb-3">
                <label class="form-label small fw-medium">
                    Catatan untuk Driver <span class="text-muted">(opsional)</span>
                </label>
                <textarea wire:model="notes_for_driver" rows="2"
                    placeholder="Cth: simpan struk belanja, hubungi sebelum beli jika tidak tersedia"
                    class="form-control rounded-3"></textarea>
            </div>
    
            <div class="d-flex gap-2">
                <button wire:click="$set('step', 1)" class="btn btn-outline-secondary rounded-3 flex-grow-1">
                    ← Kembali
                </button>
                <button wire:click="nextStepFromDetail" wire:loading.attr="disabled"
                    class="btn btn-danger rounded-3 flex-grow-1 fw-semibold">
                    <span wire:loading.remove wire:target="nextStepFromDetail">Lanjut →</span>
                    <span wire:loading wire:target="nextStepFromDetail">Memproses...</span>
                </button>
            </div>
        </div>

    {{-- ══ STEP 3: KONFIRMASI ══ --}}
    @elseif($step === 3)
        <div wire:key="step-3-content">

            <div class="app-card mb-3">
                <div class="fw-semibold small mb-2">Detail Permintaan</div>
                <div class="text-muted small">{{ $item_description }}</div>
            </div>
    
            <div class="app-card mb-3">
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
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Estimasi Berat</span>
                    <span class="small fw-medium">{{ $estimated_weight }} kg</span>
                </div>
            </div>
    
            <div class="app-card mb-3">
                <div class="fw-semibold small mb-3">Estimasi Biaya</div>
    
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">Biaya dasar</span>
                    <span class="small">Rp {{ number_format($base_fare, 0, ',', '.') }}</span>
                </div>
                @php
                    $chargeableDistance = max(0, $distance_km - 2);
                @endphp
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted small">
                        Jarak ({{ $distance_km }} km, 2 km pertama gratis)
                    </span>
                    <span class="small">Rp {{ number_format($distance_fare, 0, ',', '.') }}</span>
                </div>
                @if($chargeableDistance > 0)
                    <div class="text-muted" style="font-size:11px;margin-top:-4px;margin-bottom:8px;">
                        {{ $chargeableDistance }} km × Rp {{ number_format($per_km_fare, 0, ',', '.') }}
                    </div>
                @endif
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">{{ $estimated_weight }} kg × Rp {{ number_format($per_kg_fare, 0, ',', '.') }}</span>
                    <span class="small">Rp {{ number_format($weight_fare, 0, ',', '.') }}</span>
                </div>
    
                <hr class="my-2">
    
                <div class="d-flex justify-content-between">
                    <span class="fw-semibold">Estimasi Total</span>
                    <span class="fw-bold text-danger fs-6">Rp {{ number_format($total_fare, 0, ',', '.') }}</span>
                </div>
            </div>
    
            <div class="alert alert-warning py-2 small rounded-3 mb-3">
                ⚠️ Tarif final akan menyesuaikan <strong>berat aktual</strong> barang setelah ditimbang oleh driver. Selisih biaya dibayar tunai saat barang diterima.
            </div>
    
            <div class="app-card mb-3 d-flex align-items-center gap-3">
                <div style="font-size:24px;">💵</div>
                <div>
                    <div class="fw-medium small">Pembayaran</div>
                    <div class="text-muted" style="font-size:12px;">Tunai ke driver</div>
                </div>
            </div>
    
            <div class="d-flex gap-2">
                <button wire:click="$set('step', 2)" class="btn btn-outline-secondary rounded-3 flex-grow-1">
                    ← Kembali
                </button>
                <button wire:click="placeOrder" wire:loading.attr="disabled"
                    class="btn btn-danger rounded-3 flex-grow-1 fw-semibold">
                    <span wire:loading.remove wire:target="placeOrder">Pesan Sekarang</span>
                    <span wire:loading wire:target="placeOrder">Memproses...</span>
                </button>
            </div>
        </div>

    @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = -4.5564;
    const defaultLng = 105.4057;

    const map = L.map('map-custom').setView([defaultLat, defaultLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '@arief_programmer'
    }).addTo(map);

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                map.setView([pos.coords.latitude, pos.coords.longitude], 16);
            },
            function () {
                // Gagal/ditolak izin lokasi — tetap pakai posisi default
                console.log('Gagal mendapatkan lokasi, menggunakan posisi default.');
            },
            { enableHighAccuracy: true, timeout: 5000 }
        );
    }

    let originMarker = null;
    let destMarker   = null;
    let routeLine    = null;
    let pickingMode  = 'origin';

    let routeData = { originLat: null, originLng: null, destLat: null, destLng: null, distanceKm: 0 };

    // Tombol mode penandaan (Ambil / Tujuan)
    const ModeControl = L.Control.extend({
        options: { position: 'topright' },
        onAdd: function () {
            const div = L.DomUtil.create('div', '');
            div.style.cssText = 'display:flex;flex-direction:column;gap:4px;';
            const btnOrigin = createBtn('🟢 Ambil', 'origin', '#198754');
            const btnDest   = createBtn('🔴 Tujuan', 'dest', '#dee2e6');
            div.appendChild(btnOrigin);
            div.appendChild(btnDest);

            function createBtn(label, mode, borderColor) {
                const btn = L.DomUtil.create('button', '');
                btn.innerHTML = label;
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
    

    // Tombol posisi saya
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

    // Event saat peta diklik
    map.on('click', function (e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;

        if (pickingMode === 'origin') {
            if (originMarker) {
                originMarker.setLatLng([lat, lng]);
            } else {
                // Buat marker yang bisa di-drag (draggable: true)
                originMarker = L.marker([lat, lng], { draggable: true, icon: greenIcon() }).addTo(map);
                // 🌟 Hitung ulang rute saat pin asal selesai digeser
                originMarker.on('dragend', function () { drawRoute(); });
            }
            routeData.originLat = lat;
            routeData.originLng = lng;
        } else {
            if (destMarker) {
                destMarker.setLatLng([lat, lng]);
            } else {
                // Buat marker yang bisa di-drag (draggable: true)
                destMarker = L.marker([lat, lng], { draggable: true, icon: redIcon() }).addTo(map);
                // 🌟 Hitung ulang rute saat pin tujuan selesai digeser
                destMarker.on('dragend', function () { drawRoute(); });
            }
            routeData.destLat = lat;
            routeData.destLng = lng;
        }

        drawRoute();
    });

    
    let routeCalculationTimeout = null;
    function drawRoute() {
        if (!routeData.originLat && !routeData.destLat) return;

        const o = originMarker ? originMarker.getLatLng() : null;
        const d = destMarker ? destMarker.getLatLng() : null;

        if (o) {
            routeData.originLat = o.lat;
            routeData.originLng = o.lng;
        }
        if (d) {
            routeData.destLat = d.lat;
            routeData.destLng = d.lng;
        }

        if (o && d) {
            // Debounce supaya tidak spam API saat user masih geser-geser pin
            clearTimeout(routeCalculationTimeout);
            routeCalculationTimeout = setTimeout(() => {
                fetchRealRoute(o, d);
            }, 500);
        }

        const originAddress = document.querySelector('input[wire\\:model\\.live\\.debounce\\.500ms="origin_address"]').value;
        const destAddress   = document.querySelector('input[wire\\:model\\.live\\.debounce\\.500ms="destination_address"]').value;

        @this.set('origin_address', originAddress, false);
        @this.set('destination_address', destAddress, false);
    }

    function fetchRealRoute(o, d) {
        fetch('{{ route('api.calculate-route') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({
                origin_lat: o.lat,
                origin_lng: o.lng,
                dest_lat: d.lat,
                dest_lng: d.lng,
                profile: 'driving-car',
            }),
        })
        .then(res => res.json())
        .then(data => {
            routeData.distanceKm = data.distance_km;

            // Gambar rute asli kalau geometry tersedia
            if (routeLine) map.removeLayer(routeLine);

            if (data.geometry && data.geometry.length > 0) {
                const latlngs = data.geometry.map(coord => [coord[1], coord[0]]); // ORS: [lng,lat] → Leaflet: [lat,lng]
                routeLine = L.polyline(latlngs, { color: '#dc3545', weight: 4 }).addTo(map);
            } else {
                // Fallback garis lurus kalau geometry tidak ada
                routeLine = L.polyline([o, d], { color: '#dc3545', weight: 3, dashArray: '6,6' }).addTo(map);
            }

            map.fitBounds(routeLine.getBounds(), { padding: [40, 40] });

            // Kirim hasil jarak final ke Livewire
            @this.set('origin_latitude',  routeData.originLat.toFixed(8));
            @this.set('origin_longitude', routeData.originLng.toFixed(8));
            @this.set('dest_latitude',    routeData.destLat.toFixed(8));
            @this.set('dest_longitude',   routeData.destLng.toFixed(8));
            @this.set('distance_km',      routeData.distanceKm);
        })
        .catch(err => {
            console.error('Gagal hitung rute:', err);
        });
    }

    function haversine(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat/2) ** 2 +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon/2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

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

    // Tombol Lanjut ke Step 2
    document.getElementById('btn-next-custom-1').addEventListener('click', function () {
        if (!routeData.originLat || !routeData.destLat) {
            alert('Pin kedua lokasi di peta terlebih dahulu.');
            return;
        }
        if (!routeData.distanceKm || routeData.distanceKm <= 0) {
            alert('Sedang menghitung rute, mohon tunggu sebentar...');
            return;
        }
        @this.call('nextStepFromLocation');
    });

    // fitur search maps
    map.addControl(new ModeControl());

    // ── Kontrol pencarian lokasi ──────────────────────────────
    const geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        collapsed: true,
        placeholder: 'Cari nama lokasi...',
        errorMessage: 'Lokasi tidak ditemukan',
        suggestMinLength: 3,      
        suggestTimeout: 250,      
        queryMinLength: 1,
        geocoder: L.Control.Geocoder.nominatim({
            geocodingQueryParams: {
                countrycodes: 'id',
            }
        })
    })
    .on('markgeocode', function (e) {
        const center = e.geocode.center;
        map.setView(center, 16);

        // Tempatkan pin sesuai mode yang sedang aktif (Ambil/Tujuan)
        if (pickingMode === 'origin') {
            if (originMarker) {
                originMarker.setLatLng(center);
            } else {
                originMarker = L.marker(center, { draggable: true, icon: greenIcon() }).addTo(map);
                originMarker.on('dragend', function () { drawRoute(); });
            }
            routeData.originLat = center.lat;
            routeData.originLng = center.lng;

            // Auto isi nama alamat dari hasil pencarian
            @this.set('origin_address', e.geocode.name, false);
        } else {
            if (destMarker) {
                destMarker.setLatLng(center);
            } else {
                destMarker = L.marker(center, { draggable: true, icon: redIcon() }).addTo(map);
                destMarker.on('dragend', function () { drawRoute(); });
            }
            routeData.destLat = center.lat;
            routeData.destLng = center.lng;

            @this.set('destination_address', e.geocode.name, false);
        }

        drawRoute();

        geocoder._collapse();
    })
    .addTo(map);
});
</script>
@endpush