---
phase: "02"
plan: "02-02"
title: "Gestão de Módulos, Aulas, Materiais Anexos & Ordenação"
status: "completed"
completed_at: "2026-10-19T17:17:00-03:00"
requirements:
  - CURS-02
files_created:
  - app/Models/Module.php
  - app/Models/Lesson.php
  - app/Models/LessonAttachment.php
  - app/Livewire/Instructor/CurriculumBuilder.php
  - resources/views/livewire/instructor/curriculum-builder.blade.php
  - database/migrations/2026_10_19_000005_create_modules_table.php
  - database/migrations/2026_10_19_000006_create_lessons_table.php
  - database/migrations/2026_10_19_000007_create_lesson_attachments_table.php
  - tests/Feature/CurriculumTest.php
files_modified:
  - routes/web.php
---

# Plan 02-02 Summary: Gestão de Módulos, Aulas, Materiais Anexos & Ordenação

## Accomplished
1. **Estrutura Hierárquica Completa (CURS-02)**:
   - Migrações e modelos para `Module`, `Lesson` e `LessonAttachment`, todos com trait `BelongsToTenant` para isolamento automático por empresa.
   - Suporte a título, descrição, link de vídeo (YouTube, Vimeo, MP4), duração em minutos, indicação de prévia gratuita (`is_free_preview`) e anexos para download.
2. **Componente Reativo CurriculumBuilder em Livewire 3**:
   - Gestão da grade curricular do curso em tela única.
   - Criação e exclusão de módulos sequenciais.
   - Inclusão de aulas dentro de cada módulo com cálculo de tempo e status.
   - Reordenação dinâmica com persistência numérica (`order`) para módulos e aulas.
   - Adição e remoção de materiais complementares e links de apoio.
3. **Verificação Automatizada**:
   - 4 testes de feature em `tests/Feature/CurriculumTest.php` com 15 asserções aprovadas.
   - Suíte de regressão totalizando 27 testes aprovados e 96 asserções no projeto.
