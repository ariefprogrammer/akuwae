<div class="card border-0 shadow-sm rounded-4 p-4">

    {{-- Header step indicator --}}
    <div class="d-flex align-items-center gap-2 mb-4">
        @foreach([1,2,3] as $s)
            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                style="
                    width: 28px; height: 28px; font-size: 12px;
                    background: {{ $step >= $s ? '#dc3545' : '#e9ecef' }};
                    color: {{ $step >= $s ? '#fff' : '#adb5bd' }};
                ">
                {{ $s }}
            </div>
            @if($s < 3)
                <div class="flex-grow-1" style="height: 2px; background: {{ $step > $s ? '#dc3545' : '#e9ecef' }};"></div>
            @endif
        @endforeach
    </div>

    {{-- Error & Success --}}
    @if($error)
        <div class="alert alert-danger py-2 small">{{ $error }}</div>
    @endif
    @if($success && $step > 1)
        <div class="alert alert-success py-2 small">{{ $success }}</div>
    @endif

    {{-- ======================== STEP 1 ======================== --}}
    @if($step === 1)
        <h5 class="fw-semibold mb-4">Daftar akun baru</h5>

        <div class="mb-3">
            <label class="form-label small fw-medium">Nomor HP</label>
            <input
                wire:model="phone_number"
                type="tel"
                placeholder="08xxxxxxxxxx"
                class="form-control rounded-3 @error('phone_number') is-invalid @enderror"
            >
            @error('phone_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label small fw-medium">Daftar sebagai</label>
            <select wire:model="role" class="form-select rounded-3">
                <option value="customer">Pelanggan</option>
                <option value="driver">Mitra Driver</option>
                <option value="tenant">Pemilik Toko</option>
            </select>
        </div>

        <button
            wire:click="sendOtp"
            wire:loading.attr="disabled"
            class="btn btn-danger w-100 rounded-3 py-2 fw-semibold"
        >
            <span wire:loading.remove wire:target="sendOtp">Kirim OTP via WhatsApp</span>
            <span wire:loading wire:target="sendOtp">Mengirim OTP...</span>
        </button>

    {{-- ======================== STEP 2 ======================== --}}
    @elseif($step === 2)
        <h5 class="fw-semibold mb-1">Verifikasi OTP</h5>
        <p class="text-muted small mb-4">Masukkan kode 6 digit yang dikirim ke <strong>{{ $phone_number }}</strong></p>

        <div class="mb-4">
            <label class="form-label small fw-medium">Kode OTP</label>
            <input
                wire:model="otp"
                type="number"
                inputmode="numeric"
                maxlength="6"
                placeholder="______"
                class="form-control rounded-3 text-center fs-4 fw-bold letter-spacing-wide @error('otp') is-invalid @enderror"
            >
            @error('otp')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button
            wire:click="verifyOtp"
            wire:loading.attr="disabled"
            class="btn btn-danger w-100 rounded-3 py-2 fw-semibold"
        >
            <span wire:loading.remove wire:target="verifyOtp">Verifikasi</span>
            <span wire:loading wire:target="verifyOtp">Memverifikasi...</span>
        </button>

        <button wire:click="$set('step', 1)" class="btn btn-link text-muted w-100 mt-2 small">
            ← Ganti nomor HP
        </button>

    {{-- ======================== STEP 3 ======================== --}}
    @elseif($step === 3)
        <h5 class="fw-semibold mb-4">Lengkapi profilmu</h5>

        <div class="mb-3">
            <label class="form-label small fw-medium">Nama Lengkap</label>
            <input
                wire:model="name"
                type="text"
                placeholder="Nama kamu"
                class="form-control rounded-3 @error('name') is-invalid @enderror"
            >
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label small fw-medium">Buat PIN 6 Digit</label>
            <input
                wire:model="pin"
                type="password"
                inputmode="numeric"
                maxlength="6"
                placeholder="••••••"
                class="form-control rounded-3 @error('pin') is-invalid @enderror"
            >
            @error('pin')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label small fw-medium">Konfirmasi PIN</label>
            <input
                wire:model="pin_confirmation"
                type="password"
                inputmode="numeric"
                maxlength="6"
                placeholder="••••••"
                class="form-control rounded-3"
            >
        </div>

        <button
            wire:click="register"
            wire:loading.attr="disabled"
            class="btn btn-danger w-100 rounded-3 py-2 fw-semibold"
        >
            <span wire:loading.remove wire:target="register">Buat Akun</span>
            <span wire:loading wire:target="register">Membuat akun...</span>
        </button>
    @endif

    @if($step === 1)
        <p class="text-center text-muted small mt-4 mb-0">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-danger fw-medium text-decoration-none">Masuk</a>
        </p>
    @endif

</div>