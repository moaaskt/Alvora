<?php

namespace App\Livewire\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserManager extends Component
{
    use WithPagination;

    // Aba ativa: 'admin' | 'instructor' | 'student'
    public string $role = 'admin';

    // Filtros de busca
    public string $search = '';
    public string $companyFilter = 'all';
    public string $statusFilter = 'all';

    // Modais
    public bool $showUserModal = false;
    public ?int $editingUserId = null;

    // Campos do formulário de usuário
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $userRole = 'admin_alvora';
    public ?int $company_id = null;
    public string $password = '';
    public string $password_confirmation = '';
    public bool $is_active = true;

    // Modal de Redefinição de Senha
    public bool $showPasswordModal = false;
    public ?int $passwordUserId = null;
    public ?string $passwordUserName = null;
    public string $new_password = '';
    public string $new_password_confirmation = '';

    // Modal de Exclusão
    public bool $showDeleteModal = false;
    public ?int $deletingUserId = null;
    public ?string $deletingUserName = null;
    public bool $hasCertificatesWarning = false;
    public ?string $deleteErrorMessage = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'companyFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
    ];

    public function mount(?string $role = null): void
    {
        // Detecta papel a partir da rota ou parâmetro
        if (request()->routeIs('admin.users.admins') || $role === 'administradores' || $role === 'admin') {
            $this->role = 'admin';
        } elseif (request()->routeIs('admin.users.instructors') || $role === 'instrutores' || $role === 'instructor') {
            $this->role = 'instructor';
        } elseif (request()->routeIs('admin.users.students') || $role === 'estudantes' || $role === 'student') {
            $this->role = 'student';
        } else {
            $this->role = 'admin';
        }

        $this->userRole = $this->getDefaultRoleForTab();
    }

    public function switchRole(string $targetRole): void
    {
        if (in_array($targetRole, ['admin', 'instructor', 'student'])) {
            $this->role = $targetRole;
            $this->resetPage();
            $this->resetValidation();
            $this->userRole = $this->getDefaultRoleForTab();
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCompanyFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function getDefaultRoleForTab(): string
    {
        return match ($this->role) {
            'instructor' => UserRole::INSTRUCTOR->value,
            'student' => UserRole::STUDENT->value,
            default => UserRole::ADMIN_ALVORA->value,
        };
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->resetForm();
        $this->editingUserId = null;
        $this->userRole = $this->getDefaultRoleForTab();
        $this->is_active = true;
        $this->showUserModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $this->resetValidation();
        $this->resetForm();

        $user = User::withoutGlobalScopes()->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->userRole = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;
        $this->company_id = $user->company_id;
        $this->is_active = (bool) ($user->is_active ?? true);
        $this->password = '';
        $this->password_confirmation = '';

        $this->showUserModal = true;
    }

    public function closeUserModal(): void
    {
        $this->showUserModal = false;
        $this->editingUserId = null;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->password = '';
        $this->password_confirmation = '';
        $this->company_id = null;
        $this->is_active = true;
    }

    public function saveUser(): void
    {
        $rules = [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->editingUserId),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'userRole' => ['required', Rule::in(UserRole::values())],
            'company_id' => [
                $this->userRole === UserRole::ADMIN_ALVORA->value ? 'nullable' : 'required',
                'nullable',
                'exists:companies,id',
            ],
            'is_active' => ['boolean'],
        ];

        if ($this->editingUserId === null) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        }

        $this->validate($rules, [
            'name.required' => 'O nome completo é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 3 caracteres.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.unique' => 'Este e-mail já está cadastrado no sistema.',
            'company_id.required' => 'Selecione a empresa associada ao usuário.',
            'password.required' => 'A senha de acesso é obrigatória.',
            'password.min' => 'A senha deve conter no mínimo 8 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ]);

        $data = [
            'name' => trim($this->name),
            'email' => strtolower(trim($this->email)),
            'phone' => !empty($this->phone) ? trim($this->phone) : null,
            'role' => $this->userRole,
            'company_id' => $this->userRole === UserRole::ADMIN_ALVORA->value ? null : $this->company_id,
            'is_active' => $this->is_active,
        ];

        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->editingUserId) {
            $user = User::withoutGlobalScopes()->findOrFail($this->editingUserId);
            $user->update($data);
            session()->flash('message', "Usuário '{$user->name}' atualizado com sucesso!");
        } else {
            $data['password'] = Hash::make($this->password);
            $user = User::create($data);
            session()->flash('message', "Novo usuário '{$user->name}' criado com sucesso!");
        }

        $this->closeUserModal();
    }

    public function toggleStatus(int $userId): void
    {
        $user = User::withoutGlobalScopes()->findOrFail($userId);
        
        // Impede desativação de si mesmo
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Você não pode desativar seu próprio usuário administrador.');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusLabel = $user->is_active ? 'ativado' : 'desativado';
        session()->flash('message', "Usuário '{$user->name}' foi {$statusLabel} com sucesso.");
    }

    public function openPasswordModal(int $userId): void
    {
        $user = User::withoutGlobalScopes()->findOrFail($userId);
        $this->passwordUserId = $user->id;
        $this->passwordUserName = $user->name;
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->resetValidation();
        $this->showPasswordModal = true;
    }

    public function closePasswordModal(): void
    {
        $this->showPasswordModal = false;
        $this->passwordUserId = null;
        $this->passwordUserName = null;
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->resetValidation();
    }

    public function updatePassword(): void
    {
        $this->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'new_password.required' => 'A nova senha é obrigatória.',
            'new_password.min' => 'A senha deve conter no mínimo 8 caracteres.',
            'new_password.confirmed' => 'A confirmação de senha não confere.',
        ]);

        $user = User::withoutGlobalScopes()->findOrFail($this->passwordUserId);
        $user->password = Hash::make($this->new_password);
        $user->save();

        session()->flash('message', "Senha do usuário '{$user->name}' redefinida com sucesso!");
        $this->closePasswordModal();
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::withoutGlobalScopes()->with(['certificates', 'courses'])->findOrFail($userId);
        
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Você não pode excluir sua própria conta de administrador.');
            return;
        }

        $this->deletingUserId = $user->id;
        $this->deletingUserName = $user->name;
        $this->hasCertificatesWarning = $user->certificates()->exists();
        $this->deleteErrorMessage = null;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingUserId = null;
        $this->deletingUserName = null;
        $this->hasCertificatesWarning = false;
        $this->deleteErrorMessage = null;
    }

    public function deleteUser(): void
    {
        if (!$this->deletingUserId) {
            return;
        }

        $user = User::withoutGlobalScopes()->findOrFail($this->deletingUserId);

        if ($user->id === auth()->id()) {
            $this->deleteErrorMessage = 'Você não pode excluir sua própria conta.';
            return;
        }

        try {
            $userName = $user->name;
            $user->delete();
            session()->flash('message', "Usuário '{$userName}' excluído com sucesso.");
            $this->closeDeleteModal();
        } catch (\DomainException $e) {
            $this->deleteErrorMessage = $e->getMessage() . " Sugerimos desativar o usuário em vez de excluir.";
        } catch (\Throwable $e) {
            $this->deleteErrorMessage = "Não foi possível excluir o usuário devido a registros dependentes no sistema. Sugerimos desativá-lo.";
        }
    }

    public function exportCsv(): StreamedResponse
    {
        $query = $this->buildUserQuery();
        $users = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="alvora_usuarios_' . $this->role . '_' . date('Y-m-d') . '.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($users) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM para compatibilidade com Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Nome Completo',
                'E-mail',
                'Telefone',
                'Papel / Perfil',
                'Empresa / Tenant',
                'Status',
                'Qtd Cursos Criados',
                'Qtd Matrículas',
                'Data de Cadastro',
            ], ';');

            foreach ($users as $user) {
                $roleLabel = $user->role instanceof UserRole ? $user->role->label() : (string) $user->role;
                $companyName = $user->company?->name ?? 'Alvora Global HQ';
                $status = ($user->is_active ?? true) ? 'Ativo' : 'Inativo';

                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? '-',
                    $roleLabel,
                    $companyName,
                    $status,
                    $user->courses_count ?? 0,
                    $user->enrollments_count ?? 0,
                    $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-',
                ], ';');
            }

            fclose($handle);
        }, 200, $headers);
    }

    private function buildUserQuery()
    {
        $query = User::withoutGlobalScopes()
            ->with(['company'])
            ->withCount(['courses', 'enrollments']);

        // Filtro por papel conforme a aba ativa
        if ($this->role === 'admin') {
            $query->whereIn('role', [UserRole::ADMIN_ALVORA, UserRole::COMPANY_ADMIN]);
        } elseif ($this->role === 'instructor') {
            $query->where('role', UserRole::INSTRUCTOR);
        } elseif ($this->role === 'student') {
            $query->where('role', UserRole::STUDENT);
        }

        // Busca por nome, email ou telefone
        if (!empty($this->search)) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('phone', 'like', $term);
            });
        }

        // Filtro por Empresa
        if ($this->companyFilter !== 'all') {
            if ($this->companyFilter === 'global') {
                $query->whereNull('company_id');
            } else {
                $query->where('company_id', (int) $this->companyFilter);
            }
        }

        // Filtro por Status
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->latest('id');
    }

    public function render()
    {
        $users = $this->buildUserQuery()->paginate(15);

        // Contadores globais para as abas
        $adminCount = User::withoutGlobalScopes()->whereIn('role', [UserRole::ADMIN_ALVORA, UserRole::COMPANY_ADMIN])->count();
        $instructorCount = User::withoutGlobalScopes()->where('role', UserRole::INSTRUCTOR)->count();
        $studentCount = User::withoutGlobalScopes()->where('role', UserRole::STUDENT)->count();
        $totalUsers = User::withoutGlobalScopes()->count();

        $companies = Company::orderBy('name')->get();

        $roleTitle = match ($this->role) {
            'instructor' => 'Instrutores',
            'student' => 'Estudantes',
            default => 'Administradores',
        };

        return view('livewire.admin.user-manager', [
            'users' => $users,
            'adminCount' => $adminCount,
            'instructorCount' => $instructorCount,
            'studentCount' => $studentCount,
            'totalUsers' => $totalUsers,
            'companies' => $companies,
            'roleTitle' => $roleTitle,
        ])->layout('layouts.admin', [
            'title' => "Gestão de {$roleTitle} — ALVORA Super Admin",
            'breadcrumb' => "Usuários / {$roleTitle}",
        ]);
    }
}
