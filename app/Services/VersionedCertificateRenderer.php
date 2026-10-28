<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CertificateTemplateVersion;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Facades\Storage;

class VersionedCertificateRenderer
{
    /**
     * Render a versioned certificate into a DomPDF instance.
     */
    public function render(Certificate $certificate, CertificateTemplateVersion $version): DomPdfWrapper
    {
        $schema = $version->schema_json ?? [];
        $compiledData = $this->compileSchema($certificate, $schema);

        /** @var DomPdfWrapper $pdf */
        $pdf = Pdf::loadView('certificates.versioned-template', [
            'certificate' => $certificate,
            'version' => $version,
            'compiled' => $compiledData,
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOption('isRemoteEnabled', false);
        $pdf->setOption('isHtml5ParserEnabled', true);

        return $pdf;
    }

    /**
     * Render an in-memory preview of a template schema with demo data (no database persistence).
     *
     * @param array<string, mixed> $schema
     * @param string|null $companyName
     * @return DomPdfWrapper
     */
    public function renderPreview(array $schema, ?string $companyName = null): DomPdfWrapper
    {
        $dummyCertificate = new Certificate([
            'total_hours_minutes' => 120,
            'validation_code' => 'ALV-PREVIEW-DEMO',
            'issued_at' => now(),
            'snapshot' => [
                'mode' => 'preview',
                'student_name' => 'Colaborador de Exemplo',
                'course_title' => 'Treinamento Corporativo Avançado',
                'company_name' => $companyName ?? 'Empresa Demonstração',
                'workload_minutes' => 120,
            ],
        ]);

        $dummyVersion = new CertificateTemplateVersion([
            'version' => 0,
            'schema_json' => $schema,
            'status' => 'preview',
        ]);

        return $this->render($dummyCertificate, $dummyVersion);
    }

    /**
     * Compile schema elements with interpolated certificate snapshot data and CSS styles.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     */
    public function compileSchema(Certificate $certificate, array $schema): array
    {
        $placeholders = $this->buildPlaceholdersMap($certificate);

        $imagePath = $schema['background']['image_path'] ?? null;
        $imageAbsolutePath = null;

        if (!empty($imagePath)) {
            // 1. Prioriza o disco local privado ('local' = storage/app/private)
            $diskLocal = Storage::disk('local');
            if ($diskLocal->exists($imagePath)) {
                $imageAbsolutePath = $diskLocal->path($imagePath);
            } elseif (Storage::disk('public')->exists($imagePath)) {
                // 2. Fallback retrocompatível transparente para imagens legadas no disco público
                $imageAbsolutePath = Storage::disk('public')->path($imagePath);
            }
        }

        $background = [
            'color' => $this->sanitizeColor($schema['background']['color'] ?? '#0b0f19'),
            'image_path' => $imagePath,
            'image_absolute_path' => $imageAbsolutePath,
        ];

        $border = [
            'color' => $this->sanitizeColor($schema['border']['color'] ?? '#6366f1'),
            'width' => (int) ($schema['border']['width'] ?? 3),
            'style' => in_array($schema['border']['style'] ?? 'solid', CertificateSchemaValidator::ALLOWED_BORDER_STYLES, true)
                ? $schema['border']['style']
                : 'solid',
            'padding' => (int) ($schema['border']['padding'] ?? 10),
        ];

        $elements = [];
        $rawElements = $schema['elements'] ?? [];

        foreach ($rawElements as $rawElement) {
            $id = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) ($rawElement['id'] ?? 'elem'));
            $type = in_array($rawElement['type'] ?? 'text', CertificateSchemaValidator::ALLOWED_ELEMENT_TYPES, true)
                ? $rawElement['type']
                : 'text';

            $x = (int) ($rawElement['x'] ?? 0);
            $y = (int) ($rawElement['y'] ?? 0);
            $width = (int) ($rawElement['width'] ?? 100);
            $height = (int) ($rawElement['height'] ?? 40);

            $rawContent = (string) ($rawElement['content'] ?? '');
            $interpolatedContent = $this->interpolateContent($rawContent, $placeholders);

            $positionCss = sprintf(
                'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx; box-sizing: border-box; overflow: hidden;',
                $x,
                $y,
                $width,
                $height
            );

            $styleCss = $this->buildStyleCss($rawElement['style'] ?? []);

            $elements[] = [
                'id' => $id,
                'type' => $type,
                'position_css' => $positionCss,
                'style_css' => $styleCss,
                'content_html' => nl2br($interpolatedContent, false),
            ];
        }

        return [
            'width' => 1122,
            'height' => 793,
            'background' => $background,
            'border' => $border,
            'elements' => $elements,
        ];
    }

