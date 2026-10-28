<?php

namespace App\Services;

use App\Models\CertificateTemplate;
use App\Models\CertificateTemplateVersion;
use App\Models\Scopes\TenantScope;
use Illuminate\Support\Facades\DB;

class CertificateTemplateResolver
{
    public function __construct(
        protected CertificateSchemaValidator $validator
    ) {}

    /**
     * Resolve the active template version for a company, falling back hierarchically to platform default.
     *
     * @param int|null $companyId
     * @return CertificateTemplateVersion|null
     */
    public function resolveActiveTemplate(?int $companyId = null): ?CertificateTemplateVersion
    {
        // 1. Tenta resolver template personalizado ativo do tenant (se houver companyId)
        if (!empty($companyId)) {
            $companyTemplate = CertificateTemplate::where('company_id', $companyId)
                ->where('status', 'active')
                ->whereHas('activeVersion')
                ->orderByDesc('is_default')
                ->first();

            if ($companyTemplate && $companyTemplate->activeVersion) {
                return $companyTemplate->activeVersion;
            }
        }

        // 2. Fallback: busca template global da plataforma ALVORA (company_id is null)
        $globalTemplate = CertificateTemplate::withoutGlobalScope(TenantScope::class)
            ->whereNull('company_id')
            ->where('status', 'active')
            ->whereHas('activeVersion')
            ->where('is_default', true)
            ->first();

        if ($globalTemplate && $globalTemplate->activeVersion) {
            return $globalTemplate->activeVersion;
        }

        // 3. Auto-recuperação idempotente: garante a existência do template padrão global
        return $this->ensureDefaultTemplate();
    }

    /**
     * Ensure the default global platform template and an active version exist in the database.
     * Concurrency-safe and idempotent under multiple simultaneous requests.
     */
    public function ensureDefaultTemplate(): CertificateTemplateVersion
    {
        return DB::transaction(function () {
            // 1. Localiza ou cria o template padrão institucional sob lock
            $template = CertificateTemplate::withoutGlobalScope(TenantScope::class)
                ->whereNull('company_id')
                ->where('is_default', true)
                ->lockForUpdate()
                ->first();

            if (!$template) {
                $template = CertificateTemplate::withoutGlobalScope(TenantScope::class)
                    ->whereNull('company_id')
                    ->where('name', 'Template Padrão Institucional ALVORA')
                    ->lockForUpdate()
                    ->first();
            }

            if (!$template) {
                $template = CertificateTemplate::withoutGlobalScope(TenantScope::class)->create([
                    'company_id' => null,
                    'name' => 'Template Padrão Institucional ALVORA',
                    'description' => 'Layout oficial corporativo em tema escuro com suporte dinâmico a Dompdf.',
                    'is_default' => true,
                    'status' => 'active',
                ]);
            } else {
                if ($template->status !== 'active' || !$template->is_default) {
                    $template->update([
                        'status' => 'active',
                        'is_default' => true,
                    ]);
                }
            }

            // 2. Busca versão ativa existente do template sob lock
            $activeVersion = $template->versions()
                ->where('status', 'active')
                ->orderByDesc('version')
                ->lockForUpdate()
                ->first();

            if ($activeVersion) {
                return $activeVersion;
            }

            // 3. Tratamento seguro quando não há versão ativa
            $existingVersionsCount = $template->versions()->count();

            if ($existingVersionsCount === 0) {
                $schema = $this->getDefaultSchema();
                $this->validator->validate($schema);

                return CertificateTemplateVersion::create([
                    'template_id' => $template->id,
                    'version' => 1,
                    'schema_json' => $schema,
                    'status' => 'active',
                    'created_by' => null,
                ]);
            }

            // Se existirem versões mas nenhuma ativa, preserva a imutabilidade das versões arquivadas
            // e cria a próxima versão numérica sequencial sem violar a chave única
            $nextVersionNumber = ((int) $template->versions()->max('version')) + 1;
            $schema = $this->getDefaultSchema();
            $this->validator->validate($schema);

            return CertificateTemplateVersion::create([
                'template_id' => $template->id,
                'version' => $nextVersionNumber,
                'schema_json' => $schema,
                'status' => 'active',
                'created_by' => null,
            ]);
        });
    }

