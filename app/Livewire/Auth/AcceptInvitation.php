<?php

namespace App\Livewire\Auth;

use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class AcceptInvitation extends Component
{
    public string $token = '';
    public ?Invitation $invitation = null;
    public ?Company $company = null;
    public bool $isInvalid = false;
    public string $errorMessage = '';

    public string $name = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected array $rules = [
        'name' => 'required|string|min:3|max:255',
        'password' => 'required|string|min:8|confirmed',
    ];

    protected array $messages = [
        'name.required' => 'O nome é obrigatório.',
        'name.min' => 'O nome deve ter no mínimo 3 caracteres.',
        'password.required' => 'A senha é obrigatória.',
        'password.min' => 'A senha deve conter no mínimo 8 caracteres.',
        'password.confirmed' => 'A confirmação de senha não confere.',
    ];

    public function mount(string $token): void
    {
        $this->token = $token;
        // Bypassing tenant scope because the guest user is not authenticated yet
        $this->invitation = Invitation::withoutGlobalScopes()->where('token', $token)->first();

        if (!$this->invitation || !$this->invitation->isValid()) {
            $this->isInvalid = true;
            $this->errorMessage = 'Este link de convite expirou ou já foi utilizado. Solicite um novo acesso ao gestor de RH da sua empresa.';
            return;
        }

        $this->company = Company::find($this->invitation->company_id);
    }

    public function accept()
    {
        if ($this->isInvalid || !$this->invitation || !$this->invitation->isValid()) {
            $this->addError('general', 'Este convite não é mais válido.');
            return;
        }

        $this->validate();

        $user = DB::transaction(function () {
            // Create user
            $newUser = User::create([
                'name' => $this->name,
                'email' => $this->invitation->email,
                'password' => Hash::make($this->password),
                'company_id' => $this->invitation->company_id,
                'role' => $this->invitation->role,
            ]);

            // Link in company_users
            DB::table('company_users')->insert([
                'company_id' => $this->invitation->company_id,
                'user_id' => $newUser->id,
                'role' => $this->invitation->role,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Mark invitation as accepted
            $this->invitation->update([
                'accepted_at' => now(),
            ]);

            return $newUser;
        });

        // Log in the user directly
        Auth::login($user);
        session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.auth.accept-invitation')->layout('layouts.guest', ['title' => 'Ativar Acesso — ALVORA']);
    }
}
