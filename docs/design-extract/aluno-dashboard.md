# Design Inventory — `/aluno/dashboard`

Fonte da verdade extraída do código (nenhum código alterado). Itens não confirmáveis estão marcados **UNCERTAIN**.

## 1. FILES MAP

| Arquivo | Função |
|---|---|
| `routes/web.php` L156-157 | `Route::get('/aluno/dashboard', StudentDashboard::class)->name('student.dashboard')`, dentro do grupo `middleware(['auth','subscription.active'])` |
| `routes/web.php` L78-82 | `/dashboard` (name `dashboard`) redireciona aluno para `student.dashboard` |
| `app/Livewire/Student/Dashboard.php` | Componente Livewire (`#[Layout('layouts.app')]`). Propriedade `public string $search`. Métodos `enroll(int $courseId)`, `continueCourse(int $courseId)`, `render()` |
| `resources/views/livewire/student/dashboard.blade.php` | View da página (todo o conteúdo) |
| `resources/views/layouts/app.blade.php` | Layout: `<html lang="pt-BR" class="dark">`, navbar, `<main>`, footer, container de toasts, Tutor IA |
| `resources/views/layouts/navigation.blade.php` | Navbar (inclusa via `@include('layouts.navigation')`) |
| `resources/views/livewire/student/ai-tutor-drawer.blade.php` | Botão flutuante "Tutor Alvora AI" + drawer de chat (Livewire `student.ai-tutor-drawer`), carregado no layout se `isStudent()` |
| `app/Models/Enrollment.php`, `Course.php`, `TrackEnrollment.php`, `LearningTrack.php`, `Lesson.php`, `LessonProgress.php` | Dados (Course, LearningTrack, TrackEnrollment usam trait `BelongsToTenant`/`TenantScope`) |
| `app/Models/Scopes/TenantScope.php` | Filtra por `company_id` do usuário; `admin_alvora` ignora; usuário sem empresa vê nada (`1 = 0`) |
| `resources/css/app.css` | Tailwind 4 `@theme` com tokens, fonte Geist Variable |
| `resources/js/app.js` | Praticamente vazio (apenas `//`). Alpine vem do Livewire (usado em `x-data`/`@click`) |
| `vite` (`@vite`) + `@livewireStyles/@livewireScripts` | Assets |

Não há componentes Blade (`<x-...>`) usados na página: tudo é inline.

## 2. PAGE LAYOUT

Layout `layouts.app`, `body`: `bg-[#090D16] text-slate-100 antialiased min-h-screen flex flex-col justify-between`.

De cima para baixo:
1. **Navbar** (layout) — sticky `top-0 z-40`, altura `h-16`, container `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`, borda inferior `border-slate-800`.
2. `$header` slot (opcional) — **não usado** nesta página.
3. `<main class="py-8">` > `div max-w-7xl mx-auto px-4 sm:px-6 lg:px-8` (layout) > raiz da view `div.py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8`. (**Padding duplicado**: layout e página aplicam padding/container; max-width 7xl = 80rem.)
4. Seções da página, com `space-y-8` entre elas, nesta ordem:
   1. Cabeçalho de boas-vindas
   2. Flash messages (`success` / `warning`) — só se existirem
   3. 4 cards de métricas (grid)
   4. "Minhas Trilhas Corporativas" — só se houver trilhas
   5. "Continuar Aprendendo"
   6. "Cursos Disponíveis da Sua Empresa" (`id="catalogo"`, `pt-6 border-t border-white/5`)
5. **Footer** (layout): texto simples centralizado.
6. **Toasts** (layout): container fixo `bottom-5 right-5` (a página não dispara toasts).
7. **Botão flutuante Tutor** `fixed bottom-6 right-6 z-40` (layout, só aluno).

## 3. ELEMENT INVENTORY

Ícones são SVG inline estilo Heroicons outline (24x24, stroke 2); nomes inferidos pelo path: **UNCERTAIN** (nomes oficiais não estão no código).

