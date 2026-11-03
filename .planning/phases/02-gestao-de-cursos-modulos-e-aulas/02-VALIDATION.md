---
phase: "2"
slug: "gestao-de-cursos-modulos-e-aulas"
status: completed
nyquist_compliant: true
wave_0_complete: true
created: "2026-10-19"
---

# Phase 2 — Validation Strategy

> Validation contract for course, module and lesson management.

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| Framework | PHPUnit (Laravel 12 nativo) |
| Config file | `phpunit.xml` |
| Test database | MySQL `alvora_test` (port 3307) |
| Full suite command | `php artisan test` |

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Secure Behavior | Test Type | Automated Command | File Exists | Status |
|---------|------|------|-------------|-----------------|-----------|-------------------|-------------|--------|
| 2-01-01 | 01 | 1 | CURS-01 | CRUD de Cursos com slug único e isolamento por tenant | feature | `php artisan test --filter=CourseTest` | ❌ W0 | ⬜ pending |
| 2-01-02 | 01 | 1 | CURS-01 | Apenas instrutores/admins podem gerenciar cursos | feature | `php artisan test --filter=CoursePermissionTest` | ❌ W0 | ⬜ pending |
| 2-02-01 | 02 | 2 | CURS-02 | Criação e reordenação de módulos e aulas | feature | `php artisan test --filter=CurriculumTest` | ❌ W0 | ⬜ pending |
| 2-02-02 | 02 | 2 | CURS-02 | Anexo de materiais e validação de URLs de vídeo | feature | `php artisan test --filter=LessonAttachmentTest` | ❌ W0 | ⬜ pending |

---

## Wave 0 Requirements

- [ ] `tests/Feature/CourseTest.php`
- [ ] `tests/Feature/CurriculumTest.php`

**Approval:** approved 2026-10-19
