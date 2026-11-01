<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Tags HTML permitidas na ementa e descrição formatada dos cursos.
     *
     * @var array<int, string>
     */
    protected static array $allowedTags = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li',
        'blockquote', 'pre', 'code',
        'a', 'span'
    ];

    /**
     * Sanitiza uma string HTML removendo scripts, manipuladores de eventos e links inseguros.
     *
     * @param string|null $html
     * @return string
     */
    public static function clean(?string $html): string
    {
        if ($html === null) {
            return '';
        }

        $trimmed = trim($html);
        if ($trimmed === '' || $trimmed === '<p><br></p>' || $trimmed === '<p></p>') {
            return '';
        }

        // 1. Remove blocos inteiros de scripts e estilos com seu conteúdo interno
        $cleaned = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $trimmed);
        $cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $cleaned);

        // 2. Remove tags não autorizadas (ex: <iframe>, <object>, <embed>, <meta>, <link>)
        $cleaned = strip_tags($cleaned, self::$allowedTags);

        // 2. Remove manipuladores de evento inline (ex: onload=, onclick=, onerror=, onmouseover=)
        $cleaned = preg_replace('/\s*on[a-zA-Z]+\s*=\s*(["\'])(.*?)\1/i', '', $cleaned);
        $cleaned = preg_replace('/\s*on[a-zA-Z]+\s*=\s*[^"\'\s>]+/i', '', $cleaned);

        // 3. Sanitiza atributos href em links para evitar javascript:, data: ou vbscript:
        $cleaned = preg_replace_callback('/<a\b([^>]*)>/i', function ($matches) {
            $attributes = $matches[1];

            // Extrai o valor do href se existir
            if (preg_match('/href\s*=\s*(["\'])(.*?)\1/i', $attributes, $hrefMatches)) {
                $url = trim($hrefMatches[2]);

                // Bloqueia esquemas perigosos como javascript: ou data:
                if (preg_match('/^(javascript|data|vbscript):/i', $url)) {
                    $url = '#';
                }

                // Remove o href original e substitui com url higienizada e proteção rel/target
                $attributes = preg_replace('/href\s*=\s*(["\']).*?\1/i', '', $attributes);
                $attributes = trim($attributes);

                // Assegura rel="noopener noreferrer" e target="_blank"
                $attributes = preg_replace('/\s*rel\s*=\s*(["\']).*?\1/i', '', $attributes);
                $attributes = preg_replace('/\s*target\s*=\s*(["\']).*?\1/i', '', $attributes);

                return sprintf('<a href="%s" target="_blank" rel="noopener noreferrer"%s>', htmlspecialchars($url, ENT_QUOTES, 'UTF-8'), $attributes ? ' ' . $attributes : '');
            }

            return '<a' . $attributes . '>';
        }, $cleaned);

        return trim($cleaned);
    }
}
