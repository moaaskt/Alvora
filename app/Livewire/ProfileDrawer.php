<?php

namespace App\Livewire;

use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileDrawer extends Component
{
    use WithFileUploads;

    public bool $isOpen = false;
    public string $activeTab = 'general'; // general, security, account

    // Read-only info
    public string $email = '';
    public string $roleLabel = '';
    public ?string $companyName = null;
    public string $memberSince = '';

    // Editable general profile fields
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

    // Password change fields
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    // Activity metrics
    public int $enrollmentsCount = 0;
    public int $certificatesCount = 0;
    public int $completedLessonsCount = 0;
    public int $coursesCount = 0;
    public int $studentsTrainedCount = 0;

    #[On('open-profile-drawer')]
    public function open(): void
    {
        $this->loadUserData();
        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->photo = null;
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->resetValidation();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetValidation();
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

        // Load role specific metrics
        if ($user->isStudent()) {
            $this->enrollmentsCount = $user->enrollments()->count();
            $this->certificatesCount = $user->certificates()->count();
            $this->completedLessonsCount = $user->lessonProgress()->where('is_completed', true)->count();
        } elseif ($user->isInstructor()) {
            $this->coursesCount = $user->courses()->count();
            $this->studentsTrainedCount = Enrollment::whereIn('course_id', $user->courses()->pluck('id'))
                ->distinct('user_id')
                ->count('user_id');
        }
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

        // Security check: Never allow email updates via profile drawer
        unset($validated['email']);

        $user->update($validated);
        $user->refresh();

        $this->dispatch('profile-updated', name: $user->name, avatarUrl: $user->avatar_url);
        $this->dispatch('toast', type: 'success', title: 'Perfil atualizado!', message: 'Suas informações foram salvas com sucesso.');
    }

    public function uploadPhoto(): void
    {
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
        return view('livewire.profile-drawer', [
            'user' => $user,
        ]);
    }
}