### Navbar (`layouts/navigation.blade.php`, ramo `@else` = aluno)
| Element | Texto | Estático/Dinâmico | Fonte | Rota/ação | Visibilidade |
|---|---|---|---|---|---|
| Logo | imagem `images/logo-symbol.png`, alt "ALVORA" | Estático | asset | `route('dashboard')` (→ redireciona aluno ao dashboard) | sempre |
| Marca | "ALVORA" | Estático | — | idem | sempre |
| Subtítulo marca | nome da empresa | Dinâmico | `auth()->user()->company->name` (truncate, max 140px); se sem empresa: "Super Admin" | — | sempre |
| Link 1 | "Meu Aprendizado" | Estático | — | `student.dashboard` | `md:` e acima (`hidden md:flex`) |
| Link 2 | "Catálogo de Cursos" | Estático | — | `student.dashboard` + `#catalogo` | idem |
| Link 3 | "Minhas Trilhas" | Estático | — | `student.dashboard` (sem âncora) | idem |
| Link 4 | "Meus Certificados" | Estático | — | `student.dashboard` (sem âncora) | idem |
| Badge de papel | "Aluno" (outros: "Instrutor", "Administrador Alvora") | Dinâmico | role do usuário | — | `hidden sm:inline-flex` |
| Avatar | 2 primeiras letras maiúsculas do nome | Dinâmico | `strtoupper(substr(user->name,0,2))` | — | sempre (círculo 32px) |
| Nome do usuário | nome | Dinâmico | `user->name` | — | `hidden sm:inline-block` |
| Logout | botão só com ícone (porta/seta), `title="Sair da Plataforma"` | Estático | — | POST `route('logout')` | sempre |

Não existe menu dropdown de usuário (apenas avatar + nome + botão de logout). Não há hamburger/menu mobile.

### Cabeçalho de boas-vindas
| Element | Texto | Tipo | Fonte | Ação | Visibilidade |
|---|---|---|---|---|---|
| Chip | "Painel do Aluno" (ícone raio/bolt) | Estático | — | — | sempre |
| H1 | "Olá, {nome} 👋" | Dinâmico | `auth()->user()->name` (nome completo) | — | sempre |
| Subtexto | "Acompanhe seus cursos corporativos, continue suas aulas onde parou e aprimore suas competências profissionais." | Estático | — | — | sempre |
| Botão | "Explorar Catálogo" (ícone livro aberto) | Estático | — | link `#catalogo` (âncora) | sempre |

### Flash messages
| Element | Texto | Tipo | Fonte | Ação | Visibilidade |
|---|---|---|---|---|---|
| Sucesso (verde, ícone check-circle) | `session('success')`: "Matrícula realizada com sucesso no curso {title}!" | Dinâmico | `enroll()` | botão "✕" remove (Alpine `$el.parentElement.remove()`) | só após matrícula |
| Aviso (âmbar, ícone triângulo) | `session('warning')`: "Este curso ainda não possui aulas cadastradas." | Dinâmico | `continueCourse()` | "✕" | só se curso sem aulas |

### Cards de métricas (4)
| Label | Número | Legenda | Ícone / cor | Fonte |
|---|---|---|---|---|
| "Total Matriculado" | `$totalEnrolled` (branco) | "Cursos no seu plano" | livro aberto / azul | `$enrollments->count()` |
| "Em Andamento" | `$inProgressCount` (âmbar) | "Aulas a concluir" | relógio / âmbar | `$enrollments->where('status','active')->count()` |
| "Concluídos" | `$completedCount` (verde) | "Cursos finalizados" | check-circle / verde | `->where('status','completed')->count()` |
| "Progresso Geral" | `$averageProgress`% (roxo) | "Média de conclusão" | gráfico de barras / roxo | `(int) round(avg('progress_percent'))` se total>0, senão 0 |

Não clicáveis. Hover: `hover:border-white/10`.

