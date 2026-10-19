---
phase: "01"
plan: "01-01"
title: "Bootstrap do Laravel 12 & Landing Page Institucional B2B"
status: "completed"
completed_at: "2026-10-19T16:25:00-03:00"
requirements:
  - BILL-01
decisions:
  - D-09
  - D-10
  - D-11
  - D-12
  - D-15
files_created:
  - resources/views/layouts/guest.blade.php
  - resources/views/landing.blade.php
  - resources/views/pricing.blade.php
  - app/Livewire/LeadForm.php
  - resources/views/livewire/lead-form.blade.php
  - app/Models/Lead.php
  - app/View/Components/GuestLayout.php
  - database/migrations/2026_10_19_000000_create_leads_table.php
  - tests/Feature/LandingPageTest.php
files_modified:
  - resources/css/app.css
  - routes/web.php
  - phpunit.xml
  - .env
---

# Plan 01-01 Summary: Bootstrap do Laravel 12 & Landing Page Institucional B2B

## Accomplished
1. **Estrutura Base do Framework**:
   - Bootstrap da aplicação Laravel 12 / 13 com Livewire 3 e Tailwind CSS configurado.
   - Banco de dados MySQL 8 orquestrado em container Docker (`alvora_mysql` na porta 3307) com suporte a testes em banco dedicado `alvora_test`.
   - Layout corporativo `resources/views/layouts/guest.blade.php` com tema Dark Moderno (`#090D16`), fontes Plus Jakarta Sans e Inter e sistema global de Toasts reativos (D-11, D-16).
2. **Landing Page Institucional B2B (D-09)**:
   - Hero com proposta de valor central: combate à evasão de 41,6% com IA Preditiva.
   - Métricas de impacto corporativo (+78% de conclusão, -41,6% de evasão, 24/7 tutor IA).
   - Seção de Diferenciais de IA (Score Preditivo de Evasão, Intervenções Automatizadas, Sala de Situação do RH e Tutor IA de Baixa Latência).
   - CTAs de conversão primários focados em solicitação de demonstração corporativa.
3. **Página de Planos Corporativos (D-10)**:
   - Grade com os 3 planos corporativos: Starter (até 20 colaboradores), Pro + IA (até 100 colaboradores com Score de Evasão e Tutor) e Enterprise (acima de 100 colaboradores com geração de cursos por IA).
   - Seção de dúvidas frequentes sobre faturamento B2B, emissão de NF e regras de licenciamento.
4. **Captura Reativa de Leads (D-12)**:
   - Componente Livewire `LeadForm` com validação de dados corporativos (nome, e-mail institucional, empresa, cargo, faixa de colaboradores, mensagem).
   - Persistência na tabela `leads` com status inicial `novo` e disparo de toast de sucesso.
5. **Verificação Automatizada**:
   - 4 testes de feature em `tests/Feature/LandingPageTest.php` com 22 asserções aprovadas.
