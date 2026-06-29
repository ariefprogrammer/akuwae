<div class="px-3 py-3">

    {{-- Foto & Info Dasar --}}
    <div class="app-card mb-3 text-center">
        <div class="position-relative d-inline-block mb-2">
            @if($photo)
                <img src="{{ $photo->temporaryUrl() }}"
                    class="rounded-circle object-fit-cover"
                    style="width:80px;height:80px;">
            @elseif($currentPhoto)
                <img src="{{ Storage::url($currentPhoto) }}"
                    class="rounded-circle object-fit-cover"
                    style="width:80px;height:80px;">
            @else
                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto"
                    style="width:80px;height:80px;font-size:32px;">
                    👤
                </div>
            @endif

            <label class="position-absolute bottom-0 end-0 bg-danger rounded-circle d-flex align-items-center justify-content-center"
                style="width:28px;height:28px;cursor:pointer;border:2px solid #fff;">
                <span style="font-size:12px;color:#fff;"><i class="fas fa-pencil-alt"></i></span>
                <input wire:model="photo" type="file" accept="image/*" class="d-none">
            </label>
        </div>

        <div class="fw-bold">{{ $customer->name }}</div>
        <div class="text-muted small">{{ $phone_number }}</div>

        @error('photo')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    @if($successMessage)
        <div class="alert alert-success py-2 small rounded-3">✓ {{ $successMessage }}</div>
    @endif

    {{-- Edit Profil --}}
    <div class="app-card mb-3">
        <div class="fw-semibold small mb-3">Informasi Akun</div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Nama Lengkap</label>
            <input wire:model="name" type="text"
                class="form-control rounded-3 @error('name') is-invalid @enderror">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Nomor HP</label>
            <input type="text" value="{{ $phone_number }}" disabled
                class="form-control rounded-3 bg-light text-muted">
            <div class="text-muted mt-1" style="font-size:11px;">
                Nomor HP tidak dapat diubah.
            </div>
        </div>

        <button wire:click="save" wire:loading.attr="disabled"
            class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
            <span wire:loading.remove wire:target="save">Simpan Perubahan</span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </div>

    {{-- Alamat Tersimpan --}}
    <a href="{{ route('customer.addresses') }}" class="text-decoration-none text-dark">
        <div class="app-card mb-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div style="font-size:24px;"><i class="fas fa-map-marker-alt me-1"></i></div>
                <div>
                    <div class="fw-medium small">Alamat Tersimpan</div>
                    <div class="text-muted" style="font-size:12px;">
                        {{ $customer->savedAddresses()->count() }}/5 alamat
                    </div>
                </div>
            </div>
            <div class="text-muted">
                <i class="fas fa-chevron-right" style="font-size: 0.85em; vertical-align: middle;"></i>
            </div>
        </div>
    </a>

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
        <i class="fas fa-power-off me-2"></i> Keluar
    </button>

</div>