    /**
     * Get the static, reproducible default institutional schema JSON definition.
     *
     * @return array<string, mixed>
     */
    public function getDefaultSchema(): array
    {
        return [
            'name' => 'Template Padrão Institucional ALVORA',
            'version' => '1.0.0',
            'dimensions' => [
                'width' => 1122,
                'height' => 793,
                'orientation' => 'landscape',
                'format' => 'a4',
            ],
            'background' => [
                'color' => '#0b0f19',
            ],
            'border' => [
                'color' => '#6366f1',
                'width' => 3,
                'style' => 'solid',
                'padding' => 10,
            ],
            'elements' => [
                [
                    'id' => 'brand_logo',
                    'type' => 'text',
                    'x' => 50,
                    'y' => 45,
                    'width' => 1022,
                    'height' => 35,
                    'content' => 'ALVORA',
                    'style' => [
                        'font_family' => 'Helvetica',
                        'font_size' => '24px',
                        'font_weight' => '800',
                        'color' => '#6366f1',
                        'text_align' => 'center',
                        'letter_spacing' => '4px',
                        'text_transform' => 'uppercase',
                    ],
                ],
                [
                    'id' => 'brand_subtitle',
                    'type' => 'text',
                    'x' => 50,
                    'y' => 85,
                    'width' => 1022,
                    'height' => 20,
                    'content' => 'Treinamento Corporativo com Inteligência Artificial • {{company_name}}',
                    'style' => [
                        'font_family' => 'Helvetica',
                        'font_size' => '11px',
                        'font_weight' => 'normal',
                        'color' => '#94a3b8',
                        'text_align' => 'center',
                        'letter_spacing' => '2px',
                        'text_transform' => 'uppercase',
                    ],
                ],
                [
                    'id' => 'certificate_title',
                    'type' => 'text',
                    'x' => 50,
                    'y' => 125,
                    'width' => 1022,
                    'height' => 40,
                    'content' => 'Certificado de Capacitação',
                    'style' => [
                        'font_family' => 'Helvetica',
                        'font_size' => '30px',
                        'font_weight' => 'bold',
                        'color' => '#ffffff',
                        'text_align' => 'center',
                        'letter_spacing' => '3px',
                        'text_transform' => 'uppercase',
                    ],
                ],
                [
                    'id' => 'certifies_label',
                    'type' => 'text',
                    'x' => 50,
                    'y' => 175,
                    'width' => 1022,
                    'height' => 20,
                    'content' => 'Certificamos com distinção que',
                    'style' => [
                        'font_family' => 'Helvetica',
                        'font_size' => '12px',
                        'font_weight' => 'normal',
                        'color' => '#94a3b8',
                        'text_align' => 'center',
                        'letter_spacing' => '1px',
                        'text_transform' => 'uppercase',
                    ],
                ],
                [
                    'id' => 'student_name',
                    'type' => 'text',
                    'x' => 100,
                    'y' => 205,
                    'width' => 922,
                    'height' => 45,
                    'content' => '{{student_name}}',
                    'style' => [
                        'font_family' => 'Helvetica',
                        'font_size' => '28px',
                        'font_weight' => 'bold',
                        'color' => '#38bdf8',
                        'text_align' => 'center',
                    ],
                ],
                [
                    'id' => 'course_description',
                    'type' => 'text',
                    'x' => 160,
                    'y' => 265,
                    'width' => 802,
                    'height' => 70,
                    'content' => 'Concluiu com aproveitamento integral o curso corporativo "{{course_title}}", cumprindo satisfatoriamente toda a grade curricular e obtendo aprovação nas avaliações pedagógicas exigidas, totalizando uma carga horária estimada de {{workload}}.',
                    'style' => [
                        'font_family' => 'Helvetica',
                        'font_size' => '13px',
                        'font_weight' => 'normal',
                        'color' => '#cbd5e1',
                        'text_align' => 'center',
                        'line_height' => '1.6',
                    ],
                ],
                [
                    'id' => 'signature_pedagogy',
                    'type' => 'text',
                    'x' => 100,
                    'y' => 370,
                    'width' => 260,
                    'height' => 60,
                    'content' => "Coordenação Pedagógica\nPlataforma ALVORA B2B",
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
                [
                    'id' => 'signature_date',
                    'type' => 'text',
                    'x' => 431,
                    'y' => 370,
                    'width' => 260,
                    'height' => 60,
                    'content' => "Data de Emissão\n{{issued_date}}",
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
                [
                    'id' => 'signature_company',
                    'type' => 'text',
                    'x' => 762,
                    'y' => 370,
                    'width' => 260,
                    'height' => 60,
                    'content' => "{{company_name}}\nRecursos Humanos",
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
                [
                    'id' => 'validation_badge',
                    'type' => 'text',
                    'x' => 700,
                    'y' => 460,
                    'width' => 350,
                    'height' => 45,
                    'content' => "Código de Autenticidade: {{validation_code}}\nVerificação: {{verification_url}}",
                    'style' => [
                        'font_family' => 'monospace',
                        'font_size' => '10px',
                        'font_weight' => 'normal',
                        'color' => '#64748b',
                        'text_align' => 'right',
                    ],
                ],
            ],
        ];
    }
}
