# Phase 1: Setup do Projeto, Auth & Multi-Tenancy - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-10-19
**Phase:** 1-Setup do Projeto, Auth & Multi-Tenancy
**Areas discussed:** multi_tenancy, employee_invitation, landing_page, ui_structure

---

## Resolução de Multi-tenancy

| Option | Description | Selected |
|--------|-------------|----------|
| Global Scope Eloquent via Sessão/Usuário | Transparente, models filtram company_id automaticamente do usuário logado | ✓ |
| Rotas com Prefixo de Tenant | URLs explícitas por tenant (/empresa/{slug}/...), maior complexidade nas rotas | |
| Subdomínios | Isolamento por subdomínio (empresa.alvora.com.br) | |

**User's choice:** Global Scope Eloquent via Sessão/Usuário; 1 usuário pertence a 1 empresa por vez; admin_alvora com acesso global irrestrito; Trait BelongsToTenant com Global Scope nos models.
**Notes:** O isolamento deve ser rígido e à prova de vazamento de dados, permitindo ao dono da plataforma visualizar métricas consolidadas.

---

## Fluxo de Convite de Colaboradores

| Option | Description | Selected |
|--------|-------------|----------|
| Link com Token Temporário por E-mail | RH informa e-mail/nome, colaborador recebe link seguro | |
| Cadastro Imediato com Senha Temporária | RH gera senha provisória e colaborador troca | |
| Híbrido | Link por e-mail com opção de copiar o link diretamente no painel do RH | ✓ |

**User's choice:** Mecanismo híbrido (e-mail + cópia manual de link no painel); validade de 7 dias com renovação/revogação; seleção de papel (student ou instructor); textarea com múltiplos e-mails na v1.
**Notes:** Agiliza a distribuição de acessos no onboarding corporativo em canais internos como Slack e WhatsApp.

---

## Landing Page e Planos B2B

| Option | Description | Selected |
|--------|-------------|----------|
| Foco em Demonstração B2B | CTA principal 'Solicitar Demonstração' com formulário de lead comercial | ✓ |
| Misto | CTA para 'Solicitar Demonstração' e 'Ver Planos & Preços' | |
| Auto-serviço | Cadastro direto com trial imediato | |

**User's choice:** Foco em demonstração comercial B2B; apresentação de 3 níveis de planos corporativos (Starter, Pro, Enterprise); estética dark moderna com tons grafite/slate, índigo e esmeralda; gravação de leads no banco com visualização no painel admin_alvora.
**Notes:** Posiciona a Alvora como solução institucional séria, enfatizando a IA preditiva e a retenção de colaboradores.

---

## Estrutura de UI e Componentes

| Option | Description | Selected |
|--------|-------------|----------|
| Livewire 3 Puro + Tailwind | Componentes Blade sob medida sem pacotes de auth prontos | |
| Laravel Breeze com Livewire/Volt | Scaffolding oficial adaptado para Tailwind e convenções do projeto | ✓ |

**User's choice:** Laravel Breeze com Livewire/Volt customizado em PT-BR; layouts dedicados por perfil (admin_alvora, company_admin, student/instructor); ícones Heroicons; toasts reativos em Livewire via eventos ($dispatch).
**Notes:** Garante agilidade no scaffolding de autenticação mantendo flexibilidade total no design do produto.

---

## the agent's Discretion

- Estrutura de campos auxiliares na tabela `leads` e tabelas de token de convite.
- Estilização detalhada de componentes menores de formulário.

## Deferred Ideas

- Importação em massa de colaboradores via CSV/planilha Excel (planejado para v2).
- Suporte a multi-empresa com alternador de contexto para um mesmo usuário (v2 se houver demanda de consultores).
