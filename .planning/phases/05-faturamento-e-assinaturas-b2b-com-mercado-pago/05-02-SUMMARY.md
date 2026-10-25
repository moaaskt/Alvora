---
phase: "05"
plan: "05-02"
title: "Webhooks Mercado Pago, Ciclo de Carência, Middleware de Acesso e Dashboards de Faturamento"
status: "completed"
completed_at: "2026-10-20T05:35:00-03:00"
requirements:
  - BILL-02
  - BILL-03
files_created:
  - app/Http/Controllers/Api/MercadoPagoWebhookController.php
  - routes/api.php
  - app/Http/Middleware/CheckSubscriptionStatus.php
  - app/Livewire/Company/CompanyBilling.php
  - resources/views/livewire/company/company-billing.blade.php
  - app/Livewire/Admin/AdminBilling.php
  - resources/views/livewire/admin/admin-billing.blade.php
  - tests/Feature/BillingWebhookAndDashboardTest.php
files_modified:
  - bootstrap/app.php
  - routes/web.php
  - resources/views/layouts/navigation.blade.php
---

# Plan 05-02 Summary: Webhooks Mercado Pago, Ciclo de Carência, Middleware de Acesso e Dashboards de Faturamento

## Accomplished
1. **Recepção Idempotente de Webhooks Mercado Pago (BILL-02)**:
   - Controlador `App\Http\Controllers\Api\MercadoPagoWebhookController` com rota `POST /api/webhooks/mercadopago`.
   - Validação criptográfica do cabeçalho `x-signature` (HMAC-SHA256).
   - Ativação de novos ciclos de 30 dias para pagamentos aprovados, atualização do plano e vagas da empresa (`max_users`).
   - Ativação automática do ciclo de carência de 5 dias (`status = overdue`, `grace_period_ends_at = now + 5 days`) para pagamentos recusados ou cancelados.
2. **Middleware de Controle de Status e Inadimplência (BILL-02, D-03)**:
   - Middleware `App\Http\Middleware\CheckSubscriptionStatus` registrado com o alias `subscription.active` em `bootstrap/app.php` e aplicado ao grupo autenticado.
   - Detecção e transição automática de empresas em carência expirada para status `suspended`.
   - Bloqueio de colaboradores e alunos em relação aos cursos e quizzes em caso de suspensão contratual, redirecionando com feedback explicativo.
   - Redirecionamento restrito de gestores de RH (`company_admin`) inadimplentes exclusivamente para a tela de regularização financeira (`/empresa/faturamento`).
   - Bypass global para Super Administradores (`admin_alvora`).
3. **Painel de Faturamento da Empresa (`CompanyBilling`, BILL-03)**:
   - Componente Livewire e view Blade em `resources/views/livewire/company/company-billing.blade.php`.
   - Card superior com status do plano, indicador de renovação, barra visual de ocupação de vagas de colaboradores e badges de status.
   - Banners contextuais de carência (5 dias) e suspensão.
   - Ação de Upgrade de Plano que gera nova preferência de checkout no Mercado Pago (D-04).
   - Modal destrutivo de confirmação para cancelamento de assinatura com a cópia exata do contrato de UI.
   - Tabela de histórico de faturas paginada com status, valor e link de comprovante.
4. **Painel Global de Faturamento B2B para o Super Admin (`AdminBilling`, BILL-03)**:
   - Componente Livewire e view Blade em `resources/views/livewire/admin/admin-billing.blade.php`.
   - Rota `/admin/faturamento` restrita à role `admin_alvora`.
   - Ponto focal de KPIs: MRR (Receita Recorrente Mensal em tipografia Display 32px), total de empresas assinantes por plano (Starter, Pro, Enterprise) e contagem de inadimplência.
   - Tabela de auditoria de contratos corporativos com busca, filtros de status e listagem de pagamentos.
   - Ação manual `Sincronizar Assinaturas Mercado Pago` com estado de loading e feedback reativo.
5. **Navegação e Rotas Integradas**:
   - `routes/web.php` configurado com middleware `subscription.active` e rotas nomeadas `company.billing` e `admin.billing`.
   - Menu `resources/views/layouts/navigation.blade.php` atualizado com links ativos para `Assinatura & Plano` (RH) e `Faturamento B2B` (Super Admin).
6. **Verificação Automatizada**:
   - Suíte `tests/Feature/BillingWebhookAndDashboardTest.php` aprovada com 5 testes e 32 asserções:
     - Ativação de plano e cota via webhook com validação HMAC de assinatura.
     - Início de carência de 5 dias em pagamentos recusados.
     - Bloqueio de acesso para alunos e redirecionamento de RH em empresas suspensas.
     - Operações e cancelamento no componente `CompanyBilling`.
     - Cálculo de MRR, autorização e sincronização no componente `AdminBilling`.
   - Suíte de regressão completa aprovada com 60 testes e 220 asserções sem nenhuma falha.
