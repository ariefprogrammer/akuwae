<?php

namespace App\Livewire\Customer;

use App\Models\SavedAddress;
use Livewire\Component;

class SavedAddresses extends Component
{
    public bool   $showForm   = false;
    public ?int   $editingId  = null;

    public string $label        = '';
    public string $address_text = '';
    public string $latitude     = '';
    public string $longitude    = '';

    public string $error = '';

    public function openAddForm()
    {
        $customer = auth()->user()->customer;

        if ($customer->savedAddresses()->count() >= 5) {
            $this->error = 'Maksimal 5 alamat tersimpan. Hapus salah satu untuk menambah yang baru.';
            return;
        }

        $this->resetForm();
        $this->showForm = true;
    }

    public function openEditForm(int $id)
    {
        $address = SavedAddress::findOrFail($id);

        if ($address->customer_id !== auth()->user()->customer->id) {
            abort(403);
        }

        $this->editingId    = $id;
        $this->label        = $address->label;
        $this->address_text = $address->address_text;
        $this->latitude     = (string) $address->latitude;
        $this->longitude    = (string) $address->longitude;
        $this->error        = '';
        $this->showForm     = true;
    }

    public function resetForm()
    {
        $this->editingId    = null;
        $this->label        = '';
        $this->address_text = '';
        $this->latitude     = '';
        $this->longitude    = '';
        $this->error        = '';
        $this->resetErrorBag();
    }

    public function closeForm()
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function save()
    {
        $this->error = '';

        $this->validate([
            'label'        => 'required|string|max:50',
            'address_text' => 'required|string',
            'latitude'     => 'required|numeric|between:-90,90',
            'longitude'    => 'required|numeric|between:-180,180',
        ], [
            'latitude.required'  => 'Pin lokasi di peta.',
            'longitude.required' => 'Pin lokasi di peta.',
        ]);

        $customer = auth()->user()->customer;

        if ($this->editingId) {
            $address = SavedAddress::findOrFail($this->editingId);
            if ($address->customer_id !== $customer->id) abort(403);

            $address->update([
                'label'        => $this->label,
                'address_text' => $this->address_text,
                'latitude'     => $this->latitude,
                'longitude'    => $this->longitude,
            ]);
        } else {
            if ($customer->savedAddresses()->count() >= 5) {
                $this->error = 'Maksimal 5 alamat tersimpan.';
                return;
            }

            SavedAddress::create([
                'customer_id'  => $customer->id,
                'label'        => $this->label,
                'address_text' => $this->address_text,
                'latitude'     => $this->latitude,
                'longitude'    => $this->longitude,
            ]);
        }

        $this->showForm = false;
        $this->resetForm();
    }

    public function delete(int $id)
    {
        $address = SavedAddress::findOrFail($id);

        if ($address->customer_id !== auth()->user()->customer->id) {
            abort(403);
        }

        $address->delete();
    }

    public function render()
    {
        $addresses = auth()->user()->customer->savedAddresses()->latest()->get();

        return view('livewire.customer.saved-addresses', compact('addresses'))
            ->layout('layouts.app', ['title' => 'Alamat Tersimpan']);
    }
}