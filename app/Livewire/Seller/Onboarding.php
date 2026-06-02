<?php

namespace App\Livewire\Seller;

use App\Models\Seller;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-bare')]
class Onboarding extends Component
{
    public int $step = 1;
    public int $totalSteps = 3;

    public string $shopName = '';
    public string $handle   = '';
    public string $city     = '';
    public string $phone    = '';
    public string $email    = '';
    public string $password = '';

    public function updatedShopName(string $value): void
    {
        $this->handle = '@' . Str::slug($value);
    }

    public function nextStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'shopName' => 'required|string|min:2|max:60|unique:sellers,shop_name',
            ]),
            2 => $this->validate([
                'city' => 'required|string',
            ]),
            default => null,
        };

        if ($this->step < $this->totalSteps) {
            $this->step++;
        } else {
            $this->finish();
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    private function finish(): void
    {
        $this->validate([
            'phone'    => 'required|string',
            'email'    => 'nullable|email|unique:sellers,email',
            'password' => 'required|min:8',
        ]);

        $seller = Seller::create([
            'shop_name' => $this->shopName,
            'phone'     => $this->phone,
            'email'     => $this->email ?: null,
            'password'  => Hash::make($this->password),
            'city'      => $this->city,
            'status'    => 'pending',
        ]);

        AuthFacade::guard('seller')->login($seller);
        $this->redirect(route('seller.dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.seller.onboarding');
    }
}
