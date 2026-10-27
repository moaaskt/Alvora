<?php

namespace App\Services;

use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateVersion;
use App\Models\User;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CertificateTemplatePublishingService
{
    public function __construct(
        protected CertificateSchemaValidator $validator
    ) {}

    /**
     * Publish a template version transactionally with pessimistic locking.
     *
     * @param CertificateTemplate $template
     * @param CertificateTemplateVersion $version
     * @param User|null $user
     * @return CertificateTemplateVersion
     * @throws DomainException|AuthorizationException
     */
    public function publish(
        CertificateTemplate $template,
        CertificateTemplateVersion $version,
        ?User $user = null
    ): CertificateTemplateVersion {
        $this->authorizeTemplateAccess($template, $user);

        return DB::transaction(function () use ($template, $version) {
            // 1. Lock pessimista no template pai para serializar publicações concorrentes
            /** @var CertificateTemplate $lockedTemplate */
            $lockedTemplate = CertificateTemplate::withoutGlobalScopes()
                ->where('id', $template->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 2. Recarrega a versão candidata travando-a no contexto do template pai
            /** @var CertificateTemplateVersion $lockedVersion */
            $lockedVersion = $lockedTemplate->versions()
                ->where('id', $version->id)
                ->lockForUpdate()
                ->firstOrFail();

            // 3. Se já estiver ativa, operação idempotente
            if ($lockedVersion->status === 'active') {
                return $lockedVersion;
            }

            // 4. Confirma que a versão candidata está em estado 'draft'
            if ($lockedVersion->status !== 'draft') {
                throw new DomainException("Apenas versões em rascunho ('draft') podem ser publicadas.");
            }

            // 5. Revalida o schema integralmente no servidor antes da publicação
            $this->validator->validate($lockedVersion->schema_json ?? []);

            // 6. Arquiva versões ativas anteriores do mesmo template
            $lockedTemplate->versions()
                ->where('status', 'active')
                ->where('id', '!=', $lockedVersion->id)
                ->update(['status' => 'archived']);

            // 7. Ativa a versão candidata
            $lockedVersion->update(['status' => 'active']);

            // 8. Se for template corporativo de empresa, ativa o template e o define como padrão da empresa
            // Nota: NUNCA altera o template global da plataforma ALVORA (whereNull('company_id'))
            if (!empty($lockedTemplate->company_id)) {
                // Desmarca outros templates da MESMA empresa como default
                CertificateTemplate::withoutGlobalScopes()
                    ->where('company_id', $lockedTemplate->company_id)
                    ->where('id', '!=', $lockedTemplate->id)
                    ->update(['is_default' => false]);

                $lockedTemplate->update([
                    'status' => 'active',
                    'is_default' => true,
                ]);
            } else {
                // Template global publicado pelo super admin
                $lockedTemplate->update([
                    'status' => 'active',
                ]);
            }

            return $lockedVersion->fresh();
        });
    }

    /**
     * Create a new draft version for a template with serialized version numbering.
     *
     * @param CertificateTemplate $template
     * @param array<string, mixed> $schema
     * @param User|null $user
     * @return CertificateTemplateVersion
     * @throws DomainException|AuthorizationException
     */
    public function createDraft(
        CertificateTemplate $template,
        array $schema,
        ?User $user = null
    ): CertificateTemplateVersion {
        $this->authorizeTemplateAccess($template, $user);

        // Valida o schema antes de abrir a transação
        $validatedSchema = $this->validator->validate($schema);

        return DB::transaction(function () use ($template, $validatedSchema, $user) {
            // Trava o template pai para calcular com precisão o próximo número de versão
            /** @var CertificateTemplate $lockedTemplate */
            $lockedTemplate = CertificateTemplate::withoutGlobalScopes()
                ->where('id', $template->id)
                ->lockForUpdate()
                ->firstOrFail();

            $nextVersion = ((int) $lockedTemplate->versions()->max('version')) + 1;

            return CertificateTemplateVersion::create([
                'template_id' => $lockedTemplate->id,
                'version' => $nextVersion,
                'schema_json' => $validatedSchema,
                'status' => 'draft',
                'created_by' => $user?->id,
            ]);
        });
    }

    /**
     * Update an existing draft version schema safely.
     *
     * @param CertificateTemplate $template
     * @param CertificateTemplateVersion $version
     * @param array<string, mixed> $schema
     * @param User|null $user
     * @return CertificateTemplateVersion
     * @throws DomainException|AuthorizationException
     */
    public function updateDraft(
        CertificateTemplate $template,
        CertificateTemplateVersion $version,
        array $schema,
        ?User $user = null
    ): CertificateTemplateVersion {
        $this->authorizeTemplateAccess($template, $user);

        // Valida o schema antes da persistência
        $validatedSchema = $this->validator->validate($schema);

        return DB::transaction(function () use ($template, $version, $validatedSchema) {
            /** @var CertificateTemplate $lockedTemplate */
            $lockedTemplate = CertificateTemplate::withoutGlobalScopes()
                ->where('id', $template->id)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var CertificateTemplateVersion $lockedVersion */
            $lockedVersion = $lockedTemplate->versions()
                ->where('id', $version->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedVersion->status !== 'draft') {
                throw new DomainException("Apenas versões em rascunho ('draft') podem ser modificadas.");
            }

            if ($lockedVersion->certificates()->exists()) {
                throw new DomainException("Esta versão já possui certificados emitidos e não pode ser alterada.");
            }

            $lockedVersion->update([
                'schema_json' => $validatedSchema,
            ]);

            return $lockedVersion->fresh();
        });
    }

    /**
     * Authorize template ownership against the current user tenant.
     *
     * @throws AuthorizationException|ModelNotFoundException
     */
    public function authorizeTemplateAccess(CertificateTemplate $template, ?User $user): void
    {
        if (!$user) {
            throw new AuthorizationException('Usuário não autenticado.');
        }

        // Super Admin tem acesso global
        if ($user->isAlvoraAdmin()) {
            return;
        }

        // Empresas não podem gerenciar templates de outras empresas nem templates globais
        if (empty($template->company_id) || $template->company_id !== $user->company_id) {
            // Lança ModelNotFoundException para não revelar a existência do recurso de outro tenant
            throw new ModelNotFoundException('Template não encontrado para a sua empresa.');
        }
    }
}
