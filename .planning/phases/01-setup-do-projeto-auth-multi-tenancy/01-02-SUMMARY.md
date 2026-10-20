---
phase: "01"
plan: "01-02"
title: "Modelagem Multi-Tenant, Trait de Escopo & 4 Perfis de Acesso"
status: "completed"
completed_at: "2026-10-19T16:30:00-03:00"
requirements:
  - AUTH-02
  - AUTH-03
decisions:
  - D-01
  - D-02
  - D-03
  - D-04
files_created:
  - app/Models/Company.php
  - app/Traits/BelongsToTenant.php
  - app/Models/Scopes/TenantScope.php
  - app/Enums/UserRole.php
  - app/Http/Middleware/EnsureUserHasRole.php
  - database/migrations/2026_10_19_000001_create_companies_table.php
  - database/migrations/2026_10_19_000002_create_company_users_table.php
  - tests/Feature/TenantScopeTest.php
files_modified:
  - app/Models/User.php
  - bootstrap/app.php
---

# Plan 01-02 Summary: Modelagem Multi-Tenant, Trait de Escopo & 4 Perfis de Acesso

## Accomplished
1. **Entidades Centrais de Multi-Tenancy**:
   - Model `Company` com campos `name`, `cnpj`, `plan` (starter, pro, enterprise), `status`, `max_users` e métodos de cálculo de vagas disponíveis (`availableSlots()`).
   - Tabela de relacionamento `company_users` para colaboradores e gestores.
   - Atualização do Model `User` com `company_id`, role enum e métodos de verificação (`isAlvoraAdmin()`, `isCompanyAdmin()`, `isInstructor()`, `isStudent()`).
   - Regra de negócio D-02 implementada: cada usuário pertence estritamente a 1 empresa por vez.
2. **Global Scope de Tenant e Trait Reutilizável**:
   - `App\Models\Scopes\TenantScope` implementado (D-01): aplica automaticamente o filtro `where company_id = auth()->user()->company_id` para usuários autenticados.
   - Bypass irrestrito para `admin_alvora` (D-03): super-administradores da plataforma Alvora têm visão global de todos os tenants sem restrição de escopo.
   - `App\Traits\BelongsToTenant` (D-04): anexa o `TenantScope` e injeta automaticamente `company_id` no evento `creating` quando o usuário logado cria um recurso.
3. **Controle de Acesso por Papéis (Roles)**:
   - Enum `App\Enums\UserRole` cobrindo os 4 papéis: `admin_alvora`, `company_admin`, `instructor` e `student`.
   - Middleware `EnsureUserHasRole` registrado sob o alias `role` em `bootstrap/app.php`, protegendo rotas por perfil e permitindo bypass transparente para `admin_alvora`.
4. **Verificação Automatizada**:
   - 4 testes de feature em `tests/Feature/TenantScopeTest.php` cobrindo isolamento entre empresas, auto-injeção de `company_id`, bypass do Admin Alvora e autorização de middleware. Todos os testes aprovados com sucesso.
