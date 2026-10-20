---
phase: "01"
plan: "01-03"
title: "Autenticação Sanctum, Layouts Especializados & Gestão de Convites"
status: "completed"
completed_at: "2026-10-19T16:40:00-03:00"
requirements:
  - AUTH-01
  - AUTH-04
decisions:
  - D-05
  - D-06
  - D-07
  - D-08
  - D-13
  - D-14
  - D-16
files_created:
  - app/Models/Invitation.php
  - app/Livewire/Auth/Login.php
  - app/Livewire/Auth/AcceptInvitation.php
  - app/Livewire/Company/EmployeeManager.php
  - resources/views/layouts/app.blade.php
  - resources/views/layouts/navigation.blade.php
  - resources/views/livewire/auth/login.blade.php
  - resources/views/livewire/auth/accept-invitation.blade.php
  - resources/views/livewire/company/employee-manager.blade.php
  - resources/views/dashboard.blade.php
  - app/View/Components/AppLayout.php
  - routes/auth.php
  - database/migrations/2026_10_19_000003_create_invitations_table.php
  - tests/Feature/AuthenticationTest.php
  - tests/Feature/InvitationTest.php
files_modified:
  - routes/web.php
---

# Plan 01-03 Summary: Autenticação Sanctum, Layouts Especializados & Gestão de Convites

## Accomplished
1. **Autenticação Sanctum & Livewire em PT-BR (D-13)**:
   - Componente Livewire `App\Livewire\Auth\Login` com validações idiomáticas em português e mensagens de erro seguras contra enumeração de credenciais.
   - Rotas de autenticação isoladas em `routes/auth.php` com logout por invalidação de sessão e regeneração de CSRF token.
2. **Layouts Especializados por Papel (D-14) e Sistema de Toasts (D-16)**:
   - Layout autenticado `resources/views/layouts/app.blade.php` com suporte a headers de página e container global de toasts reativos.
   - Componente `resources/views/layouts/navigation.blade.php` exibindo rotas e menus contextuais adaptados ao papel do usuário (`admin_alvora`, `company_admin`, `instructor`, `student`).
   - Badge visual de perfil e identificador do tenant atual na barra de navegação superior.
3. **Módulo Híbrido de Convites de Colaboradores (D-05, D-06, D-07, D-08)**:
   - Componente `App\Livewire\Company\EmployeeManager` no painel do gestor de RH (`company_admin`).
   - Suporte a múltiplos e-mails colados em textarea separados por vírgula ou nova linha (D-08).
   - Atribuição direta de papel (`student` ou `instructor`) pelo RH no formulário de convite (D-07).
   - Validade padrão do convite de 7 dias com expiração controlada e ações de "Reenviar" e "Revogar" (D-06).
   - Botão para copiar o link direto de ativação com feedback visual em toast reativo (D-05, D-16).
4. **Fluxo de Ativação de Acesso do Convidado**:
   - Componente `App\Livewire\Auth\AcceptInvitation` acessado via `/convite/{token}`.
   - Verificação de validade de token e exibição de estado elegante de erro caso o link esteja expirado ou já utilizado.
   - Cadastro seguro com confirmação de senha, criação do `User` com perfil pré-definido e login imediato direcionado ao `dashboard`.
5. **Verificação Automatizada**:
   - 8 novos testes de feature em `AuthenticationTest` e `InvitationTest` (totalizando 18 testes e 68 asserções no projeto), cobrindo login, logout, envio em lote de convites, expiração de 7 dias, aceitação e autorização. Todos aprovados com 100% de sucesso.
