<div class="px-3 py-3">

    {{-- Header foto & nama --}}
    <div class="app-card mb-3 text-center">
        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto mb-2"
            style="width:80px;height:80px;font-size:32px;">
            {{ $driver->vehicle_type === 'motor' ? '🛵' : '🚗' }}
        </div>

        <div class="fw-bold">{{ $driver->name }}</div>
        <div class="text-muted small">{{ auth()->user()->phone_number }}</div>

        <span class="badge bg-success-subtle text-success rounded-pill mt-2" style="font-size:11px;">
            ✓ Terverifikasi
        </span>
    </div>

    {{-- Data Diri (read-only) --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-3">Data Diri</div>

        <div class="mb-3">
            <label class="form-label small fw-medium text-muted">Nama Lengkap</label>
            <input type="text" value="{{ $driver->name }}" disabled
                class="form-control rounded-3 bg-light text-dark">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium text-muted">Nomor HP</label>
            <input type="text" value="{{ auth()->user()->phone_number }}" disabled
                class="form-control rounded-3 bg-light text-dark">
        </div>

        <div class="mb-0">
            <label class="form-label small fw-medium text-muted">Status Akun</label>
            <input type="text" value="{{ ucfirst(auth()->user()->status) }}" disabled
                class="form-control rounded-3 bg-light text-dark">
        </div>
    </div>

    {{-- Data Kendaraan (read-only) --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-3">Data Kendaraan</div>

        <div class="mb-3">
            <label class="form-label small fw-medium text-muted">Jenis Kendaraan</label>
            <input type="text" value="{{ $driver->vehicle_type === 'motor' ? 'Motor' : 'Mobil' }}" disabled
                class="form-control rounded-3 bg-light text-dark">
        </div>

        <div class="mb-0">
            <label class="form-label small fw-medium text-muted">Nomor Plat</label>
            <input type="text" value="{{ strtoupper($driver->vehicle_plate) }}" disabled
                class="form-control rounded-3 bg-light text-dark">
        </div>
    </div>

    <p class="text-muted text-center mb-3" style="font-size:11px;">
        Data di atas hanya bisa diubah melalui tim ToLong. Hubungi admin jika ada kesalahan data.
    </p>

    {{-- Ganti PIN --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-3">Ganti PIN</div>

        @if($pinError)
            <div class="alert alert-danger py-2 small rounded-3">{{ $pinError }}</div>
        @endif
        @if($pinSuccess)
            <div class="alert alert-success py-2 small rounded-3">✓ {{ $pinSuccess }}</div>
        @endif

        <div class="mb-2">
            <label class="form-label small fw-medium">PIN Saat Ini</label>
            <input wire:model="current_pin" type="password" inputmode="numeric" maxlength="6"
                class="form-control rounded-3 @error('current_pin') is-invalid @enderror">
            @error('current_pin') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-2">
            <label class="form-label small fw-medium">PIN Baru</label>
            <input wire:model="new_pin" type="password" inputmode="numeric" maxlength="6"
                class="form-control rounded-3 @error('new_pin') is-invalid @enderror">
            @error('new_pin') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Konfirmasi PIN Baru</label>
            <input wire:model="new_pin_confirm" type="password" inputmode="numeric" maxlength="6"
                class="form-control rounded-3 @error('new_pin_confirm') is-invalid @enderror">
            @error('new_pin_confirm') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <button wire:click="changePin" wire:loading.attr="disabled"
            class="btn btn-outline-danger w-100 rounded-3 py-2 fw-semibold">
            <span wire:loading.remove wire:target="changePin">Ganti PIN</span>
            <span wire:loading wire:target="changePin">Memproses...</span>
        </button>
    </div>

    {{-- Logout --}}
    <button wire:click="logout"
        wire:confirm="Yakin ingin keluar?"
        class="btn btn-outline-secondary w-100 rounded-3 py-2 fw-semibold">
        🚪 Keluar
    </button>

</div>