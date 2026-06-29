<div class="px-3 py-3">

    {{-- Step indicator --}}
    <div class="d-flex align-items-center gap-2 mb-4 px-1">
        @foreach([1 => 'Data Kendaraan', 2 => 'Dokumen'] as $s => $label)
            <div class="d-flex align-items-center gap-2 {{ !$loop->last ? 'flex-grow-1' : '' }}">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                    style="width:28px;height:28px;font-size:12px;
                    background:{{ $step >= $s ? '#dc3545' : '#e9ecef' }};
                    color:{{ $step >= $s ? '#fff' : '#adb5bd' }};">
                    {{ $s }}
                </div>
                <span style="font-size:12px;color:{{ $step >= $s ? '#dc3545' : '#adb5bd' }};">
                    {{ $label }}
                </span>
                @if(!$loop->last)
                    <div class="flex-grow-1" style="height:2px;background:{{ $step > $s ? '#dc3545' : '#e9ecef' }};"></div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ══ STEP 1 ══ --}}
    @if($step === 1)
        <div class="app-card mb-3">
            <div class="fw-semibold small mb-3">Data Diri</div>

            <div class="mb-3">
                <label class="form-label small fw-medium">Nama Lengkap</label>
                <input wire:model="name" type="text" placeholder="Sesuai KTP"
                    class="form-control rounded-3 @error('name') is-invalid @enderror">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="app-card mb-3">
            <div class="fw-semibold small mb-3">Data Kendaraan</div>

            <div class="mb-3">
                <label class="form-label small fw-medium">Jenis Kendaraan</label>
                <div class="d-flex gap-2">
                    <label class="flex-grow-1">
                        <input wire:model="vehicle_type" type="radio" value="motor" class="d-none">
                        <div class="border rounded-3 text-center py-3 {{ $vehicle_type === 'motor' ? 'border-danger bg-danger bg-opacity-10' : '' }}"
                            style="cursor:pointer;">
                            <div style="font-size:28px;">🛵</div>
                            <div class="small fw-medium mt-1">Motor</div>
                        </div>
                    </label>
                    <label class="flex-grow-1">
                        <input wire:model="vehicle_type" type="radio" value="mobil" class="d-none">
                        <div class="border rounded-3 text-center py-3 {{ $vehicle_type === 'mobil' ? 'border-danger bg-danger bg-opacity-10' : '' }}"
                            style="cursor:pointer;">
                            <div style="font-size:28px;">🚗</div>
                            <div class="small fw-medium mt-1">Mobil</div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label small fw-medium">Nomor Plat Kendaraan</label>
                <input wire:model="vehicle_plate" type="text"
                    placeholder="Cth: BE 1234 ABC"
                    style="text-transform:uppercase;"
                    class="form-control rounded-3 @error('vehicle_plate') is-invalid @enderror">
                @error('vehicle_plate') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <button wire:click="nextStep" wire:loading.attr="disabled"
            class="btn btn-danger w-100 rounded-3 py-2 fw-semibold">
            <span wire:loading.remove wire:target="nextStep">Lanjut →</span>
            <span wire:loading wire:target="nextStep">Memproses...</span>
        </button>

    {{-- ══ STEP 2 ══ --}}
    @elseif($step === 2)
        <div class="app-card mb-3">
            <div class="fw-semibold small mb-3">Dokumen Identitas</div>

            <div class="mb-3">
                <label class="form-label small fw-medium">Nomor KTP</label>
                <input wire:model="ktp_number" type="number" inputmode="numeric"
                    maxlength="16" placeholder="16 digit nomor KTP"
                    class="form-control rounded-3 @error('ktp_number') is-invalid @enderror">
                @error('ktp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-0">
                <label class="form-label small fw-medium">Nomor SIM</label>
                <input wire:model="sim_number" type="text" placeholder="Nomor SIM aktif"
                    class="form-control rounded-3 @error('sim_number') is-invalid @enderror">
                @error('sim_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="app-card mb-3">
            <div class="fw-semibold small mb-3">Foto Dokumen</div>

            {{-- STNK --}}
            <div class="mb-3">
                <label class="form-label small fw-medium">Foto STNK</label>
                <p class="text-muted mb-1" style="font-size:11px;">Pastikan tulisan terbaca jelas.</p>

                @if($stnk_photo)
                    <img src="{{ $stnk_photo->temporaryUrl() }}"
                        class="rounded-3 w-100 object-fit-cover mb-2"
                        style="height:140px;">
                @else
                    <label class="d-flex flex-column align-items-center justify-content-center rounded-3 border w-100 mb-2"
                        style="height:140px;cursor:pointer;border-style:dashed!important;">
                        <span style="font-size:32px;">📄</span>
                        <span class="small text-muted mt-1">Tap untuk upload</span>
                        <input wire:model="stnk_photo" type="file" accept="image/*" class="d-none">
                    </label>
                @endif

                @if($stnk_photo)
                    <button wire:click="$set('stnk_photo', null)"
                        class="btn btn-sm btn-outline-danger rounded-3 w-100">
                        Ganti Foto STNK
                    </button>
                @endif
                @error('stnk_photo') <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div> @enderror
            </div>

            {{-- Selfie + KTP --}}
            <div class="mb-0">
                <label class="form-label small fw-medium">Foto Selfie + KTP</label>
                <p class="text-muted mb-1" style="font-size:11px;">Pegang KTP di samping wajah, pastikan keduanya terlihat jelas.</p>

                @if($selfie_ktp_photo)
                    <img src="{{ $selfie_ktp_photo->temporaryUrl() }}"
                        class="rounded-3 w-100 object-fit-cover mb-2"
                        style="height:140px;">
                @else
                    <label class="d-flex flex-column align-items-center justify-content-center rounded-3 border w-100 mb-2"
                        style="height:140px;cursor:pointer;border-style:dashed!important;">
                        <span style="font-size:32px;">🤳</span>
                        <span class="small text-muted mt-1">Tap untuk upload</span>
                        <input wire:model="selfie_ktp_photo" type="file" accept="image/*" class="d-none">
                    </label>
                @endif

                @if($selfie_ktp_photo)
                    <button wire:click="$set('selfie_ktp_photo', null)"
                        class="btn btn-sm btn-outline-danger rounded-3 w-100">
                        Ganti Foto Selfie
                    </button>
                @endif
                @error('selfie_ktp_photo') <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="d-flex gap-2">
            <button wire:click="$set('step', 1)"
                class="btn btn-outline-secondary rounded-3 flex-grow-1">
                ← Kembali
            </button>
            <button wire:click="save" wire:loading.attr="disabled"
                class="btn btn-danger rounded-3 flex-grow-1 fw-semibold">
                <span wire:loading.remove wire:target="save">Kirim Pendaftaran</span>
                <span wire:loading wire:target="save">Mengirim...</span>
            </button>
        </div>
    @endif

</div>