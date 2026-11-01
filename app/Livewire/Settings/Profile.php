<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    // Read-only info
    public string $email = '';
    public string $roleLabel = '';
    public ?string $companyName = null;
    public string $memberSince = '';

    // Editable profile fields
    public string $name = '';
    public ?string $phone = null;
    public ?string $job_title = null;
    public ?string $department = null;
    public ?string $bio = null;
    public ?string $linkedin_url = null;
    public ?string $facebook_url = null;
    public ?string $twitter_url = null;

    // Photo upload
    public $photo = null;

    public function updatedPhoto(): void
    {
        $this->uploadPhoto();
    }

    public function mount(): void
    {
        $this->loadUserData();
    }

    public function loadUserData(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = $user->phone;
        $this->job_title = $user->job_title;
        $this->department = $user->department;
        $this->bio = $user->bio;
        $this->linkedin_url = $user->linkedin_url;
        $this->facebook_url = $user->facebook_url;
        $this->twitter_url = $user->twitter_url;
        $this->companyName = $user->company?->name;
        $this->memberSince = $user->created_at ? $user->created_at->format('d/m/Y') : '—';

        $this->roleLabel = match ($user->role?->value ?? '') {
            'admin_alvora' => 'Super Admin Global',
            'company_admin' => 'Administrador da Empresa',
            'instructor' => 'Instrutor / Especialista',
            'student' => 'Aluno / Colaborador',
            default => 'Membro',
        };
    }

    public function saveProfile(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $rules = [
            'name' => 'required|string|min:3|max:255',
            'phone' => 'nullable|string|max:30',
        ];

        if ($user->isInstructor() || $user->isCompanyAdmin()) {
            $rules['job_title'] = 'nullable|string|max:120';
            $rules['department'] = 'nullable|string|max:100';
        }

        if ($user->isInstructor() || $user->isStudent()) {
            $rules['bio'] = 'nullable|string|max:2000';
            $rules['linkedin_url'] = 'nullable|url|max:255';
            $rules['facebook_url'] = 'nullable|url|max:255';
            $rules['twitter_url'] = 'nullable|url|max:255';
        }

        $validated = $this->validate($rules);

        // Security check: Never allow email updates
        unset($validated['email']);

        if ($this->photo) {
            $this->uploadPhoto();
        }

        $user->update($validated);
        $user->refresh();

        $this->dispatch('profile-updated', name: $user->name, avatarUrl: $user->avatar_url);
        $this->dispatch('toast', type: 'success', title: 'Perfil atualizado!', message: 'Suas informações foram salvas com sucesso.');
    }

    public function uploadPhoto(): void
    {
        if (!$this->photo) {
            return;
        }

        $this->validate([
            'photo' => 'required|image|max:3072|mimes:jpg,jpeg,png,webp',
        ]);

        $user = Auth::user();
        if (!$user) {
            return;
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $this->photo->store('avatars', 'public');
        $user->update(['avatar' => $path]);
        $this->photo = null;

        $user->refresh();

        $this->dispatch('profile-updated', name: $user->name, avatarUrl: $user->avatar_url);
        $this->dispatch('toast', type: 'success', title: 'Foto atualizada!', message: 'Sua foto de perfil foi alterada com sucesso.');
    }

    public function removePhoto(): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);
        $this->photo = null;

        $user->refresh();

        $this->dispatch('profile-updated', name: $user->name, avatarUrl: null);
        $this->dispatch('toast', type: 'info', title: 'Foto removida', message: 'Sua foto de perfil foi removida.');
    }

    public function render()
    {
        $user = Auth::user();
        $layout = match ($user?->role?->value ?? '') {
            'admin_alvora' => 'layouts.admin',
            'student' => 'layouts.student',
            default => 'layouts.instructor',
        };

        return view('livewire.settings.profile', [
            'user' => $user,
        ])->layout($layout, [
            'title' => 'Meu Perfil — Configurações',
            'breadcrumb' => 'Configurações > Meu Perfil',
        ]);
    }
}
