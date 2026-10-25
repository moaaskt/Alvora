<!-- GSD:project-start source:PROJECT.md -->

## Project

**ALVORA — Plataforma de Cursos B2B com IA**

Alvora é uma plataforma de treinamento corporativo (B2B) onde empresas contratam acesso para capacitar seus times através de cursos, trilhas de aprendizado e acompanhamento de desempenho. O diferencial central é um sistema de IA com intervenção ativa que prevê risco de evasão do colaborador em tempo hábil e age de forma proativa para retenção e engajamento.

**Core Value:** Combater a evasão no treinamento corporativo através do cálculo contínuo do Score de Risco de Evasão e intervenção preditiva automatizada via IA antes da desistência do colaborador.

### Constraints

- **Tech Stack**: Monolito Laravel 12 + Livewire 3 + Blade + MySQL 8 + Redis.
- **IA Fallback**: Groq API para chat de baixa latência; OpenRouter para análise/geração com fallback não hardcoded (`deepseek/deepseek-r1:free`, `meta-llama/llama-3.3-70b-instruct:free`, `qwen/qwen3-235b-a22b:free`).
- **Idiomático**: Código e documentação orientados em PT-BR.
- **Infraestrutura**: Orquestração com Docker e monitoramento de filas com Horizon.

<!-- GSD:project-end -->

<!-- GSD:stack-start source:STACK.md -->

## Technology Stack

- **Backend:** Monolito Modular em Laravel 12 / PHP 8.3 rodando em memória com Laravel Octane (FrankenPHP).
- **Frontend & Reatividade:** Livewire 3/4 + Blade + Tailwind CSS 4 + Vite.
- **Armazenamento:** MySQL 8.0 (ACID transacional, multi-tenant) + Redis 7 (cache, sessões, filas assíncronas).
- **Supervisão e Tempo Real:** Laravel Horizon (workers em fila) + Laravel Reverb (WebSockets na porta 8080).
- **Inteligência Artificial:** Dual-Engine: Groq API (`llama-3.3-70b-versatile` sub-1.5s chat) + OpenRouter com fallback dinâmico em 3 tiers (`deepseek-r1` ➔ `llama-3.3-70b` ➔ `qwen3`).
- **Faturamento & Documentos:** Mercado Pago API v1 (Checkout Pro e Preapprovals com webhooks HMAC-SHA256) + DomPDF (certificados A4).
- **Orquestração:** Docker Compose com 5 serviços integrados (`app`, `mysql`, `redis`, `horizon`, `reverb`).

<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->

## Conventions

- **Linguagem:** Código-fonte, models, variáveis e métodos em Inglês; documentação, interface, logs e comunicação em PT-BR.
- **Multi-Tenancy:** Todo modelo pertencente a empresas deve usar o trait `BelongsToTenant` (com `TenantScope` e injeção automática de `company_id`).
- **Autorização (RBAC):** Uso do enum `UserRole` (`admin_alvora`, `company_admin`, `instructor`, `student`) com middleware `role:`.
- **Assinatura Corporativa:** Rotas protegidas por `subscription.active` com esteira de 5 dias de carência.
- **Tarefas de IA:** Processadas em background via Horizon ou com feedback visual de carregamento (`wire:loading`).
- **Webhooks Seguros:** Validação criptográfica do cabeçalho `x-signature` via HMAC-SHA256 antes de qualquer processamento.

<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->

## Architecture

- **Padrão:** Monolito Modular Laravel com domínios delimitados (`Admin`, `Company`, `Instructor`, `Student`, `AI`, `Billing`).
- **Multi-Tenancy:** Single-database com isolamento lógico via `company_id` e bypass exclusivo para Super Admin (`admin_alvora`).
- **Motor Anti-Evasão:** Cálculo contínuo do Score (0–100) ponderando 4 fatores: Inatividade (35%), Estagnação (25%), Quizzes (20%) e Desvio de Pares (20%), persistido em `risk_scores` com breakdown JSON.
- **Gatilho de Intervenção:** Score crítico (≥ 70) dispara alerta WebSocket via Reverb para a Sala de Situação e mensagem personalizada ao colaborador via `EvasionInterventionService`.
- **Certificação:** Emissão condicionada a 100% de conclusão de aulas com código único `ALV-2026-XXXX` e validação pública em `/validar-certificado/{code}`.

<!-- GSD:architecture-end -->

<!-- GSD:skills-start source:skills/ -->

## Project Skills

No project skills found. Add skills to any of: `.agents/skills/`, `.agents/skills/`, `.cursor/skills/`, `.github/skills/`, or `.codex/skills/` with a `SKILL.md` index file.
<!-- GSD:skills-end -->

<!-- GSD:workflow-start source:GSD defaults -->

## GSD Workflow Enforcement

Before using Edit, Write, or other file-changing tools, start work through a GSD command so planning artifacts and execution context stay in sync.

Use these entry points:

- `/gsd-quick` for small fixes, doc updates, and ad-hoc tasks
- `/gsd-debug` for investigation and bug fixing
- `/gsd-execute-phase` for planned phase work

Do not make direct repo edits outside a GSD workflow unless the user explicitly asks to bypass it.
<!-- GSD:workflow-end -->

<!-- GSD:profile-start -->

## Developer Profile

> Profile not yet configured. Run `/gsd-profile-user` to generate your developer profile.
> This section is managed by `generate-claude-profile` -- do not edit manually.
<!-- GSD:profile-end -->
