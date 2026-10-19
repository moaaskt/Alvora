---
phase: "1"
slug: "setup-do-projeto-auth-multi-tenancy"
status: draft
nyquist_compliant: true
wave_0_complete: false
created: "2026-10-19"
---

# Phase 1 — Validation Strategy

> Per-phase validation contract for feedback sampling during execution.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | PHPUnit / Pest (Laravel 12 nativo) |
| **Config file** | `phpunit.xml` |
| **Quick run command** | `php artisan test --filter=TenantTest` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~5 seconds |

---

## Sampling Rate

- **After every task commit:** Run quick smoke test / lint
- **After every plan wave:** Run `php artisan test`
- **Before `/gsd-verify-work`:** Full suite must be green

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------------|-----------|-------------------|-------------|--------|
| 1-01-01 | 01 | 1 | BILL-01 | Landing page carrega sem erros | feature | `php artisan test --filter=LandingPageTest` | ❌ W0 | ⬜ pending |
| 1-02-01 | 02 | 1 | AUTH-02 | Usuário da Empresa A não vê dados da Empresa B | unit/feature | `php artisan test --filter=TenantScopeTest` | ❌ W0 | ⬜ pending |
| 1-02-02 | 02 | 1 | AUTH-03 | Controle de 4 perfis (`admin_alvora`, `company_admin`, `instructor`, `student`) | feature | `php artisan test --filter=RoleAccessTest` | ❌ W0 | ⬜ pending |
| 1-03-01 | 03 | 2 | AUTH-01 | Login com Sanctum mantém sessão ativa por perfil | feature | `php artisan test --filter=AuthenticationTest` | ❌ W0 | ⬜ pending |
| 1-03-02 | 03 | 2 | AUTH-04 | Convite de colaborador gera token e aceita cadastro | feature | `php artisan test --filter=InvitationTest` | ❌ W0 | ⬜ pending |

---

## Wave 0 Requirements

- [ ] `tests/Feature/TenantScopeTest.php` — teste de isolamento de tenant
- [ ] `tests/Feature/AuthenticationTest.php` — teste de autenticação e roles
- [ ] `tests/Feature/InvitationTest.php` — teste do fluxo de convites

---

## Validation Sign-Off

- [x] All tasks have `<automated>` verify or Wave 0 dependencies
- [x] Sampling continuity: no 3 consecutive tasks without automated verify
- [x] Wave 0 covers all MISSING references
- [x] No watch-mode flags
- [x] `nyquist_compliant: true` set in frontmatter

**Approval:** approved 2026-10-19
