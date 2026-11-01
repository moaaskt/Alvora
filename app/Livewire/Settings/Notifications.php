<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Notifications extends Component
{
    public bool $email_announcements = true;
    public bool $email_course_updates = true;
    public bool $email_assessments = true;

    public function savePreferences(): void
    {
        $this->dispatch('toast', type: 'success', title: 'Preferências salvas!', message: 'Suas preferências de notificações foram atualizadas.');
    }

    public function render()
    {
        $user = Auth::user();
        $layout = match ($user?->role?->value ?? '') {
            'admin_alvora' => 'layouts.admin',
            'student' => 'layouts.student',
            default => 'layouts.instructor',
        };

        return view('livewire.settings.notifications', [
            'user' => $user,
        ])->layout($layout, [
            'title' => 'Notificações — Configurações',
            'breadcrumb' => 'Configurações > Notificações',
        ]);
    }
}

