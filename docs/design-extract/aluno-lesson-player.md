# Design Inventory — `/aluno/cursos/{course:slug}/aulas/{lesson?}`

Fonte da verdade extraída rigorosamente do código-fonte (nenhum código alterado). Itens não confirmáveis ou ambíguos estão marcados como **UNCERTAIN** com a respectiva justificativa. Este documento serve como especificação exata do comportamento, dados e elementos visuais da tela de Player de Aula do Aluno.

---

## 1. FILES MAP

| Tipo / Papel | Arquivo | Descrição |
|---|---|---|
| Rota | [`routes/web.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/routes/web.php#L158) L158 | `Route::get('/aluno/cursos/{course:slug}/aulas/{lesson?}', LessonPlayer::class)->name('student.player')`, protegida por `middleware(['auth', 'subscription.active'])` |
| Rota Relacionada (Certificado) | [`routes/web.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/routes/web.php#L159) L159 | `Route::get('/aluno/cursos/{course:slug}/certificado', [CertificateController::class, 'download'])->name('student.certificate')` |
| Rota Relacionada (Quiz) | [`routes/web.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/routes/web.php#L160) L160 | `Route::get('/aluno/quizzes/{quiz}', QuizRunner::class)->name('student.quiz')` |
| Classe Livewire | [`app/Livewire/Student/LessonPlayer.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Livewire/Student/LessonPlayer.php) | Componente Livewire com `#[Layout('layouts.app')]`, propriedades reativas de aula, progresso, abas e anotações |
| View Principal | [`resources/views/livewire/student/lesson-player.blade.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/livewire/student/lesson-player.blade.php) | Markup Blade com topbar de progresso, player de vídeo/leitura, ações, 3 abas e sidebar do curso |
| Layout Base | [`resources/views/layouts/app.blade.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/layouts/app.blade.php) | Layout escuro (`<html class="dark">`, `bg-[#090D16]`), engloba navbar, slot `<main>`, rodapé, toasts e gaveta do Tutor IA |
| Navbar | [`resources/views/layouts/navigation.blade.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/layouts/navigation.blade.php) | Navbar incluída pelo layout via `@include('layouts.navigation')` |
| Tutor IA (Drawer) | [`resources/views/livewire/student/ai-tutor-drawer.blade.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/livewire/student/ai-tutor-drawer.blade.php) | Botão flutuante inferior direito e painel lateral injetado pelo layout `layouts.app` para alunos |
| Model Course | [`app/Models/Course.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Course.php) | Representa o curso, usa `BelongsToTenant`, possui relações `modules`, `lessons`, `quizzes` |
| Model Lesson | [`app/Models/Lesson.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Lesson.php) | Representa a aula (campos `title`, `description`, `video_url`, `duration_minutes`, `order`, etc.), usa `BelongsToTenant` |
| Model Module | [`app/Models/Module.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Module.php) | Representa o módulo do curso, usa `BelongsToTenant`, relação `lessons` |
| Model LessonProgress | [`app/Models/LessonProgress.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/LessonProgress.php) | Registra conclusão de cada aula por usuário (`completed_at`, `last_watched_second`) |
| Model LessonNote | [`app/Models/LessonNote.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/LessonNote.php) | Registra anotações pessoais do aluno por aula (`content`), chave única `[user_id, lesson_id]` |
| Model LessonAttachment | [`app/Models/LessonAttachment.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/LessonAttachment.php) | Arquivos complementares anexados à aula (`title`, `file_path`, `file_type`, `file_size_bytes`) |
| Model Enrollment | [`app/Models/Enrollment.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Enrollment.php) | Matrícula do aluno no curso; calcula e persiste `progress_percent` e `completed_at` |
| Model Quiz & QuizAttempt | [`app/Models/Quiz.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Quiz.php), [`app/Models/QuizAttempt.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/QuizAttempt.php) | Modelagem de quizzes do curso e histórico de tentativas/aprovação do aluno |
| Serviço de Certificado | [`app/Services/CertificateService.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Services/CertificateService.php) | Valida elegibilidade (`isEligible` exige 100% nas aulas e aprovação em todos os quizzes do curso) |
| Controller de Download | [`app/Http/Controllers/CertificateController.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Http/Controllers/CertificateController.php) | Processa a rota `student.certificate`; redireciona de volta ao player com aviso se não elegível |
| Tenant Scope | [`app/Models/Scopes/TenantScope.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Scopes/TenantScope.php) | Isola cursos e aulas por `company_id` |
| Estilos e Fontes | [`resources/css/app.css`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/css/app.css) | Tailwind CSS 4, tokens `@theme`, importação de `Geist Variable` |
| JavaScript | [`resources/js/app.js`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/js/app.js) | Entrada Vite (Alpine.js é provisionado pelo bundle do Livewire) |

---

## 2. PAGE LAYOUT

A tela é renderizada dentro do layout padrão [`layouts.app`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/layouts/app.blade.php) com `<html lang="pt-BR" class="dark">` e fundo `bg-[#090D16]`.

### Estrutura e Ordem de Seções (de cima para baixo):
1. **Navbar Global** (provida por `layouts.app` / `layouts.navigation`):
   - Fixa no topo: `sticky top-0 z-40 backdrop-blur-md bg-[#090D16] border-b border-slate-800`.
   - Altura: `h-16`.
   - Largura máxima: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`.
2. **Container `<main class="py-8">` do Layout**:
   - `layouts.app` envolve o `$slot` em `<main class="py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">{{ $slot }}</div></main>`.
   - **Nota de estrutura:** A raiz da view `lesson-player.blade.php` define seu próprio wrapper `div.min-h-screen.bg-[#090D16].text-slate-200`, gerando um segundo nível de background e padding aninhado.
3. **Topbar Exclusiva do Player** (`<header>` dentro de `lesson-player.blade.php`):
   - Fixa abaixo da navbar: `sticky top-0 z-30 bg-gray-900/80 backdrop-blur-md border-b border-white/10 px-4 sm:px-6 lg:px-8 py-3.5`.
   - **Nota de posicionamento:** Como a navbar do layout já usa `sticky top-0 z-40`, o `<header>` do player com `sticky top-0 z-30` empilha sob a navbar global sem offset de top configurado (`top-0`), causando sobreposição visual se ambas forem sticky simultaneamente.
   - Conteúdo: Botão Voltar ("Painel"), Título do Curso, Trilha "Módulo • Aula", Barra de Progresso com porcentagem e Botão Condicional de Certificado / Quizzes Pendentes.
4. **Mensagens Flash Globais da Página**:
   - Renderizadas logo abaixo da topbar do player, antes da grade principal:
   - Sucesso (`session('success')`): verde esmeralda com botão fechar.
   - Aviso (`session('warning')`): âmbar com botão fechar.
   - Erro (`session('error')`): rosa/vermelho com botão fechar.
5. **Grade Principal do Player** (`<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">`):
   - Grid Tailwind 12 colunas em telas grandes (`grid grid-cols-1 lg:grid-cols-12 gap-8`):
     - **Coluna Esquerda (8 colunas - `lg:col-span-8 space-y-6`):**
       1. Container do Vídeo / Conteúdo Textual (`aspect-video bg-black rounded-2xl overflow-hidden border border-white/10 shadow-2xl relative flex items-center justify-center`).
       2. Barra de Ações da Aula (`bg-gray-900/60 border border-white/5 rounded-2xl p-5`):
          - Módulo em caixa alta (`uppercase tracking-wider text-indigo-400 text-xs`).
          - Título da aula atual (`text-lg sm:text-xl font-bold text-white`).
          - Metadados: `⏱ {n} min estimados • {n} anexo(s)`.
          - Grupo de botões à direita: Botão Concluir ("Marcar como Concluída" ou "Concluída ✓"), Botão Anterior (`previousLesson`) e Botão Próximo (`nextLesson`).
       3. Abas de Conteúdo (`bg-gray-900/60 border border-white/5 rounded-2xl overflow-hidden`):
          - Cabeçalho das 3 abas: "Minhas Anotações", "Materiais de Apoio ({n})", "Sobre a Aula".
          - Painel da aba ativa.
     - **Coluna Direita (4 colunas - `lg:col-span-4 space-y-4`):**
       1. Card de Conteúdo do Curso / Grade Curricular (`bg-gray-900/60 border border-white/5 rounded-2xl p-5`):
          - Título "Conteúdo do Curso" com contador `{concluídas} / {total} aulas`.
          - Lista de módulos com título e `{n} aulas`.
          - Lista de botões de cada aula com status visual (ícone de concluído `✓`, aula atual `▶`, ou círculo vazio), título truncado e duração em minutos (`{n}m`).
6. **Rodapé Global da Aplicação** (provido por `layouts.app`):
   - `footer.border-t.border-slate-800/80.py-4.text-center.text-xs.text-slate-500`.
   - Texto: `ALVORA Plataforma B2B • Ambiente Multi-Tenant Seguro • Sessão Protegida`.
7. **Gaveta Flutuante do Tutor IA** (provido por `layouts.app` para alunos):
   - Injetado no rodapé do layout via `@livewire('student.ai-tutor-drawer')`.

---

## 3. ELEMENT INVENTORY

Tabela detalhada de **todos** os elementos visíveis da tela:

| Elemento | Texto visível exato (PT-BR) | Estático / Dinâmico | Fonte de dados (`model.field` / variável) | Ação / Rota disparada | Regra de visibilidade |
|---|---|---|---|---|---|
| **Navbar: Logo** | (imagem `logo-symbol.png`, alt "ALVORA") | Estático | `asset('images/logo-symbol.png')` | `route('dashboard')` (redireciona aluno para `student.dashboard`) | Sempre visível |
| **Navbar: Marca** | `ALVORA` | Estático | Código Blade | `route('dashboard')` | Sempre visível |
| **Navbar: Subtítulo Empresa** | `{company->name}` ou `Super Admin` | Dinâmico | `auth()->user()->company->name` | Nenhuma | Sempre visível |
| **Navbar: Link 1** | `Meu Aprendizado` | Estático | Código Blade | `route('student.dashboard')` | Visível a partir de `md:` (`hidden md:flex`) |
| **Navbar: Link 2** | `Catálogo de Cursos` | Estático | Código Blade | `route('student.dashboard') . '#catalogo'` | Visível a partir de `md:` |
| **Navbar: Link 3** | `Minhas Trilhas` | Estático | Código Blade | `route('student.dashboard')` (stub sem âncora) | Visível a partir de `md:` |
| **Navbar: Link 4** | `Meus Certificados` | Estático | Código Blade | `route('student.dashboard')` (stub sem âncora) | Visível a partir de `md:` |
| **Navbar: Badge de Papel** | `Aluno` | Dinâmico | `auth()->user()->role` | Nenhuma | `hidden sm:inline-flex` |
| **Navbar: Avatar** | Iniciais (ex: `AL`) | Dinâmico | `strtoupper(substr(user->name, 0, 2))` | Nenhuma | Sempre visível |
| **Navbar: Nome do Aluno** | `{user->name}` | Dinâmico | `auth()->user()->name` | Nenhuma | `hidden sm:inline-block` |
| **Navbar: Botão Sair** | (ícone saída, `title="Sair da Plataforma"`) | Estático | Formulário POST | `route('logout')` | Sempre visível |
| **Topbar: Botão Painel** | `Painel` (com ícone de seta para esquerda) | Estático | Código Blade | Link `route('student.dashboard')`, `title="Voltar ao Painel do Aluno"` | Sempre visível |
| **Topbar: Título do Curso** | `{course->title}` | Dinâmico | `$course->title` | Nenhuma | Sempre visível |
| **Topbar: Trilha Módulo • Aula** | `{module->title} • {lesson->title}` ou `Nenhuma aula selecionada` | Dinâmico | `$currentLesson->module->title` e `$currentLesson->title` | Nenhuma | Se `$currentLesson` existe exibe módulo e aula; senão mensagem de nenhuma aula |
| **Topbar: Rótulo Progresso** | `Progresso` | Estático | Código Blade | Nenhuma | Sempre visível |
| **Topbar: Porcentagem Progresso** | `{progressPercent}%` | Dinâmico | `$progressPercent` (de `$enrollment->progress_percent`) | Nenhuma | Sempre visível |
| **Topbar: Barra de Progresso** | (Barra em gradiente com `width: {progressPercent}%`) | Dinâmico | `$progressPercent` | Nenhuma | Sempre visível |
| **Topbar: Botão Baixar Certificado** | `Baixar Certificado 🎓` (com ícone check) | Estático | Código Blade | Link `route('student.certificate', $course->slug)` com `target="_blank"` | `$progressPercent >= 100` **E** `$this->isEligibleForCertificate() === true` (em telas `sm:` e acima) |
| **Topbar: Badge Quizzes Pendentes** | `Quizzes Pendentes 🎓` (com ícone alerta) | Estático | Código Blade | Nenhuma (elemento `<span>` inerte com cursor `cursor-not-allowed` e `title="Você precisa ser aprovado em todos os quizzes do curso para liberar o certificado"`) | `$progressPercent >= 100` **E** `$this->isEligibleForCertificate() === false` (em telas `sm:` e acima) |
| **Flash Message: Sucesso** | `{session('success')}` | Dinâmico | `session('success')` | Botão `✕` executa Alpine `@click="$el.parentElement.remove()"` | Apenas quando há flash `success` na sessão |
| **Flash Message: Aviso** | `{session('warning')}` | Dinâmico | `session('warning')` | Botão `✕` executa Alpine `@click="$el.parentElement.remove()"` | Apenas quando há flash `warning` na sessão |
| **Flash Message: Erro** | `{session('error')}` | Dinâmico | `session('error')` | Botão `✕` executa Alpine `@click="$el.parentElement.remove()"` | Apenas quando há flash `error` na sessão |
| **Player: Iframe YouTube** | (Player incorporado do YouTube) | Dinâmico | `$currentLesson->video_url` | Controles nativos do YouTube | Se `$currentLesson->video_url` contém `youtube.com` ou `youtu.be` com ID válido |
| **Player: Iframe Vimeo** | (Player incorporado do Vimeo) | Dinâmico | `$currentLesson->video_url` | Controles nativos do Vimeo | Se `$currentLesson->video_url` contém `vimeo.com` com ID numérico válido |
| **Player: Tag Video HTML5** | (Player HTML5 nativo com `controls controlsList="nodownload"`) | Dinâmico | `$currentLesson->video_url` | Controles nativos do navegador | Se `$currentLesson->video_url` não for YouTube nem Vimeo |
| **Player: Fallback de Leitura (Ícone)** | (Ícone livro aberto em caixa roxa) | Estático | Código Blade | Nenhuma | Se `$currentLesson` existe mas `video_url` é nulo/vazio |
| **Player: Fallback de Leitura (Título)** | `Conteúdo Textual e Leitura` | Estático | Código Blade | Nenhuma | Se `$currentLesson` existe mas `video_url` é nulo/vazio |
| **Player: Fallback de Leitura (Texto)** | `Esta aula possui conteúdo teórico ou materiais complementares para download disponíveis nas abas abaixo.` | Estático | Código Blade | Nenhuma | Se `$currentLesson` existe mas `video_url` é nulo/vazio |
| **Player: Fallback Sem Aulas** | `Nenhuma aula cadastrada neste curso até o momento.` | Estático | Código Blade | Nenhuma | Se `$currentLesson` é nulo |
| **Ações Aula: Módulo** | `{currentLesson->module->title}` (em uppercase) | Dinâmico | `$currentLesson->module->title ?? 'Módulo do Curso'` | Nenhuma | Se `$currentLesson` existe |
| **Ações Aula: Título** | `{currentLesson->title}` | Dinâmico | `$currentLesson->title` | Nenhuma | Se `$currentLesson` existe |
| **Ações Aula: Duração Estimada** | `⏱ {currentLesson->duration_minutes} min estimados` | Dinâmico | `$currentLesson->duration_minutes` | Nenhuma | Se `$currentLesson` existe e `$currentLesson->duration_minutes` for maior que zero |
| **Ações Aula: Separador Bullet** | `•` | Estático | Código Blade | Nenhuma | Se `$currentLesson->duration_minutes` for maior que zero |
| **Ações Aula: Contador Anexos** | `{currentLesson->attachments->count()} anexo(s)` | Dinâmico | `$currentLesson->attachments->count()` | Nenhuma | Se `$currentLesson` existe |
| **Ações Aula: Botão Concluir (Pendente)** | `Marcar como Concluída` (com ícone círculo check) | Estático | Código Blade | `wire:click="toggleComplete"` | Se `$currentLesson` existe e `$isCompleted === false` |
| **Ações Aula: Botão Concluir (Concluído)** | `Concluída ✓` (com ícone check SVG e caractere `✓`) | Estático | Código Blade | `wire:click="toggleComplete"` | Se `$currentLesson` existe e `$isCompleted === true` |
| **Ações Aula: Botão Aula Anterior** | (Ícone chevron esquerda, `title="Aula Anterior"`) | Estático | Código Blade | `wire:click="previousLesson"`, desabilitado com `@disabled(!$hasPrevious)` | Se `$currentLesson` existe |
| **Ações Aula: Botão Próxima Aula** | (Ícone chevron direita, `title="Próxima Aula"`) | Estático | Código Blade | `wire:click="nextLesson"`, desabilitado com `@disabled(!$hasNext)` | Se `$currentLesson` existe |
| **Aba: Minhas Anotações** | `Minhas Anotações` (com ícone lápis/bloco) | Estático | Código Blade | `wire:click="setTab('notes')"` | Se `$currentLesson` existe |
| **Aba: Materiais de Apoio** | `Materiais de Apoio ({currentLesson->attachments->count()})` (com ícone clipe) | Dinâmico | `$currentLesson->attachments->count()` | `wire:click="setTab('materials')"` | Se `$currentLesson` existe |
| **Aba: Sobre a Aula** | `Sobre a Aula` (com ícone informação) | Estático | Código Blade | `wire:click="setTab('overview')"` | Se `$currentLesson` existe |
| **Conteúdo Aba Anotações: Aviso** | `Suas anotações são particulares e salvas automaticamente` (com ícone cadeado) | Estático | Código Blade | Nenhuma | Se `$activeTab === 'notes'` |
| **Conteúdo Aba Anotações: Botão Salvar** | `Salvar Agora` | Estático | Código Blade | `wire:click="saveNote"` | Se `$activeTab === 'notes'` |
| **Conteúdo Aba Anotações: Textarea** | Placeholder: `Escreva suas considerações, insights ou tópicos importantes desta aula...` | Dinâmico | Ligado a `$noteContent` via `wire:model.live.debounce.1000ms="noteContent"` e `wire:change="saveNote"` | Salva nota no banco ao digitar (debounce 1s) ou ao desfocar | Se `$activeTab === 'notes'` |
| **Conteúdo Aba Materiais: Vazio** | `Nenhum material complementar anexado a esta aula.` | Estático | Código Blade | Nenhuma | Se `$activeTab === 'materials'` e `$currentLesson->attachments->isEmpty()` |
| **Conteúdo Aba Materiais: Item Título** | `{attachment->title}` | Dinâmico | `$attachment->title` | Nenhuma | Para cada anexo em `$currentLesson->attachments` |
| **Conteúdo Aba Materiais: Item Detalhe** | `{file_size_kb} KB • {file_type}` | Dinâmico | `round($attachment->file_size_bytes / 1024, 1)` e `strtoupper($attachment->file_type ?? 'ARQUIVO')` | Nenhuma | Para cada anexo |
| **Conteúdo Aba Materiais: Botão Baixar** | `Baixar` (com ícone download) | Estático | Código Blade | Link direto `asset('storage/' . $attachment->file_path)` com atributos `target="_blank" download` | Para cada anexo |
| **Conteúdo Aba Sobre: Título** | `Objetivo Pedagógico` | Estático | Código Blade | Nenhuma | Se `$activeTab === 'overview'` |
| **Conteúdo Aba Sobre: Descrição** | `{course->description}` ou fallback fixo | Dinâmico | `$course->description ?? 'Capacitação corporativa focada em retenção e aplicabilidade prática.'` | Nenhuma | Se `$activeTab === 'overview'` (**Nota:** usa a descrição do **curso**, e não da aula) |
| **Sidebar: Título Seção** | `Conteúdo do Curso` | Estático | Código Blade | Nenhuma | Sempre visível |
| **Sidebar: Contador Geral** | `{concluídas} / {total} aulas` | Dinâmico | `count($completedLessonIds)` e `$course->lessons->count()` | Nenhuma | Sempre visível |
| **Sidebar: Linha de Módulo - Título** | `{module->title}` | Dinâmico | `$module->title` | Nenhuma | Para cada módulo ordenado por `order` |
| **Sidebar: Linha de Módulo - Qtd** | `{module->lessons->count()} aulas` | Dinâmico | `$module->lessons->count()` | Nenhuma | Para cada módulo |
| **Sidebar: Linha de Aula - Botão** | (Linha clicável com status, título e duração) | Dinâmico | `$lesson->title` | `wire:click="selectLesson({lesson->id})"` | Para cada aula do módulo ordenada por `order` |
| **Sidebar: Status Aula Concluída** | `✓` (círculo verde `bg-emerald-500/20 text-emerald-400 text-[10px]`) | Estático | `$isDone` (se `lesson->id` está em `$completedLessonIds`) | Nenhuma | Se aula está concluída |
| **Sidebar: Status Aula Atual** | `▶` (círculo azul índigo `bg-indigo-500 text-white text-[9px] animate-pulse`) | Estático | `$isCurrent` (se `lesson->id === currentLesson->id`) | Nenhuma | Se aula é a atual selecionada e não concluída |
| **Sidebar: Status Aula Pendente** | (Círculo vazio com borda cinza `border border-gray-600`) | Estático | Se não é concluída nem atual | Nenhuma | Se aula é pendente |
| **Sidebar: Linha de Aula - Título** | `{lesson->title}` | Dinâmico | `$lesson->title` (com classe `truncate`) | Nenhuma | Para cada aula |
| **Sidebar: Linha de Aula - Duração** | `{lesson->duration_minutes}m` | Dinâmico | `$lesson->duration_minutes` | Nenhuma | Se `$lesson->duration_minutes` for maior que zero |
| **Rodapé do Layout** | `ALVORA Plataforma B2B • Ambiente Multi-Tenant Seguro • Sessão Protegida` | Estático | Código Blade de `layouts.app` | Nenhuma | Sempre visível |
| **Botão Flutuante Tutor IA** | `Tutor Alvora AI` (com ponto verde pulsante e ícone mensagem) | Estático | Injetado por `@livewire('student.ai-tutor-drawer')` | `wire:click="openDrawer"` | Aluno autenticado (`auth()->user()->isStudent()`) |

---

## 4. LESSON TYPES AND CONTENT AREA

### 4.1 Tipos de Aula Suportados no Banco e no Código
- Na tabela `lessons` (`database/migrations/2026_10_19_000006_create_lessons_table.php`), **não existe uma coluna de enum ou tipo** (`type`). A distinção é inferida dinamicamente no Blade pela presença ou ausência da coluna `video_url` (`nullable string`).
- **Aulas com Vídeo:** Quando `$currentLesson->video_url` possui valor não nulo e não vazio.
- **Aulas Textuais / Leitura:** Quando `$currentLesson->video_url` é nulo ou vazio. O container exibe um card centralizado com ícone de livro, título `"Conteúdo Textual e Leitura"` e texto orientando o aluno a consultar as abas abaixo.
- **Estado Sem Aula Selecionada / Sem Aulas:** Quando `$currentLesson` é nulo (curso sem aulas), o container exibe o texto `"Nenhuma aula cadastrada neste curso até o momento."`.

### 4.2 Provedores de Vídeo Suportados e Regras de Embed
A resolução do embed em `lesson-player.blade.php` (L118-159) verifica a string em `$currentLesson->video_url`:

1. **YouTube:**
   - Detecção: `str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')`.
   - Expressão regular para extração do ID:
     ```php
     preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $match);
     $youtubeId = $match[1] ?? null;
     ```
   - Embed URL gerada: `https://www.youtube-nocookie.com/embed/{$youtubeId}?rel=0&modestbranding=1`.
   - Renderização: `<iframe>` com `allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"` e `allowfullscreen`.

2. **Vimeo:**
   - Detecção: `str_contains($url, 'vimeo.com')`.
   - Expressão regular para extração do ID:
     ```php
     preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $url, $match);
     $vimeoId = $match[3] ?? null;
     ```
   - Embed URL gerada: `https://player.vimeo.com/video/{$vimeoId}?title=0&byline=0&portrait=0`.
   - Renderização: `<iframe>` com `allow="autoplay; fullscreen; picture-in-picture"` e `allowfullscreen`.

3. **Arquivo de Vídeo Direto (MP4, WebM, etc.) ou URL genérica:**
   - Fallback quando não é YouTube nem Vimeo:
   - Renderização: Tag `<video class="w-full h-full object-contain" controls controlsList="nodownload" src="{{ $currentLesson->video_url }}">`.

### 4.3 Comportamento com URLs Inválidas ou Inacessíveis
- Se a URL contiver `youtube.com` mas o formato for inválido (não casar na regex de 11 caracteres), `$youtubeId` é `null`. O código cai no fallback do `<video src="...">`, tentando renderizar a URL quebrada em uma tag de vídeo HTML5 nativa (gerando erro de reprodução de mídia no navegador).
- O mesmo ocorre com URLs de Vimeo inválidas: se a regex não capturar o ID numérico, cai no `<video src="...">`.
- Não há mensagem de erro amigável na UI para URLs de vídeo quebradas ou bloqueadas.

### 4.4 Rastreamento de Progresso do Vídeo vs. Ação Manual
- **Progresso 100% manual:** Embora a tabela `lesson_progress` possua a coluna `last_watched_second` (com default `0`), **não há nenhum script JavaScript, player SDK (YouTube Iframe API ou Vimeo Player SDK) ou listener de tempo implementado na aplicação**.
- O progresso **não** é atualizado pelo tempo assistido no player. Ele é acionado **exclusivamente** pelo clique do usuário no botão `"Marcar como Concluída"` (`wire:click="toggleComplete"`).

---

## 5. STATES AND RULES (EXACT CONDITIONS)

### 5.1 Botão de Conclusão de Aula (`toggleComplete`)
- **Estado 1: Aula Pendente** (`$isCompleted === false`):
  - Rótulo visível: `"Marcar como Concluída"`.
  - Estilo: `bg-indigo-600 hover:bg-indigo-500 text-white shadow-lg shadow-indigo-600/30`.
  - Ícone: SVG check em círculo aberto.
- **Estado 2: Aula Concluída** (`$isCompleted === true`):
  - Rótulo visível: `"Concluída ✓"`. (**Inconsistência:** exibe um ícone SVG de check e também o caractere textual `✓`).
  - Estilo: `bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 hover:bg-emerald-500/30`.
- **Comportamento ao Clicar:**
  - O método `toggleComplete()` busca ou instancia o registro em `LessonProgress` (`firstOrNew(['company_id' => $user->company_id, 'user_id' => $user->id, 'lesson_id' => $this->currentLesson->id])`).
  - **Permite desfazer (Toggle):** Se `completed_at` já estava preenchido, define `completed_at = null`, `$this->isCompleted = false` e grava na sessão `session()->flash('success', 'Aula marcada como pendente.')`. Se era nulo, define `completed_at = now()`, `$this->isCompleted = true` e grava `session()->flash('success', 'Parabéns! Aula concluída com sucesso.')`.
  - Executa `$progress->save()`.
  - Em seguida, executa `$this->progressPercent = $this->enrollment->recalculateProgress()`.
  - Executa `$this->loadLessonState()` (atualiza a lista `$completedLessonIds`, refletindo imediatamente o novo ícone `✓` na sidebar e na contagem `{concluídas} / {total} aulas` sem necessidade de recarregar a página).
  - Permanece na mesma aula (não redireciona).

### 5.2 Cálculo de Progresso no Topbar
- Fonte: `$enrollment->recalculateProgress()` em [`app/Models/Enrollment.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Enrollment.php#L70-L105).
- Fórmula exata:
  - `$lessonIds = $course->lessons()->pluck('id');`
  - Se `$totalLessons === 0`: progresso é `0%`.
  - Se `$totalLessons > 0`: conta registros em `LessonProgress` onde `user_id == $user->id`, `lesson_id IN ($lessonIds)` e `completed_at IS NOT NULL`.
  - `$percent = (int) round(($completedCount / $totalLessons) * 100);` limitado entre 0 e 100.
  - Se `$percent >= 100` e `$enrollment->completed_at` for nulo, atualiza `$enrollment->completed_at = now()` e `$enrollment->status = 'completed'`.
  - Se `$percent < 100` e `$enrollment->status === 'completed'`, reverte para `completed_at = null` e `status = 'active'`.

### 5.3 Botão / Badge "Quizzes Pendentes" vs. "Baixar Certificado"
- **Condição geral para ambos:** Só aparecem quando `$progressPercent >= 100` (em telas `sm:` e acima, `hidden sm:inline-flex`). Abaixo de 100%, nenhum dos dois é renderizado.
- **Caso 1: Elegível ao Certificado** (`$this->isEligibleForCertificate() === true`):
  - Verifica `CertificateService::isEligible($user, $course)`:
    1. `$enrollment->progress_percent >= 100`.
    2. Para cada quiz em `$course->quizzes`, `$quiz->hasUserPassed($user->id)` deve ser verdadeiro. Se o curso não possuir quizzes, esta condição passa automaticamente.
  - Exibe botão verde link: `<a href="{{ route('student.certificate', $course->slug) }}" target="_blank">Baixar Certificado 🎓</a>`.
- **Caso 2: Não Elegível (Quizzes Pendentes)** (`$this->isEligibleForCertificate() === false`):
  - Ocorre quando o aluno completou 100% das aulas, mas reprovou ou não realizou um ou mais quizzes obrigatórios do curso.
  - Exibe um `<span>` estático inerte (não é link nem botão clicável):
    `<span title="Você precisa ser aprovado em todos os quizzes do curso para liberar o certificado" class="... cursor-not-allowed">Quizzes Pendentes 🎓</span>`.
  - **Inconsistência Crítica de Fluxo:** Esse elemento não possui link nem redirecionamento para o quiz (`QuizRunner`). A rota `student.quiz` existe no sistema, mas **não há link para ela em nenhum lugar da tela do player**. O aluno não consegue navegar para o quiz a partir deste botão.
  - **Bloqueio de Certificado:** Sim, quizzes pendentes bloqueiam a emissão do certificado. Se o aluno tentar acessar a URL do certificado diretamente (`/aluno/cursos/{slug}/certificado`), o `CertificateController` rejeita a requisição e o redireciona de volta para o player com a flash message: `"Colaborador não atingiu os requisitos de 100% de conclusão ou aprovação nos quizzes para emissão do certificado."`.

### 5.4 Navegação Anterior / Próxima (`previousLesson` e `nextLesson`)
- **Lista ordenada:** Calculada por `$this->getOrderedLessons()`, que percorre os módulos ordenados por `modules.order` ascendente e, dentro de cada módulo, as aulas ordenadas por `lessons.order` ascendente.
- **Transição entre módulos:** A lista é linear (`flatMap`). Quando a última aula do Módulo 1 é atingida, clicar em "Próxima Aula" navega com sucesso para a primeira aula do Módulo 2.
- **Estado Desabilitado:**
  - Primeira aula do curso: `$hasPrevious === false` -> botão "Aula Anterior" recebe `@disabled(true)` e classes `disabled:opacity-40 disabled:cursor-not-allowed`.
  - Última aula do curso: `$hasNext === false` -> botão "Próxima Aula" recebe `@disabled(true)` e classes `disabled:opacity-40 disabled:cursor-not-allowed`.
- **Conclusão Automática:** **Não existe conclusão automática**. Navegar para a próxima aula não marca a aula anterior como concluída.
- **Salva Anotações:** Antes de trocar a aula ativa, `selectLesson()` executa `$this->saveNote()`, garantindo que anotações digitadas na aula anterior não sejam perdidas.

### 5.5 Grade Curricular Lateral (Sidebar)
- **Agrupamento e Ordenação:** Módulos ordenados por `order ASC`. Aulas de cada módulo ordenadas por `order ASC`.
- **Aula Ativa:** `$isCurrent = $currentLesson && $currentLesson->id === $lesson->id;`. Destaque visual: fundo `bg-indigo-600/20`, borda `border-indigo-500/40`, texto branco em negrito e ícone de play pulsante `▶`.
- **Aula Concluída:** Se `in_array($lesson->id, $completedLessonIds)`, exibe o badge circular verde com check `✓`. Se a aula estiver concluída E for a aula atual, a regra do Blade prioriza o `$isDone` (L404-414).
- **Aulas Bloqueadas / Travas de Acesso:** **Não existem aulas bloqueadas**. Todas as aulas de todos os módulos estão sempre clicáveis e disponíveis para acesso livre a qualquer momento, sem exigir conclusão prévia da anterior e sem bloqueio por quiz.
- **Colapso de Módulos:** Os módulos **não são colapsáveis** (não há acordeão nem Alpine `x-collapse`). Todos os módulos e aulas ficam permanentemente expandidos.
- **Formatação de Duração:** Na sidebar, exibe `{lesson->duration_minutes}m` (ex: `15m`).
- **Plural de "aulas":**
  - No topo da sidebar: `count($completedLessonIds) . ' / ' . $course->lessons->count() . ' aulas'` (sempre "aulas", mesmo se 1).
  - No cabeçalho do módulo: `$module->lessons->count() . ' aulas'` (mesmo se 1, exibe `"1 aulas"`).

### 5.6 Aba 1: Minhas Anotações (`notes`)
- **Armazenamento:** Tabela `lesson_notes` (`app/Models/LessonNote.php`), colunas `company_id`, `user_id`, `lesson_id`, `content`.
- **Escopo:** Isolado por aluno e por aula (`unique(['user_id', 'lesson_id'])`). Ao alternar de aula, o textarea é recarregado com a anotação correspondente àquela aula.
- **Mecanismo de Salvamento:**
  - `wire:model.live.debounce.1000ms="noteContent"` (envia atualização ao Livewire após 1000ms sem digitar).
  - `wire:change="saveNote"` (salva no banco quando o usuário clica fora ou altera o textarea).
  - Botão explícito `"Salvar Agora"` com `wire:click="saveNote"`.
  - Salvamento automático acionado em `selectLesson()` ao navegar para outra aula.
- **Feedback de Salvamento:** **Não há feedback visual de salvo** (não há mensagem "Salvo com sucesso", toast ou spinner de loading).
- **Limite de Caracteres:** A coluna no banco é `longText`. Não há validação de tamanho máximo (`maxlength`) nem contador de caracteres na interface.

### 5.7 Aba 2: Materiais de Apoio (`materials`)
- **Fonte de Dados:** `$currentLesson->attachments` (relação com `LessonAttachment`).
- **Campos Exibidos:**
  - Título: `$attachment->title`.
  - Metadados: tamanho em KB (`round($attachment->file_size_bytes / 1024, 1) . ' KB'`) e extensão/tipo em maiúsculas (`strtoupper($attachment->file_type ?? 'ARQUIVO')`).
  - Link de Download: `<a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank" download>` com texto `"Baixar"`.
- **Estado Vazio:** Se `$currentLesson->attachments->isEmpty()`, exibe o parágrafo: `"Nenhum material complementar anexado a esta aula."`.

### 5.8 Aba 3: Sobre a Aula (`overview`)
- **Campos Exibidos:**
  - Título de seção fixo: `"Objetivo Pedagógico"`.
  - Texto: `$course->description ?? 'Capacitação corporativa focada em retenção e aplicabilidade prática.'`.
  - **Inconsistência Identificada:** Embora a tabela `lessons` possua a coluna `description`, a view renderiza `$course->description` (a descrição do curso, e não a descrição da aula). Não há estado vazio customizado além do fallback fixo de string.

### 5.9 Comportamento ao Atingir 100% de Conclusão
- A barra de progresso no topo vai para 100% verde esmeralda.
- O model `Enrollment` atualiza `status = 'completed'` e grava `completed_at = now()`.
- O Livewire renderiza a notificação flash `"Parabéns! Aula concluída com sucesso."`.
- O botão `"Baixar Certificado 🎓"` (ou o aviso `"Quizzes Pendentes 🎓"`) surge na topbar.
- **Não há redirecionamento automático**, modal comemorativo ou disparo de confetes; o aluno permanece na mesma tela.

### 5.10 Estados de Erro, Tenant e Ausência de Dados
- **Curso de outro Tenant:** `LessonPlayerTest::test_student_cannot_access_course_or_lessons_from_another_tenant` comprova que o `TenantScope` intercepta a busca por slug do curso e aborta com `404 Not Found`.
- **Aula de outro Curso na URL:** Se a rota for acessada com `/aluno/cursos/{slug}/aulas/{lesson_id}` onde `lesson_id` pertence a outro curso, o `mount()` ignora o ID recebido e faz fallback para a primeira aula pendente do curso correto.
- **Aluno não matriculado:** O método `mount()` executa `Enrollment::firstOrCreate(...)` automaticamente, realizando a auto-matrícula do aluno sem exibir erro.
- **Curso sem nenhuma aula cadastrada:**
  - `$this->currentLesson` fica como `null`.
  - Topbar exibe `"Nenhuma aula selecionada"`.
  - Player exibe `"Nenhuma aula cadastrada neste curso até o momento."`.
  - Barra de ações da aula e as abas não são renderizadas (`@if ($currentLesson)`).
  - Sidebar exibe `"0 / 0 aulas"`.

---

## 6. NAVIGATION

### Navbar Superior (`layouts/navigation.blade.php`)
Renderizada acima do player:
1. **Logo / Marca "ALVORA":** Aponta para `route('dashboard')` (que redireciona o aluno autenticado para `student.dashboard`).
2. **"Meu Aprendizado":** Aponta para `route('student.dashboard')`.
3. **"Catálogo de Cursos":** Aponta para `route('student.dashboard') . '#catalogo'`.
4. **"Minhas Trilhas":** Aponta para `route('student.dashboard')` (**Stub:** não possui âncora específica).
5. **"Meus Certificados":** Aponta para `route('student.dashboard')` (**Stub:** não possui rota de listagem de certificados; a emissão é individual por curso).
6. **Avatar e Nome do Usuário:** Indicadores informativos (sem menu dropdown).
7. **Botão de Logout:** Formulário POST com CSRF acionando `route('logout')`.

### Topbar do Player
- **Botão "Painel":** Link `<a href="{{ route('student.dashboard') }}">` com ícone de seta para esquerda e texto `"Painel"`, retornando diretamente ao dashboard do aluno.

---

## 7. DESIGN TOKENS CURRENTLY IN USE

### Cores Utilizadas nesta Tela
- **Hardcoded no Blade (fora do `@theme`):**
  - Background da página: `bg-[#090D16]`.
  - Elementos em cinza e ardósia do Tailwind padrão: `bg-gray-900/80`, `bg-gray-900/60`, `bg-gray-950/60`, `bg-gray-950/40`, `bg-gray-800`, `hover:bg-gray-700`, `text-gray-300`, `text-gray-400`, `text-gray-500`, `border-white/10`, `border-white/5`, `border-gray-600`.
  - Elementos em Índigo padrão: `bg-indigo-600`, `hover:bg-indigo-500`, `bg-indigo-500/10`, `bg-indigo-600/20`, `text-indigo-400`, `border-indigo-500`, `shadow-indigo-600/30`.
  - Elementos em Esmeralda padrão: `bg-emerald-500/20`, `bg-emerald-500/10`, `text-emerald-300`, `text-emerald-400`, `border-emerald-500/20`, `border-emerald-500/30`, `border-emerald-500/40`, `shadow-emerald-500/20`.
  - Elementos em Âmbar padrão: `bg-amber-500/10`, `text-amber-300`, `text-amber-300/80`, `text-amber-400`, `border-amber-500/20`, `border-amber-500/30`.
  - Elementos em Rosa/Vermelho padrão: `bg-rose-500/10`, `text-rose-300`, `text-rose-400`, `border-rose-500/20`.

### Tipografia
- Fonte da aplicação: `Geist Variable` (declarada no `@theme` de `app.css`).
- Tamanhos empregados:
  - Título do Curso: `text-sm sm:text-base font-bold`.
  - Título da Aula (Topbar): `text-xs text-gray-400`.
  - Título da Aula (Ações): `text-lg sm:text-xl font-bold`.
  - Módulo da Aula (Ações): `text-xs font-medium uppercase tracking-wider`.
  - Metadados da Aula: `text-xs text-gray-400`.
  - Botão de Conclusão: `text-xs sm:text-sm font-semibold`.
  - Abas: `text-xs sm:text-sm font-semibold`.
  - Conteúdo das Abas: `text-sm text-white` / `text-sm text-gray-300`.
  - Sidebar: Título do módulo em `text-xs font-semibold`, quantidade de aulas em `text-[10px] text-gray-500`, título da aula em `text-xs`, duração em `text-[11px] text-gray-500`.

### Raios de Borda (Border Radius)
- Topbar do player: retangular (sem raio).
- Container do Vídeo: `rounded-2xl` (16px).
- Bloco de Ações da Aula: `rounded-2xl`.
- Bloco das Abas: `rounded-2xl`.
- Container da Sidebar: `rounded-2xl`.
- Botões de ação, itens de aula da sidebar e textarea: `rounded-xl` (12px).
- Badges e botões de abas: `rounded-lg` (8px).
- Badges de status circular: `rounded-full`.

### Sombras e Efeitos Visuais
- Sombra do container de vídeo: `shadow-2xl`.
- Sombra do botão de conclusão primário: `shadow-lg shadow-indigo-600/30`.
- Sombra do botão de certificado: `shadow-lg shadow-emerald-500/20`.
- Backdrop blur: `backdrop-blur-md` na navbar e na topbar do player.

### Ícones, Emojis e Caracteres Especiais
- **SVGs Inline:** Todos os ícones (seta de voltar, check, alerta, fechar, livro, chevron anterior/próximo, lápis, clipe, info, download, cadeado) são SVGs inline avulsos (não utilizam o componente `<x-icon>`).
- **Emojis presentes no código:**
  - `🎓` no botão `"Baixar Certificado 🎓"` (L51).
  - `🎓` na badge `"Quizzes Pendentes 🎓"` (L59).
- **Caracteres Especiais / Glifos utilizados no lugar de ícones:**
  - `⏱` (cronômetro) nos minutos estimados da aula (L191).
  - `✓` (check) dentro do texto do botão `"Concluída ✓"` (L211).
  - `✕` (multiplicação) nos botões de fechar notificação flash (L77, L91, L105).
  - `✓` no indicador circular de aula concluída da sidebar (L406).
  - `▶` no indicador circular de aula em andamento da sidebar (L410).

---

## 8. RESPONSIVE BEHAVIOR

1. **Breakpoints Utilizados:**
   - `sm:` (640px), `md:` (768px), `lg:` (1024px).
2. **Topbar do Player:**
   - Em mobile (`< sm:`): o cabeçalho empilha em coluna (`flex-col sm:flex-row sm:items-center`).
   - O botão `"Baixar Certificado 🎓"` e a badge `"Quizzes Pendentes 🎓"` possuem classe `hidden sm:inline-flex`, ficando **completamente ocultos em telas pequenas/mobile**.
   - Barra de progresso: largura `w-36` em mobile e `sm:w-48` a partir de 640px.
3. **Proporção do Vídeo:**
   - Mantida rigidamente em 16:9 através da classe `aspect-video` do Tailwind no container pai. Em qualquer resolução, o vídeo redimensiona proporcionalmente sem cortes.
4. **Disposição das Colunas:**
   - Abaixo de `lg` (`< 1024px`): a grade passa de 12 colunas para 1 coluna (`grid-cols-1 lg:grid-cols-12`).
   - A coluna do player (8 colunas) fica em cima.
   - A sidebar da grade curricular (4 colunas) empilha abaixo das abas de apoio.
5. **Barra de Ações da Aula:**
   - Em telas pequenas, o cabeçalho da aula e o grupo de botões de navegação empilham (`flex-col sm:flex-row sm:items-center justify-between gap-4`).
6. **Abas:**
   - Os títulos das abas utilizam `text-xs sm:text-sm` com quebra flexível para caber na largura disponível.

---

## 9. THINGS THAT DO NOT EXIST

Recursos comuns em plataformas de vídeo / cursos corporativos que **NÃO EXISTEM** no código atual:

1. **Controle de velocidade de reprodução (1.25x, 1.5x, 2x):** Não há seletor ou atalho nativo construído.
2. **Auto-play / Reprodução automática da próxima aula:** O término de um vídeo não inicia nem carrega a próxima aula automaticamente.
3. **Aulas travadas por pré-requisito ou ordem:** Não existe bloqueio de aulas (`locked`). Qualquer aula pode ser aberta livremente a qualquer momento.
4. **Retomada a partir do timestamp (Resume playback):** Não há persistência de tempo assistido nem recuperação do segundo onde o aluno parou.
5. **Área de comentários ou fórum de discussão da aula:** Não há tabela, relação ou componente de dúvidas/comentários entre alunos ou instrutor.
6. **Avaliação da aula com estrelas / feedback (Rating):** Não existe campo de avaliação de 1 a 5 estrelas ou NPS por aula.
7. **Download em lote ("Baixar todos os anexos"):** Não há funcionalidade de compactar anexos em arquivo zip. Cada material deve ser baixado individualmente.
8. **Transcrições de áudio / legendas sincronizadas:** Não há suporte a legendas VTT ou transcrições de texto na página.
9. **Marcação de favoritos / bookmarks de tempo:** Não existe mecanismo para favoritar trechos de vídeo.
10. **Modo Teatro / Tela cheia customizada:** Não há botão customizado de modo teatro (apenas o fullscreen nativo do iframe/vídeo).
11. **Atalhos de teclado:** Teclas como espaço para pausar ou setas para avançar/retroceder não possuem listeners globais na página.
12. **Link funcional para realização de quiz dentro do player:** Não há link, card de quiz na grade lateral ou botão para abrir o `QuizRunner` a partir do player.
13. **Tutor IA integrado no contexto do player:** Embora o drawer flutuante global esteja presente no layout, não há integração contextual com o conteúdo da aula atual (ex: botão "Perguntar sobre esta aula").
14. **Busca interna de aulas na grade curricular:** A sidebar não possui campo de busca ou filtro de aulas.
15. **Módulos colapsáveis na grade curricular:** Não há acordeão para recolher módulos.
16. **Mini-player PIP flutuante:** Não há modo picture-in-picture desacoplado ao rolar a página para baixo.

---

## 10. BUGS AND INCONSISTENCIES

Listagem rigorosa das inconsistências, bugs e oportunidades encontradas no código:

1. **Erro de Pluralização no cabeçalho do módulo:**
   - Na linha 385 da view: `{{ $module->lessons->count() }} aulas`. Quando o módulo possui apenas 1 aula, exibe `"1 aulas"`.
2. **Erro de Pluralização nos anexos da aula:**
   - Na linha 194 da view: `{{ $currentLesson->attachments->count() }} anexo(s)`. Utiliza a formatação com parênteses em vez de singular/plural adequado.
3. **Inconsistência Visual no Botão Concluído:**
   - Na linha 211 da view: exibe um SVG de check E o caractere textual `✓` logo em seguida: `Concluída ✓`.
4. **Uso de Emojis e Caracteres Especiais no lugar de Ícones:**
   - `🎓` em `"Baixar Certificado 🎓"` e `"Quizzes Pendentes 🎓"` (L51, L59).
   - `⏱` em `⏱ {{ $currentLesson->duration_minutes }} min estimados` (L191).
   - `✕` como caractere textual nos botões de fechar notificação flash (L77, L91, L105).
   - `✓` e `▶` como caracteres de texto na listagem da sidebar (L406, L410).
5. **Vídeo de Demonstração / Teste no Banco de Dados:**
   - No seeder oficial ([`DatabaseSeeder.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/database/seeders/DatabaseSeeder.php#L172)), o vídeo cadastrado na primeira aula é a URL de teste `https://www.youtube.com/watch?v=dQw4w9WgXcQ` (Rickroll).
6. **Links da Navbar Apontando para Stubs:**
   - `"Minhas Trilhas"` e `"Meus Certificados"` em [`layouts/navigation.blade.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/layouts/navigation.blade.php#L74-L79) apontam para `route('student.dashboard')` sem âncoras ou rotas dedicadas.
7. **Badge "Quizzes Pendentes" Inerte e Sem Rota:**
   - Na linha 54 da view, o elemento é um `<span>` com `cursor-not-allowed`. O aluno não possui nenhum botão ou link para acessar o quiz pendente (`student.quiz`), travando a conclusão do curso se ele não souber a URL direta.
8. **Conteúdo da Aba "Sobre a Aula" Exibe Dados do Curso:**
   - Na linha 361 da view, o texto pedagógico é preenchido com `$course->description ?? 'Capacitação corporativa...'`, ignorando o campo `$currentLesson->description` existente no banco.
9. **Sobreposição de Headers Fixos (`Sticky` Conflict):**
   - A navbar de `layouts.app` possui `sticky top-0 z-40`. A topbar de `lesson-player.blade.php` possui `sticky top-0 z-30`. Ambas competem pelo topo `top: 0`, gerando sobreposição visual no scroll.
10. **Botões de Certificado Ocultos em Mobile:**
    - As linhas 48 e 56 possuem a classe `hidden sm:inline-flex`. Um aluno acessando pelo smartphone que concluir 100% das aulas não consegue ver nem clicar no botão de baixar certificado na topbar.
11. **Falta de Feedback ao Salvar Anotações:**
    - Clicar em `"Salvar Agora"` executa `$this->saveNote()` no backend silenciosamente, sem exibir confirmação ou estado de "Salvo".
12. **Inconsistência de Tokens de Cor e Modo Escuro:**
    - A tela utiliza `bg-[#090D16]` hardcoded e a paleta genérica `gray-*` e `indigo-*` em vez de adotar os tokens padronizados `@theme` definidos em `app.css` (`edtech-navy`, `edtech-blue`, etc.).

---

## 11. STITCH BRIEF

```markdown
PÁGINA: Player de Aula do Aluno (Visualizador de Conteúdo e Aulas)
ROTA DO SISTEMA: /aluno/cursos/{course:slug}/aulas/{lesson?}
PÚBLICO: Aluno corporativo autenticado (B2B)

ESTRUTURA DA TELA:
1. Navbar Global (Topo):
   - Logo ALVORA com nome da empresa do colaborador.
   - Navegação: "Meu Aprendizado", "Catálogo de Cursos", "Minhas Trilhas", "Meus Certificados".
   - Identificação do aluno com iniciais no avatar, nome completo, rótulo "Aluno" e botão "Sair da Plataforma".

2. Topbar do Player (Abaixo da Navbar):
   - Botão "Painel" com ícone de voltar à esquerda (retorna a /aluno/dashboard).
   - Título do curso e linha de navegação com "Módulo • Aula Atual".
   - Bloco de progresso à direita: texto "Progresso", porcentagem calculada (ex: "50%") e barra horizontal preenchida.
   - Estado condicional de 100% de progresso:
     * Se elegível: Botão "Baixar Certificado" (abre PDF em nova aba).
     * Se houver quizzes não aprovados: Indicador "Quizzes Pendentes" com aviso de bloqueio.

3. Mensagens de Notificação (Topo do conteúdo):
   - Alertas dispensáveis com botão fechar para confirmação de conclusão ("Parabéns! Aula concluída com sucesso.") ou retorno de pendência.

4. Área Principal (Grade em 2 Colunas no Desktop, 1 Coluna no Mobile):
   - Coluna Principal (Esquerda):
     * Player de Vídeo: Proporção 16:9 widescreen. Suporta embed de YouTube, Vimeo ou tag HTML5 para MP4.
     * Caso a aula não tenha vídeo: Painel explicativo "Conteúdo Textual e Leitura" orientando a leitura e materiais.
     * Barra de Ações da Aula:
       - Nome do módulo em caixa alta.
       - Título da aula atual.
       - Indicadores: minutos estimados e quantidade de anexos.
       - Botão de Conclusão: "Marcar como Concluída" (estado pendente) ou "Concluída" (estado concluído). Permite alternar/desfazer.
       - Botões de navegação: "Aula Anterior" e "Próxima Aula" (com estado desabilitado nos limites).
     * Painel de Abas Abaixo do Player:
       - Aba 1 ("Minhas Anotações"): Textarea para notas pessoais com salvamento automático ao digitar e botão "Salvar Agora".
       - Aba 2 ("Materiais de Apoio (n)"): Lista de arquivos para download com título, tamanho em KB, tipo e botão "Baixar".
       - Aba 3 ("Sobre a Aula"): Descrição pedagógica do conteúdo.
   - Coluna Lateral (Direita - Grade Curricular):
     * Card "Conteúdo do Curso" com contador consolidado de aulas concluídas (ex: "3 / 6 aulas").
     * Lista de módulos do curso em ordem numérica, contendo a lista de suas respectivas aulas.
     * Linhas de aula com indicador de estado: ícone de concluído, ícone de aula ativa em reprodução ou círculo vazio para pendente, título truncado e duração em minutos.

O QUE NÃO EXISTE NO SISTEMA (NÃO INCLUIR):
- Controle de velocidade (1.5x, 2x), legendas, transcrição ou PIP.
- Rastreamento automático por tempo assistido (conclusão é 100% manual).
- Aulas travadas/bloqueadas por ordem ou dependência.
- Acordeão/colapso de módulos na grade lateral.
- Comentários, dúvidas ou avaliações por estrelas.
- Download em lote de arquivos.
```

