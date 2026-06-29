<div class="px-3 py-3">

    @if($error)
        <div class="alert alert-danger py-2 small rounded-3">{{ $error }}</div>
    @endif

    @if(!$showForm)
        {{-- List alamat --}}
        @forelse($addresses as $address)
            <div class="app-card mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size:11px;">
                                {{ $address->label }}
                            </span>
                        </div>
                        <div class="text-muted small">{{ $address->address_text }}</div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0 ms-2">
                        <button wire:click="openEditForm({{ $address->id }})"
                            class="btn btn-sm btn-outline-secondary rounded-3 py-0 px-2" style="font-size:11px;">
                            Edit
                        </button>
                        <button wire:click="delete({{ $address->id }})"
                            wire:confirm="Hapus alamat '{{ $address->label }}'?"
                            class="btn btn-sm btn-outline-danger rounded-3 py-0 px-2" style="font-size:11px;">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <div style="font-size:48px;">📍</div>
                <p class="fw-semibold mt-3 mb-1">Belum Ada Alamat Tersimpan</p>
                <p class="text-muted small">Simpan alamat favoritmu agar lebih cepat saat order.</p>
            </div>
        @endforelse

        @if($addresses->count() < 5)
            <button wire:click="openAddForm"
                class="btn btn-danger w-100 rounded-3 py-2 fw-semibold mt-2">
                + Tambah Alamat
            </button>
        @endif

    @else
        {{-- Form tambah/edit --}}
        <div class="app-card mb-3 p-0 overflow-hidden" wire:ignore wire:key="map-container-address">
            <div id="map-address" style="height:260px;z-index:0;"></div>
        </div>

        <div class="app-card mb-3">
            <label class="form-label small fw-medium">Label Alamat</label>
            <div class="d-flex gap-2 mb-2">
                @foreach(['Rumah', 'Kantor', 'Lainnya'] as $preset)
                    <button type="button" wire:click="$set('label', '{{ $preset }}')"
                        class="btn btn-sm rounded-3 {{ $label === $preset ? 'btn-danger' : 'btn-outline-secondary' }}">
                        {{ $preset }}
                    </button>
                @endforeach
            </div>
            <input wire:model="label" type="text" placeholder="Cth: Rumah, Kantor, Kos"
                class="form-control rounded-3 @error('label') is-invalid @enderror">
            @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="app-card mb-3">
            <label class="form-label small fw-medium">Detail Alamat</label>
            <textarea wire:model="address_text" rows="2"
                placeholder="Cth: Jl. Merdeka No. 10, dekat Indomaret"
                class="form-control rounded-3 @error('address_text') is-invalid @enderror"></textarea>
            @error('address_text') <div class="invalid-feedback">{{ $message }}</div> @enderror

            <div class="row g-2 mt-2">
                <div class="col-6">
                    <input id="input-lat-addr" type="text" readonly value="{{ $latitude }}"
                        placeholder="Latitude"
                        class="form-control form-control-sm rounded-3 bg-light @error('latitude') is-invalid @enderror">
                    @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-6">
                    <input id="input-lng-addr" type="text" readonly value="{{ $longitude }}"
                        placeholder="Longitude"
                        class="form-control form-control-sm rounded-3 bg-light @error('longitude') is-invalid @enderror">
                    @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button wire:click="closeForm" class="btn btn-outline-secondary rounded-3 flex-grow-1 py-2">
                Batal
            </button>
            <button wire:click="save" wire:loading.attr="disabled"
                class="btn btn-danger rounded-3 flex-grow-1 py-2 fw-semibold">
                <span wire:loading.remove wire:target="save">Simpan Alamat</span>
                <span wire:loading wire:target="save">Menyimpan...</span>
            </button>
        </div>
    @endif

</div>

@push('scripts')
<script>
(function () {
    function initAddressMap() {
        const mapEl = document.getElementById('map-address');
        if (!mapEl || mapEl._leaflet_id) return; 

        const latInput = document.getElementById('input-lat-addr')?.value;
        const lngInput = document.getElementById('input-lng-addr')?.value;

        let map;
        let marker = null;

        // KONDISI 1: Jika sudah ada data koordinat (mode EDIT alamat)
        if (latInput && lngInput) {
            const initLat = parseFloat(latInput);
            const initLng = parseFloat(lngInput);

            map = L.map('map-address').setView([initLat, initLng], 16);
            setupBaseMap(map);
            
            marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);
            setupMarkerEvents(marker);
            setupControlsAndClicks(map);
        } 
        // KONDISI 2: Jika koordinat kosong (mode TAMBAH alamat baru), Otomatis cari lokasi user
        else {
            // Set view sementara ke koordinat default sambil menunggu GPS
            map = L.map('map-address').setView([-6.2000, 106.8166], 12); 
            setupBaseMap(map);
            setupControlsAndClicks(map);

            // Cek fitur GPS browser
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        const userLat = pos.coords.latitude;
                        const userLng = pos.coords.longitude;

                        // Geser peta & zoom otomatis ke lokasi user saat ini
                        map.setView([userLat, userLng], 17);
                        placeMarker(userLat, userLng);
                    },
                    function (error) {
                        console.warn("Gagal mendapatkan lokasi otomatis: ", error.message);
                        // Fallback jika GPS ditolak/gagal: gunakan koordinat default pusat
                        map.setView([-4.5564, 105.4057], 13);
                    },
                    { enableHighAccuracy: true, timeout: 5000 }
                );
            }
        }

        function setupBaseMap(mapInstance) {
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(mapInstance);
        }

        function setupControlsAndClicks(mapInstance) {
            // Tombol manual internal 
            const LocateControl = L.Control.extend({
                options: { position: 'topleft' },
                onAdd: function () {
                    const btn = L.DomUtil.create('button', '');
                    btn.innerHTML = '📍';
                    btn.style.cssText = 'width:34px;height:34px;font-size:16px;cursor:pointer;border-radius:6px;border:1px solid #ccc;background:#fff;';
                    L.DomEvent.on(btn, 'click', function (e) {
                        L.DomEvent.stopPropagation(e);
                        navigator.geolocation.getCurrentPosition(function (pos) {
                            mapInstance.setView([pos.coords.latitude, pos.coords.longitude], 17);
                            placeMarker(pos.coords.latitude, pos.coords.longitude);
                        });
                    });
                    return btn;
                }
            });
            mapInstance.addControl(new LocateControl());

            mapInstance.on('click', function (e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
            });
        }

        function placeMarker(lat, lng) {
            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                setupMarkerEvents(marker);
            }
            updateCoords(lat, lng);
        }

        function setupMarkerEvents(markerInstance) {
            markerInstance.on('dragend', function (e) {
                const p = e.target.getLatLng();
                updateCoords(p.lat, p.lng);
            });
        }

        function updateCoords(lat, lng) {
            const latR = lat.toFixed(8);
            const lngR = lng.toFixed(8);
            
            const latEl = document.getElementById('input-lat-addr');
            const lngEl = document.getElementById('input-lng-addr');
            
            if (latEl) latEl.value = latR;
            if (lngEl) lngEl.value = lngR;
            
            @this.set('latitude', latR, false);
            @this.set('longitude', lngR, false);
        }

        // Memastikan render map tidak terpotong saat container muncul
        setTimeout(() => map.invalidateSize(), 300);
    }

    // Mendaftarkan ke lifecycle Livewire v3
    document.addEventListener('livewire:initialized', () => {
        initAddressMap();
        
        Livewire.hook('request', ({ respond }) => {
            respond(() => {
                setTimeout(() => {
                    initAddressMap();
                }, 100);
            });
        });
    });
})();
</script>
@endpush