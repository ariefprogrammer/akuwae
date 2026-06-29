<div class="container py-4" style="max-width: 640px;">

    @if($tenant->verification_status === 'pending')
        <div class="alert alert-warning rounded-3">
            <strong>⏳ Menunggu Verifikasi</strong><br>
            <span class="small">Tokomu sedang direview oleh tim ToLong. Biasanya selesai dalam 1x24 jam.</span>
        </div>
    @elseif($tenant->verification_status === 'rejected')
        <div class="alert alert-danger rounded-3">
            <strong>❌ Verifikasi Ditolak</strong><br>
            <span class="small">Tokomu tidak lolos verifikasi. Hubungi admin untuk informasi lebih lanjut.</span>
        </div>
    @else
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0">{{ $tenant->store_name }}</h4>
                <span class="badge bg-success-subtle text-success rounded-pill small">✓ Aktif</span>
            </div>
        </div>

        {{-- Statistik Pendapatan --}}
        <div class="row g-2 mb-3">
            <div class="col-4">
                <div class="app-card text-center py-3">
                    <div class="text-muted" style="font-size:11px;">Hari Ini</div>
                    <div class="fw-bold text-danger" style="font-size:14px;">
                        Rp {{ $earningsToday }}
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="app-card text-center py-3">
                    <div class="text-muted" style="font-size:11px;">Bulan Ini</div>
                    <div class="fw-bold text-danger" style="font-size:14px;">
                        Rp {{ $earningsThisMonth }}
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="app-card text-center py-3">
                    <div class="text-muted" style="font-size:11px;">Selamanya</div>
                    <div class="fw-bold text-danger" style="font-size:14px;">
                        Rp {{ $earningsAllTime }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Grafik Pesanan 30 Hari Terakhir --}}
        <div class="app-card mb-3">
            <div class="fw-semibold small mb-3">Pesanan Bulan Ini</div>
            <div style="position: relative; height: 200px;">
                <canvas id="orderChart"></canvas>
            </div>
        </div>

        {{-- Menu Terlaris --}}
        <div class="app-card mb-3">
            <div class="fw-semibold small mb-3">Menu Terlaris</div>

            @forelse($topMenus as $item)
                <div class="d-flex align-items-center gap-3 py-2 border-top">
                    @if($item->menu?->photos->isNotEmpty())
                        <img src="{{ Storage::url($item->menu->photos->first()->photo_url) }}"
                            class="rounded-3 object-fit-cover flex-shrink-0"
                            style="width:48px;height:48px;">
                    @else
                        <div class="rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width:48px;height:48px;font-size:20px;">🍴</div>
                    @endif

                    <div class="flex-grow-1 min-width-0">
                        <div class="small fw-medium text-truncate">
                            {{ $item->menu?->item_name ?? 'Menu telah dihapus' }}
                        </div>
                        <div class="text-muted" style="font-size:11px;">
                            Terjual {{ $item->total_qty }}x
                        </div>
                    </div>

                    <div class="text-danger fw-semibold small flex-shrink-0">
                        Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                    </div>
                </div>
            @empty
                <div class="text-center py-3">
                    <div style="font-size:32px;">📊</div>
                    <p class="text-muted small mt-2 mb-0">Belum ada data penjualan menu.</p>
                </div>
            @endforelse
        </div>
    @endif

    @livewire('tenant.incoming-order')

    {{-- Order aktif --}}
    @livewire('tenant.active-order')

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
</div>