<?php

declare(strict_types=1);

namespace App\Livewire\Auth;

use App\Livewire\Forms\Auth\LoginForm;
use App\Support\Toast;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
final class Login extends Component
{
    public LoginForm $form;

    public function login()
    {
        $this->form->validate();

        $this->form->authenticate();

        Session::regenerate();

        Toast::success('Berhasil masuk ke sistem MERCY.');

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
