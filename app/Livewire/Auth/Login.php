<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|string|email',
        'password' => 'required|string',
    ];

    protected array $messages = [
        'email.required' => 'O e-mail corporativo é obrigatório.',
        'email.email' => 'Informe um e-mail corporativo válido.',
        'password.required' => 'A senha é obrigatória.',
    ];

    public function login()
    {
        $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            throw ValidationException::withMessages([
                'email' => 'Credenciais inválidas. Verifique seu e-mail e senha corporativos.',
            ]);
        }

        session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest', ['title' => 'Entrar na Plataforma — ALVORA']);
    }
}
