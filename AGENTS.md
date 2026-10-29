<!-- GSD:project-start source:PROJECT.md -->

## Project

**ALVORA — Plataforma de Cursos B2B com IA**

Alvora é uma plataforma de treinamento corporativo (B2B) onde empresas contratam acesso para capacitar seus times através de cursos, trilhas de aprendizado e acompanhamento de desempenho. O diferencial central é um sistema de IA com intervenção ativa que prevê risco de evasão do colaborador em tempo hábil e age de forma proativa para retenção e engajamento.

**Core Value:** Combater a evasão no treinamento corporativo através do cálculo contínuo do Score de Risco de Evasão e intervenção preditiva automatizada via IA antes da desistência do colaborador.

### Constraints

- **Tech Stack**: Monolito Modular Laravel 13 (`laravel/framework v13.37.0`) + Livewire 4 (`livewire/livewire v4.4.7`) + Blade + Tailwind CSS 4 + PHP 8.3.
- **Banco de Dados**: MySQL 8.0 em Produção (ACID, multi-tenant); SQLite para testes locais rápidos (`database/testing.sqlite`). Testes de concorrência, integridade referencial e restrições devem rodar também em MySQL.
- **IA Fallback**: Groq API para chat de baixa latência; OpenRouter para análise/geração com fallback não hardcoded (`deepseek/deepseek-r1:free`, `meta-llama/llama-3.3-70b-instruct:free`, `qwen/qwen3-235b-a22b:free`).
- **Idiomático**: Código e documentação orientados em PT-BR.
- **Infraestrutura**: Orquestração via Docker Compose com serviços previstos (`app`, `mysql`, `redis`, `horizon`, `reverb`). Horizon, Reverb e Octane **não são requisitos obrigatórios de runtime** para o funcionamento da aplicação nem do módulo de certificados.

<!-- GSD:project-end -->

<!-- GSD:stack-start source:STACK.md -->

## Technology Stack

- **Backend:** Monolito Modular em Laravel 13 (`laravel/framework: v13.37.0`) / PHP 8.3.
- **Frontend & Reatividade:** Livewire 4 (`livewire/livewire: v4.4.7`) + Blade + Tailwind CSS 4 (`@tailwindcss/vite: 4.3.3`, `tailwindcss: 4.3.3`) + Vite 8 (`vite: 8.3.4`).
- **Armazenamento & Persistência:**
  - **Produção:** MySQL 8.0 (ACID transacional, isolamento lógico multi-tenant).
  - **Testes Locais:** SQLite (`database/testing.sqlite`).
  - **Validação de Concorrência:** Testes de concorrência, restrições e comportamento específico de banco devem executar também em MySQL (via container local ou CI); não presumir que testes SQLite comprovam o comportamento concorrente em MySQL.
- **Cache, Sessões e Filas:** Driver de banco (`database`) / cache em memória localmente; Redis 7 configurado no Docker Compose para ambientes produtivos.
- **Infraestrutura Operacional:**
  - Emissão e download de certificados operam de forma síncrona sob demanda via Dompdf (`barryvdh/laravel-dompdf v3.1.2`, `dompdf/dompdf v3.1.6`).
  - Horizon, Reverb e Octane constam no Docker Compose como alvo de infraestrutura assíncrona/tempo real, mas **não são dependências obrigatórias instaladas** no runtime atual.
- **Inteligência Artificial:** Dual-Engine: Groq API (`llama-3.3-70b-versatile` sub-1.5s chat) + OpenRouter com fallback dinâmico em 3 tiers (`deepseek-r1` ➔ `llama-3.3-70b` ➔ `qwen3`).
- **Faturamento & Documentos:** Mercado Pago API v1 (Checkout Pro e Preapprovals com webhooks HMAC-SHA256) + DomPDF (certificados A4 sob demanda).
- **Orquestração:** Docker Compose com serviços previstos (`app`, `mysql`, `redis`, `horizon`, `reverb`).

<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->

## Conventions

- **Linguagem:** Código-fonte, models, variáveis e métodos em Inglês; documentação, interface, logs e comunicação em PT-BR.
- **Multi-Tenancy:** Todo modelo pertencente a empresas deve usar o trait `BelongsToTenant` (com `TenantScope` e injeção automática de `company_id`).
- **Autorização (RBAC):** Uso do enum `UserRole` (`admin_alvora`, `company_admin`, `instructor`, `student`) com middleware `role:`.
- **Assinatura Corporativa:** Rotas protegidas por `subscription.active` com esteira de 5 dias de carência.
- **Tarefas de IA:** Processadas em background ou acionadas com feedback visual de carregamento (`wire:loading`).
- **Webhooks Seguros:** Validação criptográfica do cabeçalho `x-signature` via HMAC-SHA256 antes de qualquer processamento.

<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->

## Architecture

- **Padrão:** Monolito Modular Laravel com domínios delimitados (`Admin`, `Company`, `Instructor`, `Student`, `AI`, `Billing`).
- **Multi-Tenancy:** Single-database com isolamento lógico via `company_id` e bypass exclusivo para Super Admin (`admin_alvora`).
- **Motor Anti-Evasão:** Cálculo contínuo do Score (0–100) ponderando 4 fatores: Inatividade (35%), Estagnação (25%), Quizzes (20%) e Desvio de Pares (20%), persistido em `risk_scores` com breakdown JSON.
- **Gatilho de Intervenção:** Score crítico (≥ 70) dispara alerta para a Sala de Situação e mensagem personalizada ao colaborador via `EvasionInterventionService`.
- **Módulo de Certificados (Diretrizes Aprovadas):**
  - **Estabilização prévia:** Estabilizar o fluxo existente (correção de página do PDF, tratamento cross-tenant em validação, concorrência) antes de construir o editor visual.
  - **Preservação de rotas e códigos:** Manter `student.certificate`, `certificates.verify`, UUID e formato de código único `ALV-YYYY-XXXXXX`.
  - **Ponto único de renderização:** `CertificateService::generatePdf()` despacha para o renderizador legado (quando `template_version_id` for nulo) ou para o renderizador versionado (quando vinculado a uma versão baseada em schema JSON).
  - **Geração sob demanda síncrona:** PDFs gerados no momento do download sem exigência de armazenamento permanente de binários; carregamento remoto do Dompdf mantido desativado (`enable_remote = false`).
  - **Snapshots históricos:** Certificados utilizam snapshots dos dados de emissão. Certificados antigos migrados a partir de dados atuais devem ser identificados explicitamente como reconstruídos, sem alegar precisão histórica não comprovada.
  - **Fonte da verdade do editor:** Schema JSON validado no servidor; prévia gerada pelo mesmo renderizador de servidor utilizado no PDF final.
  - **Decisões de negócio pendentes:** Questões como fluxo de reemissão, regras de exclusão/retenção, escopo dos templates e mascaramento LGPD de dados públicos requerem aprovação e não podem ser inventadas pelo agente.

<!-- GSD:architecture-end -->

<!-- GSD:skills-start source:skills/ -->

## Project Skills

- [trello-sync-skill](.agents/skills/trello-sync-skill/SKILL.md): Sincroniza tarefas e pendências do sistema Alvora com o quadro do Trello de forma automática usando comandos como `/trello-sync-skill` ou pedidos diretos de anotação.
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
