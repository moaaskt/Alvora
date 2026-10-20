<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqAiService
{
    protected string $apiKey;
    protected string $model;
    protected string $endpoint;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('ai.groq.api_key', '');
        $this->model = config('ai.groq.model', 'llama-3.3-70b-versatile');
        $this->endpoint = config('ai.groq.endpoint', 'https://api.groq.com/openai/v1/chat/completions');
        $this->timeout = config('ai.groq.timeout', 8);
    }

    /**
     * Send chat messages to Groq API with context injection.
     *
     * @param array $messages Array of ['role' => 'user'|'assistant'|'system', 'content' => '...']
     * @param array $context Additional context ['course_id', 'lesson_id', 'user_id', ...]
     * @return array ['content' => string, 'tokens_used' => ?int]
     */
    public function chat(array $messages, array $context = []): array
    {
        $systemPrompt = $this->buildSystemPrompt($context);

        $payloadMessages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ...$messages,
        ];

        // If no API key configured (e.g. testing or local without credentials), return graceful mock response
        if (empty($this->apiKey) && !app()->runningUnitTests()) {
            return [
                'content' => "Olá! Sou o Alvora AI Tutor. Em ambiente de demonstração local, estou operando em modo simulado. Você pode me perguntar sobre os conceitos da aula atual!",
                'tokens_used' => 42,
            ];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->post($this->endpoint, [
                    'model' => $this->model,
                    'messages' => $payloadMessages,
                    'temperature' => 0.5,
                    'max_tokens' => 1024,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $reply = $data['choices'][0]['message']['content'] ?? '';
                $tokensUsed = $data['usage']['total_tokens'] ?? null;

                return [
                    'content' => trim($reply),
                    'tokens_used' => $tokensUsed,
                ];
            }

            Log::error('Erro na resposta da Groq API', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'content' => 'Desculpe, tive uma oscilação momentânea ao consultar a inteligência do curso. Por favor, tente perguntar novamente em instantes.',
                'tokens_used' => 0,
            ];
        } catch (\Throwable $e) {
            Log::error('Exceção ao chamar Groq API: ' . $e->getMessage());

            return [
                'content' => 'Não foi possível conectar ao Alvora AI Tutor no momento. Verifique sua conexão ou tente novamente em alguns segundos.',
                'tokens_used' => 0,
            ];
        }
    }

    protected function buildSystemPrompt(array $context): string
    {
        $prompt = "Você é o Alvora AI Tutor, um assistente corporativo de aprendizado amigável, didático, conciso e encorajador da plataforma ALVORA.\n";
        $prompt .= "Seu objetivo é tirar dúvidas do colaborador, explicar conceitos da aula com clareza prática e sugerir reflexões para fixação.\n";
        $prompt .= "Responda sempre em Português do Brasil de forma clara e objetiva.\n";

        if (!empty($context['lesson_id'])) {
            $lesson = Lesson::find($context['lesson_id']);
            if ($lesson) {
                $prompt .= "\n--- CONTEXTO DA AULA ATUAL ---\n";
                $prompt .= "Título da Aula: {$lesson->title}\n";
                if ($lesson->description) {
                    $prompt .= "Descrição da Aula: {$lesson->description}\n";
                }
                if ($lesson->course) {
                    $prompt .= "Curso: {$lesson->course->title}\n";
                }
                if ($lesson->module) {
                    $prompt .= "Módulo: {$lesson->module->title}\n";
                }
            }
        } elseif (!empty($context['course_id'])) {
            $course = Course::find($context['course_id']);
            if ($course) {
                $prompt .= "\n--- CONTEXTO DO CURSO ATUAL ---\n";
                $prompt .= "Título do Curso: {$course->title}\n";
                $prompt .= "Descrição: {$course->description}\n";
            }
        }

        if (!empty($context['user_name'])) {
            $prompt .= "\nNome do colaborador: {$context['user_name']}\n";
        }

        return $prompt;
    }
}
