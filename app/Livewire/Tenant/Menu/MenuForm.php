<?php

namespace App\Livewire\Tenant\Menu;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuPhoto;
use App\Models\Tenant;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class MenuForm extends Component
{
    use WithFileUploads;

    public ?Tenant $tenant  = null;
    public ?Menu   $menu    = null;
    public bool    $isEdit  = false;

    // Fields
    public int    $menu_category_id = 0;
    public string $item_name        = '';
    public string $description      = '';
    public string $price            = '';
    public bool   $is_available     = true;
    public array $photos = [];
    public $newPhoto = null; 
    public array  $existingPhotos   = [];

    public function mount(?Menu $menu = null)
    {
        $this->tenant = auth()->user()->tenant;

        if (!$this->tenant || $this->tenant->verification_status !== 'approved') {
            return redirect()->route('tenant.dashboard');
        }

        if ($menu && $menu->exists) {
            $this->isEdit           = true;
            $this->menu             = $menu;
            $this->menu_category_id = $menu->menu_category_id;
            $this->item_name        = $menu->item_name;
            $this->description      = $menu->description ?? '';
            $this->price            = (string) $menu->price;
            $this->is_available     = $menu->is_available;
            $this->existingPhotos   = $menu->photos->toArray();
        }
    }

    public function deleteExistingPhoto(int $photoId)
    {
        $photo = MenuPhoto::findOrFail($photoId);
        Storage::disk('public')->delete($photo->photo_url);
        $photo->delete();
        $this->existingPhotos = array_filter(
            $this->existingPhotos,
            fn($p) => $p['id'] !== $photoId
        );
    }

    public function save()
    {
        $this->validate([
            'menu_category_id' => 'required|exists:menu_categories,id',
            'item_name'        => 'required|string|max:100',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0',
            'photos'           => 'nullable|array',
            'photos.*'         => 'image|max:2048',
        ], [
            'menu_category_id.required' => 'Pilih kategori menu.',
        ]);

        // Cek total foto tidak melebihi 4
        $totalPhotos = count($this->existingPhotos) + count($this->photos);
        if ($totalPhotos > 4) {
            $this->addError('photos', 'Total foto maksimal 4 (sudah ada ' . count($this->existingPhotos) . ').');
            return;
        }

        if ($this->isEdit) {
            $this->menu->update([
                'menu_category_id' => $this->menu_category_id,
                'item_name'        => $this->item_name,
                'description'      => $this->description,
                'price'            => $this->price,
                'is_available'     => $this->is_available,
            ]);
            $menuId = $this->menu->id;
        } else {
            $newMenu = Menu::create([
                'menu_category_id' => $this->menu_category_id,
                'item_name'        => $this->item_name,
                'description'      => $this->description,
                'price'            => $this->price,
                'is_available'     => $this->is_available,
            ]);
            $menuId = $newMenu->id;
        }

        // Simpan foto baru
        foreach ($this->photos as $photo) {
            $path = $photo->store('menus/photos', 'public');
            MenuPhoto::create([
                'menu_id'   => $menuId,
                'photo_url' => $path,
            ]);
        }

        return redirect()->route('tenant.menu.index');
    }

    public function render()
    {
        $categories = MenuCategory::where('tenant_id', $this->tenant->id)->get();

        return view('livewire.tenant.menu.menu-form', compact('categories'))
            ->layout('layouts.app', ['title' => $this->isEdit ? 'Edit Menu' : 'Tambah Menu']);
    }

    public function removeNewPhoto(int $index)
    {
        array_splice($this->photos, $index, 1);
    }

    public function updatedNewPhoto()
    {
        $this->validate([
            'newPhoto' => 'image|max:2048',
        ]);

        $totalPhotos = count($this->existingPhotos) + count($this->photos);
        if ($totalPhotos >= 4) {
            $this->addError('newPhoto', 'Maksimal 4 foto.');
            $this->newPhoto = null;
            return;
        }

        $this->photos[] = $this->newPhoto;
        $this->newPhoto = null;
    }
}