### Card de Trilha (`$trackEnrollments`, grid 2 col em lg)
| Element | Texto | Tipo | Fonte | Visibilidade |
|---|---|---|---|---|
| Título da seção | "Minhas Trilhas Corporativas" (bolinha verde) | Estático | — | só se `$trackEnrollments->isNotEmpty()` |
| Contador | "{n} trilha(s) atribuída(s)" | Dinâmico | `$trackEnrollments->count()` | idem |
| Badge departamento | texto | Dinâmico | `track->department` | só se preenchido |
| Badge | "Obrigatória" (ícone triângulo de alerta, âmbar) | Dinâmico | `track->is_mandatory` true | |
| Badge | "Opcional" (cinza) | Dinâmico | `is_mandatory` false | |
| Título | texto | Dinâmico | `track->title` | sempre |
| Descrição | texto, `line-clamp-2` | Dinâmico | `track->description` | só se preenchida |
| Label progresso | "Progresso Consolidado da Trilha" + "{n}%" (verde se ≥100, senão índigo) | Dinâmico | `trackEnrollment->progress_percent` | |
| Barra | largura = percent | Dinâmico | idem | |
| Cabeçalho lista | "CURSOS DA TRILHA ({n}):" (uppercase) | Dinâmico | `track->courses->count()` | |
| Item de curso | círculo com ordem (`$loop->iteration`), título (truncate 1 linha), à direita "Concluído ✓" ou "{n}%" | Dinâmico | `course->title`, `Enrollment.progress_percent/status` | |

Cards de trilha **não têm botão/link/ação**.

### Card de Curso matriculado ("Continuar Aprendendo")
| Element | Texto | Tipo | Fonte | Ação |
|---|---|---|---|---|
| Título seção | "Continuar Aprendendo" (bolinha índigo) | Estático | | |
| Contador | "{n} curso(s) matriculado(s)" | Dinâmico | `$enrollments->count()` | |
| Capa | `<img>` (alt=título), altura `h-44` | Dinâmico | `course->thumbnail` | ou placeholder (ver §4) |
| Badge sobre capa (top-right) | "Concluído ✓" ou "{progress_percent}% Concluído" | Dinâmico | `enrollment->status`, `progress_percent` | |
| Título | texto, `line-clamp-1` | Dinâmico | `course->title` | |
| Descrição | `line-clamp-2` | Dinâmico | `course->description` ou "Sem descrição informada." | |
| Progresso | "Progresso" + "{n}%" + barra `h-2` | Dinâmico | `progress_percent` | |
| Botão | "Continuar Aula" / "Rever Aulas" (ícone play) | Dinâmico | `status` | `wire:click="continueCourse(id)"` → redireciona a `student.player` |

Todo o card **não** é clicável; só o botão.

### Busca e catálogo
| Element | Texto | Tipo | Fonte | Ação |
|---|---|---|---|---|
| Título | "Cursos Disponíveis da Sua Empresa" (bolinha verde) | Estático | | |
| Subtítulo | "Explore novos temas e desenvolva novas habilidades" | Estático | | |
| Input busca (ícone lupa) | placeholder "Buscar por curso..." | Dinâmico | `$search` | `wire:model.live.debounce.300ms="search"` |
| Capa | `h-40` | Dinâmico | `course->thumbnail` | |
| Badge capa (bottom-left) | "{n} aulas" | Dinâmico | `lessons_count` (`withCount('lessons')`) | sempre "aulas" (sem singular) |
| Título | `line-clamp-1` | Dinâmico | `course->title` | |
| Descrição | `line-clamp-2` | Dinâmico | `description` ou "Sem descrição detalhada cadastrada para este curso." | |
| Botão | "Matricular-se Gratuitamente" (ícone "+") | Estático | | `wire:click="enroll(id)"` |

### Footer / extras do layout
| Element | Texto | Observação |
|---|---|---|
| Footer | "ALVORA Plataforma B2B • Ambiente Multi-Tenant Seguro • Sessão Protegida" | arquivo contém mojibake (ver §9) |
| Botão flutuante | "Tutor Alvora AI" (ícone balão de chat, ponto pulsante), gradiente verde | só `isStudent()`; abre drawer |
| Drawer do tutor | Título "Alvora AI Tutor" + tag "Groq Fast"; subtítulo "Contexto: {aula}" ou "Assistente geral de aprendizado"; vazio: "Como posso te ajudar hoje?"; input "Digite sua dúvida sobre a aula..."; botões "Nova conversa" (title) e fechar | estado `$isOpen`; fora do escopo detalhado |

Elementos por papel: navbar muda totalmente por papel (admin/instrutor/aluno); badge de papel; Tutor só aluno. Plano: nenhuma regra de plano na tela (a assinatura é checada pelo middleware `subscription.active` na rota). Tenant: subtítulo da marca e filtro de dados.

## 4. DYNAMIC RULES AND STATES

