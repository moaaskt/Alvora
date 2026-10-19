---
phase: "02"
plan: "02-01"
title: "Modelagem e CRUD de Cursos com Status de Publicação"
status: "completed"
completed_at: "2026-10-19T17:07:00-03:00"
requirements:
  - CURS-01
files_created:
  - app/Models/Course.php
  - app/Livewire/Instructor/CourseList.php
  - app/Livewire/Instructor/CourseEditor.php
  - resources/views/livewire/instructor/course-list.blade.php
  - resources/views/livewire/instructor/course-editor.blade.php
  - database/migrations/2026_10_19_000004_create_courses_table.php
  - tests/Feature/CourseTest.php
files_modified:
  - routes/web.php
  - resources/views/layouts/navigation.blade.php
---

# Plan 02-01 Summary: Modelagem e CRUD de Cursos com Status de Publicação

## Accomplished
1. **Entidade de Cursos & Multi-Tenancy (CURS-01)**:
   - Migration `courses` com `company_id`, `instructor_id`, `title`, `slug`, `description`, `thumbnail` e `status` (`draft`, `published`, `archived`).
   - Índice único para `['company_id', 'slug']` garantindo URLs limpas e exclusividade dentro de cada empresa.
   - Model `Course` com trait `BelongsToTenant` e gerador autônomo de slug único.
2. **Interface Reativa do Instrutor em Livewire 3**:
   - `CourseList`: Grade com busca textual instantânea, filtros por status, badges visuais, paginação e botão de alternância de publicação (`draft` <-> `published`) com toasts reativos.
   - `CourseEditor`: Formulário completo com atualização reativa de slug via digitação do título, seleção de instrutor e redirecionamento direto para a grade curricular.
3. **Controle de Acesso por Papel**:
   - Rotas sob `/instrutor/cursos` restritas a `instructor` e `company_admin` (com bypass global para `admin_alvora`).
   - Alunos comuns são bloqueados com HTTP 403.
4. **Verificação Automatizada**:
   - 5 testes de feature em `tests/Feature/CourseTest.php` aprovados com 13 asserções, cobrindo autorização, criação, auto-slug, alternância de status e isolamento estrito multi-tenant.
