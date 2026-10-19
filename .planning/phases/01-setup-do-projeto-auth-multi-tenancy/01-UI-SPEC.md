---
phase: "1"
slug: "setup-do-projeto-auth-multi-tenancy"
status: approved
shadcn_initialized: false
preset: none
created: "2026-10-19"
---

# Phase 1 — UI Design Contract: Setup do Projeto, Auth & Multi-Tenancy

> Visual and interaction contract for frontend surfaces in Phase 1. Defines design system tokens, typography, colors, component styling, copywriting and UI state coverage.

---

## Design System

| Property | Value |
|----------|-------|
| Tool | none (Tailwind CSS nativo com Blade Components) |
| Preset | not applicable |
| Component library | none (Livewire 3 + Blade Components sob medida) |
| Icon library | Heroicons (SVG inline e componentes Blade) |
| Font | Plus Jakarta Sans (primária para títulos e interface) / Inter (corpo) |

---

## Spacing Scale

Declared values (múltiplos de 4px alinhados à escala do Tailwind CSS):

| Token | Value | Usage |
|-------|-------|-------|
| xs | 4px | Gaps internos de ícones, badges, inline micro-padding |
| sm | 8px | Espaçamento entre inputs e labels compactos, botões compactos |
| md | 16px | Espaçamento padrão de inputs, cards compactos, gaps de grid |
| lg | 24px | Padding interno de cards de dashboard e formulários |
| xl | 32px | Gaps entre seções secundárias de página e barras de navegação |
| 2xl | 48px | Separação entre blocos de conteúdo da landing page |
| 3xl | 64px | Padding superior/inferior de seções principais da landing page |

Exceptions: none

---

## Typography

| Role | Size | Weight | Line Height |
|------|------|--------|-------------|
| Body | 14px / 16px | 400 (Regular) | 1.5 (24px) |
| Label | 12px / 14px | 500 (Medium) / 600 (Semibold) | 1.25 |
| Heading (H3/H2) | 20px / 24px / 30px | 600 (Semibold) / 700 (Bold) | 1.3 |
| Display (H1/Hero) | 36px / 48px / 60px | 700 (Bold) / 800 (Extrabold) | 1.15 |

---

## Color

Paleta Dark Moderna orientada à tecnologia, confiabilidade corporativa e IA:

| Role | Value | Usage |
|------|-------|-------|
| Dominant (60%) | `#090D16` (Slate Profundo) / `#0F172A` (Surface) | Fundo geral da aplicação, superfícies primárias |
| Secondary (30%) | `#1E293B` (Cards / Sidebar) / `#334155` (Bordas) | Cards, painéis laterais, modais, cabeçalhos, inputs |
| Accent (10%) | `#6366F1` (Índigo) / `#10B981` (Esmeralda) | CTAs principais, indicadores de retenção/segurança, badges |
| Destructive | `#EF4444` (Rose/Red) | Ações destrutivas (revogar convite, desativar usuário) |

Accent reserved for:
- Botões de Ação Primária ("Solicitar Demonstração", "Enviar Convites", "Entrar")
- Badges de plano ativo e status "Entregue" / "Ativo"
- Destaques de métricas positivas e anéis de foco de acessibilidade (`focus:ring-indigo-500`)

---

## Copywriting Contract

| Element | Copy |
|---------|------|
| Primary CTA (Landing) | "Solicitar Demonstração" |
| Secondary CTA (Landing) | "Conhecer os Planos Corporativos" |
| Primary CTA (Auth) | "Entrar na Plataforma" |
| Primary CTA (Convite RH) | "Disparar Convites" |
| Secondary Action (Convite RH) | "Copiar link de convite" |
| Feedback Link Copiado | "Link copiado para a área de transferência!" |
| Empty state heading (Colaboradores) | "Nenhum colaborador cadastrado ainda" |
| Empty state body (Colaboradores) | "Comece convidando os membros da sua equipe para iniciar os treinamentos corporativos." |
| Empty state heading (Leads no Admin) | "Nenhuma solicitação de demonstração pendente" |
| Empty state body (Leads no Admin) | "Novos contatos da landing page aparecerão listados aqui." |
| Error state (Login) | "Credenciais inválidas. Verifique seu e-mail e senha corporativos." |
| Error state (Convite expirado) | "Este link de convite expirou ou já foi utilizado. Solicite um novo acesso ao gestor de RH da sua empresa." |
| Destructive confirmation (Revogar) | "Revogar convite: Tem certeza de que deseja cancelar este link de acesso para {email}?" |

---

## UI Considerations

| Category | Element(s) | Status | Resolution / Reason |
|----------|------------|--------|---------------------|
| empty | Lista de Colaboradores (RH) | ✅ covered | Exibe estado vazio elegante com ilustração sutil e botão direto para abrir modal de convites. |
| empty | Lista de Convites Pendentes | ✅ covered | Renderiza texto informativo: "Não há convites aguardando ativação no momento." |
| loading | Formulário de Login & Demonstração | ✅ covered | Desabilita o botão primário e exibe spinner com `wire:loading` durante a submissão. |
| error | Validação de formulários Livewire | ✅ covered | Mensagens de erro inline abaixo dos inputs em vermelho suave (`text-rose-400 text-xs mt-1`). |
| feedback | Ações de Sucesso (Convite/Configuração) | ✅ covered | Toast reativo flutuante no canto superior direito disparado via `$dispatch('toast', { message, type: 'success' })`. |
| overflow | Textarea de múltiplos e-mails no convite | ✅ covered | Sanitização e separação automática por vírgula ou quebra de linha com visualização de contagem de e-mails válidos. |
| state-badge | Status de Convite | ✅ covered | Badges coloridos: `Pendente` (amarelo âmbar), `Ativado` (esmeralda), `Expirado` (cinza ardósia). |

---

## Registry Safety

| Registry | Blocks Used | Safety Gate |
|----------|-------------|-------------|
| Tailwind CSS & Blade | Componentes nativos sob medida | not required |

---

## Checker Sign-Off

- [x] Dimension 1 Copywriting: PASS
- [x] Dimension 2 Visuals: PASS
- [x] Dimension 3 Color: PASS
- [x] Dimension 4 Typography: PASS
- [x] Dimension 5 Spacing: PASS
- [x] Dimension 6 Registry Safety: PASS
- [x] Dimension 7 Inventory Provenance: PASS

**Approval:** approved 2026-10-19
