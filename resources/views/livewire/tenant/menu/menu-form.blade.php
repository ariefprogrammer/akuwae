<div class="px-3 py-3">

    {{-- Kategori --}}
    <div class="mb-3">
        <label class="form-label small fw-medium">Kategori</label>
        <select wire:model="menu_category_id"
            class="form-select rounded-3 @error('menu_category_id') is-invalid @enderror">
            <option value="0">-- Pilih Kategori --</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
            @endforeach
        </select>
        @error('menu_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        @if($categories->isEmpty())
            <p class="text-muted" style="font-size:12px;">
                Belum ada kategori.
                <a href="{{ route('tenant.menu.index') }}" class="text-danger">Tambah dulu.</a>
            </p>
        @endif
    </div>

    {{-- Nama Item --}}
    <div class="mb-3">
        <label class="form-label small fw-medium">Nama Item</label>
        <input wire:model="item_name" type="text" placeholder="Cth: Nasi Goreng Spesial"
            class="form-control rounded-3 @error('item_name') is-invalid @enderror">
        @error('item_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Deskripsi --}}
    <div class="mb-3">
        <label class="form-label small fw-medium">Deskripsi <span class="text-muted">(opsional)</span></label>
        <textarea wire:model="description" rows="2"
            placeholder="Bahan, rasa, atau keterangan lain..."
            class="form-control rounded-3"></textarea>
    </div>

    {{-- Harga --}}
    <div class="mb-3">
        <label class="form-label small fw-medium">Harga (Rp)</label>
        <input wire:model="price" type="number" min="0" placeholder="15000"
            class="form-control rounded-3 @error('price') is-invalid @enderror">
        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Status --}}
    <div class="mb-3 d-flex align-items-center justify-content-between app-card">
        <div>
            <div class="fw-medium small">Status Item</div>
            <div class="text-muted" style="font-size:12px;">
                {{ $is_available ? 'Item tersedia untuk dipesan' : 'Item sedang habis' }}
            </div>
        </div>
        <div class="form-check form-switch mb-0">
            <input wire:model="is_available" class="form-check-input" type="checkbox"
                style="width:40px;height:22px;">
        </div>
    </div>

    {{-- Upload foto --}}
    <div class="mb-4">
        <label class="form-label small fw-medium">
            Foto Menu <span class="text-muted">(maks. 4 foto)</span>
        </label>

        @php
            $totalSlots = count($existingPhotos) + count($photos);
        @endphp

        <div class="d-flex gap-2 flex-wrap">

            {{-- Foto existing (edit mode) --}}
            @foreach($existingPhotos as $photo)
                <div class="position-relative">
                    <img src="{{ Storage::url($photo['photo_url']) }}"
                        class="rounded-3 object-fit-cover"
                        style="width:80px;height:80px;">
                    <button wire:click="deleteExistingPhoto({{ $photo['id'] }})"
                        wire:confirm="Hapus foto ini?"
                        class="btn btn-danger position-absolute top-0 end-0 rounded-circle p-0 d-flex align-items-center justify-content-center"
                        style="width:20px;height:20px;font-size:10px;line-height:1;">✕</button>
                </div>
            @endforeach

            {{-- Preview foto baru --}}
            @foreach($photos as $i => $photo)
                <div class="position-relative">
                    <img src="{{ $photo->temporaryUrl() }}"
                        class="rounded-3 object-fit-cover"
                        style="width:80px;height:80px;">
                    <button wire:click="removeNewPhoto({{ $i }})"
                        class="btn btn-danger position-absolute top-0 end-0 rounded-circle p-0 d-flex align-items-center justify-content-center"
                        style="width:20px;height:20px;font-size:10px;line-height:1;">✕</button>
                </div>
            @endforeach

            {{-- Tombol + tambah foto (muncul kalau slot masih ada) --}}
            @if($totalSlots < 4)
                <label class="rounded-3 d-flex flex-column align-items-center justify-content-center"
                    style="width:80px;height:80px;cursor:pointer;border:2px dashed #dee2e6;">
                    <span style="font-size:22px;color:#adb5bd;">+</span>
                    <span style="font-size:10px;color:#adb5bd;">Tambah</span>
                    <input wire:model="newPhoto" type="file" accept="image/*" class="d-none">
                </label>
            @endif

        </div>

        <p class="text-muted mt-1 mb-0" style="font-size:11px;">{{ $totalSlots }}/4 foto</p>

        @error('newPhoto') <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div> @enderror
        @error('photos')   <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div> @enderror
    </div>

    {{-- Tombol --}}
    <div class="d-flex gap-2">
        <a href="{{ route('tenant.menu.index') }}"
            class="btn btn-outline-secondary rounded-3 flex-grow-1">
            Batal
        </a>
        <button wire:click="save" wire:loading.attr="disabled"
            class="btn btn-danger rounded-3 flex-grow-1">
            <span wire:loading.remove wire:target="save">
                {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Menu' }}
            </span>
            <span wire:loading wire:target="save">Menyimpan...</span>
        </button>
    </div>
</div>