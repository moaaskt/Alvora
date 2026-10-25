# Pesquisa da Fase 06: Alvora AI — Score de Evasão, Intervenção, Chat e Criação

## Visão Geral e Contexto Técnico

Esta fase implementa os diferenciais centrais de inteligência artificial da plataforma corporativa ALVORA:
1. **Motor de Risco de Evasão (EVAS-01, EVAS-02):** Análise preditiva em background com pontuação ponderada (0 a 100), registro histórico e classificação (`low`, `medium`, `high`).
2. **Sistema de Intervenção Ativa e Sala de Situação (EVAS-03, EVAS-04):** Ações autônomas imediatas para alunos em risco alto (mensagens motivacionais e reforço de conteúdo gerados por IA) e painel heatmap para RH.
3. **Chat Tutor Alvora AI (CHAT-01, CHAT-02):** Assistente conversacional de baixa latência em tempo real para alunos, potencializado pela API Groq com injeção automática de contexto da aula em reprodução.
4. **Geração de Cursos e Roteador de Fallback OpenRouter (CURS-03, INST-01, INST-02, INST-03):** Geração inteligente de ementas curriculares e diagnósticos com chaveamento automático de modelos em cascata (`deepseek-r1` -> `llama-3.3-70b` -> `qwen3`).

---

## Padrões Existentes e Integração no Codebase

- **Multi-tenancy:** Todos os modelos associados a dados de empresas e colaboradores utilizam `App\Traits\BelongsToTenant` e o escopo `company_id`.
- **Livewire 3 / Blade:** Todos os componentes interativos usam a reatividade do Livewire 3 com dispatchers de eventos de browser e Alpine.js.
- **Cliente HTTP do Laravel:** `Illuminate\Support\Facades\Http` para chamadas de API externas, permitindo `Http::fake()` limpo e determinístico em testes automatizados.
- **Fila e Jobs:** Jobs com `ShouldQueue` para processamento desacoplado de recomputação de scores e envios de intervenção.
- **Banco de Dados:** MySQL/SQLite compatível, migrations versionadas.

---

## Design de Dados e Novas Tabelas

1. `risk_scores`:
   - `id`, `company_id`, `user_id` (foreignId cascade)
   - `score` (integer: 0 a 100)
   - `level` (enum/string: low, medium, high)
   - `breakdown` (json: detalhes dos 4 fatores calculados)
   - `calculated_at` (timestamp)
   - `timestamps`

2. `ai_interventions`:
   - `id`, `company_id`, `user_id` (foreignId cascade)
   - `risk_score_id` (foreignId nullable)
   - `message` (text)
   - `recommended_lesson_id` (foreignId nullable)
   - `status` (string: sent, viewed, acknowledged)
   - `read_at` (timestamp nullable)
   - `timestamps`

3. `ai_conversations`:
   - `id`, `company_id`, `user_id` (foreignId cascade)
   - `title` (string nullable)
   - `course_id` (foreignId nullable)
   - `lesson_id` (foreignId nullable)
   - `timestamps`

4. `ai_messages`:
   - `id`, `conversation_id` (foreignId cascade)
   - `sender` (enum: user, assistant, system)
   - `content` (text)
   - `tokens_used` (integer nullable)
   - `timestamps`

---

## Clientes de IA e Configuração

- `config/ai.php`:
  - `groq`: `api_key`, `base_url`, `default_model` (ex: `llama-3.3-70b-versatile` ou `mixtral-8x7b-32768`)
  - `openrouter`: `api_key`, `base_url`, `models_fallback` (`deepseek/deepseek-r1:free`, `meta-llama/llama-3.3-70b-instruct:free`, `qwen/qwen3-235b-a22b:free`)
  - `timeout_seconds`: 8
