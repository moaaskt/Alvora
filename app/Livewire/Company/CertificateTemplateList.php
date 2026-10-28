<?php

namespace App\Livewire\Company;

use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateVersion;
use App\Services\CertificateTemplateResolver;
use DomainException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CertificateTemplateList extends Component
{
    public string $search = '';
    public string $statusFilter = '';

    public bool $showCreateModal = false;
    public string $newTemplateName = '';
    public string $newTemplateDescription = '';
    public bool $cloneFromDefault = true;

    protected array $rules = [
        'newTemplateName' => 'required|string|min:3|max:255',
        'newTemplateDescription' => 'nullable|string|max:1000',
    ];

    protected array $messages = [
        'newTemplateName.required' => 'O nome do template é obrigatório.',
        'newTemplateName.min' => 'O nome deve ter no mínimo 3 caracteres.',
        'newTemplateName.max' => 'O nome não pode exceder 255 caracteres.',
    ];

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['newTemplateName', 'newTemplateDescription']);
        $this->cloneFromDefault = true;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    /**
     * Create a new corporate template for the authenticated company.
     */
    public function createTemplate(CertificateTemplateResolver $resolver): void
    {
        $this->validate();

        $user = Auth::user();
        if (!$user) {
            $this->addError('newTemplateName', 'Usuário não autenticado.');
            return;
        }

        $companyId = $user->company_id;
        if (empty($companyId) && !$user->isAlvoraAdmin()) {
            $this->addError('newTemplateName', 'Apenas usuários vinculados a uma empresa corporativa podem criar templates.');
            $this->dispatch('toast', message: 'Usuário sem vínculo corporativo ativo.', type: 'error');
            return;
        }

        try {
            $template = DB::transaction(function () use ($user, $companyId, $resolver) {
                // Obtém o schema inicial a partir do padrão institucional da ALVORA
                $initialSchema = $resolver->getDefaultSchema();
                $initialSchema['name'] = $this->newTemplateName;

                // Cria o template sob o TenantScope (injetando automaticamente company_id)
                $template = CertificateTemplate::create([
                    'company_id' => $companyId,
                    'name' => $this->newTemplateName,
                    'description' => $this->newTemplateDescription ?: null,
                    'status' => 'draft',
                    'is_default' => false,
                ]);

                // Cria a versão 1 em estado draft
                CertificateTemplateVersion::create([
                    'template_id' => $template->id,
                    'version' => 1,
                    'schema_json' => $initialSchema,
                    'status' => 'draft',
                    'created_by' => $user->id,
                ]);

                return $template;
            });

            $this->showCreateModal = false;
            $this->reset(['newTemplateName', 'newTemplateDescription']);

            session()->flash('success', "Template '{$template->name}' criado com sucesso em modo rascunho.");
            $this->dispatch('template-created', templateId: $template->id);
            $this->dispatch('toast', message: "Template corporativo '{$template->name}' criado com sucesso.", type: 'success');
        } catch (\Throwable $e) {
            $this->addError('newTemplateName', 'Falha ao salvar template: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Erro ao criar template.', type: 'error');
        }
    }

    /**
     * Duplicate an existing corporate template as a new independent draft template.
     */
    public function duplicateTemplate(int $templateId): void
    {
        $user = Auth::user();
        if (!$user) {
            return;
        }

        $sourceTemplate = CertificateTemplate::findOrFail($templateId);

        // Autoriza acesso de tenant
        if (!$user->isAlvoraAdmin() && $sourceTemplate->company_id !== $user->company_id) {
            $this->dispatch('toast', message: 'Acesso não autorizado.', type: 'error');
            return;
        }

        // Obtém o schema da versão ativa ou da versão mais recente
        $sourceVersion = $sourceTemplate->activeVersion
            ?? $sourceTemplate->versions()->orderByDesc('version')->first();

        $schema = $sourceVersion ? $sourceVersion->schema_json : app(CertificateTemplateResolver::class)->getDefaultSchema();
        $newName = "Cópia de " . $sourceTemplate->name;
        $schema['name'] = $newName;

        try {
            $newTemplate = DB::transaction(function () use ($user, $sourceTemplate, $newName, $schema) {
                $newTemplate = CertificateTemplate::create([
                    'company_id' => $sourceTemplate->company_id,
                    'name' => $newName,
                    'description' => $sourceTemplate->description ? "{$sourceTemplate->description} (Duplicado)" : null,
                    'status' => 'draft',
                    'is_default' => false,
                ]);

                CertificateTemplateVersion::create([
                    'template_id' => $newTemplate->id,
                    'version' => 1,
                    'schema_json' => $schema,
                    'status' => 'draft',
                    'created_by' => $user->id,
                ]);

                return $newTemplate;
            });

            session()->flash('success', "Template duplicado com sucesso como '{$newTemplate->name}'.");
            $this->dispatch('template-created', templateId: $newTemplate->id);
            $this->dispatch('toast', message: "Template duplicado com sucesso.", type: 'success');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Erro ao duplicar template: ' . $e->getMessage(), type: 'error');
        }
    }

    /**
     * Set a template as the default for this company.
     */
    public function setAsDefault(int $templateId): void
    {
        $template = CertificateTemplate::findOrFail($templateId);

        if (!$template->activeVersion) {
            session()->flash('warning', 'O template precisa ter uma versão publicada para ser definido como padrão.');
            return;
        }

        // Desmarca outros templates da mesma empresa
        CertificateTemplate::where('company_id', $template->company_id)
            ->where('id', '!=', $template->id)
            ->update(['is_default' => false]);

        $template->update([
            'status' => 'active',
            'is_default' => true,
        ]);

        session()->flash('success', "O template '{$template->name}' agora é o modelo padrão de certificados da sua empresa.");
        $this->dispatch('toast', message: 'Template padrão atualizado.', type: 'success');
    }

    /**
     * Archive an existing corporate template.
     */
    public function archiveTemplate(int $templateId): void
    {
        $template = CertificateTemplate::findOrFail($templateId);

        $template->update([
            'status' => 'archived',
            'is_default' => false,
        ]);

        session()->flash('info', "O template '{$template->name}' foi arquivado.");
        $this->dispatch('toast', message: 'Template arquivado com sucesso.', type: 'info');
    }

    public function render(CertificateTemplateResolver $resolver)
    {
        $user = Auth::user();
        $companyId = $user?->company_id;

        // 1. Templates da empresa do usuário (escopados rigorosamente pelo TenantScope)
        $templatesQuery = CertificateTemplate::with(['versions', 'activeVersion'])
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->latest('id');

        $templates = $templatesQuery->get();

        // 2. Resolução do template e versão atualmente utilizados pela empresa
        $activeVersionInUse = $resolver->resolveActiveTemplate($companyId);
        $templateInUse = $activeVersionInUse?->template;
        $isUsingGlobalDefault = $templateInUse && empty($templateInUse->company_id);

        // 3. Objeto do template padrão global ALVORA (apenas para exibição institucional informativa)
        $globalTemplate = CertificateTemplate::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->whereNull('company_id')
            ->where('is_default', true)
            ->first();

        return view('livewire.company.certificate-template-list', [
            'templates' => $templates,
            'activeVersionInUse' => $activeVersionInUse,
            'templateInUse' => $templateInUse,
            'isUsingGlobalDefault' => $isUsingGlobalDefault,
            'globalTemplate' => $globalTemplate,
        ])->layout('layouts.instructor', [
            'title' => 'Gestão de Templates de Certificados — ALVORA',
            'breadcrumb' => 'Templates de Certificados',
        ]);
    }
}