**Card de curso matriculado**
- Badge: `status === 'completed'` → "Concluído ✓"; caso contrário → "{progress_percent}% Concluído" (inclui 0% → "0% Concluído").
- Botão: `completed` → "Rever Aulas"; senão → "Continuar Aula" (0%, andamento: mesmo rótulo; **não existe "Começar"**).
- Sem `thumbnail`: placeholder com gradiente `from-indigo-950/60 to-gray-900` e ícone play em círculo (`w-12 h-12 opacity-50`).
- Título: 1 linha (`line-clamp-1`); descrição: 2 linhas (`line-clamp-2`); truncamento por CSS, sem limite de caracteres.
- Status possíveis no código desta tela: apenas `active` e `completed`. Outros valores contariam em "Total" mas não em Em Andamento/Concluídos. Ordem: `latest('updated_at')`.
- `continueCourse`: próxima aula sem `completed_at` (ordem módulo→aula); se todas concluídas vai à primeira; se não há aulas → flash warning e permanece.

**Trilha**
- "Obrigatória" ⇐ `learning_tracks.is_mandatory` = true; senão "Opcional".
- Progresso consolidado: a cada render, `updateConsolidatedProgress()` → `LearningTrack::calculateUserProgress`: soma de `progress_percent` das matrículas do usuário nos cursos da trilha ÷ total de cursos da trilha (curso sem matrícula conta 0), arredondado, limitado 0–100; 0 cursos → 0. Se ≥100, status vira `completed` e `completed_at` é gravado. **Efeito colateral**: salva no banco em todo render.
- Cursos listados na ordem do pivot `track_courses.order`; número = posição; "Concluído ✓" se matrícula do curso `completed`, senão "{n}%" (0% se não matriculado).
- Cursos da trilha em que o aluno não está matriculado aparecem como "0%" (sem botão de matrícula).

**Cards de métricas**: fórmulas na §3.

**Cursos disponíveis**
- Query: `Course` com `status = 'published'`, `id` fora dos cursos já matriculados, filtro opcional `title LIKE %search%`, `withCount('lessons')`, `latest()` (created_at desc). "Da sua empresa" = `TenantScope` automático em `Course` (company_id do usuário). `admin_alvora` veria todas; usuário sem empresa vê nenhuma.
- Busca: server-side via Livewire, `wire:model.live.debounce.300ms`, só título (não descrição). Sem botão de limpar. Sem paginação.
- `enroll`: cria `Enrollment` (`firstOrCreate`: company_id, user_id, course_id; status `active`, progress 0, `enrolled_at` now), flash success; o curso migra para "Continuar Aprendendo" no re-render. Não há confirmação nem checagem de que o curso pertence ao tenant além do global scope. Sem limite/pagamento (texto "Gratuitamente").

**Empty states**
- Sem trilhas: **NO EMPTY STATE EXISTS** (seção inteira some).
- Sem matrículas: ícone livro + "Você ainda não está matriculado em nenhum curso" + "Navegue pelo catálogo corporativo abaixo e inscreva-se para começar seu aprendizado."
- Sem cursos disponíveis (e também busca sem resultado): "Nenhum novo curso disponível encontrado no momento." (**mesmo texto** para ambos; não existe texto específico de busca sem resultados).

**Loading/erro**: nenhum `wire:loading` na página; sem skeleton; sem estado de erro (`firstOrFail` em curso inexistente gera 404 do Laravel). Flash: apenas os dois acima, sem auto-fechar. Container de toasts existe no layout mas a página não usa. Existe um `animate-fade-in` na flash de sucesso (classe sem definição em `app.css`: **UNCERTAIN** se funciona).

## 5. NAVIGATION

| Link | Rota | Destino existe? | Regra ativo |
|---|---|---|---|
| Logo/ALVORA | `dashboard` | Sim; redireciona aluno para `/aluno/dashboard` | — |
| Meu Aprendizado | `student.dashboard` | Sim | `routeIs('student.dashboard')` → `text-indigo-400 font-semibold` |
| Catálogo de Cursos | `student.dashboard#catalogo` | Sim (âncora na mesma página) | Nenhuma |
| Minhas Trilhas | `student.dashboard` | **Stub**: mesma página, sem âncora (não há página de trilhas do aluno) | Nenhuma |
| Meus Certificados | `student.dashboard` | **Stub**: mesma página; não existe listagem de certificados | Nenhuma |
| Logout (ícone) | POST `logout` | Sim | — |
| Menu de usuário | **NO EXISTE** | — | — |

