---
gsd_state_version: "1.0"
current_phase: 5
current_phase_name: Faturamento e Assinaturas B2B com Mercado Pago
status: ready_to_plan
stopped_at: Ready to plan Phase 5
last_updated: "2026-10-19T18:23:00.000Z"
last_activity: 2026-10-19
last_activity_desc: Ready to plan Phase 5 (Faturamento e Assinaturas B2B com Mercado Pago)
progress:
  total_phases: 7
  completed_phases: 4
  total_plans: 12
  completed_plans: 10
  percent: 57
---

# Project State

## Project Reference

See: .planning/PROJECT.md (updated 2026-10-19)

**Core value:** Combater a evasão no treinamento corporativo através do cálculo contínuo do Score de Risco de Evasão e intervenção preditiva automatizada via IA antes da desistência do colaborador.
**Current focus:** Phase 5: Faturamento e Assinaturas B2B com Mercado Pago

## Current Position

Phase: 5 (Faturamento e Assinaturas B2B com Mercado Pago) — READY TO PLAN
Plan: 0 of 2 in current phase
Status: Ready to plan
Last activity: 2026-10-19 — Transição para Fase 5 após conclusão integral da Fase 4

Progress: [██████░░░░] 57%

## Performance Metrics

**Velocity:**

- Total plans completed: 10
- Average duration: ~10 min
- Total execution time: ~1.7 hours

**By Phase:**

| Phase | Plans | Total | Avg/Plan |
|-------|-------|-------|----------|
| 1. Setup do Projeto, Auth & Multi-Tenancy | 3/3 | 35 min | ~12 min |
| 2. Gestão de Cursos, Módulos e Aulas | 2/2 | 20 min | ~10 min |
| 3. Player de Aula e Experiência do Colaborador | 2/2 | 24 min | ~12 min |
| 4. Quizzes, Certificados e Trilhas de Aprendizado | 3/3 | 25 min | ~8 min |
| 5. Faturamento e Assinaturas B2B com Mercado Pago | 0/2 | - | - |
| 6. Alvora AI — Score de Evasão, Intervenção, Chat e Criação | 0/4 | - | - |
| 7. Infraestrutura, Tempo Real e Observabilidade | 0/2 | - | - |

**Recent Trend:**

- Last 5 plans: 03-01, 03-02, 04-01, 04-02, 04-03 completed
- Trend: Fast, Robust & Verified (50/50 tests passing with DatabaseTransactions)

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

### Pending Todos

None.

### Blockers/Concerns

None.

## Deferred Items

*(none)*

## Session Continuity

Last session: 2026-10-19
Stopped at: Phase 4 completed; ready to plan Phase 5
Resume file: .planning/ROADMAP.md
