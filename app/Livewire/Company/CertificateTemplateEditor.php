<?php

namespace App\Livewire\Company;

use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateVersion;
use App\Services\CertificateSchemaValidator;
use App\Services\CertificateTemplatePublishingService;
use App\Services\CertificateTemplateResolver;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CertificateTemplateEditor extends Component
{
    #[Locked]
    public int $templateId;

    #[Locked]
    public int $versionId;

    #[Locked]
    public int $versionNumber = 1;

    #[Locked]
    public string $versionStatus = 'draft';
    public string $templateName = '';

    /** @var array<string, mixed> */
    public array $schema = [];

    public ?string $selectedElementId = null;
    public ?string $validationError = null;
    public ?string $successMessage = null;

    public function mount(
        CertificateTemplate $template,
        ?CertificateTemplateVersion $version,
        CertificateTemplatePublishingService $publishingService,
        CertificateTemplateResolver $resolver
    ): void {
        $user = Auth::user();

        try {
            $publishingService->authorizeTemplateAccess($template, $user);
        } catch (ModelNotFoundException) {
            abort(404);
        } catch (AuthorizationException) {
            abort(403);
        }

        $this->templateId = $template->id;
        $this->templateName = $template->name;

        // Resolve a versão a ser carregada pertencente estritamente a este template
        if ($version && $version->exists) {
            $resolvedVersion = $template->versions()->where('id', $version->id)->first();
            if (!$resolvedVersion) {
                abort(404, 'Versão não encontrada para este template.');
            }
        } else {
            // Prioriza a versão em rascunho mais recente; se não houver, carrega a versão ativa; ou a última versão
            $resolvedVersion = $template->versions()->where('status', 'draft')->latest('version')->first()
                ?? $template->activeVersion
                ?? $template->versions()->latest('version')->first();
        }

        if (!$resolvedVersion) {
            $defaultSchema = $resolver->getDefaultSchema();
            $defaultSchema['name'] = $template->name;
            $resolvedVersion = $publishingService->createDraft($template, $defaultSchema, $user);
        }

        $this->loadVersionState($resolvedVersion);
    }

    /**
     * Load state from a validated CertificateTemplateVersion instance.
     */
    protected function loadVersionState(CertificateTemplateVersion $version): void
    {
        $this->versionId = $version->id;
        $this->versionNumber = (int) $version->version;
        $this->versionStatus = (string) $version->status;
        $this->schema = $version->schema_json ?? [];

        $elements = $this->schema['elements'] ?? [];
        if (!empty($elements) && is_array($elements)) {
            $firstId = $elements[0]['id'] ?? null;
            if (!$this->selectedElementId || !$this->findElementIndex($this->selectedElementId) !== null) {
                $this->selectedElementId = $firstId;
            }
        } else {
            $this->selectedElementId = null;
        }
    }

    /**
     * Resolve the authoritative template and version from the database under TenantScope & ownership checks.
     *
     * @return array{0: CertificateTemplate, 1: CertificateTemplateVersion}
     */
    protected function resolveAuthorizedContext(CertificateTemplatePublishingService $publishingService): array
    {
        $user = Auth::user();

        $template = CertificateTemplate::withoutGlobalScopes()->find($this->templateId);
        if (!$template) {
            abort(404, 'Template não encontrado.');
        }

        try {
            $publishingService->authorizeTemplateAccess($template, $user);
        } catch (ModelNotFoundException) {
            abort(404, 'Template não encontrado.');
        } catch (AuthorizationException) {
            abort(403, 'Acesso não autorizado.');
        }

        $version = $template->versions()->where('id', $this->versionId)->first();
        if (!$version) {
            abort(404, 'Versão não encontrada neste template.');
        }

        return [$template, $version];
    }

    /**
     * Switch between existing versions of the template.
     */
    public function selectVersion(int $targetVersionId, CertificateTemplatePublishingService $publishingService): void
    {
        $this->resetMessages();
        [$template] = $this->resolveAuthorizedContext($publishingService);

        $targetVersion = $template->versions()->where('id', $targetVersionId)->first();
        if (!$targetVersion) {
            abort(404, 'Versão não pertence a este template.');
        }

        $this->selectedElementId = null;
        $this->loadVersionState($targetVersion);
    }

    /**
     * Select an element on the canvas for property editing.
     */
    public function selectElement(string $elementId): void
    {
        $this->resetMessages();
        if ($this->findElementIndex($elementId) !== null) {
            $this->selectedElementId = $elementId;
        }
    }

    /**
     * Update an element's position and dimensions on the canvas (clamped to A4 1122x793).
     */
    public function updateElementPosition(string $elementId, int $x, int $y, ?int $width = null, ?int $height = null): void
    {
        $this->resetMessages();
        $index = $this->findElementIndex($elementId);
        if ($index === null) {
            return;
        }

        $currentWidth = $width !== null ? max(20, min(CertificateSchemaValidator::MAX_WIDTH, $width)) : (int) ($this->schema['elements'][$index]['width'] ?? 200);
        $currentHeight = $height !== null ? max(15, min(CertificateSchemaValidator::MAX_HEIGHT, $height)) : (int) ($this->schema['elements'][$index]['height'] ?? 40);

        $clampedX = max(0, min(CertificateSchemaValidator::MAX_WIDTH - $currentWidth, $x));
        $clampedY = max(0, min(CertificateSchemaValidator::MAX_HEIGHT - $currentHeight, $y));

        $this->schema['elements'][$index]['x'] = $clampedX;
        $this->schema['elements'][$index]['y'] = $clampedY;
        $this->schema['elements'][$index]['width'] = $currentWidth;
        $this->schema['elements'][$index]['height'] = $currentHeight;
        $this->selectedElementId = $elementId;
    }

    /**
     * Add a new element of a supported type to the canvas.
     */
    public function addElement(string $type = 'text'): void
    {
        $this->resetMessages();

        if (!in_array($type, CertificateSchemaValidator::ALLOWED_ELEMENT_TYPES, true)) {
            $this->validationError = "Tipo de elemento não suportado pelo schema: '{$type}'.";
            return;
        }

        $uniqueSuffix = substr(uniqid(), -5);
        $newId = $type . '_' . $uniqueSuffix;

        $defaultsByType = [
            'text' => [
                'id' => $newId,
                'type' => 'text',
                'x' => 160,
                'y' => 320,
                'width' => 800,
                'height' => 45,
                'content' => 'Texto Institucional Personalizado',
                'style' => [
                    'font_family' => 'Helvetica',
                    'font_size' => '16px',
                    'font_weight' => 'normal',
                    'color' => '#ffffff',
                    'text_align' => 'center',
                ],
            ],
            'signature_group' => [
                'id' => $newId,
                'type' => 'signature_group',
                'x' => 430,
                'y' => 520,
                'width' => 260,
                'height' => 60,
                'content' => "Diretoria Executiva\n{{company_name}}",
                'style' => [
                    'font_family' => 'Helvetica',
                    'font_size' => '11px',
                    'font_weight' => 'bold',
                    'color' => '#94a3b8',
                    'text_align' => 'center',
                    'border_color' => '#475569',
                    'border_width' => 1,
                    'border_style' => 'solid',
                    'padding' => 6,
                ],
            ],
            'auth_badge' => [
                'id' => $newId,
                'type' => 'auth_badge',
                'x' => 680,
                'y' => 660,
                'width' => 380,
                'height' => 45,
                'content' => "Código: {{validation_code}}\nValidação: {{verification_url}}",
                'style' => [
                    'font_family' => 'monospace',
                    'font_size' => '10px',
                    'font_weight' => 'normal',
                    'color' => '#64748b',
                    'text_align' => 'right',
                ],
            ],
            'box' => [
                'id' => $newId,
                'type' => 'box',
                'x' => 100,
                'y' => 100,
                'width' => 300,
                'height' => 80,
                'content' => '',
                'style' => [
                    'background_color' => '#1e293b',
                    'border_color' => '#6366f1',
                    'border_width' => 1,
                    'border_style' => 'solid',
                    'border_radius' => 8,
                    'padding' => 8,
                ],
            ],
            'line' => [
                'id' => $newId,
                'type' => 'line',
                'x' => 260,
                'y' => 480,
                'width' => 600,
                'height' => 4,
                'content' => '',
                'style' => [
                    'background_color' => '#475569',
                ],
            ],
        ];

        $newElement = $defaultsByType[$type];
        $this->schema['elements'][] = $newElement;
        $this->selectedElementId = $newId;
    }

    /**
     * Insert an authorized placeholder token into the currently selected element's content.
     */
    public function insertPlaceholder(string $placeholder): void
    {
        $this->resetMessages();

        if (!in_array($placeholder, CertificateSchemaValidator::ALLOWED_PLACEHOLDERS, true)) {
            $this->validationError = "Marcador dinâmico não autorizado: '{$placeholder}'.";
            return;
        }

        if (!$this->selectedElementId) {
            $this->validationError = 'Selecione um elemento no canvas para inserir a variável.';
            return;
        }

        $index = $this->findElementIndex($this->selectedElementId);
        if ($index === null) {
            return;
        }

        $currentContent = (string) ($this->schema['elements'][$index]['content'] ?? '');
        $this->schema['elements'][$index]['content'] = trim($currentContent . ' ' . $placeholder);
    }

    /**
     * Remove an element from the canvas (preventing empty elements collection).
     */
    public function removeElement(string $elementId): void
    {
        $this->resetMessages();

        $elements = $this->schema['elements'] ?? [];
        if (count($elements) <= 1) {
            $this->validationError = 'O certificado deve conter pelo menos um elemento no canvas.';
            return;
        }

        $filtered = array_values(array_filter($elements, fn ($el) => ($el['id'] ?? '') !== $elementId));
        $this->schema['elements'] = $filtered;

        if ($this->selectedElementId === $elementId) {
            $this->selectedElementId = $this->schema['elements'][0]['id'] ?? null;
        }
    }

    /**
     * Save current schema as a draft.
     * Only mutates the current version if it is in 'draft' status and has no issued certificates.
     */
    public function saveDraft(
        CertificateTemplatePublishingService $publishingService,
        CertificateSchemaValidator $validator
    ): void {
        $this->resetMessages();
        [$template, $version] = $this->resolveAuthorizedContext($publishingService);
        $user = Auth::user();

        try {
            $sanitizedSchema = $this->sanitizeAndNormalizeSchema($validator);
            $updatedVersion = $publishingService->updateDraft($template, $version, $sanitizedSchema, $user);

            $this->loadVersionState($updatedVersion);
            $this->successMessage = "Rascunho (v{$updatedVersion->version}) validado e salvo com sucesso.";
            $this->dispatch('toast', message: $this->successMessage, type: 'success');
        } catch (DomainException $e) {
            $this->validationError = $e->getMessage();
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    /**
     * Create a new draft version cloned from the current schema state.
     * Essential when the current version is already published ('active') or archived, preserving immutability.
     */
    public function createNewDraftFromCurrent(
        CertificateTemplatePublishingService $publishingService,
        CertificateSchemaValidator $validator
    ): void {
        $this->resetMessages();
        [$template] = $this->resolveAuthorizedContext($publishingService);
        $user = Auth::user();

        try {
            $sanitizedSchema = $this->sanitizeAndNormalizeSchema($validator);
            $newDraft = $publishingService->createDraft($template, $sanitizedSchema, $user);

            $this->loadVersionState($newDraft);
            $this->successMessage = "Nova versão de rascunho (v{$newDraft->version}) criada para edição segura.";
            $this->dispatch('toast', message: $this->successMessage, type: 'success');
        } catch (DomainException $e) {
            $this->validationError = $e->getMessage();
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    /**
     * Publish the current draft version via CertificateTemplatePublishingService.
     */
    public function publishVersion(
        CertificateTemplatePublishingService $publishingService,
        CertificateSchemaValidator $validator
    ): void {
        $this->resetMessages();
        [$template, $version] = $this->resolveAuthorizedContext($publishingService);
        $user = Auth::user();

        try {
            // Se estiver em draft, salva e valida as alterações pendentes no servidor antes de publicar
            if ($version->status === 'draft') {
                $sanitizedSchema = $this->sanitizeAndNormalizeSchema($validator);
                $version = $publishingService->updateDraft($template, $version, $sanitizedSchema, $user);
            }

            $publishedVersion = $publishingService->publish($template, $version, $user);
            $this->loadVersionState($publishedVersion);

            $this->successMessage = "Versão v{$publishedVersion->version} publicada com sucesso! Este modelo agora está ativo.";
            $this->dispatch('toast', message: $this->successMessage, type: 'success');
        } catch (DomainException $e) {
            $this->validationError = $e->getMessage();
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    /**
     * Normalize numeric inputs from Livewire wire:model bindings and run strict server-side schema validation.
     *
     * @return array<string, mixed>
     * @throws DomainException
     */
    protected function sanitizeAndNormalizeSchema(CertificateSchemaValidator $validator): array
    {
        $candidate = $this->schema;

        // Garante as dimensões A4 Landscape exigidas pelo backend
        $candidate['dimensions'] = [
            'width' => CertificateSchemaValidator::MAX_WIDTH,
            'height' => CertificateSchemaValidator::MAX_HEIGHT,
            'orientation' => 'landscape',
            'format' => 'a4',
        ];

        if (isset($candidate['border']) && is_array($candidate['border'])) {
            if (isset($candidate['border']['width'])) {
                $candidate['border']['width'] = (int) $candidate['border']['width'];
            }
            if (isset($candidate['border']['padding'])) {
                $candidate['border']['padding'] = (int) $candidate['border']['padding'];
            }
        }

        if (isset($candidate['elements']) && is_array($candidate['elements'])) {
            foreach ($candidate['elements'] as $idx => $el) {
                if (!is_array($el)) {
                    continue;
                }
                $candidate['elements'][$idx]['x'] = (int) ($el['x'] ?? 0);
                $candidate['elements'][$idx]['y'] = (int) ($el['y'] ?? 0);
                $candidate['elements'][$idx]['width'] = (int) ($el['width'] ?? 100);
                $candidate['elements'][$idx]['height'] = (int) ($el['height'] ?? 40);

                if (isset($el['style']) && is_array($el['style'])) {
                    // Remove chaves de estilo vazias para não falhar na validação de formato
                    foreach ($el['style'] as $sKey => $sVal) {
                        if ($sVal === '' || $sVal === null) {
                            unset($candidate['elements'][$idx]['style'][$sKey]);
                        }
                    }
                    if (isset($candidate['elements'][$idx]['style']['border_width'])) {
                        $candidate['elements'][$idx]['style']['border_width'] = (int) $candidate['elements'][$idx]['style']['border_width'];
                    }
                    if (isset($candidate['elements'][$idx]['style']['padding'])) {
                        $candidate['elements'][$idx]['style']['padding'] = (int) $candidate['elements'][$idx]['style']['padding'];
                    }
                    if (isset($candidate['elements'][$idx]['style']['border_radius'])) {
                        $candidate['elements'][$idx]['style']['border_radius'] = (int) $candidate['elements'][$idx]['style']['border_radius'];
                    }
                }
            }
        }

        return $validator->validate($candidate);
    }

    /**
     * Find the array index of an element by its ID.
     */
    public function findElementIndex(?string $elementId): ?int
    {
        if (!$elementId || empty($this->schema['elements']) || !is_array($this->schema['elements'])) {
            return null;
        }

        foreach ($this->schema['elements'] as $idx => $el) {
            if (($el['id'] ?? null) === $elementId) {
                return (int) $idx;
            }
        }

        return null;
    }

    protected function resetMessages(): void
    {
        $this->validationError = null;
        $this->successMessage = null;
    }

    public function render(CertificateTemplatePublishingService $publishingService)
    {
        [$template, $currentVersion] = $this->resolveAuthorizedContext($publishingService);

        $versions = $template->versions()->orderByDesc('version')->get();
        $selectedIndex = $this->findElementIndex($this->selectedElementId);
        $isReadOnlyVersion = $this->versionStatus !== 'draft' || $currentVersion->certificates()->exists();

        return view('livewire.company.certificate-template-editor', [
            'template' => $template,
            'currentVersion' => $currentVersion,
            'versions' => $versions,
            'selectedIndex' => $selectedIndex,
            'isReadOnlyVersion' => $isReadOnlyVersion,
            'allowedFonts' => CertificateSchemaValidator::ALLOWED_FONTS,
            'allowedFontWeights' => CertificateSchemaValidator::ALLOWED_FONT_WEIGHTS,
            'allowedTextAligns' => CertificateSchemaValidator::ALLOWED_TEXT_ALIGNS,
            'allowedBorderStyles' => CertificateSchemaValidator::ALLOWED_BORDER_STYLES,
            'allowedPlaceholders' => CertificateSchemaValidator::ALLOWED_PLACEHOLDERS,
            'allowedElementTypes' => CertificateSchemaValidator::ALLOWED_ELEMENT_TYPES,
        ])->layout('layouts.instructor', [
            'title' => 'Editor Visual de Certificado — ' . $template->name,
            'breadcrumb' => 'Editor de Certificado',
        ]);
    }
}
