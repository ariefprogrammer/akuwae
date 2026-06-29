<div>

    {{-- ══ STEP 1: BROWSE ══ --}}
    @if($step === 1)
        <div class="px-3 pt-3">

            {{-- Search --}}
            <div class="mb-3">
                <input wire:model.live.debounce.300ms="search" type="text"
                    placeholder="Cari toko atau menu..."
                    class="form-control rounded-3">
            </div>

            {{-- Tab --}}
            <div class="d-flex gap-2 mb-3">
                <button wire:click="$set('activeTab', 'toko')"
                    class="btn btn-sm rounded-3 flex-grow-1 {{ $activeTab === 'toko' ? 'btn-danger' : 'btn-outline-secondary' }}">
                    <i class="fas fa-store me-1"></i> Pilih Toko
                </button>
                <button wire:click="$set('activeTab', 'menu')"
                    class="btn btn-sm rounded-3 flex-grow-1 {{ $activeTab === 'menu' ? 'btn-danger' : 'btn-outline-secondary' }}">
                    <i class="fas fa-utensils me-1"></i> Pilih Menu
                </button>
            </div>

            @if($error)
                <div class="alert alert-danger py-2 small rounded-3">{{ $error }}</div>
            @endif

            {{-- ── Tab Toko ── --}}
            @if($activeTab === 'toko')
                @forelse($tenants as $tenant)
                    <div class="app-card mb-3">

                        {{-- Header toko — klik untuk expand/collapse menu --}}
                        <div class="d-flex align-items-center gap-3"
                            wire:click="toggleTenant('{{ $tenant->id }}')"
                            style="cursor:pointer;">

                            @if($tenant->photo)
                                <img src="{{ Storage::url($tenant->photo) }}"
                                    class="rounded-3 object-fit-cover flex-shrink-0"
                                    style="width:56px;height:56px;">
                            @else
                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                    style="width:56px;height:56px;font-size:28px;">
                                    <i class="fas fa-store text-secondary"></i>
                                </div>
                            @endif

                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $tenant->store_name }}</div>
                                <div class="text-muted small">{{ $tenant->category }}</div>
                                @php
                                    $tenantItemCount = collect($cart[$tenant->id]['items'] ?? [])->sum('qty');
                                @endphp
                                @if($tenantItemCount > 0)
                                    <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size:10px;">
                                        {{ $tenantItemCount }} item dipilih
                                    </span>
                                @endif
                            </div>

                            <div class="text-muted flex-shrink-0" style="font-size:18px;">
                                <i class="fas fa-chevron-{{ $expandedTenantId === $tenant->id ? 'up' : 'down' }}"></i>
                            </div>
                        </div>

                        {{-- Daftar menu — hanya tampil jika toko ini di-expand --}}
                        @if($expandedTenantId === $tenant->id)
                            <div class="mt-3">
                                @foreach($tenant->menuCategories()->with(['menus' => fn($q) => $q->where('is_available', true)->with('photos')])->get() as $category)
                                    @if($category->menus->isNotEmpty())
                                        <div class="text-muted small fw-medium mb-2 mt-2">
                                            <i class="fas fa-tag me-1"></i>{{ $category->category_name }}
                                        </div>
                                        @foreach($category->menus as $menu)
                                            <div class="d-flex align-items-center gap-3 py-2 border-top">

                                                @if($menu->photos->isNotEmpty())
                                                    <img src="{{ Storage::url($menu->photos->first()->photo_url) }}"
                                                        class="rounded-3 object-fit-cover flex-shrink-0"
                                                        style="width:52px;height:52px;">
                                                @else
                                                   <div class="rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                                        style="width:52px;height:52px;font-size:22px;">
                                                        <i class="fas fa-hamburger text-secondary"></i>
                                                    </div>
                                                @endif

                                                <div class="flex-grow-1">
                                                    <div class="small fw-medium">{{ $menu->item_name }}</div>
                                                    @if($menu->description)
                                                        <div class="text-muted" style="font-size:11px;">
                                                            {{ Str::limit($menu->description, 50) }}
                                                        </div>
                                                    @endif
                                                    <div class="text-danger small fw-semibold">
                                                        Rp {{ number_format($menu->price, 0, ',', '.') }}
                                                    </div>
                                                </div>

                                                @php
                                                    $qty = $cart[$tenant->id]['items'][$menu->id]['qty'] ?? 0;
                                                @endphp

                                                @if($qty > 0)
                                                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                                        <button wire:click="decrementItem('{{ $tenant->id }}', {{ $menu->id }})"
                                                            class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                                                            style="width:28px;height:28px;">−</button>
                                                        <span class="fw-semibold small">{{ $qty }}</span>
                                                        <button wire:click="incrementItem('{{ $tenant->id }}', {{ $menu->id }})"
                                                            class="btn btn-sm btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                                                            style="width:28px;height:28px;">+</button>
                                                    </div>
                                                @else
                                                    <button wire:click="addToCart({{ $menu->id }})"
                                                        class="btn btn-sm btn-outline-danger rounded-3 flex-shrink-0">
                                                        + Tambah
                                                    </button>
                                                @endif

                                            </div>
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="text-center py-5">
                        <div style="font-size:48px;">🏪</div>
                        <p class="text-muted small mt-2">Belum ada toko yang buka.</p>
                    </div>
                @endforelse
            @endif

            {{-- ── Tab Menu ── --}}
            @if($activeTab === 'menu')
                @forelse($menus as $menu)
                    @php
                        $tenantId = $menu->menuCategory->tenant->id;
                        $qty = $cart[$tenantId]['items'][$menu->id]['qty'] ?? 0;
                    @endphp
                    <div class="app-card mb-2 d-flex align-items-center gap-3">
                        @if($menu->photos->isNotEmpty())
                            <img src="{{ Storage::url($menu->photos->first()->photo_url) }}"
                                class="rounded-3 object-fit-cover flex-shrink-0"
                                style="width:56px;height:56px;">
                        @else
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:56px;height:56px;font-size:24px;">🍴</div>
                        @endif

                        <div class="flex-grow-1 min-width-0">
                            <div class="small fw-medium text-truncate">{{ $menu->item_name }}</div>
                            <div class="text-muted" style="font-size:11px;">
                                {{ $menu->menuCategory->tenant->store_name }}
                            </div>
                            <div class="text-danger small fw-semibold">
                                Rp {{ number_format($menu->price, 0, ',', '.') }}
                            </div>
                        </div>

                        @if($qty > 0)
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <button wire:click="decrementItem('{{ $tenantId }}', {{ $menu->id }})"
                                    class="btn btn-sm btn-outline-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    style="width:28px;height:28px;">−</button>
                                <span class="fw-semibold small">{{ $qty }}</span>
                                <button wire:click="incrementItem('{{ $tenantId }}', {{ $menu->id }})"
                                    class="btn btn-sm btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    style="width:28px;height:28px;">+</button>
                            </div>
                        @else
                            <button wire:click="addToCart({{ $menu->id }})"
                                class="btn btn-sm btn-outline-danger rounded-3 flex-shrink-0">
                                + Tambah
                            </button>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5">
                        <div style="font-size:48px;">🍽️</div>
                        <p class="text-muted small mt-2">Tidak ada menu ditemukan.</p>
                    </div>
                @endforelse
            @endif

        </div>

        {{-- Floating cart button --}}
        @if($totalItems > 0)
            <div class="position-fixed w-100 px-3"
                style="bottom:70px;left:50%;transform:translateX(-50%);max-width:480px;z-index:99;">
                <button wire:click="goToStep2"
                    class="btn btn-danger w-100 rounded-3 py-2 fw-semibold shadow">
                    <span class="badge bg-white text-danger me-2">{{ $totalItems }}</span>
                    Lanjut ke Pengiriman
                    <span class="ms-2">Rp {{ number_format($food_subtotal, 0, ',', '.') }}</span>
                </button>
            </div>
        @endif

    {{-- ══ STEP 2: LOKASI PENGIRIMAN ══ --}}
    @elseif($step === 2)
        <div class="px-3 pt-3">

            <div class="app-card mb-3 p-0 overflow-hidden">
                <div id="map-makan" style="height:260px;z-index:0;"></div>
            </div>

            <div class="app-card mb-3">
                <label class="form-label small fw-medium">Alamat Pengiriman</label>
                <input wire:model="delivery_address" type="text"
                    placeholder="Nama atau alamat lengkap"
                    class="form-control rounded-3 @error('delivery_address') is-invalid @enderror">
                @error('delivery_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                @if($delivery_latitude)
                    <div class="text-muted mt-1" style="font-size:11px;">
                        📍 {{ $delivery_latitude }}, {{ $delivery_longitude }}
                    </div>
                @endif
            </div>

            <div id="distance-box-makan" class="app-card mb-3 text-center" style="display:none;">
                <div class="text-muted small">Estimasi Jarak dari Toko Terdekat</div>
                <div class="fw-bold text-danger fs-5">
                    <span id="distance-display-makan">0</span>
                </div>
            </div>

            <div class="app-card mb-3">
                <label class="form-label small fw-medium">
                    Catatan untuk Driver <span class="text-muted">(opsional)</span>
                </label>
                <textarea wire:model="notes_for_driver" rows="2"
                    placeholder="Cth: depan gang, pakai intercom"
                    class="form-control rounded-3"></textarea>
            </div>

            @if($error)
                <div class="alert alert-danger py-2 small rounded-3">{{ $error }}</div>
            @endif

            <div class="d-flex gap-2">
                <button wire:click="$set('step', 1)"
                    class="btn btn-outline-secondary rounded-3 flex-grow-1">
                    ← Kembali
                </button>
                <button id="btn-next-makan"
                    class="btn btn-danger rounded-3 flex-grow-1 fw-semibold">
                    Lanjut →
                </button>
            </div>

        </div>

    {{-- ══ STEP 3: KONFIRMASI ══ --}}
    @elseif($step === 3)
        <div class="px-3 pt-3">

            {{-- Ringkasan per toko --}}
            @foreach($cart as $tenantId => $tenantCart)
                <div class="app-card mb-3">
                    <div class="fw-semibold small mb-2">🏪 {{ $tenantCart['tenant']['store_name'] }}</div>
                    @foreach($tenantCart['items'] as $item)
                        <div class="d-flex justify-content-between align-items-start py-1 border-top">
                            <div class="flex-grow-1">
                                <div class="small">{{ $item['qty'] }}× {{ $item['item_name'] }}</div>
                                @if($item['notes'])
                                    <div class="text-muted" style="font-size:11px;">📝 {{ $item['notes'] }}</div>
                                @endif
                            </div>
                            <div class="small fw-medium flex-shrink-0 ms-2">
                                Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach

            {{-- Ringkasan biaya --}}
            <div class="app-card mb-3">
                <div class="fw-semibold small mb-3">Ringkasan Biaya</div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Subtotal Makanan</span>
                    <span class="small">Rp {{ number_format($food_subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Ongkos Kirim ({{ $distance_km }} km)</span>
                    <span class="small">Rp {{ number_format($delivery_fee, 0, ',', '.') }}</span>
                </div>

                <hr class="my-2">

                <div class="d-flex justify-content-between">
                    <span class="fw-semibold">Total</span>
                    <span class="fw-bold text-danger fs-6">
                        Rp {{ number_format($total_fare, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Alamat pengiriman --}}
            <div class="app-card mb-3 d-flex align-items-center gap-3">
                <div style="font-size:24px;">📍</div>
                <div>
                    <div class="fw-medium small">Dikirim ke</div>
                    <div class="text-muted" style="font-size:12px;">{{ $delivery_address }}</div>
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

            @if($error)
                <div class="alert alert-danger py-2 small rounded-3">{{ $error }}</div>
            @endif

            <div class="d-flex gap-2">
                <button wire:click="$set('step', 2)"
                    class="btn btn-outline-secondary rounded-3 flex-grow-1">
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
    // Peta hanya diinisialisasi saat step 2 aktif
    Livewire.hook('morph.updated', ({ el }) => {
        if (document.getElementById('map-makan') && !window._makanMapInit) {
            initMakanMap();
        }
    });

    function initMakanMap() {
        window._makanMapInit = true;

        const defaultLat = -4.5564;
        const defaultLng = 105.4057;

        const map = L.map('map-makan').setView([defaultLat, defaultLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let deliveryMarker = null;

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
                        placeDeliveryMarker(pos.coords.latitude, pos.coords.longitude);
                    });
                });
                return btn;
            }
        });
        map.addControl(new LocateControl());

        map.on('click', function (e) {
            placeDeliveryMarker(e.latlng.lat, e.latlng.lng);
        });

        function placeDeliveryMarker(lat, lng) {
            if (deliveryMarker) {
                deliveryMarker.setLatLng([lat, lng]);
            } else {
                deliveryMarker = L.marker([lat, lng], {
                    draggable: true,
                    icon: L.divIcon({
                        html: '<div style="width:14px;height:14px;background:#dc3545;border:2px solid #fff;border-radius:50%;box-shadow:0 1px 4px rgba(0,0,0,0.3);"></div>',
                        className: '', iconAnchor: [7, 7]
                    })
                }).addTo(map);

                deliveryMarker.on('dragend', function (e) {
                    const p = e.target.getLatLng();
                    updateDelivery(p.lat, p.lng);
                });
            }
            updateDelivery(lat, lng);
        }

        function updateDelivery(lat, lng) {
            // Hitung jarak dari toko pertama di keranjang (placeholder 1km)
            // Di produksi bisa pakai koordinat toko terdekat
            const distKm = 1.0;

            document.getElementById('distance-display-makan').textContent = (distKm * 1.3).toFixed(2) + ' km';
            document.getElementById('distance-box-makan').style.display = 'block';

            window._deliveryData = { lat, lng, distKm };
        }
    }

    // Inject ke Livewire saat tombol lanjut diklik
    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'btn-next-makan') {
            if (!window._deliveryData) return;
            const d = window._deliveryData;

            @this.set('delivery_latitude',  d.lat.toFixed(8));
            @this.set('delivery_longitude', d.lng.toFixed(8));
            @this.set('distance_km',        parseFloat((d.distKm * 1.3).toFixed(2)));

            setTimeout(() => @this.call('goToStep3'), 200);
        }
    });
});
</script>
@endpush