<div class="px-3 py-3">

    {{-- Status Buka/Tutup --}}
    <div class="app-card d-flex align-items-center justify-content-between mb-3">
        <div>
            <div class="fw-semibold small">Status Toko</div>
            <div style="font-size:12px;" class="{{ $is_open ? 'text-success' : 'text-danger' }}">
                {{ $is_open ? '🟢 Toko sedang buka' : '🔴 Toko sedang tutup' }}
            </div>
        </div>
        <div class="form-check form-switch mb-0">
            <input wire:click="toggleOpen" class="form-check-input" type="checkbox"
                {{ $is_open ? 'checked' : '' }}
                style="width:40px;height:22px;cursor:pointer;">
        </div>
    </div>

    {{-- Success / Error --}}
    @if($successMessage)
        <div class="alert alert-success py-2 small rounded-3">✓ {{ $successMessage }}</div>
    @endif
    @if($errorMessage)
        <div class="alert alert-danger py-2 small rounded-3">{{ $errorMessage }}</div>
    @endif

    {{-- Foto Toko --}}
    <div class="app-card mb-3">
        <label class="form-label small fw-medium">Foto Toko</label>

        {{-- Preview foto saat ini --}}
        @if($currentPhoto && !$store_photo)
            <div class="mb-2">
                <img src="{{ Storage::url($currentPhoto) }}"
                    class="rounded-3 object-fit-cover w-100"
                    style="height:160px;">
            </div>
        @endif

        {{-- Preview foto baru --}}
        @if($store_photo)
            <div class="mb-2">
                <img src="{{ $store_photo->temporaryUrl() }}"
                    class="rounded-3 object-fit-cover w-100"
                    style="height:160px;">
            </div>
        @endif

        <input wire:model="store_photo" type="file" accept="image/*"
            class="form-control rounded-3 @error('store_photo') is-invalid @enderror">
        @error('store_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Info Toko --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-3">Informasi Toko</div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Nama Toko</label>
            <input wire:model="store_name" type="text"
                class="form-control rounded-3 @error('store_name') is-invalid @enderror">
            @error('store_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Kategori</label>
            <select wire:model="category"
                class="form-select rounded-3 @error('category') is-invalid @enderror">
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

        <div class="mb-0">
            <label class="form-label small fw-medium">Alamat Lengkap</label>
            <textarea wire:model="address" rows="2"
                class="form-control rounded-3 @error('address') is-invalid @enderror"></textarea>
            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    {{-- Lokasi --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-2">Lokasi Toko</div>
        <p class="text-muted mb-2" style="font-size:12px;">Klik peta untuk update pin lokasi.</p>

        <div id="map" class="rounded-3 border" style="height:250px;z-index:0;"></div>

        <div class="row g-2 mt-2">
            <div class="col-6">
                <input id="input-lat" type="text" readonly value="{{ $latitude }}"
                    placeholder="Latitude"
                    class="form-control form-control-sm rounded-3 bg-light @error('latitude') is-invalid @enderror">
                @error('latitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-6">
                <input id="input-lng" type="text" readonly value="{{ $longitude }}"
                    placeholder="Longitude"
                    class="form-control form-control-sm rounded-3 bg-light @error('longitude') is-invalid @enderror">
                @error('longitude') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Jam Operasional --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-3">Jam Operasional</div>
        @foreach($operational_hours as $day => $hours)
            <div class="d-flex align-items-center gap-2 mb-2">
                <div style="width:65px;font-size:13px;">{{ $day }}</div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox"
                        wire:click="toggleDay('{{ $day }}')"
                        {{ $hours['open'] ? 'checked' : '' }}
                        style="width:34px;height:18px;">
                </div>
                @if($hours['open'])
                    <input type="time" wire:model="operational_hours.{{ $day }}.start"
                        class="form-control form-control-sm rounded-3" style="width:100px;">
                    <span class="text-muted small">–</span>
                    <input type="time" wire:model="operational_hours.{{ $day }}.end"
                        class="form-control form-control-sm rounded-3" style="width:100px;">
                @else
                    <span class="text-muted small">Tutup</span>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Tombol Simpan --}}
    <button wire:click="save" wire:loading.attr="disabled"
        class="btn btn-danger w-100 rounded-3 py-2 fw-semibold mb-3">
        <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
        <span wire:loading wire:target="save">Menyimpan...</span>
    </button>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const initLat = {{ $latitude ?: -4.5564 }};
    const initLng = {{ $longitude ?: 105.4057 }};

    const map = L.map('map').setView([initLat, initLng], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Marker langsung muncul di lokasi toko saat ini
    let marker = L.marker([initLat, initLng], { draggable: true }).addTo(map);

    marker.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        updateCoords(pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
    });

    // Tombol posisi saya
    const LocateControl = L.Control.extend({
        options: { position: 'topleft' },
        onAdd: function () {
            const btn = L.DomUtil.create('button', '');
            btn.innerHTML = '📍';
            btn.title = 'Posisi Saya';
            btn.style.cssText = 'width:34px;height:34px;font-size:16px;cursor:pointer;border-radius:6px;border:1px solid #ccc;background:#fff;';
            L.DomEvent.on(btn, 'click', function (e) {
                L.DomEvent.stopPropagation(e);
                btn.innerHTML = '⏳';
                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        map.setView([lat, lng], 17);
                        marker.setLatLng([lat, lng]);
                        updateCoords(lat, lng);
                        btn.innerHTML = '📍';
                    },
                    function () {
                        alert('Gagal mendapatkan lokasi.');
                        btn.innerHTML = '📍';
                    }
                );
            });
            return btn;
        }
    });
    map.addControl(new LocateControl());

    function updateCoords(lat, lng) {
        const latR = lat.toFixed(8);
        const lngR = lng.toFixed(8);
        document.getElementById('input-lat').value = latR;
        document.getElementById('input-lng').value = lngR;
        @this.set('latitude',  latR, false);
        @this.set('longitude', lngR, false);
    }
});
</script>
@endpush