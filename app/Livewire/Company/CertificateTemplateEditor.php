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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CertificateTemplateEditor extends Component
{
    use WithFileUploads;

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

    /** @var mixed Upload temporário da arte de fundo */
    public $backgroundImage;

    /** @var bool Alternância de exibição de dados de exemplo no canvas */
    public bool $showPreviewData = false;

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

    /**
     * Upload a background artwork image for the current template version draft.
     */
    public function uploadBackgroundImage(CertificateTemplatePublishingService $publishingService): void
    {
        $this->resetMessages();
        [$template, $version] = $this->resolveAuthorizedContext($publishingService);

        if ($this->versionStatus !== 'draft' || $version->certificates()->exists()) {
            $this->validationError = 'Não é possível alterar a arte de fundo de uma versão publicada ou vinculada a certificados já emitidos. Crie um novo rascunho.';
            $this->backgroundImage = null;
            return;
        }

        $this->validate([
            'backgroundImage' => [
                'required',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:5120', // 5MB máx
                'dimensions:min_width=800,min_height=500,max_width=4000,max_height=4000',
            ],
        ], [
            'backgroundImage.required' => 'Selecione uma imagem de fundo para enviar.',
            'backgroundImage.image' => 'O arquivo enviado deve ser uma imagem válida.',
            'backgroundImage.mimes' => 'A arte de fundo deve estar no formato PNG, JPG/JPEG ou WebP.',
            'backgroundImage.max' => 'O arquivo da imagem de fundo não pode exceder 5MB.',
            'backgroundImage.dimensions' => 'As dimensões da imagem devem ser de no mínimo 800x500px e no máximo 4000x4000px.',
        ]);

        $ext = strtolower($this->backgroundImage->getClientOriginalExtension());
        if (!in_array($ext, ['png', 'jpg', 'jpeg', 'webp'], true)) {
            $ext = 'png';
        }

        $tenantFolder = $template->company_id ? "tenant-{$template->company_id}" : "platform";
        $fileName = "bg_{$template->id}_" . Str::random(16) . ".{$ext}";
        $storedPath = $this->backgroundImage->storeAs("certificate-backgrounds/{$tenantFolder}", $fileName, 'public');

        if (!$storedPath) {
            $this->validationError = 'Falha ao salvar a imagem no armazenamento do servidor.';
            return;
        }

        // Atualiza o schema com o caminho relativo gerado pelo servidor
        $this->schema['background']['image_path'] = $storedPath;

        // Ao carregar arte de fundo personalizada, zera a borda CSS padrão para não conflitar com a moldura gráfica
        if (isset($this->schema['border'])) {
            $this->schema['border']['style'] = 'none';
            $this->schema['border']['width'] = 0;
        }

        $this->backgroundImage = null;
        $this->successMessage = 'Arte de fundo carregada com sucesso! Posicione as caixas de dados dinâmicos sobre a arte.';
        $this->dispatch('toast', message: 'Arte de fundo atualizada com sucesso.', type: 'success');
    }

    /**
     * Remove the background image reference from the current draft schema.
     * Preserves historic binary files for issued certificates and previous versions.
     */
    public function removeBackgroundImage(CertificateTemplatePublishingService $publishingService): void
    {
        $this->resetMessages();
        [$template, $version] = $this->resolveAuthorizedContext($publishingService);

        if ($this->versionStatus !== 'draft' || $version->certificates()->exists()) {
            $this->validationError = 'Não é possível alterar uma versão publicada. Crie um novo rascunho.';
            return;
        }

        if (isset($this->schema['background']['image_path'])) {
            unset($this->schema['background']['image_path']);
            $this->successMessage = 'Arte de fundo removida do rascunho atual.';
            $this->dispatch('toast', message: 'Arte de fundo removida.', type: 'info');
        }
    }

    /**
     * Add a pre-configured functional dynamic field box to the canvas.
     */
    public function addDynamicField(string $fieldKey): void
    {
        $this->resetMessages();

        $uniqueSuffix = substr(uniqid(), -5);
        $newId = 'field_' . $uniqueSuffix;

        $presets = [
            'student_name' => [
                'id' => $newId,
                'type' => 'text',
                'x' => 160,
                'y' => 310,
                'width' => 800,
                'height' => 50,
                'content' => '{{student_name}}',
                'style' => [
                    'font_family' => 'Helvetica',
                    'font_size' => '28px',
                    'font_weight' => 'bold',
                    'color' => '#ffffff',
                    'text_align' => 'center',
                ],
            ],
            'course_title' => [
                'id' => $newId,
                'type' => 'text',
                'x' => 160,
                'y' => 380,
                'width' => 800,
                'height' => 45,
                'content' => 'concluiu com êxito o treinamento corporativo de {{course_title}}',
                'style' => [
                    'font_family' => 'Helvetica',
                    'font_size' => '18px',
                    'font_weight' => 'normal',
                    'color' => '#cbd5e1',
                    'text_align' => 'center',
                ],
            ],
            'company_name' => [
                'id' => $newId,
                'type' => 'text',
                'x' => 160,
                'y' => 435,
                'width' => 800,
                'height' => 35,
                'content' => 'promovido pela organização {{company_name}}',
                'style' => [
                    'font_family' => 'Helvetica',
                    'font_size' => '14px',
                    'font_weight' => 'normal',
                    'color' => '#94a3b8',
                    'text_align' => 'center',
                ],
            ],
            'workload' => [
                'id' => $newId,
                'type' => 'text',
                'x' => 360,
                'y' => 475,
                'width' => 400,
                'height' => 30,
                'content' => 'Carga Horária Total: {{workload}}',
                'style' => [
                    'font_family' => 'Helvetica',
                    'font_size' => '13px',
                    'font_weight' => '500',
                    'color' => '#64748b',
                    'text_align' => 'center',
                ],
            ],
            'issued_date' => [
                'id' => $newId,
                'type' => 'text',
                'x' => 100,
                'y' => 670,
                'width' => 300,
                'height' => 30,
                'content' => 'Emitido em: {{issued_date}}',
                'style' => [
                    'font_family' => 'Helvetica',
                    'font_size' => '11px',
                    'font_weight' => 'normal',
                    'color' => '#64748b',
                    'text_align' => 'left',
                ],
            ],
            'validation_code' => [
                'id' => $newId,
                'type' => 'text',
                'x' => 720,
                'y' => 670,
                'width' => 300,
                'height' => 30,
                'content' => 'Código: {{validation_code}}',
                'style' => [
                    'font_family' => 'monospace',
                    'font_size' => '11px',
                    'font_weight' => 'bold',
                    'color' => '#64748b',
                    'text_align' => 'right',
                ],
            ],
            'verification_url' => [
                'id' => $newId,
                'type' => 'text',
                'x' => 200,
                'y' => 710,
                'width' => 720,
                'height' => 30,
                'content' => 'Verificação de autenticidade: {{verification_url}}',
                'style' => [
                    'font_family' => 'monospace',
                    'font_size' => '10px',
                    'font_weight' => 'normal',
                    'color' => '#475569',
                    'text_align' => 'center',
                ],
            ],
            'signature_group' => [
                'id' => $newId,
                'type' => 'signature_group',
                'x' => 430,
                'y' => 530,
                'width' => 260,
                'height' => 65,
                'content' => "Diretoria de Treinamento e Desenvolvimento\n{{company_name}}",
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
        ];

        if (!isset($presets[$fieldKey])) {
            $this->validationError = "Campo predefinido não encontrado: '{$fieldKey}'.";
            return;
        }

        $this->schema['elements'][] = $presets[$fieldKey];
        $this->selectedElementId = $newId;
        $this->dispatch('toast', message: 'Campo dinâmico adicionado ao modelo.', type: 'info');
    }

    /**
     * Toggle sample data display on canvas boxes.
     */
    public function togglePreviewData(): void
    {
        $this->showPreviewData = !$this->showPreviewData;
    }

    public function render(CertificateTemplatePublishingService $publishingService)
    {
        [$template, $currentVersion] = $this->resolveAuthorizedContext($publishingService);

        $versions = $template->versions()->orderByDesc('version')->get();
        $selectedIndex = $this->findElementIndex($this->selectedElementId);
        $isReadOnlyVersion = $this->versionStatus !== 'draft' || $currentVersion->certificates()->exists();

        $backgroundImagePath = $this->schema['background']['image_path'] ?? null;
        $backgroundImageUrl = null;
        if (!empty($backgroundImagePath) && Storage::disk('public')->exists($backgroundImagePath)) {
            $backgroundImageUrl = Storage::disk('public')->url($backgroundImagePath);
        }

        $demoReplacements = [
            '{{student_name}}' => 'Lucas Colaborador',
            '{{course_title}}' => 'Gestão Estratégica e Liderança Empresarial',
            '{{company_name}}' => $template->company?->name ?? 'ALVORA Educação Corporativa',
            '{{workload}}' => '40 horas',
            '{{issued_date}}' => date('d/m/Y'),
            '{{validation_code}}' => 'ALV-2026-EXEMPLO',
            '{{verification_url}}' => url('/validar-certificado/ALV-2026-EXEMPLO'),
        ];

        return view('livewire.company.certificate-template-editor', [
            'template' => $template,
            'currentVersion' => $currentVersion,
            'versions' => $versions,
            'selectedIndex' => $selectedIndex,
            'isReadOnlyVersion' => $isReadOnlyVersion,
            'backgroundImageUrl' => $backgroundImageUrl,
            'showPreviewData' => $this->showPreviewData,
            'demoReplacements' => $demoReplacements,
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