    /**
     * Build the map of allowed dynamic placeholder values from certificate snapshot / attributes.
     *
     * @return array<string, string>
     */
    protected function buildPlaceholdersMap(Certificate $certificate): array
    {
        $studentName = $certificate->student_name ?? 'Colaborador';
        $courseTitle = $certificate->course_title ?? 'Curso Concluído';
        $companyName = $certificate->company_name ?? '';
        $workload = $certificate->formattedWorkload();
        $issuedDate = $certificate->issued_at ? $certificate->issued_at->format('d/m/Y') : now()->format('d/m/Y');
        $validationCode = $certificate->validation_code ?? '';
        $verificationUrl = url('/validar-certificado/' . $validationCode);

        return [
            '{{student_name}}' => htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'),
            '{{course_title}}' => htmlspecialchars($courseTitle, ENT_QUOTES, 'UTF-8'),
            '{{company_name}}' => htmlspecialchars($companyName, ENT_QUOTES, 'UTF-8'),
            '{{workload}}' => htmlspecialchars($workload, ENT_QUOTES, 'UTF-8'),
            '{{issued_date}}' => htmlspecialchars($issuedDate, ENT_QUOTES, 'UTF-8'),
            '{{validation_code}}' => htmlspecialchars($validationCode, ENT_QUOTES, 'UTF-8'),
            '{{verification_url}}' => htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8'),
        ];
    }

    /**
     * Interpolate only authorized placeholders, escaping any other raw characters.
     *
     * @param array<string, string> $placeholders
     */
    protected function interpolateContent(string $rawContent, array $placeholders): string
    {
        // Divide o conteúdo preservando os marcadores conhecidos
        // Primeiro escapa todo o texto estático
        $escaped = htmlspecialchars($rawContent, ENT_QUOTES, 'UTF-8');

        // Em seguida substitui as tags conhecidas (já escapadas pelo htmlspecialchars acima)
        foreach ($placeholders as $tag => $sanitizedValue) {
            $escapedTag = htmlspecialchars($tag, ENT_QUOTES, 'UTF-8');
            $escaped = str_replace($escapedTag, $sanitizedValue, $escaped);
        }

        return $escaped;
    }

    /**
     * Build inline CSS style declaration from sanitized schema style parameters.
     *
     * @param array<string, mixed> $style
     */
    protected function buildStyleCss(array $style): string
    {
        $cssRules = [];

        if (!empty($style['font_family']) && in_array($style['font_family'], CertificateSchemaValidator::ALLOWED_FONTS, true)) {
            $cssRules[] = 'font-family: ' . $style['font_family'] . ', sans-serif';
        }

        if (!empty($style['font_size']) && preg_match('/^\d{1,3}px$/', (string) $style['font_size'])) {
            $cssRules[] = 'font-size: ' . $style['font_size'];
        }

        if (!empty($style['font_weight']) && in_array((string) $style['font_weight'], CertificateSchemaValidator::ALLOWED_FONT_WEIGHTS, true)) {
            $cssRules[] = 'font-weight: ' . $style['font_weight'];
        }

        if (!empty($style['color']) && preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $style['color'])) {
            $cssRules[] = 'color: ' . $style['color'];
        }

        if (!empty($style['text_align']) && in_array($style['text_align'], CertificateSchemaValidator::ALLOWED_TEXT_ALIGNS, true)) {
            $cssRules[] = 'text-align: ' . $style['text_align'];
        }

        if (!empty($style['letter_spacing']) && preg_match('/^\d{1,2}px$/', (string) $style['letter_spacing'])) {
            $cssRules[] = 'letter-spacing: ' . $style['letter_spacing'];
        }

        if (!empty($style['line_height']) && preg_match('/^(?:\d(?:\.\d{1,2})?|\d{1,2}px)$/', (string) $style['line_height'])) {
            $cssRules[] = 'line-height: ' . $style['line_height'];
        }

        if (!empty($style['text_transform']) && in_array($style['text_transform'], ['uppercase', 'lowercase', 'capitalize', 'none'], true)) {
            $cssRules[] = 'text-transform: ' . $style['text_transform'];
        }

        if (!empty($style['background_color']) && preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $style['background_color'])) {
            $cssRules[] = 'background-color: ' . $style['background_color'];
        }

        if (!empty($style['border_color']) && !empty($style['border_width'])) {
            $borderWidth = (int) $style['border_width'];
            $borderStyle = in_array($style['border_style'] ?? 'solid', CertificateSchemaValidator::ALLOWED_BORDER_STYLES, true)
                ? $style['border_style']
                : 'solid';
            $borderColor = preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $style['border_color'])
                ? $style['border_color']
                : '#475569';

            $cssRules[] = "border: {$borderWidth}px {$borderStyle} {$borderColor}";
        }

        if (isset($style['padding']) && is_numeric($style['padding'])) {
            $cssRules[] = 'padding: ' . ((int) $style['padding']) . 'px';
        }

        if (isset($style['border_radius']) && is_numeric($style['border_radius'])) {
            $cssRules[] = 'border-radius: ' . ((int) $style['border_radius']) . 'px';
        }

        return implode('; ', $cssRules);
    }

    /**
     * Sanitize and validate hex color.
     */
    protected function sanitizeColor(mixed $value, string $fallback = '#0b0f19'): string
    {
        if (is_string($value) && preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
            return $value;
        }

        return $fallback;
    }
}
