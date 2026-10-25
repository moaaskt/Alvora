---
wave: 1
depends_on: []
files_modified:
  - app/Livewire/Instructor/CourseEditor.php
  - resources/views/livewire/instructor/course-editor.blade.php
  - resources/views/livewire/instructor/curriculum-builder.blade.php
  - resources/views/livewire/instructor/quiz-editor.blade.php
  - tests/Feature/CourseTest.php
status: complete
---

# Quick Task: 20261023-course-editor-tabbed-ux

## Objective
Reformular a experiência de criação e edição de cursos para o modelo de **4 Abas**, inspirado no Academy LMS:
1. **Aba 1: Informações Básicas** (Título, Instrutor Responsável, Descrição e Objetivos).
2. **Aba 2: Grade Curricular** (Gerador com IA, Módulos, Aulas em vídeo, Materiais anexos).
3. **Aba 3: Avaliações & Quizzes** (Provas de fixação, questões de múltipla escolha, nota de corte).
4. **Aba 4: Mídia & Publicação** (Thumbnail/Capa com preview ao vivo, Slug/URL amigável, Checklist de prontidão do curso, Seletor de Status Rascunho/Publicado).

### Requisitos e Regras:
- Criação fluida: ao criar novo curso, o professor preenche o básico e avança para a grade com 1 clique.
- Edição integrada: navegação entre abas sem fricção, mantendo total retrocompatibilidade e isolamento multi-tenant.
- Suíte de testes (`php artisan test`) 100% verde sem regressões.
