<?php

namespace App\Livewire\Tenant\Menu;

use App\Models\MenuCategory;
use App\Models\Tenant;
use Livewire\Component;

class MenuIndex extends Component
{
    public ?Tenant $tenant = null;

    // State modal kategori
    public bool   $showCategoryModal = false;
    public string $category_name     = '';
    public ?int   $editCategoryId    = null;
    public string $categoryError     = '';

    public function mount()
    {
        $this->tenant = auth()->user()->tenant;

        if (!$this->tenant || $this->tenant->verification_status !== 'approved') {
            return redirect()->route('tenant.dashboard');
        }
    }

    // ── Kategori ─────────────────────────────────────────────

    public function openAddCategory()
    {
        $this->editCategoryId = null;
        $this->category_name  = '';
        $this->categoryError  = '';
        $this->showCategoryModal = true;
    }

    public function openEditCategory(int $id)
    {
        $cat = MenuCategory::findOrFail($id);
        $this->editCategoryId = $id;
        $this->category_name  = $cat->category_name;
        $this->categoryError  = '';
        $this->showCategoryModal = true;
    }

    public function saveCategory()
    {
        $this->categoryError = '';
        $this->validate([
            'category_name' => 'required|string|max:50',
        ]);

        if ($this->editCategoryId) {
            MenuCategory::findOrFail($this->editCategoryId)
                ->update(['category_name' => $this->category_name]);
        } else {
            MenuCategory::create([
                'tenant_id'     => $this->tenant->id,
                'category_name' => $this->category_name,
            ]);
        }

        $this->showCategoryModal = false;
        $this->category_name     = '';
        $this->editCategoryId    = null;
    }

    public function deleteCategory(int $id)
    {
        MenuCategory::findOrFail($id)->delete();
    }

    // ── Item Menu ─────────────────────────────────────────────

    public function toggleAvailable(int $menuId)
    {
        $menu = \App\Models\Menu::findOrFail($menuId);
        $menu->update(['is_available' => !$menu->is_available]);
    }

    public function deleteMenu(int $menuId)
    {
        \App\Models\Menu::findOrFail($menuId)->delete();
    }

    public function render()
    {
        $categories = MenuCategory::where('tenant_id', $this->tenant->id)
            ->with(['menus.photos'])
            ->orderBy('id')
            ->get();

        return view('livewire.tenant.menu.menu-index', compact('categories'))
            ->layout('layouts.app', ['title' => 'Menu Saya']);
    }
}