# Phase 1: Setup do Projeto, Auth & Multi-Tenancy - Context

**Gathered:** 2026-10-19
**Status:** Ready for planning

<domain>
## Phase Boundary

Esta fase estabelece a fundação do monolito Laravel 12 + Livewire 3 + Blade + Tailwind CSS, implementando o isolamento multi-tenant lógico por empresa (`companies`, `company_users`), controle de 4 papéis de acesso via Laravel Sanctum (`admin_alvora`, `company_admin`, `instructor`, `student`), mecanismo de convite de colaboradores e as páginas públicas institucionais (Landing Page com solicitação de demonstração e apresentação de planos B2B).
</domain>

<decisions>
## Implementation Decisions

### Multi-Tenancy & Isolamento de Dados
- **D-01:** Resolução de Tenant transparente via Global Scope no Eloquent baseado na sessão e no usuário autenticado (`company_id`). — **Reversibility:** costly — mudar para rotas com prefixo ou subdomínios exigiria reestruturar rotas, links e controllers.
- **D-02:** Um usuário pertence estritamente a 1 empresa por vez (simplificado e direto para o modelo corporativo B2B). — **Reversibility:** costly — permitir múltiplos tenants por usuário exigiria chave composta e seletor de tenant ativo.
- **D-03:** Acesso global irrestrito para `admin_alvora` (dono da plataforma faz bypass automático do escopo de tenant para inspecionar e gerenciar qualquer empresa cliente). — **Reversibility:** reversible — lógica centralizada na trait de escopo.
- **D-04:** Trait `BelongsToTenant` com Global Scope aplicada a todos os models com tenant, auto-injetando `company_id` no evento `saving()` e filtrando queries por padrão. — **Reversibility:** costly — padrão arquitetural central de isolamento de dados.

### Fluxo de Convites de Colaboradores
- **D-05:** Mecanismo híbrido de convites: disparo de e-mail transacional com token único temporário + botão no painel do RH para copiar o link direto (facilitando envio ágil via WhatsApp ou Slack corporativo). — **Reversibility:** reversible — complementa a entrega sem quebrar o fluxo de e-mail.
- **D-06:** Validade padrão do token de convite fixada em 7 dias, com botões de "Reenviar" e "Revogar" no painel do RH. — **Reversibility:** reversible — campo `expires_at` configurável.
- **D-07:** Gestor da empresa seleciona no formulário de convite se o novo usuário terá perfil `student` (colaborador/aluno padrão) ou `instructor` (instrutor corporativo). — **Reversibility:** reversible — atribuição de role no aceite do convite.
- **D-08:** Formulário unitário com suporte a múltiplos e-mails colados em textarea separados por vírgula para disparo em lote na Fase 1. — **Reversibility:** reversible — prepara o terreno para importação via CSV na v2.

### Landing Page & Planos B2B
- **D-09:** Foco de conversão voltado para demonstração B2B (CTA principal "Solicitar Demonstração" abrindo formulário de captura de lead comercial). — **Reversibility:** reversible — landing institucional flexível.
- **D-10:** Apresentação de 3 níveis corporativos de planos (Starter, Pro, Enterprise) com comparativo visual de benefícios, limite de colaboradores e destaque para a IA preditiva de evasão. — **Reversibility:** reversible — conteúdo visual Blade.
- **D-11:** Identidade visual dark moderna com acentos de confiança (tons grafite/slate, índigo/azul elétrico e acentos esmeralda), transmitindo sofisticação, tecnologia e IA. — **Reversibility:** reversible — tokens de estilo Tailwind CSS.
- **D-12:** Processamento de leads salvando em tabela própria no banco de dados (`leads`) com feedback visual imediato e exibição no dashboard do `admin_alvora`. — **Reversibility:** reversible — tabela simples de captura.

### Estrutura de UI & Componentes
- **D-13:** Scaffolding inicial baseado em Laravel Breeze com Livewire/Volt customizado e adaptado para a stack Tailwind CSS com traduções e padrões idiomáticos em PT-BR. — **Reversibility:** costly — fundação de autenticação do monolito.
- **D-14:** Estrutura de layouts dedicados por perfil (layout base com navegações/sidebars contextuais para `admin_alvora`, `company_admin` e `student`/`instructor`). — **Reversibility:** reversible — organização de views Blade.
- **D-15:** Biblioteca de ícones baseada em Heroicons (SVG inline ou componentes Blade limpos, sem dependências externas pesadas). — **Reversibility:** reversible — componentes Blade reutilizáveis.
- **D-16:** Notificações e feedback de usuário usando toasts reativos em Livewire disparados via eventos (`$dispatch('toast', ...)`). — **Reversibility:** reversible — componente de feedback isolado.

### the agent's Discretion
- Definição exata dos campos da tabela `leads` (nome, e-mail corporativo, empresa, cargo, telefone, mensagem).
- Estrutura exata das migrations de tabelas acessórias (`invitations`, `company_users`).
- Estilização detalhada das views de login, registro de senha do convite e recuperação de senha.
</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Documentos de Planejamento e Arquitetura
- `.planning/PROJECT.md` — Contexto geral do produto Alvora, proposta de valor, entidades e convenções
- `.planning/REQUIREMENTS.md` § Authentication & Multi-tenancy — Requisitos funcionais AUTH-01 a AUTH-04 e BILL-01
- `.planning/ROADMAP.md` § Phase 1 — Metas, planos e critérios de sucesso da Fase 1

### Referências Técnicas
- Laravel 12 Sanctum & Session Auth Documentation
- Livewire 3 Documentation (Events & Reactive Components)
</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- Repositório inicializado em Git e estruturado pelo GSD; código da aplicação Laravel 12 será provisionado nos planos desta fase.

### Established Patterns
- Monolito Laravel 12 com Livewire 3 e Blade templates.
- Multi-tenancy lógico centrado em `company_id` com a trait `BelongsToTenant`.
- Nomes de métodos, tabelas de domínio, variáveis e regras de negócio em PT-BR ou convenção mista padrão Laravel/PT-BR.

### Integration Points
- Rotas web e de autenticação (`routes/web.php`, `routes/auth.php`).
- Middlewares de tenant e perfil (`TenantScopeMiddleware`, `RoleMiddleware`).
- Componentes Livewire de login, aceite de convite e gestão de usuários.
</code_context>

<specifics>
## Specific Ideas
- No painel do RH, ao enviar convite, disponibilizar um botão "Copiar link" com feedback "Link copiado!" para que o gestor possa colar diretamente no Slack/WhatsApp do colaborador.
- A landing page deve destacar explicitamente o combate à evasão de 41,6% no treinamento corporativo como o diferencial central da Alvora.
</specifics>

<deferred>
## Deferred Ideas
- Importação em massa de colaboradores via planilha CSV/Excel — Deferido para a v2 (na Fase 1 o formulário aceita múltiplos e-mails colados).
- Personalização de marca (White-label: logo e cores do tenant) no portal do aluno — Deferido para versão futura.
</deferred>

---

*Phase: 1-Setup do Projeto, Auth & Multi-Tenancy*
*Context gathered: 2026-10-19*
