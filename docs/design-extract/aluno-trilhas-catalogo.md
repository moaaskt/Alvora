# Investigação Técnica: Separação do Dashboard do Aluno em 3 Telas

Este documento apresenta a análise técnica detalhada da base de código do ALVORA para subsidiar a divisão do painel do aluno atual em três páginas independentes:
1. **Meu Aprendizado (Dashboard do Aluno)**
2. **Minhas Trilhas Corporativas**
3. **Catálogo de Cursos Corporativos**

Nenhuma alteração de código ou banco de dados foi realizada nesta etapa.

---

## 1. ESTADO ATUAL (CURRENT STATE)

### Consultas e Carregamento no Dashboard Atual
No componente Livewire atual [`App\Livewire\Student\Dashboard`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Livewire/Student/Dashboard.php#L90-L136):

1. **Cursos Matriculados (`$enrollments`)**:
   - **Query**:
     ```php
     Enrollment::with(['course.modules.lessons'])
         ->where('user_id', $user->id)
         ->latest('updated_at')
         ->get();
     ```
   - **Multi-tenancy / Scopes**: O model `Enrollment` utiliza a trait `BelongsToTenant` que injeta o escopo global `TenantScope` (`company_id = Auth::user()->company_id`). O filtro `where('user_id', $user->id)` isola as matrículas do colaborador autenticado.
   - **Eager Loading**: Carrega `course.modules.lessons` aninhado.
   - **Paginação / Limite**: Nenhum. Invoca `->get()`, retornando todos os registros na memória.

2. **Métricas Consolidadas (Cards de Estatísticas)**:
   - Calculadas em memória a partir da Collection `$enrollments`:
     - `totalEnrolled`: `$enrollments->count()`
     - `completedCount`: `$enrollments->where('status', 'completed')->count()`
     - `inProgressCount`: `$enrollments->where('status', 'active')->count()`
     - `averageProgress`: `$enrollments->avg('progress_percent')` (arredondado para inteiro).

3. **Cursos Disponíveis da Empresa (`$availableCourses`)**:
   - **Query**:
     ```php
     Course::where('status', 'published')
         ->whereNotIn('id', $enrolledCourseIds)
         ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
         ->withCount('lessons')
         ->latest()
         ->get();
     ```
   - **Multi-tenancy**: `Course` utiliza a trait `BelongsToTenant` (`company_id`). O aluno enxerga apenas cursos publicados do seu próprio tenant que ainda não matriculou.
   - **Eager Loading / Agregações**: Executa `withCount('lessons')` para alimentar o texto `"X aulas"`.
   - **Paginação / Limite**: Sem paginação (usa `->get()`).

4. **Trilhas de Aprendizado Corporativas (`$trackEnrollments`)**:
   - **Query**:
     ```php
     \App\Models\TrackEnrollment::with(['track.courses'])
         ->where('user_id', $user->id)
         ->get();
     ```
   - **Multi-tenancy**: `TrackEnrollment` possui `BelongsToTenant` e filtra por `user_id`.
   - **Eager Loading**: Carrega `track.courses` (que por padrão é ordenado pela coluna de pivô `track_courses.order`).
   - **Paginação / Limite**: Sem limite ou paginação (`->get()`).
   - **Efeito Colateral em Leitura**: Itera sobre todas as matrículas em trilha e chama `$te->updateConsolidatedProgress()`, executando queries de `UPDATE` a cada renderização da página.

---

## 2. DATA AVAILABLE FOR THE CATALOG

Análise dos modelos [`Course`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Course.php), [`Module`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Module.php), [`Lesson`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Lesson.php) e migrations correspondentes:

### Colunas e Atributos Reais

| Campo / Relação | Tabela / Origem | Existe no Banco? | Preenchido no Formulário do Instrutor? | Exibido ao Aluno Hoje? | Detalhes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **`title`** | `courses.title` | **Sim** (`string`) | **Sim** (`CourseEditor`) | **Sim** | Título do curso |
| **`slug`** | `courses.slug` | **Sim** (`string`) | **Sim** (gerado do título) | **Não** (usado em rotas de URL) | Identificador amigável único por tenant |
| **`description`** | `courses.description` | **Sim** (`text`, nullable) | **Sim** (`CourseEditor`) | **Sim** (truncado em 2 linhas) | Descrição do curso |
| **`thumbnail`** | `courses.thumbnail` | **Sim** (`string`, nullable) | **Sim** (`CourseEditor`) | **Sim** (capa 16:10 do card) | URL/path da imagem de capa |
| **`status`** | `courses.status` | **Sim** (`string`) | **Sim** (`draft`, `published`, `archived`) | **Não** (apenas cursos `published` aparecem) | Filtro de publicação |
| **`instructor_id`** / `instructor` | `courses.instructor_id` | **Sim** (FK `users.id`) | **Sim** (selecionado no form) | **NÃO** no dashboard do aluno (apenas no admin/vitrine) | Relação `belongsTo(User::class)` existe |
| **`created_at`** / `published` | `courses.created_at` | **Sim** (`timestamps`) | Automático pelo Eloquent | **Não** (usado internamente em `latest()`) | Data de criação no sistema |
| **Total de Aulas (`lessons_count`)** | `courses.lessons` | **Sim** (relação `hasMany`) | Calculado via `withCount('lessons')` | **Sim** (`{{ $course->lessons_count }} aulas`) | Agregação relacional |
| **Duração Total (`total_duration`)** | `lessons.duration_minutes` | **Sim** no banco (`lessons`), mas agregável | Campo `duration_minutes` existe em `Lesson` | **NÃO** no dashboard do aluno (usado na landing/admin) | Requer `withSum('lessons as total_duration_minutes', 'duration_minutes')` |
| **Módulos (`modules`)** | `modules` | **Sim** (relação `hasMany`) | Cadastrado no `CurriculumBuilder` | **Não** no card de catálogo | Estrutura de conteúdo |

### O Que EXPLICITAMENTE NÃO EXISTE no Modelo de Cursos (Sem Migração)
- **`category` / `area`**: **NÃO EXISTE**. Não há coluna de categoria, tags ou área de conhecimento na tabela `courses`. *(Nota: O teste `StudentDashboardTest` linha 262 insere `'category' => 'Infraestrutura'` apenas porque testes usam arrays flexíveis, mas a coluna não existe no schema da migration `2026_10_19_000004_create_courses_table.php`)*.
- **`tags`**: **NÃO EXISTE**. Não há tabela ou coluna de tags.
- **`level` / `dificuldade`**: **NÃO EXISTE**. Não existe campo de nível (iniciante, intermediário, avançado).
- **`rating` / `avaliação` / `estrelas`**: **NÃO EXISTE**. Não existe sistema de avaliação de cursos por alunos.
- **`price` / `preço`**: **NÃO EXISTE**. O modelo da Alvora é B2B Corporativo (assinatura por empresa); não há precificação individual por curso. O botão atual exibe `"Matricular-se Gratuitamente"` porque o acesso decorre do contrato corporativo da empresa.
- **`instructor name` visível ao aluno**: Não é exibido no card do aluno atualmente (embora a relação `$course->instructor->name` exista no banco, hoje não compõe o card do aluno).
- **`duration_total` coluna direta**: Não existe coluna de duração total pré-calculada em `courses` (apenas a soma dinâmica de `lessons.duration_minutes`).
- **`mandatory` em cursos avulsos**: A flag de obrigatoriedade pertence à Trilha (`learning_tracks.is_mandatory`), não ao modelo `Course`.

---

## 3. DATA AVAILABLE FOR TRACKS

Análise dos modelos [`LearningTrack`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/LearningTrack.php), [`TrackEnrollment`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/TrackEnrollment.php) e migration [`2026_10_19_000013_create_tracks_tables.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/database/migrations/2026_10_19_000013_create_tracks_tables.php):

### Colunas e Atributos Reais

| Campo / Relação | Tabela / Origem | Existe no Banco? | Preenchido pelo Gestor/RH? | Exibido ao Aluno Hoje? | Detalhes |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **`title`** | `learning_tracks.title` | **Sim** (`string`) | **Sim** (`TrackManager`) | **Sim** | Título da trilha |
| **`slug`** | `learning_tracks.slug` | **Sim** (`string`) | Automático via slugify | **Não** | Identificador URL único por tenant |
| **`description`** | `learning_tracks.description` | **Sim** (`text`, nullable) | **Sim** (`TrackManager`) | **Sim** (truncado em 2 linhas) | Descrição do objetivo da trilha |
| **`department`** | `learning_tracks.department` | **Sim** (`string`, nullable) | **Sim** (`TrackManager`) | **Sim** (texto discreto no cabeçalho) | Ex: "Tecnologia", "Compliance" |
| **`is_mandatory`** | `learning_tracks.is_mandatory` | **Sim** (`boolean`, default `true`) | **Sim** (`TrackManager`) | **Sim** (`Obrigatória` em âmbar ou `Opcional` em cinza) | Indicador de conformidade |
| **Ordenação de Cursos** | `track_courses.order` | **Sim** (`unsignedInteger`) | **Sim** (`TrackManager`) | **Sim** (numeração `1.`, `2.`, etc.) | Coluna `order` na tabela pivô `track_courses` |
| **Progresso Consolidado** | `track_enrollments.progress_percent` | **Sim** (`unsignedTinyInteger`, 0-100) | Calculado pelo sistema | **Sim** (barra e percentual) | Persistido na matrícula da trilha |
| **Status da Trilha** | `track_enrollments.status` | **Sim** (`string`, default `'active'`) | Atualizado para `'completed'` quando 100% | **Sim** (badge/cor verde se concluído) | Valores: `active`, `completed` |
| **Data de Conclusão** | `track_enrollments.completed_at` | **Sim** (`timestamp`, nullable) | Preenchido automaticamente aos 100% | **Não** no layout atual | Timestamp de término |

### Regras de Negócio e Comportamento Operacional
1. **Prazos / Deadlines**: **NÃO EXISTEM**. Não existem colunas como `due_date`, `deadline` ou `expires_at` em `learning_tracks` nem em `track_enrollments`.
2. **Quem atribui trilhas?**:
   - Apenas o RH / Administrador da Empresa (`company_admin` ou `instructor`) através do [`TrackManager`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Livewire/Company/TrackManager.php).
   - O gestor pode selecionar colaboradores manualmente ou marcar `"assignAllEmployees"` para matricular todos os colaboradores da empresa.
   - **Não há atribuição automática por departamento**: O campo `department` na trilha é meramente informativo/textual; não dispara triggers ou vínculos automáticos baseados no departamento do usuário.
3. **Auto-matrícula em Trilhas**:
   - **Hoje não existe auto-matrícula de aluno em trilhas**.
   - O aluno hoje **só enxerga** trilhas nas quais já possui registro prévio em `track_enrollments` (`TrackEnrollment::where('user_id', $user->id)`).
   - Se o aluno não tiver nenhuma trilha atribuída, a seção inteira de trilhas fica oculta (`@if ($trackEnrollments->isNotEmpty())`).

---

## 4. WHAT CAN BE FILTERED, SORTED AND PAGINATED

### Catálogo de Cursos Corporativos (`student.catalog`)

#### Filtros Viáveis SEM Novas Migrações
- **Busca por texto**: Filtrar em `courses.title` e `courses.description` (`like %term%`).
- **Status de Matrícula**:
  - `Disponíveis para Matrícula` (`whereNotIn('id', $enrolledCourseIds)`)
  - `Já Matriculados` (`whereIn('id', $enrolledCourseIds)`)
  - `Todos` (sem filtro de exclusão)
- **Instrutor Responsável**: Filtrar por `courses.instructor_id` usando a lista de instrutores que possuem cursos publicados no tenant.

#### Ordenações Viáveis SEM Novas Migrações
- **Mais Recentes**: `orderByDesc('created_at')` ou `latest('id')` (padrão atual).
- **Mais Antigos**: `orderBy('created_at')`.
- **Alfabética (A-Z / Z-A)**: `orderBy('title', 'asc')` / `orderBy('title', 'desc')`.
- **Quantidade de Aulas**: `orderBy('lessons_count', 'desc')` (combinado com `withCount('lessons')`).
- **Duração Total**: `orderBy('total_duration_minutes', 'desc')` (combinado com `withSum('lessons as total_duration_minutes', 'duration_minutes')`).

#### Filtros que EXIGEM Migração [REQUIRES MIGRATION]
- `Filtrar por Categoria / Área` [REQUIRES MIGRATION: adicionar coluna `category_id` ou `category` em `courses`].
- `Filtrar por Nível de Dificuldade` [REQUIRES MIGRATION: adicionar coluna `level` (`beginner`, `intermediate`, `advanced`) em `courses`].
- `Filtrar por Tags` [REQUIRES MIGRATION: criar tabelas `tags` e `course_tag`].
- `Filtrar por Avaliação / Rating` [REQUIRES MIGRATION: criar tabela de reviews/ratings].

---

### Minhas Trilhas Corporativas (`student.tracks`)

#### Filtros Viáveis SEM Novas Migrações
- **Busca por texto**: Filtrar em `learning_tracks.title` e `learning_tracks.description`.
- **Status de Conclusão**:
  - `Todas as Trilhas`
  - `Em Andamento` (`track_enrollments.status = 'active'` ou `progress_percent < 100`)
  - `Concluídas` (`track_enrollments.status = 'completed'` ou `progress_percent = 100`)
- **Obrigatoriedade**:
  - `Todas`
  - `Obrigatórias` (`learning_tracks.is_mandatory = true`)
  - `Opcionais` (`learning_tracks.is_mandatory = false`)
- **Departamento**: Filtrar por `learning_tracks.department` (valores distintos agrupados existentes no tenant).

#### Ordenações Viáveis SEM Novas Migrações
- **Mais Recentes Atribuídas**: `orderByDesc('track_enrollments.created_at')`.
- **Maior / Menor Progresso**: `orderBy('track_enrollments.progress_percent', 'desc'|'asc')`.
- **Alfabética da Trilha**: `orderBy('learning_tracks.title', 'asc'|'desc')`.

#### Filtros que EXIGEM Migração [REQUIRES MIGRATION]
- `Filtrar por Prazo / Data Limite` [REQUIRES MIGRATION: criar `due_date` em `track_enrollments`].
- `Trilhas Disponíveis para Auto-Inscrição` [REQUIRES MIGRATION / REGRA DE NEGÓCIO: política de catálogo de trilhas abertas].

---

### Padrão de Paginação Livewire no Projeto
Verificado em [`CourseList.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Livewire/Instructor/CourseList.php), [`EmployeeManager.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Livewire/Company/EmployeeManager.php) e [`SituationRoom.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Livewire/Company/SituationRoom.php):
- **Trait**: `use Livewire\WithPagination;`.
- **Reset de Página**: Método hooks `updatingSearch(): void { $this->resetPage(); }` e `updatingFilter(): void { $this->resetPage(); }`.
- **Tamanho de Página**: Entre 9 e 12 itens para grids de cards (ex: `->paginate(9)` para grids de 3 colunas `grid-cols-1 md:grid-cols-2 lg:grid-cols-3` preserva simetria visual perfeita).
- **Query String**: Atributos modernos do Livewire 3/4 `#[Url(as: 'q', history: true)]` ou `$queryString = ['search' => ['except' => '']]` para manter URL limpa e sincronizada com paginação e busca.

---

## 5. TRACK PROGRESS WRITES

### Diagnóstico do Problema Atual
No arquivo [`app/Livewire/Student/Dashboard.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Livewire/Student/Dashboard.php#L123-L125), o método `render()` executa:
```php
foreach ($trackEnrollments as $te) {
    $te->updateConsolidatedProgress();
}
```
E dentro de [`TrackEnrollment::updateConsolidatedProgress()`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/TrackEnrollment.php#L68-L81):
```php
public function updateConsolidatedProgress(): void
{
    $calculatedProgress = $this->track->calculateUserProgress($this->user_id);
    $this->progress_percent = $calculatedProgress;

    if ($calculatedProgress >= 100) {
        $this->status = 'completed';
        if (! $this->completed_at) {
            $this->completed_at = now();
        }
    }

    $this->save(); // <--- WRITE NO BANCO EM CADA HTTP GET / RENDER DO LIVEWIRE!
}
```

### Impacto
- Cada requisição de exibição de tela (inclusive digitação com debounce de 300ms na busca) dispara updates no banco de dados para todas as trilhas do aluno.
- Cria contenção de lock desnecessária em MySQL e gera escrita pesada em operações puramente idempotentes de leitura.

### Propostas de Desacoplamento (Sem Implementar)
1. **Abordagem Reativa Orientada a Evento (Recomendada no Domínio)**:
   - O progresso de um curso só muda quando o aluno assiste/conclui uma aula no [`LessonPlayer`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Livewire/Student/LessonPlayer.php) (`toggleLessonCompletion`).
   - Ao recalcular o progresso da matrícula do curso (`$enrollment->recalculateProgress()`), disparar a atualização de quaisquer `TrackEnrollment` associadas que contenham aquele `course_id`.
   - Também é disparado quando o gestor adiciona ou remove cursos da trilha no `TrackManager` (onde já existe chamada para recalcular).
2. **Cálculo em Memória para Exibição (Zero Writes na Leitura)**:
   - Se a tela apenas precisa exibir o progresso consolidado atual, ela pode obter a média das matrículas dos cursos já carregadas em memória via relação `$track->courses`, sem emitir `UPDATE` no banco durante o `render()`.
   - A coluna `track_enrollments.progress_percent` fica restrita para consultas analíticas de relatórios corporativos e é atualizada apenas quando ocorre uma transição de conclusão de aula.

---

## 6. DRAFT COURSES IN TRACKS

### Investigação da Relação
Na model [`LearningTrack.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/LearningTrack.php#L88-L94):
```php
public function courses(): BelongsToMany
{
    return $this->belongsToMany(Course::class, 'track_courses', 'track_id', 'course_id')
        ->withPivot('order')
        ->orderBy('pivot_order');
}
```

### Confirmação
1. **A relação `courses()` NÃO possui a restrição `where('status', 'published')`**.
2. **No dashboard do aluno**:
   - `$trackEnrollments = TrackEnrollment::with(['track.courses'])->where('user_id', $user->id)->get();`
   - Na view: `@foreach ($track->courses as $course)`
3. **Conclusão**:
   - **SIM, um curso em rascunho (`draft`) ou arquivado (`archived`) pode aparecer na lista de cursos da trilha para o aluno**.
   - Se o gestor adicionou o curso enquanto publicado (ou se adicionou via seeder/banco) e posteriormente alterou seu status para `draft` via `togglePublish`, o curso continuará sendo exibido dentro da trilha do aluno.
   - Além disso, o método `$track->calculateUserProgress()` incluirá o curso não publicado no divisor do progresso total, podendo travar a trilha do aluno sem que ele consiga concluí-la.
   - **Recomendação para a nova tela de trilhas**: Filtrar a relação na listagem do aluno para `where('status', 'published')` ou definir um escopo de visualização apropriado.

---

## 7. ROUTES AND NAVIGATION

### Rotas e Links Atuais
- **Rotas Atuais no Portal do Aluno** ([`routes/web.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/routes/web.php#L156-L161)):
  - `student.dashboard` ➔ `/aluno/dashboard`
  - `student.player` ➔ `/aluno/cursos/{course:slug}/aulas/{lesson?}`
  - `student.certificate` ➔ `/aluno/cursos/{course:slug}/certificado`
  - `student.quiz` ➔ `/aluno/quizzes/{quiz}`

- **Navegação Atual**:
  - Na **Sidebar do Aluno** ([`resources/views/livewire/student/dashboard.blade.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/livewire/student/dashboard.blade.php#L23-L48)):
    - Item 1: `href="#meu-aprendizado"` ("Meu Aprendizado")
    - Item 2: `href="#minhas-trilhas"` ("Minhas Trilhas") - só aparece se `$trackEnrollments->isNotEmpty()`
    - Item 3: `href="#catalogo"` ("Catálogo de Cursos")
  - Na **Top Navbar** ([`resources/views/layouts/student.blade.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/layouts/student.blade.php#L42-L51)):
    - `route('student.dashboard')` ("Meu Aprendizado")
    - `route('student.dashboard') . '#catalogo'` ("Catálogo de Cursos")
    - Note que na top navbar oficial `layouts/student.blade.php` não há ainda link para Trilhas.

### Proposta de Novas Rotas
Para desacoplar as 3 telas em URLs dedicadas com rotas RESTful e nomes idiomáticos:
1. **Dashboard**: `Route::get('/aluno/dashboard', StudentDashboard::class)->name('student.dashboard');`
2. **Trilhas**: `Route::get('/aluno/trilhas', StudentTracks::class)->name('student.tracks');`
3. **Catálogo**: `Route::get('/aluno/catalogo', StudentCatalog::class)->name('student.catalog');`

### Mudanças Necessárias em Layouts e Menus
- **Sidebar & Navbar**:
  - Trocar âncoras `#minhas-trilhas` e `#catalogo` por links diretos `route('student.tracks')` e `route('student.catalog')`.
  - Atualizar a classe ativa de navegação para usar `request()->routeIs('student.dashboard')`, `request()->routeIs('student.tracks')` e `request()->routeIs('student.catalog')`.
  - Na tela de LessonPlayer e QuizRunner, manter os botões de retorno apontando para `student.dashboard`.

### Testes que Dependem das Seções e Âncoras Atuais
Ao pesquisar os testes automatizados existentes:
1. [`tests/Feature/StudentDashboardTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/StudentDashboardTest.php):
   - `test_student_can_access_student_dashboard()` (L38-L41): Valida `assertSee('Meu Aprendizado')`, `assertSee('Continuar Aprendendo')`, `assertSee('Cursos Disponíveis da Sua Empresa')`.
   - `test_student_can_enroll_in_published_course()` (L92-L94): Executa `Livewire::test(StudentDashboard::class)->call('enroll', $course->id)`. Se a ação de matricular for movida para o novo componente de Catálogo, este teste precisará ser espelhado no teste do Catálogo.
   - `test_student_dashboard_search_and_empty_states()` (L252-L270): Testa a busca de catálogo no componente `StudentDashboard::class`.
2. [`tests/Feature/LearningTrackTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/LearningTrackTest.php):
   - `test_student_dashboard_displays_enrolled_corporate_tracks()` (L298-L304): Testa `Livewire::test(StudentDashboard::class)->assertSee('Minhas Trilhas Corporativas')` e a listagem das trilhas. Ao separar na rota `student.tracks`, o teste de trilhas passará a validar o componente dedicado `StudentTracks::class`.

---

## 8. EMPTY AND EDGE STATES

### Estados Vazios Existentes Hoje
1. **Cursos Matriculados (Continuar Aprendendo)**:
   - Exibido quando `$enrollments->isEmpty()`.
   - Texto atual: *"Você ainda não está matriculado em nenhum curso. Navegue pelo catálogo corporativo abaixo e inscreva-se para começar seu aprendizado."* com ícone `book-open`.
2. **Trilhas Corporativas**:
   - **Não existe empty state hoje**. O bloco inteiro é omitido silenciosamente via `@if ($trackEnrollments->isNotEmpty())`.
3. **Catálogo de Cursos**:
   - Sem busca digitada: *"Nenhum novo curso disponível encontrado no momento."*
   - Com busca digitada sem match: *"Nenhum curso encontrado para a sua busca."*

### Novos Estados Vazios e de Borda Necessários com o Split

1. **Na Nova Tela "Minhas Trilhas" (`/aluno/trilhas`)**:
   - **Zero trilhas atribuídas**: Como a tela passa a ser um destino navegável próprio na barra lateral, não pode mais ficar oculta ou em branco. Necessita de um empty state acolhedor:
     - Título: *"Nenhuma trilha atribuída no momento"*.
     - Descrição: *"Sua empresa ainda não vinculou seu perfil a uma trilha corporativa de desenvolvimento. Cursos avulsos continuam disponíveis no catálogo."*
     - Ação/CTA: Botão direcionando para `"Explorar Catálogo de Cursos"`.
   - **Busca por trilha sem resultados**: *"Nenhuma trilha encontrada para os termos pesquisados."* com opção de limpar filtro.
   - **Filtro de status sem resultados**: Ex: Aluno filtra por "Concluídas", mas ainda não terminou nenhuma. Mensagem: *"Você ainda não possui trilhas concluídas."*

2. **Na Nova Tela "Catálogo de Cursos" (`/aluno/catalogo`)**:
   - **Empresa sem cursos cadastrados**: *"Sua empresa ainda não publicou treinamentos no catálogo corporativo."*
   - **Colaborador já matriculado em todos os cursos disponíveis**: Quando `availableCourses` for vazio porque o aluno já se inscreveu em 100% dos cursos da empresa:
     - Mensagem: *"Parabéns! Você já está matriculado em todos os cursos disponíveis da sua empresa."*
     - Ação/CTA: Botão `"Ir para Meu Aprendizado"` para continuar assistindo.
   - **Busca ou filtro combinado sem retorno**: *"Nenhum curso corresponde aos filtros selecionados."* com botão de resetar filtros.
   - **Página além da última (Paginação inválida)**: O Livewire `WithPagination` gerencia o reset de página, mas se a URL vier com `?page=99`, exibir fallback gracioso com reset para a página 1.

3. **No Dashboard Principal ("Meu Aprendizado")**:
   - Mantém o empty state de continuar aprendendo quando o aluno não tem matrículas, com botão redirecionando para a rota `/aluno/catalogo`.
   - Pode exibir um card banner/teaser de Trilhas: se tiver trilhas ativas, exibe as 2 mais recentes com link `"Ver todas as trilhas (X) ->"`. Se não tiver nenhuma, exibe card informativo convidando a explorar o catálogo.

---

## 9. STITCH BRIEF

```yaml
brief:
  objective: "Split student experience into 3 focused screens: Dashboard, Minhas Trilhas and Catálogo de Cursos"
  theme: "ALVORA EdTech Light Mode (edtech-blue #0056D2, bg #F4F6F8, surface #FFFFFF, text #0F172A)"
  shared_layout:
    sidebar: "280px sticky: Student avatar, name, company name, menu links (Meu Aprendizado, Minhas Trilhas, Catálogo de Cursos, Sair)"
    top_navbar: "Logo Alvora, company name, current page pills, student avatar, logout button"

  screen_1_dashboard:
    title: "Meu Aprendizado"
    route: "/aluno/dashboard"
    contents:
      - greeting: "Olá, {user.name} + subtitle"
      - stat_cards_4:
          - "Total Matriculado ({totalEnrolled} cursos no seu plano)"
          - "Em Andamento ({inProgressCount} cursos a concluir)"
          - "Concluídos ({completedCount} cursos finalizados)"
          - "Progresso Geral ({averageProgress}% média de conclusão)"
      - active_tracks_teaser: "Resumo das trilhas ativas (se houver) com progresso e link 'Ver todas as trilhas'"
      - enrolled_courses_grid:
          card_fields:
            - thumbnail: "16:10 aspect ratio cover (fallback to gradient book-open icon)"
            - title: "Course title (1 line truncated)"
            - description: "Course description (max 2 lines)"
            - progress_bar: "8px tall bar with % (emerald if 100%, blue gradient if active)"
            - cta: "Button 44px 'Continuar Aula' or 'Rever Aulas' (if completed)"
          empty_state: "Ícone book-open, 'Você ainda não está matriculado em nenhum curso', link para catálogo"

  screen_2_tracks:
    title: "Minhas Trilhas Corporativas"
    route: "/aluno/trilhas"
    filters_feasible:
      - search: "Busca textual por título e descrição"
      - status: "Todas | Em Andamento | Concluídas"
      - priority: "Todas | Obrigatórias | Opcionais"
      - sort: "Mais recentes | Maior progresso | Ordem alfabética"
    pagination: "Livewire pagination (6 or 8 track cards per page)"
    card_fields:
      - department: "Plain muted text (e.g. 'Tecnologia', 'Compliance')"
      - badge: "Text-only: 'Obrigatória' (edtech-amber with triangle icon) or 'Opcional' (muted)"
      - title: "Track title"
      - description: "Track description (max 2 lines)"
      - consolidated_progress: "10px tall progress bar with %"
      - course_list: "Ordered numbered list (order, course title, status 'Concluído' or progress %)"
    empty_states:
      - zero_assigned: "Nenhuma trilha atribuída no momento (com botão para ir ao catálogo)"
      - no_search_results: "Nenhuma trilha encontrada para a busca"

  screen_3_catalog:
    title: "Catálogo de Cursos Corporativos"
    route: "/aluno/catalogo"
    filters_feasible:
      - search: "Busca textual por título e descrição"
      - enrollment_status: "Disponíveis para Matrícula | Já Matriculados | Todos"
      - sort: "Mais recentes | Alfabética (A-Z) | Mais aulas | Maior duração"
    pagination: "Livewire pagination (9 or 12 cards per page in 3 columns)"
    card_fields:
      - thumbnail: "16:10 cover image"
      - lessons_count: "{lessons_count} aulas"
      - total_duration: "Opcional via withSum: '{duration} min'"
      - title: "Course title"
      - description: "Course description (2 lines clamp)"
      - cta: "Button 'Matricular-se Gratuitamente' (se disponível) ou 'Continuar Curso' (se já matriculado)"
    empty_states:
      - all_enrolled: "Parabéns! Você já está matriculado em todos os cursos disponíveis"
      - no_search_results: "Nenhum curso encontrado para a sua busca"

  do_not_draw:
    - "Category / Tag pills (categories/tags DO NOT exist in database)"
    - "Difficulty level badges (levels DO NOT exist in database)"
    - "Star ratings or student review counts (reviews DO NOT exist)"
    - "Prices / Currency values (corporate B2B subscription model)"
    - "Track deadlines / due dates (deadlines DO NOT exist in database)"
    - "Emoji icons or heavy gamification pills"
```

