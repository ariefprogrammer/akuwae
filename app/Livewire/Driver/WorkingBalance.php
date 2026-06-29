<?php

namespace App\Livewire\Driver;

use App\Models\WorkingBalance as WorkingBalanceModel;
use App\Models\WorkingBalanceTopupRequest;
use Livewire\Component;
use Livewire\WithFileUploads;

class WorkingBalance extends Component
{
    use WithFileUploads;

    public $balance;
    public $transactions;
    public $topupRequests;

    public bool   $showTopupForm = false;
    public string $topupAmount   = '';
    public        $proofPhoto    = null;
    public string $error         = '';
    public string $success       = '';

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $wb = WorkingBalanceModel::getOrCreateFor(auth()->id());
        $this->balance       = $wb->balance;
        $this->transactions  = $wb->transactions()->take(20)->get();
        $this->topupRequests = WorkingBalanceTopupRequest::where('user_id', auth()->id())
            ->latest()->take(10)->get();
    }

    public function submitTopup()
    {
        $this->error = '';

        $this->validate([
            'topupAmount' => 'required|numeric|min:10000',
            'proofPhoto'  => 'nullable|image|max:2048',
        ], [
            'topupAmount.min' => 'Minimal top up Rp 10.000.',
        ]);

        $photoPath = null;
        if ($this->proofPhoto) {
            $photoPath = $this->proofPhoto->store('working-balance/proofs', 'public');
        }

        WorkingBalanceTopupRequest::create([
            'user_id'     => auth()->id(),
            'amount'      => $this->topupAmount,
            'proof_photo' => $photoPath,
            'status'      => 'pending',
        ]);

        $this->success = 'Permintaan top up terkirim, menunggu konfirmasi admin.';
        $this->showTopupForm = false;
        $this->topupAmount   = '';
        $this->proofPhoto    = null;
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.driver.working-balance')
            ->layout('layouts.app', ['title' => 'Saldo Kerja']);
    }
}