---
status: complete
completed_at: 2026-10-23
commit_sha: pending
files_modified:
  - app/Livewire/Instructor/CourseEditor.php
  - app/Livewire/Instructor/QuizEditor.php
  - resources/views/livewire/instructor/course-editor.blade.php
  - resources/views/livewire/instructor/curriculum-builder.blade.php
  - resources/views/livewire/instructor/quiz-editor.blade.php
  - routes/web.php
  - tests/Feature/CourseTest.php
  - tests/Feature/QuizTest.php
---

# Quick Task Summary: 20261023-dedicated-quizzes-tab-and-quiz-edit-fix

## O que foi realizado
1. **Aba Dedicada para Quizzes & Avaliações (Aba 3):**
   - Transformação da Aba 3 em uma **aba 100% dedicada** dentro de `CourseEditor` (`?tab=quizzes`), alinhada às demais abas do workspace do curso.
   - Construção da interface com título e descrição da etapa pedagógica, botão de destaque `+ Nova Avaliação`, empty state amigável e informativo, e cards individuais para cada avaliação com nota de corte, contagem de questões, módulo associado e ações diretas de editar e excluir.
   - Implementação do método `deleteQuiz(int $quizId)` com confirmação e notificação toast no `CourseEditor`.
   - Navegação fluida de rodapé permitindo retornar para a Grade Curricular ou avançar para a Aba 4 (Mídia & Publicação).
   - Remoção completa da seção redundante `#quizzes-do-curso` no rodapé da Grade Curricular (`curriculum-builder.blade.php`), mantendo a grade focada em módulos e aulas.

2. **Correção do Erro ao Editar Quiz (`quizzes.edit`):**
   - Corrigida a assinatura do método `mount(?Course $course = null, ?Quiz $quiz = null)` em `QuizEditor.php`. O parâmetro `$course` agora é opcional e, se não fornecido via rota, é resolvido diretamente a partir de `$quiz->course`.
   - Adicionada rota alias `Route::get('/cursos/{course}/quizzes/{quiz}/editar', QuizEditor::class)->name('courses.quizzes.edit')` em `routes/web.php`.
   - Atualizados os redirecionamentos após salvar o quiz e os links de "Cancelar" / "Voltar" para retornarem à aba dedicada: `route('instructor.courses.edit', ['course' => $this->course->id, 'tab' => 'quizzes'])`.

3. **Garantia de Qualidade & Testes:**
   - Adicionados testes em `tests/Feature/QuizTest.php` cobrindo o acesso HTTP 200 à tela de edição de quiz pelas duas rotas (`instructor.quizzes.edit` e `instructor.courses.quizzes.edit`).
   - Adicionado teste em `tests/Feature/CourseTest.php` validando a gestão de quizzes na aba dedicada e exclusão.
   - 95/95 testes da suíte completa passaram com 100% de sucesso.
