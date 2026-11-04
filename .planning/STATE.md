---
gsd_state_version: "1.0"
current_phase: 8
current_phase_name: "Governança de Rotas, Permissões Contextuais e Isolamento"
status: ready_to_execute
stopped_at: "Fase 08 formalizada e pronta para revisão/aprovação de execução"
last_updated: "2026-11-03T00:02:00.000Z"
last_activity: 2026-11-03
last_activity_desc: "Abertura formal da Fase 08 (B2B + B2C) sem alterações em código ou banco de dados"
progress:
  total_phases: 14
  completed_phases: 8
  total_plans: 26
  completed_plans: 20
  percent: 77
---

# Project State

## Project Reference

See: `.planning/PROJECT.md` e `.planning/milestones/v1.0-ROADMAP.md`

**Core value:** Plataforma LMS multi-tenant que atende tanto instrutores independentes (B2C) quanto organizações corporativas (B2B), com combate à evasão via Score Preditivo de Risco e intervenções ativas de IA.  
**Current focus:** Fase 08 — Governança de Rotas, Permissões Contextuais e Isolamento de Cursos Corporativos.

## Current Position

Phase: 08 (Governança de Rotas, Permissões Contextuais e Isolamento) — EM ANDAMENTO
Plan: 08-01 (In Execution)
Status: Tasks 8.1, 8.2 and 8.3 executed and committed; Task 8.4 ready
Last activity: 2026-11-04 — Implementação das Tarefas 8.1, 8.2 e 8.3 concluída.

Progress: [█████████░] 85%

## Performance & Test Status

**Suíte de Testes Automatizados (PHPUnit / Feature):**
- Total de Testes da Aplicação: 322 testes executados (321 aprovados, 1 skipped, 0 falhas).
- Novos testes: `UserContextualPermissionsTest` (3 testes), `DecoupledRoutesAndNavigationTest` (7 testes), `CourseAccessScopeAndPoliciesTest` (6 testes) — 100% aprovados.

## Roadmap das Fases B2B + B2C (08 a 13)

| Phase | Nome | Status |
|---|---|---|
| **08** | **Governança de Rotas, Permissões Contextuais e Isolamento** | **Em Execução (Tarefas 8.1, 8.2 e 8.3 concluídas)** 🔨 |
| 09 | Suporte a Instrutores Independentes e Schema de Cursos | Futura |
| 10 | Catálogo Público e Ciclo de Vida de Matrículas | Futura |
| 11 | Progresso, Avaliações e Certificados Híbridos | Futura |
| 12 | Inteligência de Evasão Multi-Contexto e Idempotência | Futura |
| 13 | Testes Integrados, Validação de Concorrência e Estabilização | Futura |

## Accumulated Context

### Recent Architectural Decisions
- [Phase 08]: `company_users` é formalizada como autoridade suprema para permissões corporativas; `users.company_id` atua como contexto de sessão ativa validado contra a associação.
- [Phase 08]: Separação física de rotas entre `/empresa/*` (gestão corporativa) e `/instrutor/*` (gestão pedagógica), permitindo trânsito fluido para usuários com múltiplos papéis.
- [Phase 08]: `ResourceAccessScope` restringe rascunhos corporativos estritamente ao instrutor autor e gestores autorizados.
- [Phase 08]: `EnrollmentCreationService` centraliza as regras de autorização de matrículas corporativas; o model `Enrollment` concentra a integridade relacional.

### Pending Todos
- [ ] Revisão e aprovação formal do plano `08-01-PLAN.md` antes de iniciar qualquer implementação.

### Blockers/Concerns
- Nenhuma alteração de código ou banco de dados realizada.
