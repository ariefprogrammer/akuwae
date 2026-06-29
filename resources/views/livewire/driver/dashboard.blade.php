<div>
    @if($driver->verification_status === 'pending')
        <div class="px-3 py-3">
            <div class="app-card text-center py-4">
                <div style="font-size:48px;">⏳</div>
                <h6 class="fw-bold mt-3">Menunggu Verifikasi</h6>
                <p class="text-muted small mb-0">Dokumenmu sedang direview tim ToLong.<br>Biasanya selesai dalam 1x24 jam.</p>
            </div>
        </div>

    @elseif($driver->verification_status === 'rejected')
        <div class="px-3 py-3">
            <div class="app-card text-center py-4">
                <div style="font-size:48px;">❌</div>
                <h6 class="fw-bold mt-3">Verifikasi Ditolak</h6>
                <p class="text-muted small mb-0">Hubungi admin untuk informasi lebih lanjut.</p>
            </div>
        </div>

    @else
        <div class="px-3 pt-3 pb-2"
            style="background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);">

            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="text-white-50 small">Halo,</div>
                    <div class="text-white fw-bold fs-6">{{ $driver->name }} 👋</div>
                </div>
                <a href="#" class="text-white text-decoration-none">
                    <div style="width:36px;height:36px;background:rgba(255,255,255,0.2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;">
                        <i class="fas fa-bell"></i>
                    </div>
                </a>
            </div>

            {{-- Saldo TolongPay --}}
            <div class="rounded-3 p-3 mb-3"
                style="background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);">
                <div class="text-white-50 small mb-1">Saldo TolongPay</div>
                <div class="d-flex align-items-center justify-content-between">
                    <div class="text-white fw-bold fs-5">Rp {{ number_format($balance, 0, ',', '.') }}</div>
                    <a href="{{ route('driver.balance') }}"
                        class="btn btn-sm btn-light rounded-3 fw-semibold text-danger px-3"
                        style="font-size:12px;">
                        + Top Up
                    </a>
                </div>
            </div>

        </div>

        <div class="px-3 py-3">

            {{-- Toggle Online --}}
            <div class="app-card d-flex align-items-center justify-content-between mb-3">
                <div>
                    <div class="fw-semibold">Status Kamu</div>
                    <div style="font-size:13px;" class="{{ $driver->is_online ? 'text-success' : 'text-muted' }}">
                        {{ $driver->is_online ? '🟢 Sedang Online' : '⚫ Sedang Offline' }}
                    </div>
                </div>
                <div class="form-check form-switch mb-0">
                    <input wire:click="toggleOnline" class="form-check-input" type="checkbox"
                        {{ $driver->is_online ? 'checked' : '' }}
                        style="width:48px;height:26px;cursor:pointer;">
                </div>
            </div>

            @if($success)
                <div class="alert alert-success py-2 small rounded-3">✓ {{ $success }}</div>
            @endif

            {{-- ════════ CARD PENDAPATAN ════════ --}}
            <div class="row g-2 mb-3">
                <div class="col-4">
                    <div class="app-card text-center py-3 px-2 h-100">
                        <div class="text-muted mb-1" style="font-size:10px;">Hari Ini</div>
                        <div class="fw-bold text-danger" style="font-size:13px;">
                            Rp {{ $earningsToday }}
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="app-card text-center py-3 px-2 h-100">
                        <div class="text-muted mb-1" style="font-size:10px;">Bulan Ini</div>
                        <div class="fw-bold text-danger" style="font-size:13px;">
                            Rp {{ $earningsThisMonth }}
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="app-card text-center py-3 px-2 h-100">
                        <div class="text-muted mb-1" style="font-size:10px;">Selamanya</div>
                        <div class="fw-bold text-danger" style="font-size:13px;">
                            Rp {{ $earningsAllTime }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order aktif --}}
            @if($activeOrder)
                <div class="app-card mb-3">
                    <div class="fw-semibold small mb-2">Order Aktif</div>

                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span class="text-muted small">Harga</span>
                        <span class="small fw-bold">Rp {{ number_format($activeOrder->total_fare, 0, ',', '.') }}</span>
                    </div>

                    @if($activeOrder->service_type === 'custom' && $activeOrder->customDetail)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Barang</span>
                            <span class="small fw-medium text-end" style="max-width:60%;">
                                {{ Str::limit($activeOrder->customDetail->item_description, 60) }}
                            </span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Dari</span>
                        <span class="small fw-medium text-end" style="max-width:60%;">
                            {{ $activeOrder->location->origin_address }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Ke</span>
                        <span class="small fw-medium text-end" style="max-width:60%;">
                            {{ $activeOrder->location->destination_address }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted small">Status</span>
                        <span class="badge rounded-pill
                            {{ match($activeOrder->status) {
                                'processing'    => 'bg-warning-subtle text-warning',
                                'ready'         => 'bg-success-subtle text-success',
                                'pickup'        => 'bg-info-subtle text-info',
                                'item_mismatch' => 'bg-danger-subtle text-danger',
                                'arrived'       => 'bg-info-subtle text-info',
                                'delivering'    => 'bg-primary-subtle text-primary',
                                default         => 'bg-secondary-subtle text-secondary'
                            } }}"
                            style="font-size:10px;">
                            {{ match($activeOrder->status) {
                                'processing'    => $activeOrder->service_type === 'custom' ? '🛵 Menuju Lokasi Ambil' : '🛵 Menuju Toko',
                                'ready'         => '✅ Makanan Siap',
                                'pickup'        => '📦 Mengambil Pesanan',
                                'item_mismatch' => '⚠️ Menunggu Konfirmasi Customer',
                                'arrived'       => '📍 Tiba di Tujuan',
                                'delivering'    => $activeOrder->service_type === 'custom' ? '🚀 Menuju Tujuan' : '🚀 Mengantar',
                                default         => $activeOrder->status
                            } }}
                        </span>
                    </div>

                    {{-- Tombol buka Google Maps --}}
                    @if(in_array($activeOrder->status, ['processing', 'pickup']))
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $activeOrder->location->origin_latitude }},{{ $activeOrder->location->origin_longitude }}"
                            target="_blank"
                            class="btn btn-outline-success w-100 rounded-3 py-2 fw-semibold mb-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-location-arrow"></i> Buka Maps Penjemputan
                        </a>
                    @elseif(in_array($activeOrder->status, ['delivering', 'arrived']))
                        <a href="https://www.google.com/maps/dir/?api=1&destination={{ $activeOrder->location->destination_latitude }},{{ $activeOrder->location->destination_longitude }}"
                            target="_blank"
                            class="btn btn-outline-danger w-100 rounded-3 py-2 fw-semibold mb-2 d-flex align-items-center justify-content-center gap-2">
                            <i class="fas fa-location-arrow"></i> Buka Maps Pengantaran
                        </a>
                    @endif

                    {{-- ════════ TOLONG CUSTOM ════════ --}}
                    @if($activeOrder->service_type === 'custom')

                        @if($activeOrder->status === 'processing')
                            <button wire:click="markPickedUp"
                                class="btn btn-success w-100 rounded-3 py-2 fw-semibold">
                                <i class="fas fa-box-open me-2"></i> Barang Diambil
                            </button>

                        @elseif($activeOrder->status === 'item_mismatch')
                            <div class="text-center text-warning small fw-medium py-2">
                                <i class="fas fa-hourglass-half fa-spin me-1"></i> Menunggu konfirmasi customer terkait perubahan barang...
                            </div>

                        @elseif($activeOrder->status === 'delivering')
                            <button wire:click="markArrived"
                                wire:loading.attr="disabled"
                                class="btn btn-success w-100 rounded-3 py-2 fw-semibold">
                                <span wire:loading.remove wire:target="markArrived"><i class="fas fa-map-marker-alt me-1"></i> Sampai di Tujuan</span>
                                <span wire:loading wire:target="markArrived">Memproses...</span>
                            </button>

                        @elseif($activeOrder->status === 'arrived')
                            <button wire:click="openCompleteForm"
                                class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
                                <i class="fas fa-check me-2"></i> Barang Diterima
                            </button>
                        @endif

                    {{-- ════════ TOLONG MAKAN / ANTAR ════════ --}}
                    @else
                        @if($activeOrder->status === 'ready')
                            <button wire:click="pickupOrder"
                                wire:loading.attr="disabled"
                                class="btn btn-success w-100 rounded-3 py-2 fw-semibold">
                                <span wire:loading.remove wire:target="pickupOrder"><i class="fas fa-box-open me-2"></i> Ambil Pesanan</span>
                                <span wire:loading wire:target="pickupOrder">Memproses...</span>
                            </button>
                        @elseif($activeOrder->status === 'processing')
                            <div class="text-center text-muted small py-2">
                                Menuju toko untuk mengambil pesanan...
                            </div>
                        @elseif($activeOrder->status === 'pickup')
                            <div class="text-center text-muted small py-2">
                                Sedang mengambil pesanan di toko...
                            </div>
                        @elseif($activeOrder->status === 'delivering')
                            <button wire:click="completeOrder"
                                wire:loading.attr="disabled"
                                wire:confirm="Konfirmasi pesanan sudah diserahkan ke customer?"
                                class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
                                <span wire:loading.remove wire:target="completeOrder"><i class="fas fa-check me-2"></i> Pesanan Selesai</span>
                                <span wire:loading wire:target="completeOrder">Memproses...</span>
                            </button>
                        @endif
                    @endif

                </div>

            @else
                {{-- Info kendaraan + grafik --}}
                <div class="app-card mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="font-size:36px;">{{ $driver->vehicle_type === 'motor' ? '🛵' : '🚗' }}</div>
                        <div>
                            <div class="fw-semibold">{{ $driver->name }}</div>
                            <div class="text-muted small">{{ strtoupper($driver->vehicle_plate) }}</div>
                            <span class="badge bg-success-subtle text-success rounded-pill" style="font-size:10px;">✓ Terverifikasi</span>
                        </div>
                    </div>
                </div>

                {{-- Grafik pesanan 30 hari --}}
                <div class="app-card mb-3">
                    <div class="fw-semibold small mb-3">📈 Pesanan 30 Hari Terakhir</div>
                    <div style="height:140px;position:relative;">  
                        <canvas id="orderChart"></canvas>          
                    </div>
                </div>

                <div class="app-card text-center py-4">
                    <div style="font-size:36px;">📭</div>
                    <p class="text-muted small mt-2 mb-0">
                        {{ $driver->is_online ? 'Menunggu order masuk...' : 'Aktifkan status online untuk menerima order.' }}
                    </p>
                </div>
            @endif

            @if($activeOrder)
                @livewire('order-chat', ['order' => $activeOrder], key('chat-'.$activeOrder->id))
            @endif

        </div>

        {{-- Popup konfirmasi: Apakah barang sesuai? --}}
        @if($showPickupConfirm)
            <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-end"
                style="background:rgba(0,0,0,0.5);z-index:999;">
                <div class="bg-white w-100 rounded-top-4 p-4" style="max-width:480px;margin:0 auto;">
                    <div class="text-center mb-3">
                        <div style="font-size:40px;"><i class="fas fa-box-open me-2"></i></div>
                        <h6 class="fw-bold mt-2">Apakah barang sesuai dengan pesanan?</h6>
                        @if($activeOrder?->customDetail)
                            <p class="text-muted small mb-0">
                                "{{ $activeOrder->customDetail->item_description }}"
                            </p>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <button wire:click="confirmItemMatch(false)"
                            class="btn btn-outline-danger rounded-3 flex-grow-1 py-2">
                            Tidak
                        </button>
                        <button wire:click="confirmItemMatch(true)"
                            wire:loading.attr="disabled"
                            class="btn btn-success rounded-3 flex-grow-1 py-2 fw-semibold">
                            Ya, Sesuai
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Popup form alasan mismatch --}}
        @if($showMismatchForm)
            <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-end"
                style="background:rgba(0,0,0,0.5);z-index:999;">
                <div class="bg-white w-100 rounded-top-4 p-4" style="max-width:480px;margin:0 auto;">
                    <h6 class="fw-bold mb-2">Jelaskan perbedaannya</h6>
                    <p class="text-muted small mb-2">
                        Customer akan melihat penjelasan ini dan memutuskan untuk melanjutkan atau membatalkan pesanan.
                    </p>
                    <textarea wire:model="mismatchReason" rows="3"
                        placeholder="Cth: Barang yang diminta habis, saya gantikan dengan merk lain seharga Rp..."
                        class="form-control rounded-3 mb-2 @error('mismatchReason') is-invalid @enderror"></textarea>
                    @error('mismatchReason')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <div class="d-flex gap-2">
                        <button wire:click="$set('showMismatchForm', false)"
                            class="btn btn-outline-secondary rounded-3 flex-grow-1 py-2">
                            Batal
                        </button>
                        <button wire:click="submitMismatch"
                            wire:loading.attr="disabled"
                            class="btn btn-danger rounded-3 flex-grow-1 py-2 fw-semibold">
                            <span wire:loading.remove wire:target="submitMismatch">Konfirmasi Perubahan</span>
                            <span wire:loading wire:target="submitMismatch">Mengirim...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Popup form barang diterima + foto --}}
        @if($showCompleteForm)
            <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-end"
                style="background:rgba(0,0,0,0.5);z-index:999;">
                <div class="bg-white w-100 rounded-top-4 p-4" style="max-width:480px;margin:0 auto;">
                    <h6 class="fw-bold mb-2">Konfirmasi Barang Diterima</h6>
                    <p class="text-muted small mb-2">
                        Lampirkan foto bukti serah terima (opsional).
                    </p>
                    @if($deliveryProofPhoto)
                        <img src="{{ $deliveryProofPhoto->temporaryUrl() }}"
                            class="rounded-3 w-100 object-fit-cover mb-2" style="height:140px;">
                    @endif
                    <input wire:model="deliveryProofPhoto" type="file" accept="image/*"
                        class="form-control rounded-3 mb-2 @error('deliveryProofPhoto') is-invalid @enderror">
                    @error('deliveryProofPhoto')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <div class="d-flex gap-2">
                        <button wire:click="$set('showCompleteForm', false)"
                            class="btn btn-outline-secondary rounded-3 flex-grow-1 py-2">
                            Batal
                        </button>
                        <button wire:click="completeCustomOrder"
                            wire:loading.attr="disabled"
                            class="btn btn-danger rounded-3 flex-grow-1 py-2 fw-semibold">
                            <span wire:loading.remove wire:target="completeCustomOrder">Selesai</span>
                            <span wire:loading wire:target="completeCustomOrder">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Incoming Order Component --}}
        @livewire('driver.incoming-order')

    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Chart pesanan 30 hari ──────────────────────────────
    const canvas = document.getElementById('orderChart');
    if (canvas) {
        const labels = @json($chartLabels);
        const data   = @json($chartData);

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pesanan',
                    data: data,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,0.08)',
                    borderWidth: 2,
                    pointRadius: 2,
                    pointHoverRadius: 4,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y + ' pesanan'
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: { size: 9 },
                            maxTicksLimit: 8,
                            maxRotation: 0,
                        },
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 9 },
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    }
                }
            }
        });
    }
});
</script>
@endpush