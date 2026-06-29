<div class="container py-4" style="max-width: 640px;">
    <h4 class="fw-bold mb-1">Setup Toko Kamu</h4>
    <p class="text-muted small mb-4">Lengkapi data toko sebelum mulai berjualan.</p>

    @if($error)
        <div class="alert alert-danger py-2 small">{{ $error }}</div>
    @endif

    {{-- Nama Toko --}}
    <div class="mb-3">
        <label class="form-label small fw-medium">Nama Toko</label>
        <input wire:model="store_name" type="text" placeholder="Contoh: Warung Bu Sari"
            class="form-control rounded-3 @error('store_name') is-invalid @enderror">
        @error('store_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Kategori --}}
    <div class="mb-3">
        <label class="form-label small fw-medium">Kategori</label>
        <select wire:model="category" class="form-select rounded-3 @error('category') is-invalid @enderror">
            <option value="">-- Pilih Kategori --</option>
            <option value="Makanan Berat">Makanan Berat</option>
            <option value="Minuman">Minuman</option>
            <option value="Snack & Jajanan">Snack & Jajanan</option>
            <option value="Dessert">Dessert</option>
            <option value="Bakery">Bakery</option>
            <option value="Lainnya">Lainnya</option>
        </select>
        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Alamat --}}
    <div class="mb-3">
        <label class="form-label small fw-medium">Alamat Lengkap</label>
        <textarea wire:model="address" rows="2" placeholder="Jl. Contoh No. 1, Kelurahan..."
            class="form-control rounded-3 @error('address') is-invalid @enderror"></textarea>
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Koordinat --}}
    <div class="mb-3">
        <label class="form-label small fw-medium">Lokasi Toko</label>
        <p class="text-muted" style="font-size: 12px;">Klik atau drag marker untuk pin lokasi tokomu.</p>

        {{-- Peta --}}
        <div id="map" class="rounded-3 border" style="height: 300px; z-index: 0;"></div>

        {{-- Koordinat tersimpan (hidden, diisi JS) --}}
        <div class="row g-2 mt-2">
            <div class="col-6">
                <input id="input-lat" type="text" readonly
                    placeholder="Latitude"
                    class="form-control form-control-sm rounded-3 bg-light @error('latitude') is-invalid @enderror">
                @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-6">
                <input id="input-lng" type="text" readonly
                    placeholder="Longitude"
                    class="form-control form-control-sm rounded-3 bg-light @error('longitude') is-invalid @enderror">
                @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Script Leaflet --}}
    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const defaultLat = -4.5564;
            const defaultLng = 105.4057;

            const map = L.map('map').setView([defaultLat, defaultLng], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            let marker = null;

            // ── Tombol "Posisi Saya" ──────────────────────────────────
            const LocateControl = L.Control.extend({
                options: { position: 'topleft' },
                onAdd: function () {
                    const btn = L.DomUtil.create('button', 'btn btn-white border shadow-sm');
                    btn.innerHTML = '📍';
                    btn.title     = 'Posisi Saya';
                    btn.style.cssText = 'width:34px;height:34px;font-size:16px;cursor:pointer;border-radius:6px;';

                    L.DomEvent.on(btn, 'click', function (e) {
                        L.DomEvent.stopPropagation(e);
                        if (!navigator.geolocation) {
                            alert('Browser kamu tidak mendukung geolocation.');
                            return;
                        }
                        btn.innerHTML = '⏳';
                        navigator.geolocation.getCurrentPosition(
                            function (pos) {
                                const lat = pos.coords.latitude;
                                const lng = pos.coords.longitude;
                                map.setView([lat, lng], 17);
                                placeMarker(lat, lng);
                                btn.innerHTML = '◉';
                            },
                            function () {
                                alert('Gagal mendapatkan lokasi. Pastikan izin lokasi diaktifkan.');
                                btn.innerHTML = '◉';
                            }
                        );
                    });

                    return btn;
                }
            });
            map.addControl(new LocateControl());

            // ── Klik peta → pindah marker ─────────────────────────────
            map.on('click', function (e) {
                placeMarker(e.latlng.lat, e.latlng.lng);
            });

            // ── Helper: taruh/pindah marker & update Livewire ─────────
            function placeMarker(lat, lng) {
                if (marker) {
                    marker.setLatLng([lat, lng]);
                } else {
                    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
                    marker.on('dragend', function (e) {
                        const pos = e.target.getLatLng();
                        updateCoords(pos.lat, pos.lng);
                    });
                }
                updateCoords(lat, lng);
            }

            function updateCoords(lat, lng) {
                const latRounded = lat.toFixed(8);
                const lngRounded = lng.toFixed(8);

                // Update input display
                document.getElementById('input-lat').value = latRounded;
                document.getElementById('input-lng').value = lngRounded;

                // Update Livewire tanpa trigger re-render
                @this.set('latitude',  latRounded, false);
                @this.set('longitude', lngRounded, false);
            }
        });
        </script>
    @endpush

    {{-- Jam Operasional --}}
    <div class="mb-4">
        <label class="form-label small fw-medium">Jam Operasional</label>
        <div class="card border-0 bg-light rounded-3 p-3">
            @foreach($operational_hours as $day => $hours)
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div style="width: 70px; font-size: 13px;">{{ $day }}</div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox"
                            wire:click="toggleDay('{{ $day }}')"
                            {{ $hours['open'] ? 'checked' : '' }}>
                    </div>

                    @if($hours['open'])
                        <input type="time" wire:model="operational_hours.{{ $day }}.start"
                            class="form-control form-control-sm rounded-3" style="width: 110px;">
                        <span class="text-muted small">–</span>
                        <input type="time" wire:model="operational_hours.{{ $day }}.end"
                            class="form-control form-control-sm rounded-3" style="width: 110px;">
                    @else
                        <span class="text-muted small">Tutup</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <button wire:click="save" wire:loading.attr="disabled"
        class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
        <span wire:loading.remove wire:target="save">Simpan & Kirim untuk Review</span>
        <span wire:loading wire:target="save">Menyimpan...</span>
    </button>
</div>