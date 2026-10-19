<?php

namespace App\Livewire\Company;

use App\Enums\UserRole;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EmployeeManager extends Component
{
    public string $emailsText = '';
    public string $role = 'student';
    public bool $showInviteModal = false;

    protected array $rules = [
        'emailsText' => 'required|string',
        'role' => 'required|in:student,instructor',
    ];

    protected array $messages = [
        'emailsText.required' => 'Informe ao menos um e-mail para enviar o convite.',
        'role.required' => 'Selecione o papel do colaborador.',
    ];

    public function inviteEmployees(): void
    {
        $this->validate();

        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            $this->dispatch('toast', message: 'Você precisa estar vinculado a uma empresa para convidar colaboradores.', type: 'error');
            return;
        }

        // D-08: Split emails by commas, semicolons or newlines
        $rawEmails = preg_split('/[\r\n,;]+/', $this->emailsText);
        $validEmails = [];
        $invalidEmails = [];

        foreach ($rawEmails as $raw) {
            $cleaned = trim($raw);
            if (empty($cleaned)) {
                continue;
            }
            if (filter_var($cleaned, FILTER_VALIDATE_EMAIL)) {
                $validEmails[] = strtolower($cleaned);
            } else {
                $invalidEmails[] = $cleaned;
            }
        }

        $validEmails = array_unique($validEmails);

        if (empty($validEmails)) {
            $this->addError('emailsText', 'Nenhum e-mail válido foi encontrado. Verifique o formato informado.');
            return;
        }

        $createdCount = 0;

        foreach ($validEmails as $email) {
            // Check if user already exists
            if (User::where('email', $email)->exists()) {
                continue;
            }

            // Invalidate or update previous pending invitation if any
            Invitation::where('company_id', $company->id)
                ->where('email', $email)
                ->whereNull('accepted_at')
                ->delete();

            // D-05 & D-06: 7-day expiration
            Invitation::create([
                'company_id' => $company->id,
                'email' => $email,
                'role' => $this->role,
                'token' => Invitation::generateToken(),
                'expires_at' => now()->addDays(7),
                'created_by' => $user->id,
            ]);

            $createdCount++;
        }

        $this->reset(['emailsText', 'showInviteModal']);
        $this->role = 'student';

        if ($createdCount > 0) {
            $this->dispatch('toast', 
                message: "{$createdCount} convite(s) gerado(s) com sucesso!", 
                type: 'success'
            );
        } else {
            $this->dispatch('toast', 
                message: 'Os e-mails informados já pertencem a usuários cadastrados.', 
                type: 'info'
            );
        }
    }

    public function resendInvitation(int $invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);

        // D-06: Renew token and extend validity for 7 more days
        $invitation->update([
            'token' => Invitation::generateToken(),
            'expires_at' => now()->addDays(7),
        ]);

        $this->dispatch('toast', message: "Convite para {$invitation->email} renovado por mais 7 dias!", type: 'success');
    }

    public function revokeInvitation(int $invitationId): void
    {
        $invitation = Invitation::findOrFail($invitationId);
        $email = $invitation->email;
        $invitation->delete();

        $this->dispatch('toast', message: "Convite para {$email} revogado.", type: 'info');
    }

    public function render()
    {
        $user = Auth::user();
        $company = $user->company;

        $employees = $company ? $company->users()->orderBy('name')->get() : collect();
        $pendingInvitations = $company ? Invitation::where('company_id', $company->id)
            ->whereNull('accepted_at')
            ->orderByDesc('created_at')
            ->get() : collect();

        return view('livewire.company.employee-manager', [
            'company' => $company,
            'employees' => $employees,
            'pendingInvitations' => $pendingInvitations,
        ])->layout('layouts.app', ['title' => 'Gestão de Colaboradores — ALVORA']);
    }
}
