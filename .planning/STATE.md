---
gsd_state_version: "1.0"
current_phase: 5
current_phase_name: Faturamento e Assinaturas B2B com Mercado Pago
status: completed
stopped_at: Phase 05 completed (2/2 plans)
last_updated: "2026-10-20T08:36:00.000Z"
last_activity: 2026-10-20
last_activity_desc: Phase 5 completed (Faturamento e Assinaturas B2B com Mercado Pago)
state_head: developer
progress:
  total_phases: 7
  completed_phases: 5
  total_plans: 12
  completed_plans: 12
  percent: 71
---

# Project State

## Project Reference

See: .planning/PROJECT.md (updated 2026-10-19)

**Core value:** Combater a evasão no treinamento corporativo através do cálculo contínuo do Score de Risco de Evasão e intervenção preditiva automatizada via IA antes da desistência do colaborador.
**Current focus:** Phase 6: Alvora AI — Score de Evasão, Intervenção, Chat e Criação

## Current Position

Phase: 5 (Faturamento e Assinaturas B2B com Mercado Pago) — COMPLETED
Plan: 2 of 2 in current phase (12 of 12 total plans)
Status: Phase 5 completed, ready for Phase 6
Last activity: 2026-10-20 — Fase 5 concluída com sucesso (05-01 e 05-02)

Progress: [███████░░░] 71%

## Performance Metrics

**Velocity:**

- Total plans completed: 12
- Average duration: ~10 min
- Total execution time: ~2.1 hours

**By Phase:**

| Phase | Plans | Total | Avg/Plan |
|-------|-------|-------|----------|
| 1. Setup do Projeto, Auth & Multi-Tenancy | 3/3 | 35 min | ~12 min |
| 2. Gestão de Cursos, Módulos e Aulas | 2/2 | 20 min | ~10 min |
| 3. Player de Aula e Experiência do Colaborador | 2/2 | 24 min | ~12 min |
| 4. Quizzes, Certificados e Trilhas de Aprendizado | 3/3 | 25 min | ~8 min |
| 5. Faturamento e Assinaturas B2B com Mercado Pago | 2/2 | 22 min | ~11 min |
| 6. Alvora AI — Score de Evasão, Intervenção, Chat e Criação | 0/4 | - | - |
| 7. Infraestrutura, Tempo Real e Observabilidade | 0/2 | - | - |

**Recent Trend:**

- Last 5 plans: 04-02, 04-03, 05-01, 05-02 completed
- Trend: Fast, Robust & Verified (60/60 tests passing with DatabaseTransactions)

## Accumulated Context

### Decisions

- [Phase 1]: Monolito Laravel 12 + Livewire 3 escolhido para produtividade e arquitetura reativa integrada.
- [Phase 1]: Multi-tenancy lógico isolado via `companies` e `company_id`.
- [Phase 1]: Trait `BelongsToTenant` com `TenantScope` e auto-injeção de `company_id` em criação de modelos.
- [Phase 1]: Super admin `admin_alvora` com bypass global transparente de escopo de tenant.
- [Phase 1]: Convites de colaboradores com validade de 7 dias, suporte a múltiplos e-mails e seleção de perfil (`student` / `instructor`).
- [Phase 2]: Modelagem hierárquica `Course` -> `Module` -> `Lesson` -> `LessonAttachment` todos com `BelongsToTenant`.
- [Phase 2]: Grade curricular unificada gerenciada via componente Livewire `CurriculumBuilder` com reordenação dinâmica.
- [Phase 3]: Player de vídeo com anotações reativas em tempo real e barra de progresso imediata no componente `LessonPlayer`.
- [Phase 4]: Quizzes com múltiplos tipos de questões, feedback imediato e histórico de tentativas gravadas em `quiz_attempts`.
- [Phase 4]: Certificados em PDF A4 paisagem gerados via `barryvdh/laravel-dompdf` com código de autenticidade único `ALV-YYYY-XXXXXX` e rota de verificação pública sem login.
- [Phase 4]: Trilhas Corporativas com cálculo consolidado em tempo real de progresso e atribuição em lote por departamento ou para todos os colaboradores.
- [Phase 5]: Integração com Mercado Pago Checkout Pro (redirecionamento seguro para evitar manuseio de dados PCI no servidor).
- [Phase 5]: Webhook idempotente com validação criptográfica HMAC-SHA256 (`x-signature`).
- [Phase 5]: Tolerância de carência de 5 dias em inadimplência (`status = overdue`) antes da suspensão total (`status = suspended`).
- [Phase 5]: Middleware `CheckSubscriptionStatus` bloqueando colaboradores mas preservando acesso do RH estritamente para regularização em `/empresa/faturamento`.
- [Phase 5]: Upgrades com cobrança integral e renovação instantânea do ciclo de 30 dias com cota expandida de vagas (`max_users`).

### Pending Todos

None.

### Blockers/Concerns

None.

## Deferred Items

*(none)*

## Session Continuity

Last session: 2026-10-20T08:36:00.000Z
Stopped at: Phase 05 completed (2/2 plans)
Resume file: .planning/phases/06-alvora-ai-score-de-evasao-intervencao-chat-e-criacao/
