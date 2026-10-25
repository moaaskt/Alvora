---
status: complete
date: 2026-10-23
slug: course-editor-tabbed-ux
files_changed:
  - app/Livewire/Instructor/CourseEditor.php
  - resources/views/livewire/instructor/course-editor.blade.php
  - resources/views/livewire/instructor/curriculum-builder.blade.php
  - resources/views/livewire/instructor/quiz-editor.blade.php
  - tests/Feature/CourseTest.php
tests_passed: 93
---

# Summary: Quick Task 20261023-course-editor-tabbed-ux

## O que foi implementado
Reformulação integral da experiência de criação e edição de treinamentos corporativos para o **modelo de 4 Abas** inspirado no Academy LMS:

1. **Aba 1: Informações Básicas**
   - Criação sem fricção: o professor apenas preenche o Título, Instrutor responsável e Descrição/Objetivos.
   - Geração automática de slug da URL (o professor não é obrigado a preencher slug na mão).
   - Botão de ação proeminente: "Criar Curso e Montar Grade Curricular →".
   - Ao salvar um novo curso, ele é persistido como rascunho e avança automaticamente para a Aba 2 (Grade Curricular).

2. **Aba 2: Grade Curricular (Aulas & Módulos)**
   - Integração com a mesma barra superior de 4 abas.
   - Acesso ao Gerador de Ementa com Inteligência Artificial (OpenRouter Cascata).
   - Criação e reordenação de módulos e aulas em vídeo (YouTube, Vimeo, MP4) com preview gratuito.
   - Anexo de materiais complementares e documentos.

3. **Aba 3: Quizzes & Avaliações**
   - Contador de avaliações vinculadas ao curso.
   - Criação e edição de testes de fixação com nota de corte (%).
   - Questões de múltipla escolha com justificativa pedagógica.

4. **Aba 4: Mídia & Publicação**
   - Configuração de Capa/Thumbnail com **card de pré-visualização ao vivo em tempo real** (mostrando exatamente como o aluno verá na vitrine).
   - Configuração de URL amigável (`/aluno/cursos/...`).
   - **Checklist de Prontidão**: valida visualmente se os dados básicos, aulas e capa estão preenchidos.
   - Seletor de status com cartões clicáveis: Rascunho vs. Publicado vs. Arquivado.

5. **Harmonização Visual & Testes:**
   - Unificação do design do QuizEditor com a paleta slate escura padrão da plataforma.
   - Criação de novos testes automatizados cobrindo a navegação por abas e criação automática de curso.
   - Suíte de testes 100% verde (93 testes, 377 asserções aprovadas).
