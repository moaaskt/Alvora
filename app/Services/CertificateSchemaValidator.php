<?php

namespace App\Services;

use DomainException;

class CertificateSchemaValidator
{
    public const MAX_WIDTH = 1122;
    public const MAX_HEIGHT = 793;

    public const ALLOWED_ROOT_KEYS = [
        'name',
        'version',
        'dimensions',
        'background',
        'border',
        'elements',
    ];

    public const ALLOWED_ELEMENT_TYPES = [
        'text',
        'box',
        'line',
        'signature_group',
        'auth_badge',
    ];

    public const ALLOWED_FONTS = [
        'Helvetica',
        'Times-Roman',
        'Courier',
        'sans-serif',
        'serif',
        'monospace',
    ];

    public const ALLOWED_FONT_WEIGHTS = [
        'normal',
        'bold',
        '300',
        '400',
        '500',
        '600',
        '700',
        '800',
    ];

    public const ALLOWED_TEXT_ALIGNS = [
        'left',
        'center',
        'right',
        'justify',
    ];

    public const ALLOWED_BORDER_STYLES = [
        'solid',
        'dashed',
        'dotted',
        'double',
        'none',
    ];

    public const ALLOWED_PLACEHOLDERS = [
        '{{student_name}}',
        '{{course_title}}',
        '{{company_name}}',
        '{{workload}}',
        '{{issued_date}}',
        '{{validation_code}}',
        '{{verification_url}}',
    ];

    public const ALLOWED_STYLE_KEYS = [
        'font_family',
        'font_size',
        'font_weight',
        'color',
        'text_align',
        'letter_spacing',
        'line_height',
        'background_color',
        'border_color',
        'border_width',
        'border_style',
        'padding',
        'border_radius',
        'text_transform',
    ];

    /**
     * Validate and sanitize a certificate template schema JSON payload.
     *
     * @param array<string, mixed> $schema
     * @return array<string, mixed>
     * @throws DomainException
     */
    public function validate(array $schema): array
    {
        // 1. Rejeita propriedades desconhecidas na raiz
        foreach (array_keys($schema) as $key) {
            if (!in_array($key, self::ALLOWED_ROOT_KEYS, true)) {
                throw new DomainException("Propriedade de raiz desconhecida ou não autorizada no schema: '{$key}'.");
            }
        }

        // 2. Valida dimensões
        $dimensions = $schema['dimensions'] ?? null;
        if (!is_array($dimensions)) {
            throw new DomainException("A propriedade 'dimensions' é obrigatória e deve ser um objeto.");
        }

        $width = $dimensions['width'] ?? null;
        $height = $dimensions['height'] ?? null;
        $format = $dimensions['format'] ?? null;
        $orientation = $dimensions['orientation'] ?? null;

        if (!is_numeric($width) || $width <= 0 || $width > self::MAX_WIDTH) {
            throw new DomainException("Largura inválida em 'dimensions': deve estar entre 1 e " . self::MAX_WIDTH . "px.");
        }

        if (!is_numeric($height) || $height <= 0 || $height > self::MAX_HEIGHT) {
            throw new DomainException("Altura inválida em 'dimensions': deve estar entre 1 e " . self::MAX_HEIGHT . "px.");
        }

        if ($format !== 'a4' || $orientation !== 'landscape') {
            throw new DomainException("O formato deve ser 'a4' e a orientação 'landscape' para assegurar conformidade do Dompdf.");
        }

        // 3. Valida background
        if (isset($schema['background'])) {
            if (!is_array($schema['background'])) {
                throw new DomainException("A propriedade 'background' deve ser um objeto.");
            }
            $bg = $schema['background'];
            foreach (array_keys($bg) as $bgKey) {
                if (!in_array($bgKey, ['color', 'border_color', 'opacity'], true)) {
                    throw new DomainException("Propriedade desconhecida em 'background': '{$bgKey}'. Recursos externos ou imagens remotas não são permitidos.");
                }
            }
            if (isset($bg['color'])) {
                $this->validateColor($bg['color'], 'background.color');
            }
        }

        // 4. Valida borda global
        if (isset($schema['border'])) {
            if (!is_array($schema['border'])) {
                throw new DomainException("A propriedade 'border' deve ser um objeto.");
            }
            $border = $schema['border'];
            foreach (array_keys($border) as $bKey) {
                if (!in_array($bKey, ['color', 'width', 'style', 'padding'], true)) {
                    throw new DomainException("Propriedade desconhecida em 'border': '{$bKey}'.");
                }
            }
            if (isset($border['color'])) {
                $this->validateColor($border['color'], 'border.color');
            }
            if (isset($border['style']) && !in_array($border['style'], self::ALLOWED_BORDER_STYLES, true)) {
                throw new DomainException("Estilo de borda inválido: '{$border['style']}'.");
            }
        }

        // 5. Valida elementos
        $elements = $schema['elements'] ?? null;
        if (!is_array($elements) || empty($elements)) {
            throw new DomainException("O schema deve conter pelo menos um elemento na coleção 'elements'.");
        }

        $seenIds = [];
        foreach ($elements as $idx => $element) {
            if (!is_array($element)) {
                throw new DomainException("Elemento na posição {$idx} deve ser um objeto.");
            }

            $id = $element['id'] ?? null;
            if (empty($id) || !is_string($id)) {
                throw new DomainException("Elemento na posição {$idx} deve ter um 'id' textual não vazio.");
            }

            if (isset($seenIds[$id])) {
                throw new DomainException("ID duplicado no schema: '{$id}'. Cada elemento deve possuir um identificador único.");
            }
            $seenIds[$id] = true;

            $type = $element['type'] ?? null;
            if (!in_array($type, self::ALLOWED_ELEMENT_TYPES, true)) {
                throw new DomainException("Tipo de elemento inválido no elemento '{$id}': '{$type}'.");
            }

            // Coordenadas e limites geométricos estritos
            $x = $element['x'] ?? null;
            $y = $element['y'] ?? null;
            $elemWidth = $element['width'] ?? null;
            $elemHeight = $element['height'] ?? null;

            if (!is_numeric($x) || $x < 0 || $x > self::MAX_WIDTH) {
                throw new DomainException("Coordenada 'x' inválida no elemento '{$id}'.");
            }

            if (!is_numeric($y) || $y < 0 || $y > self::MAX_HEIGHT) {
                throw new DomainException("Coordenada 'y' inválida no elemento '{$id}'.");
            }

            if (!is_numeric($elemWidth) || $elemWidth <= 0 || ($x + $elemWidth) > (self::MAX_WIDTH + 10)) {
                throw new DomainException("Largura transborda os limites da folha A4 no elemento '{$id}'.");
            }

            if (!is_numeric($elemHeight) || $elemHeight <= 0 || ($y + $elemHeight) > (self::MAX_HEIGHT + 10)) {
                throw new DomainException("Altura transborda os limites da folha A4 no elemento '{$id}'.");
            }

            // Validação de conteúdo textual e marcadores dinâmicos
            if (isset($element['content'])) {
                if (!is_string($element['content'])) {
                    throw new DomainException("O 'content' do elemento '{$id}' deve ser uma string.");
                }

                $content = $element['content'];

                // Rejeita qualquer tag HTML (<script>, <iframe>, <a>, etc.)
                if ($content !== strip_tags($content)) {
                    throw new DomainException("HTML arbitrário ou tags embutidas não são permitidos no conteúdo do elemento '{$id}'.");
                }

                // Valida marcadores dinâmicos {{...}}
                if (preg_match_all('/\{\{[^}]+\}\}/', $content, $matches)) {
                    foreach ($matches[0] as $placeholder) {
                        if (!in_array($placeholder, self::ALLOWED_PLACEHOLDERS, true)) {
                            throw new DomainException("Marcador dinâmico não autorizado no elemento '{$id}': '{$placeholder}'.");
                        }
                    }
                }
            }

            // Validação de estilos do elemento
            if (isset($element['style'])) {
                if (!is_array($element['style'])) {
                    throw new DomainException("A propriedade 'style' do elemento '{$id}' deve ser um objeto.");
                }

                $this->validateElementStyles($element['style'], $id);
            }
        }

        return $schema;
    }

