<?php

namespace App\Livewire\Seller;

use App\Models\Seller;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.seller-bare')]
class Login extends Component
{
    public string $mode = 'oauth'; // oauth | email | forgot
    public int $step = 1;

    public string $email = '';
    public string $password = '';
    public string $forgotEmail = '';

    public function switchMode(string $mode, int $step = 1): void
    {
        $this->mode = $mode;
        $this->step = $step;
        $this->resetErrorBag();
    }

    public function nextStep(): void
    {
        $this->step++;
    }

    public function login(): void
    {
        $this->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (AuthFacade::guard('seller')->attempt(['email' => $this->email, 'password' => $this->password])) {
            session()->regenerate();
            $this->redirect(route('seller.dashboard'), navigate: true);
        } else {
            $this->addError('password', 'These credentials do not match our records.');
        }
    }

    public function sendForgotLink(): void
    {
        $this->validate(['forgotEmail' => 'required|email']);
        // Password reset email dispatched via standard Laravel password broker
        \Illuminate\Support\Facades\Password::broker('sellers')
            ->sendResetLink(['email' => $this->forgotEmail]);
        $this->step = 2;
    }

    public function render()
    {
        return view('livewire.seller.auth');
    }
}
