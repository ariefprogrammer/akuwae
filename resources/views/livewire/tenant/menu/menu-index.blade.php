<div>
    {{-- Header action --}}
    <div class="px-3 pt-3 d-flex justify-content-between align-items-center">
        <button wire:click="openAddCategory"
            class="btn btn-outline-danger btn-sm rounded-3">
            + Kategori
        </button>
        <a href="{{ route('tenant.menu.create') }}"
            class="btn btn-danger btn-sm rounded-3">
            + Item Menu
        </a>
    </div>

    {{-- Kosong --}}
    @if($categories->isEmpty())
        <div class="text-center py-5 px-4">
            <div style="font-size: 48px;">🍽️</div>
            <p class="fw-semibold mt-3 mb-1">Belum ada menu</p>
            <p class="text-muted small">Mulai dengan tambah kategori, lalu tambahkan item menu.</p>
        </div>
    @endif

    {{-- List kategori & menu --}}
    <div class="px-3 pt-3">
        @foreach($categories as $category)
            <div class="app-card mb-3">
                {{-- Header kategori --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-semibold">{{ $category->category_name }}</span>
                    <div class="d-flex gap-2">
                        <button wire:click="openEditCategory({{ $category->id }})"
                            class="btn btn-sm btn-outline-secondary py-0 px-2"
                            style="font-size:11px;">Edit</button>
                        <button wire:click="deleteCategory({{ $category->id }})"
                            wire:confirm="Hapus kategori ini beserta semua item-nya?"
                            class="btn btn-sm btn-outline-danger py-0 px-2"
                            style="font-size:11px;">Hapus</button>
                    </div>
                </div>

                {{-- Item menu --}}
                @forelse($category->menus as $menu)
                    <div class="d-flex align-items-center gap-3 py-2 border-top">
                        {{-- Foto --}}
                        @if($menu->photos->isNotEmpty())
                            <img src="{{ Storage::url($menu->photos->first()->photo_url) }}"
                                class="rounded-3 object-fit-cover flex-shrink-0"
                                style="width:56px;height:56px;">
                        @else
                            <div class="rounded-3 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:56px;height:56px;font-size:24px;">🍴</div>
                        @endif

                        {{-- Info --}}
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-medium small text-truncate">{{ $menu->item_name }}</div>
                            <div class="text-danger small fw-semibold">
                                Rp {{ number_format($menu->price, 0, ',', '.') }}
                            </div>
                            <span class="badge rounded-pill {{ $menu->is_available ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}"
                                style="font-size:10px;">
                                {{ $menu->is_available ? 'Tersedia' : 'Habis' }}
                            </span>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex flex-column gap-1 flex-shrink-0">
                            <a href="{{ route('tenant.menu.edit', $menu->id) }}"
                                class="btn btn-sm btn-outline-secondary py-0 px-2"
                                style="font-size:11px;">Edit</a>
                            <button wire:click="toggleAvailable({{ $menu->id }})"
                                class="btn btn-sm btn-outline-warning py-0 px-2"
                                style="font-size:11px;">
                                {{ $menu->is_available ? 'Habis' : 'Tersedia' }}
                            </button>
                            <button wire:click="deleteMenu({{ $menu->id }})"
                                wire:confirm="Hapus item ini?"
                                class="btn btn-sm btn-outline-danger py-0 px-2"
                                style="font-size:11px;">Hapus</button>
                        </div>
                    </div>
                @empty
                    <p class="text-muted small mb-0 pt-2">Belum ada item di kategori ini.</p>
                @endforelse
            </div>
        @endforeach
    </div>

    {{-- Modal Kategori --}}
    @if($showCategoryModal)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-end"
            style="background:rgba(0,0,0,0.4);z-index:999;">
            <div class="bg-white w-100 rounded-top-4 p-4" style="max-width:480px;margin:0 auto;">
                <h6 class="fw-bold mb-3">
                    {{ $editCategoryId ? 'Edit Kategori' : 'Tambah Kategori' }}
                </h6>

                <div class="mb-3">
                    <input wire:model="category_name" type="text"
                        placeholder="Nama kategori (Contoh : Bakso / Cemilan)"
                        class="form-control rounded-3 @error('category_name') is-invalid @enderror">
                    @error('category_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button wire:click="$set('showCategoryModal', false)"
                        class="btn btn-outline-secondary rounded-3 flex-grow-1">
                        Batal
                    </button>
                    <button wire:click="saveCategory"
                        wire:loading.attr="disabled"
                        class="btn btn-danger rounded-3 flex-grow-1">
                        <span wire:loading.remove wire:target="saveCategory">Simpan</span>
                        <span wire:loading wire:target="saveCategory">Menyimpan...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>