    /**
     * Validate element style properties against strict whitelists.
     *
     * @param array<string, mixed> $styles
     * @param string $elementId
     */
    protected function validateElementStyles(array $styles, string $elementId): void
    {
        foreach ($styles as $styleKey => $styleValue) {
            if (!in_array($styleKey, self::ALLOWED_STYLE_KEYS, true)) {
                throw new DomainException("Propriedade de estilo não autorizada no elemento '{$elementId}': '{$styleKey}'.");
            }

            if ($styleKey === 'font_family') {
                if (!in_array($styleValue, self::ALLOWED_FONTS, true)) {
                    throw new DomainException("Fonte não autorizada no elemento '{$elementId}': '{$styleValue}'.");
                }
            }

            if ($styleKey === 'font_weight') {
                if (!in_array((string) $styleValue, self::ALLOWED_FONT_WEIGHTS, true)) {
                    throw new DomainException("Peso de fonte não autorizado no elemento '{$elementId}': '{$styleValue}'.");
                }
            }

            if ($styleKey === 'text_align') {
                if (!in_array($styleValue, self::ALLOWED_TEXT_ALIGNS, true)) {
                    throw new DomainException("Alinhamento de texto não autorizado no elemento '{$elementId}': '{$styleValue}'.");
                }
            }

            if ($styleKey === 'border_style') {
                if (!in_array($styleValue, self::ALLOWED_BORDER_STYLES, true)) {
                    throw new DomainException("Estilo de borda não autorizado no elemento '{$elementId}': '{$styleValue}'.");
                }
            }

            if (in_array($styleKey, ['color', 'background_color', 'border_color'], true)) {
                $this->validateColor((string) $styleValue, "{$elementId}.style.{$styleKey}");
            }
        }
    }

    /**
     * Validate color formats (hex #rgb, #rrggbb, #rrggbbaa ou rgba/rgb simples).
     */
    protected function validateColor(string $color, string $context): void
    {
        $color = trim($color);

        // Regex para HEX
        if (preg_match('/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $color)) {
            return;
        }

        // Regex para rgb / rgba seguro
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(?:,\s*(?:0|1|0?\.\d+)\s*)?\)$/', $color)) {
            return;
        }

        // Palavra-chave transparente
        if ($color === 'transparent') {
            return;
        }

        throw new DomainException("Formato de cor inválido em '{$context}': '{$color}'. Use hexadecimal ou rgba().");
    }
}
