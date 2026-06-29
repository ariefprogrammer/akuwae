<div class="px-3 py-3">

    {{-- Quick range --}}
    <div class="d-flex gap-2 mb-3" style="overflow-x:auto; white-space:nowrap;">
        <button wire:click="setQuickRange('today')" class="btn btn-sm btn-outline-danger rounded-3">Hari Ini</button>
        <button wire:click="setQuickRange('week')" class="btn btn-sm btn-outline-danger rounded-3">Minggu Ini</button>
        <button wire:click="setQuickRange('month')" class="btn btn-sm btn-outline-danger rounded-3">Bulan Ini</button>
        <button wire:click="setQuickRange('last_month')" class="btn btn-sm btn-outline-danger rounded-3">Bulan Lalu</button>
    </div>

    {{-- Date range picker --}}
    <div class="app-card mb-3">
        <div class="row g-2">
            <div class="col-6">
                <label class="form-label small fw-medium">Dari Tanggal</label>
                <input wire:model="startDate" type="date"
                    class="form-control form-control-sm rounded-3 @error('startDate') is-invalid @enderror">
                @error('startDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-6">
                <label class="form-label small fw-medium">Sampai Tanggal</label>
                <input wire:model="endDate" type="date"
                    class="form-control form-control-sm rounded-3 @error('endDate') is-invalid @enderror">
                @error('endDate') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    {{-- Detail Order --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-3">Detail Order</div>

        @forelse($orderDetails as $item)
            <div class="d-flex justify-content-between align-items-start py-2 border-top">
                <div class="flex-grow-1 min-width-0">
                    <div class="small fw-medium text-truncate">
                        {{ $item->menu?->item_name ?? 'Menu telah dihapus' }}
                    </div>
                    <div class="text-muted" style="font-size:11px;">
                        {{ $item->total_qty }} × Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}
                    </div>
                </div>
                <div class="text-danger fw-semibold small flex-shrink-0 ms-2 text-end">
                    Rp {{ number_format($item->total_revenue, 0, ',', '.') }}
                </div>
            </div>
        @empty
            <div class="text-center py-3">
                <div style="font-size:32px;">🧾</div>
                <p class="text-muted small mt-2 mb-0">Tidak ada detail order pada rentang ini.</p>
            </div>
        @endforelse
    </div>

    {{-- Ringkasan Pendapatan --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-3">Ringkasan Pendapatan</div>

        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Total Order Selesai</span>
            <span class="small fw-medium">{{ $totalOrders }} order</span>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Omset</span>
            <span class="small fw-medium">Rp {{ $grossRevenue }}</span>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span class="text-muted small">Potongan Aplikasi</span>
            <span class="small fw-medium text-danger">- Rp {{ $platformFee }}</span>
        </div>

        <hr class="my-2">

        <div class="d-flex justify-content-between">
            <span class="fw-semibold small">Pendapatan Bersih</span>
            <span class="fw-bold text-danger">Rp {{ $netRevenue }}</span>
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
                <p class="text-muted small mt-2 mb-0">Tidak ada data penjualan pada rentang ini.</p>
            </div>
        @endforelse
    </div>

</div>