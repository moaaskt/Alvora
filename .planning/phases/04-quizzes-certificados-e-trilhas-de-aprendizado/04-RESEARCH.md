# Phase 4 Research: Quizzes, Certificados e Trilhas de Aprendizado

## Architecture & Domain Decisions

### 1. Quizzes e Avaliações de Múltipla Escolha (EVAL-01, EVAL-02)
- **Vínculo Hierárquico:** Um Quiz pertence a um `Module` ou a um `Course` e é protegido pela trait `BelongsToTenant`.
- **Composição Pedagógica:**
  - `quizzes`: `id`, `company_id`, `course_id`, `module_id` (nullable), `title`, `passing_score` (default 70%), `max_attempts` (default 0 para ilimitadas).
  - `quiz_questions`: `id`, `company_id`, `quiz_id`, `question_text`, `explanation` (feedback pedagógico), `order`.
  - `quiz_options`: `id`, `quiz_question_id`, `option_text`, `is_correct` (boolean).
  - `quiz_attempts`: `id`, `company_id`, `user_id`, `quiz_id`, `score` (0-100), `passed` (boolean), `answers_json`, `completed_at`.
- **Mecânica Reativa Livewire 3:**
  - O aluno visualiza uma questão por vez ou a prova completa com transições fluidas.
  - Ao submeter, a correção é instantânea: exibe alternativas corretas/incorretas, porcentagem final obtida e se atingiu o limiar de aprovação (`passing_score`).

### 2. Geração de Certificados em PDF com DomPDF (EVAL-03)
- **Pacote:** `barryvdh/laravel-dompdf` (^3.0).
- **Critérios de Emissão:**
  - 100% das aulas concluídas (`enrollments.progress_percent == 100`).
  - Aprovação em todos os quizzes obrigatórios do curso (`passed == true`).
- **Composição do Certificado:**
  - `certificates`: `id`, `company_id`, `user_id`, `course_id`, `uuid`, `validation_code` (ex: `ALV-YYYY-XXXXXX`), `total_hours_minutes`, `issued_at`.
  - Layout corporativo Blade premium com fundo elegante, assinatura institucional, nome da empresa cliente, nome do colaborador, data e QR code/código de autenticidade para validação pública no endpoint `/validar-certificado/{code}`.

### 3. Trilhas de Aprendizado Corporativas (EVAL-04)
- **Diferencial Corporativo (RH):** Organização de cursos em uma jornada sequencial ou temática, vinculada a departamentos (ex: "Tecnologia", "Liderança", "Onboarding Comercial") com marcação de obrigatoriedade.
- **Estrutura:**
  - `learning_tracks`: `id`, `company_id`, `title`, `slug`, `description`, `department` (nullable), `is_mandatory` (boolean default true).
  - `track_courses`: `id`, `track_id`, `course_id`, `order`.
  - `track_enrollments`: `id`, `company_id`, `user_id`, `track_id`, `progress_percent`, `status` (`active`, `completed`).
- **Experiência do Usuário:**
  - Gestor de RH cria e associa colaboradores ou departamentos inteiros.
  - Colaborador visualiza o badge "Trilha Obrigatória" com progresso cumulativo de todos os cursos participantes.