Consequência: ao estar no dashboard, só "Meu Aprendizado" aparece ativo. Existem rotas `student.player`, `student.certificate` (download por curso) e `student.quiz`, mas não ligadas à navbar nem ao dashboard (certificado não é linkado nesta tela).

## 6. DESIGN TOKENS CURRENTLY IN USE

Dark mode: **hardcoded** — `<html class="dark">` fixo no layout e classes escuras diretas (`bg-gray-900/60`, `text-white`); não há toggle nem `dark:` variants nesta tela. `app.css` tem regra `html.light body` (nunca aplicada neste layout).

Tokens em `app.css` `@theme`: fonte `--font-sans/display/body` = "Geist Variable"; `--font-serif` Newsreader; `alvora-dark #090D16`, `alvora-surface #0F172A`, `alvora-card #1E293B`, `alvora-border #334155`, `alvora-indigo #6366F1`, `alvora-emerald #10B981`; conjunto `edtech-*` (claro: bg #F8FAFC, blue #2563EB…); conjunto "Stitch" (`surface #F8F9FF`, `primary #004AC6`, `primary-container #2563EB`, `tertiary #006242`, etc.). **Nenhum desses tokens é usado nesta tela** (nenhum `alvora-*`, `edtech-*`, `primary`).

Em uso (todos utilitários Tailwind padrão ou hardcoded):
| Uso | Valor |
|---|---|
| Fundo da página | `bg-[#090D16]` (hex hardcoded; = `alvora-dark`), também em `body` do CSS |
| Navbar | `bg-[#090D16]`, `border-slate-800`, `backdrop-blur-md` |
| Cards | `bg-gray-900/60`, `border-white/5` (hover `/10`), `rounded-2xl`; cabeçalho `bg-gradient-to-r from-gray-900 via-gray-900 to-indigo-950/40`, `shadow-2xl`, blob `bg-indigo-500/10 blur-3xl` |
| Primário (botões) | `bg-indigo-600` / hover `indigo-500`, `shadow-lg shadow-indigo-600/30`; "Matricular" `bg-indigo-600/90` + `border-indigo-500/30` |
| Cores semânticas | índigo (progresso/primário), âmbar (em andamento/obrigatória/aviso), esmeralda (concluído/sucesso), roxo (média), azul (total), rosa (logout hover) |
| Texto | títulos `text-white`; secundário `text-gray-400`/`gray-500`; trilha usa `slate-*` (mistura gray e slate) |
| Barra de progresso | trilho `bg-gray-800`, preenchimento `bg-gradient-to-r from-indigo-500 to-emerald-400`, `h-2.5` (trilha) / `h-2` (curso) |
| Fonte (Geist) | H1 `text-2xl sm:text-3xl font-extrabold tracking-tight`; H2 `text-lg font-bold`; H3 curso `text-base font-bold`; H3 trilha `text-lg font-bold`; corpo `text-sm`/`text-xs`; métrica `text-2xl font-bold`; badges `text-[10px]` e `text-xs`; label uppercase `text-[11px]` |
| Raios | `rounded-2xl` (cards), `rounded-xl` (botões, input, ícones), `rounded-full` (chips/badges de capa), `rounded-md`/`rounded-lg` (badges menores) |
| Espaçamento | `space-y-8` (seções), `gap-4` (métricas), `gap-6` (cards), `p-5`/`p-6`/`p-8` |
| Sombras | `shadow-2xl` (header), `shadow-xl` (trilha), `shadow-lg` (botões) |
| Hardcoded fora de tokens | `bg-[#090D16]`, `text-[10px]`, `text-[11px]`, `max-w-[140px]`, `w-72` na busca |

## 7. RESPONSIVE BEHAVIOR
- Navbar: links `hidden md:flex` (abaixo de 768px **somem sem alternativa** — não há menu mobile); badge de papel e nome do usuário `hidden sm:*`; sempre visíveis: logo, avatar, logout.
- Container: padding `px-4 sm:px-6 lg:px-8`; `max-w-7xl`.
- Header: coluna → `md:flex-row md:items-center`; título `text-2xl` → `sm:text-3xl`; padding `p-6` → `sm:p-8`.
- Métricas: `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`.
- Trilhas: `grid-cols-1 lg:grid-cols-2`.
- Cursos (matriculados e disponíveis): `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`.
- Cabeçalho do catálogo: coluna → `sm:flex-row`; busca `w-full sm:w-72`.
- Tutor flutuante: fixo em todos os tamanhos; drawer `max-w-md`.

## 8. THINGS THAT DO NOT EXIST (verificado no código)
- Lista/seção de certificados na tela (link "Meus Certificados" é stub; rota de download existe mas não é linkada aqui)
- Página própria de trilhas do aluno; botão/link em cards de trilha
- Notificações / sino
- Gamificação (pontos, badges, níveis, ranking), streaks, metas
- Calendário/agenda/prazos (trilhas obrigatórias não têm prazo exibido)
- Recomendações personalizadas / "sugerido para você"
- Score de Risco de Evasão ou qualquer indicador de IA preditiva visível ao aluno
- Mensagens de intervenção da IA nesta tela
- Menu dropdown de usuário, perfil, configurações, trocar senha
- Menu mobile/hamburger
- Alternância de tema claro/escuro
- Filtros por categoria/nível/departamento, ordenação, paginação, "ver todos"
- Instrutor, duração, categoria, nível, avaliação, preço nos cards de curso (só título, descrição, capa, nº de aulas)
- "Última aula assistida" / próxima aula por nome nos cards
- Skeleton/spinner de carregamento; estados de erro
- Link para o Tutor de IA no corpo da página (só botão flutuante do layout — esse **existe**, apenas para alunos)
- Cancelar matrícula

## 9. BUGS AND INCONSISTENCIES FOUND
1. **Footer com encoding quebrado**: `layouts/app.blade.php` contém literalmente "SessÃ£o Protegida", "RodapÃ©", "AplicaÃ§Ã£o" (arquivo salvo com UTF-8 duplamente codificado). Confirmado no código.
2. Mojibake provável também em `Dashboard.php` (strings do flash "Matr�cula…", "nǜo possui") e `ai-tutor-drawer.blade.php` ("Vocǟ", "dǧvidas"); pode ser só a exibição do terminal — **UNCERTAIN**, verificar no navegador. Também `TenantScope.php` com "UsuÃ¡rio".
3. **Badge "Concluído ✓" sobre a capa**: é `absolute top-3 right-3` dentro da área da capa por design (sobreposição intencional). Se há sobreposição "problemática", não é verificável só pelo código — **UNCERTAIN** (precisa de screenshot).
4. **Dados de teste "ty / asdasdasda"** no card de trilha: vêm do banco (`learning_tracks.title/description`), não do código — não encontrados no código; **UNCERTAIN** da origem exata.
5. Links "Minhas Trilhas" e "Meus Certificados" apontam para a própria página (stubs); "Catálogo de Cursos" só ativo por âncora sem estado ativo.
6. Legenda "Aulas a concluir" no card "Em Andamento" é incorreta: o número conta cursos `active`, não aulas.
7. "Cursos no seu plano" no card "Total Matriculado": "plano" não existe como conceito na tela.
8. Padding/container duplicado (layout + página) → conteúdo mais estreito que o necessário em telas pequenas (`px` duplo).
9. Navbar some em <768px sem menu alternativo; nome do usuário e papel somem <640px.
10. Mistura de paletas `gray-*` e `slate-*`; tokens do `app.css` não usados.
11. Busca sem resultado exibe a mesma mensagem do estado vazio ("Nenhum novo curso disponível…").
12. "{n} aulas" não trata singular (1 aulas); "curso(s)", "trilha(s) atribuída(s)" usam plural genérico.
13. Botão "Matricular-se Gratuitamente" mesmo em ambiente B2B com assinatura/plano.
14. `animate-fade-in` sem definição no CSS do projeto (UNCERTAIN).
15. `render()` grava no banco (progresso da trilha) a cada renderização/digitação na busca (a cada atualização Livewire).
16. Sem `wire:loading`: clique em "Matricular-se"/"Continuar Aula" sem feedback visual; duplo clique possível (mitigado por `firstOrCreate`).
17. Todo o card de curso não é clicável, somente o botão; capa e título não levam à aula.
18. Possível N+1 na lista de cursos da trilha: `$enrollments->firstWhere` por curso (em memória, sem query extra; apenas custo O(n·m)).
19. Emojis no H1 ("👋") e "✓"/"✕" como caracteres textuais, não ícones.

## 10. STITCH BRIEF

```
SCREEN: Student dashboard "Painel do Aluno" (PT-BR), route /aluno/dashboard. Dark theme only (no light toggle).
Authenticated student of a B2B company. Content width max 7xl, single column of stacked sections.

SHARED NAVBAR (sticky): logo symbol + "ALVORA" + company name below it. Links (hidden under 768px, no mobile menu):
"Meu Aprendizado" (active), "Catálogo de Cursos" (scrolls to catalog section), "Minhas Trilhas" and "Meus Certificados"
(both just point to this same page; no real destination pages). Right side: badge "Aluno", avatar with 2 initials,
user name, logout icon button (title "Sair da Plataforma"). No user dropdown.

SECTIONS, in order:
1. Welcome card: chip "Painel do Aluno"; H1 "Olá, {nome completo} 👋"; text "Acompanhe seus cursos corporativos,
   continue suas aulas onde parou e aprimore suas competências profissionais."; button "Explorar Catálogo" (anchor to catalog).
2. Flash messages (only when they occur, dismissible with ✕): success "Matrícula realizada com sucesso no curso {título}!";
   warning "Este curso ainda não possui aulas cadastradas."
3. Four stat cards (label / number / caption), not clickable:
   "Total Matriculado" / count / "Cursos no seu plano"
   "Em Andamento" / count of active enrollments / "Aulas a concluir"
   "Concluídos" / count of completed / "Cursos finalizados"
   "Progresso Geral" / average % / "Média de conclusão"
4. "Minhas Trilhas Corporativas" with "{n} trilha(s) atribuída(s)". ONLY rendered if the student has track enrollments
   (no empty state). Track card: optional department badge; badge "Obrigatória" (mandatory) or "Opcional"; title;
   optional description (2 lines max); "Progresso Consolidado da Trilha" + % + progress bar (average of the track's courses,
   missing enrollment = 0%); "CURSOS DA TRILHA ({n}):" ordered list: numbered row, course title, right side either
   "Concluído ✓" or "{n}%". No buttons/links in track card.
5. "Continuar Aprendendo" with "{n} curso(s) matriculado(s)". Empty state: "Você ainda não está matriculado em nenhum curso" /
   "Navegue pelo catálogo corporativo abaixo e inscreva-se para começar seu aprendizado." Course card: cover image (or placeholder
   icon if none), badge on cover "Concluído ✓" (if completed) or "{n}% Concluído"; title (1 line); description (2 lines) or
   "Sem descrição informada."; "Progresso" + % + bar; button "Continuar Aula" (or "Rever Aulas" if completed) -> opens lesson player.
6. "Cursos Disponíveis da Sua Empresa" / "Explore novos temas e desenvolva novas habilidades". Search input, placeholder
   "Buscar por curso..." (live, debounced 300ms, searches title only). Cards: cover (or placeholder), badge "{n} aulas",
   title (1 line), description (2 lines) or "Sem descrição detalhada cadastrada para este curso.", button
   "Matricular-se Gratuitamente" (enrolls immediately, course moves to section 5). Only published courses of the student's
   company that the student is not yet enrolled in. Empty (also for search with no results): "Nenhum novo curso disponível encontrado no momento."
7. Footer: "ALVORA Plataforma B2B • Ambiente Multi-Tenant Seguro • Sessão Protegida".
8. Floating button bottom-right "Tutor Alvora AI" (students only) opens a chat drawer "Alvora AI Tutor" (tag "Groq Fast");
   empty chat "Como posso te ajudar hoje?"; input "Digite sua dúvida sobre a aula...".

RESPONSIVE FACTS: stats 1/2/4 columns (base/sm/lg); tracks 1/2 columns (base/lg); courses 1/2/3 columns (base/md/lg).

DOES NOT EXIST (do not invent): certificates list, notifications, gamification/points/badges/streaks, calendar/deadlines,
recommendations, risk score or any AI-prediction info for the student, user dropdown/profile, mobile menu, theme toggle,
filters/sort/pagination, category/instructor/duration/price/rating on cards, last-lesson info, loading skeletons,
error states, cancel enrollment, links/actions on track cards, dedicated tracks page.
```
