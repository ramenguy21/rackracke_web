<?php

namespace App\Livewire\Seller;

use Illuminate\Support\Facades\Auth as AuthFacade;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller', params: ['active' => 'Settings'])]
class Settings extends Component
{
    public string $shopName = '';
    public string $bio = '';
    public string $phone = '';
    public bool $saved = false;

    public function mount(): void
    {
        $seller = auth('seller')->user();
        $this->shopName = $seller->shop_name;
        $this->bio      = $seller->bio ?? '';
        $this->phone    = $seller->phone;
    }

    public function save(): void
    {
        $this->validate([
            'shopName' => 'required|string|max:60',
            'phone'    => 'required|string',
        ]);

        auth('seller')->user()->update([
            'shop_name' => $this->shopName,
            'bio'       => $this->bio,
            'phone'     => $this->phone,
        ]);

        $this->saved = true;
        $this->dispatch('saved');
    }

    public function logout(): void
    {
        AuthFacade::guard('seller')->logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect(route('seller.auth'), navigate: true);
    }

    public function render()
    {
        return view('livewire.seller.settings', [
            'seller' => auth('seller')->user(),
        ]);
    }
}
