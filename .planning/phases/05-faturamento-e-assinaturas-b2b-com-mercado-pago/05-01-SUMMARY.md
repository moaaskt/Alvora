---
phase: "05"
plan: "05-01"
title: "Integração com Mercado Pago, Models de Assinatura e Checkout Pro"
status: "completed"
completed_at: "2026-10-20T05:27:00-03:00"
requirements:
  - BILL-02
files_created:
  - database/migrations/2026_10_20_000014_create_subscriptions_and_invoices_tables.php
  - app/Models/Subscription.php
  - app/Models/Invoice.php
  - app/Services/MercadoPagoService.php
  - app/Http/Controllers/BillingReturnController.php
  - resources/views/billing/success.blade.php
  - resources/views/billing/pending.blade.php
  - resources/views/billing/failure.blade.php
  - tests/Feature/MercadoPagoCheckoutTest.php
files_modified:
  - app/Models/Company.php
  - config/services.php
  - routes/web.php
---

# Plan 05-01 Summary: Integração com Mercado Pago, Models de Assinatura e Checkout Pro

## Accomplished
1. **Modelos e Migrations de Faturamento B2B (BILL-02)**:
   - Migration `2026_10_20_000014_create_subscriptions_and_invoices_tables.php` criando:
     - `subscriptions`: Armazena `company_id`, `mp_preapproval_id`, `plan` ('starter', 'pro', 'enterprise'), `status`, `amount`, períodos de vigência (`current_period_start`/`current_period_end`) e controle de carência (`grace_period_ends_at`).
     - `invoices`: Registro de transações com `mp_payment_id`, `mp_preference_id`, `amount`, `payment_method`, status e data de quitação.
   - Modelos Eloquent `Subscription` e `Invoice` com a trait `BelongsToTenant` para isolamento de dados.
   - Relacionamentos e helpers incorporados em `Company` (`subscriptions()`, `invoices()`, `activeSubscription()`, `hasActiveSubscription()`, `isOverdue()`, `isSuspended()`).
2. **Serviço de Integração Mercado Pago (`MercadoPagoService`)**:
   - Centralização das credenciais em `config/services.php`.
   - `createCheckoutPreference()`: Geração de preferências do Checkout Pro para pagamentos com URLs de retorno (`/sucesso`, `/pendente`, `/erro`), referência externa da empresa e `notification_url` para webhooks.
   - `createPreapproval()`: Geração de assinaturas recorrentes com débito automático em cartão via endpoint `/preapproval`.
   - `getPayment()` e `getPreapproval()` para consulta e conciliação.
   - `validateSignature()`: Validação de integridade do cabeçalho `x-signature` via HMAC-SHA256.
3. **Rotas e Telas de Retorno do Checkout**:
   - `BillingReturnController` com rotas protegidas em `empresa/faturamento/`:
     - `/sucesso`: feedback positivo de ativação de assinatura com ID da transação e CTA para o dashboard.
     - `/pendente`: aviso informativo para pagamentos em processamento (Pix/Boleto).
     - `/erro`: tela de erro com orientação para tentar novamente ou selecionar outro meio de pagamento.
   - Telas construídas de acordo com as especificações de design do `05-UI-SPEC.md`.
4. **Verificação Automatizada**:
   - Suíte `tests/Feature/MercadoPagoCheckoutTest.php` aprovada com 5 testes e 19 asserções cobrindo persistência multi-tenant, geração de preferência com mock HTTP, assinaturas preapproval, renderização de telas de retorno e restrição de acesso a não-autenticados.
