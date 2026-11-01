<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Security extends Component
{
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Informe sua senha atual.',
            'new_password.required' => 'Digite a nova senha.',
            'new_password.min' => 'A nova senha deve ter no mínimo 8 caracteres.',
            'new_password.confirmed' => 'A confirmação da nova senha não confere.',
        ]);

        $user = Auth::user();
        if (!$user) {
            return;
        }

        if (!Hash::check($this->current_password, $user->password)) {
            $this->addError('current_password', 'A senha atual informada está incorreta.');
            return;
        }

        $user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('toast', type: 'success', title: 'Senha alterada!', message: 'Sua senha foi atualizada com sucesso.');
    }

    public function render()
    {
        $user = Auth::user();
        $layout = match ($user?->role?->value ?? '') {
            'admin_alvora' => 'layouts.admin',
            'student' => 'layouts.student',
            default => 'layouts.instructor',
        };

        return view('livewire.settings.security', [
            'user' => $user,
        ])->layout($layout, [
            'title' => 'Segurança e Senha — Configurações',
            'breadcrumb' => 'Configurações > Segurança e Senha',
        ]);
    }
}

