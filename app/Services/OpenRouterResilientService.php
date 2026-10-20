<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterResilientService
{
    protected string $apiKey;
    protected string $endpoint;
    protected array $modelsFallback;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('ai.openrouter.api_key', '');
        $this->endpoint = config('ai.openrouter.endpoint', 'https://openrouter.ai/api/v1/chat/completions');
        $this->modelsFallback = config('ai.openrouter.models_fallback', [
            'deepseek/deepseek-r1:free',
            'meta-llama/llama-3.3-70b-instruct:free',
            'qwen/qwen3-235b-a22b:free',
        ]);
        $this->timeout = config('ai.openrouter.timeout', 8);
    }

    /**
     * Executes prompt across the cascade of fallback models until success.
     *
     * @return array ['content' => string, 'model_used' => string, 'attempts' => int]
     */
    public function complete(string $prompt, array $options = []): array
    {
        // Se em mock/ambiente sem chave, retorna resposta padrão de contingência
        if (empty($this->apiKey) && !app()->runningUnitTests()) {
            return [
                'content' => $this->getMockCurriculumJson(),
                'model_used' => 'mock-local',
                'attempts' => 1,
            ];
        }

        $attempts = 0;

        foreach ($this->modelsFallback as $model) {
            $attempts++;
            try {
                $response = Http::withToken($this->apiKey)
                    ->withHeaders([
                        'HTTP-Referer' => config('app.url', 'http://localhost:8000'),
                        'X-Title' => 'ALVORA B2B Learning',
                    ])
                    ->timeout($this->timeout)
                    ->post($this->endpoint, [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => $options['temperature'] ?? 0.3,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';

                    return [
                        'content' => trim($content),
                        'model_used' => $model,
                        'attempts' => $attempts,
                    ];
                }

                Log::warning("OpenRouter fallback: Modelo '{$model}' falhou com status {$response->status()}, tentando próximo da cascata...", [
                    'body' => $response->body(),
                ]);
            } catch (\Throwable $e) {
                Log::warning("OpenRouter fallback: Exceção no modelo '{$model}': {$e->getMessage()}, tentando próximo...");
            }
        }

        // Se todos os modelos falharem
        Log::error("OpenRouter fallback: Todos os " . count($this->modelsFallback) . " modelos falharam. Usando resposta de contingência.");

        return [
            'content' => $this->getMockCurriculumJson(),
            'model_used' => 'fallback-contingency',
            'attempts' => $attempts,
        ];
    }

    /**
     * Generates structured curriculum array from topic and audience.
     */
    public function generateStructuredCurriculum(string $topic, string $targetAudience = 'Colaboradores Corporativos'): array
    {
        $prompt = <<<PROMPT
Você é um designer instrucional e especialista sênior em treinamento corporativo.
Gere uma ementa curricular de curso estruturada para o tema: "{$topic}".
Público-alvo: "{$targetAudience}".

Retorne EXCLUSIVAMENTE um objeto JSON válido, sem texto antes ou depois, com o seguinte schema:
{
  "title": "Título Estratégico do Curso",
  "description": "Descrição executiva e objetivos de aprendizagem do curso",
  "modules": [
    {
      "title": "Módulo 1: Nome do Módulo",
      "lessons": [
        {
          "title": "Aula 1: Título da Aula",
          "description": "Objetivo e conteúdo resumido da aula",
          "duration_minutes": 15
        }
      ]
    }
  ]
}
PROMPT;

        $result = $this->complete($prompt);
        $rawContent = $result['content'];

        // Tenta limpar eventuais blocos markdown ```json ... ```
        if (preg_match('/```json(.*?)```/s', $rawContent, $matches)) {
            $rawContent = trim($matches[1]);
        } elseif (preg_match('/```(.*?)```/s', $rawContent, $matches)) {
            $rawContent = trim($matches[1]);
        }

        $decoded = json_decode($rawContent, true);

        if (is_array($decoded) && isset($decoded['modules'])) {
            $decoded['_meta'] = [
                'model_used' => $result['model_used'],
                'attempts' => $result['attempts'],
            ];
            return $decoded;
        }

        // Em caso de falha de parsing, usa template estruturado de segurança
        $fallback = json_decode($this->getMockCurriculumJson(), true);
        $fallback['_meta'] = [
            'model_used' => $result['model_used'],
            'attempts' => $result['attempts'],
        ];
        return $fallback;
    }

    protected function getMockCurriculumJson(): string
    {
        return json_encode([
            'title' => 'Treinamento Estratégico em Transformação Digital',
            'description' => 'Capacitação prática em inovação, ferramentas digitais e aumento de produtividade das equipes corporativas.',
            'modules' => [
                [
                    'title' => 'Módulo 1: Fundamentos da Inovação Corporativa',
                    'lessons' => [
                        [
                            'title' => 'Visão Geral e Mindset Digital',
                            'description' => 'Compreendendo como a cultura digital acelera entregas em times corporativos.',
                            'duration_minutes' => 15,
                        ],
                        [
                            'title' => 'Ferramentas de Colaboração Ágil',
                            'description' => 'Melhores práticas no uso de plataformas e redução de ruído de comunicação.',
                            'duration_minutes' => 20,
                        ],
                    ],
                ],
                [
                    'title' => 'Módulo 2: Automação e Inteligência no Trabalho',
                    'lessons' => [
                        [
                            'title' => 'Identificação de Gargalos e Oportunidades',
                            'description' => 'Mapeamento de rotinas operacionais elegíveis para otimização com IA.',
                            'duration_minutes' => 25,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
