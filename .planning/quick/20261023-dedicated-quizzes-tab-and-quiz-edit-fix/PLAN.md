---
wave: 1
depends_on: []
files_modified:
  - app/Livewire/Instructor/QuizEditor.php
  - app/Livewire/Instructor/CourseEditor.php
  - resources/views/livewire/instructor/course-editor.blade.php
  - resources/views/livewire/instructor/curriculum-builder.blade.php
  - resources/views/livewire/instructor/quiz-editor.blade.php
  - routes/web.php
  - tests/Feature/QuizTest.php
status: complete
---

# Quick Task: 20261023-dedicated-quizzes-tab-and-quiz-edit-fix

## Objective
1. **Aba Dedicada para Quizzes (Aba 3):**
   - Transformar a Aba 3 ("3. Quizzes & Avaliações") em uma **aba 100% dedicada** dentro de `CourseEditor`, assim como as abas de Informações Básicas e Mídia & Publicação.
   - Remover os quizzes do rodapé da Grade Curricular (`curriculum-builder.blade.php`), mantendo a grade focada exclusivamente nos módulos, aulas e vídeos.
   - Na aba dedicada de Quizzes: exibir lista de quizzes com suas métricas, botão destacado "+ Criar Nova Avaliação", ações de editar e excluir, e botão de rodapé para avançar para a Aba 4.

2. **Correção do Erro ao Editar Quiz:**
   - Corrigir a assinatura do método `mount(?Course $course = null, ?Quiz $quiz = null)` em `QuizEditor.php`. Na rota `/quizzes/{quiz}/editar`, o Laravel injetava apenas `$quiz`, quebrando a rota por falta do primeiro parâmetro `$course`.
   - Garantir que `$this->course` seja resolvido a partir de `$quiz->course` caso `$course` não seja passado na rota.
   - Adicionar alias de rota `/cursos/{course}/quizzes/{quiz}/editar` para total flexibilidade.
   - Atualizar redirecionamentos e breadcrumbs para retornarem diretamente à Aba Dedicada de Quizzes (`/cursos/{course}/editar?tab=quizzes`).

3. **Validação de Testes:**
   - Adicionar teste para acesso HTTP GET em `route('instructor.quizzes.edit', $quiz)`.
   - Garantir que toda a suíte de testes do Laravel passe com 100% de sucesso.
