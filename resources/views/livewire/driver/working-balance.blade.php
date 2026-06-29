<div class="d-flex flex-column" style="min-height: 100vh;">

    {{-- Bagian atas: Saldo, alert, form top up --}}
    <div class="px-3 py-3">

        {{-- Saldo --}}
        <div class="app-card mb-3 shadow-sm text-center" style="background:linear-gradient(135deg,#dc3545,#b02a37);">
            <div class="text-white-50 small mb-1">Saldo Kerja</div>
            <div class="text-white fw-bold fs-3 mb-2">Rp {{ number_format($balance, 0, ',', '.') }}</div>
            <button wire:click="$set('showTopupForm', true)"
                class="btn btn-light btn-sm rounded-3 fw-semibold text-danger px-4">
                + Top Up
            </button>
        </div>

        @if($balance < 20000)
            <div class="alert alert-warning rounded-3 small shadow-sm">
                ⚠️ Saldo di bawah minimum. Kamu mungkin tidak menerima order baru sampai top up.
            </div>
        @endif

        @if($success)
            <div class="alert alert-success rounded-3 small shadow-sm">✓ {{ $success }}</div>
        @endif

        {{-- Form Top Up --}}
        @if($showTopupForm)
            <div class="app-card shadow-sm">
                <div class="fw-semibold small mb-3">Top Up Saldo</div>

                @if($error)
                    <div class="alert alert-danger py-2 small rounded-3">{{ $error }}</div>
                @endif

                <div class="mb-3">
                    <label class="form-label small fw-medium">Nominal Top Up</label>
                    <input wire:model="topupAmount" type="number" min="10000" placeholder="Cth: 50000"
                        class="form-control rounded-3 @error('topupAmount') is-invalid @enderror">
                    @error('topupAmount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-medium">Bukti Transfer (opsional)</label>
                    @if($proofPhoto)
                        <img src="{{ $proofPhoto->temporaryUrl() }}" class="rounded-3 mb-2 w-100" style="max-height:160px;object-fit:cover;">
                    @endif
                    <input wire:model="proofPhoto" type="file" accept="image/*" class="form-control rounded-3">
                </div>

                <div class="d-flex gap-2">
                    <button wire:click="$set('showTopupForm', false)" class="btn btn-outline-secondary rounded-3 flex-grow-1">
                        Batal
                    </button>
                    <button wire:click="submitTopup" wire:loading.attr="disabled"
                        class="btn btn-danger rounded-3 flex-grow-1 fw-semibold">
                        <span wire:loading.remove wire:target="submitTopup">Kirim Permintaan</span>
                        <span wire:loading wire:target="submitTopup">Mengirim...</span>
                    </button>
                </div>
            </div>
        @endif

    </div>

    {{-- Bagian bawah: putih penuh mulai dari tab sampai bawah --}}
    <div class="bg-white flex-grow-1 px-3 pt-3 pb-4" x-data="{ tab: 'mutasi' }">

        <div class="d-flex app-card shadow-sm p-1 mb-3" style="gap:4px;">
            <button type="button" @click="tab = 'mutasi'"
                :class="tab === 'mutasi' ? 'btn-danger text-white' : 'btn-light text-muted'"
                class="btn btn-sm rounded-3 flex-grow-1 fw-semibold">
                Mutasi
            </button>
            <button type="button" @click="tab = 'topup'"
                :class="tab === 'topup' ? 'btn-danger text-white' : 'btn-light text-muted'"
                class="btn btn-sm rounded-3 flex-grow-1 fw-semibold">
                Top Up
            </button>
        </div>

        {{-- Riwayat Mutasi --}}
        <div x-show="tab === 'mutasi'" x-cloak>
            @forelse($transactions as $trx)
                <div class="app-card mb-2 shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-medium">
                            {{ match($trx->type) {
                                'topup' => '💰 Top Up',
                                'commission_deduction' => '➖ Potongan Komisi',
                                default => 'Penyesuaian'
                            } }}
                        </div>
                        <div class="text-muted" style="font-size:11px;">{{ $trx->created_at->translatedFormat('d M Y, H:i') }}</div>
                    </div>
                    <span class="fw-semibold small {{ $trx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $trx->amount >= 0 ? '+' : '' }}Rp {{ number_format($trx->amount, 0, ',', '.') }}
                    </span>
                </div>
            @empty
                <div class="text-center py-4">
                    <div style="font-size:32px;">📭</div>
                    <p class="text-muted small mt-2">Belum ada riwayat mutasi.</p>
                </div>
            @endforelse
        </div>

        {{-- Permintaan Top Up --}}
        <div x-show="tab === 'topup'" x-cloak>
            @forelse($topupRequests as $req)
                <div class="app-card mb-2 shadow-sm d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small fw-medium">Rp {{ number_format($req->amount, 0, ',', '.') }}</div>
                        <div class="text-muted" style="font-size:11px;">{{ $req->created_at->translatedFormat('d M Y, H:i') }}</div>
                    </div>
                    <span class="badge rounded-pill
                        {{ $req->status === 'approved' ? 'bg-success-subtle text-success' :
                           ($req->status === 'rejected' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning') }}"
                        style="font-size:10px;">
                        {{ match($req->status) { 'approved' => 'Disetujui', 'rejected' => 'Ditolak', default => 'Menunggu' } }}
                    </span>
                </div>
            @empty
                <div class="text-center py-4">
                    <div style="font-size:32px;">📭</div>
                    <p class="text-muted small mt-2">Belum ada permintaan top up.</p>
                </div>
            @endforelse
        </div>

    </div>

</div>