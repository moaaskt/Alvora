---
status: complete
completed_at: 2026-10-23
slug: 20261023-instructor-navbar-urls-fix
files_modified:
  - routes/web.php
  - resources/views/layouts/navigation.blade.php
  - resources/views/dashboard.blade.php
  - resources/views/livewire/auth/login.blade.php
  - app/Enums/UserRole.php
  - app/Http/Middleware/CheckSubscriptionStatus.php
  - app/Services/MercadoPagoService.php
  - tests/Feature/SituationRoomAndInterventionTest.php
  - tests/Feature/MercadoPagoCheckoutTest.php
  - tests/Feature/BillingWebhookAndDashboardTest.php
  - tests/Feature/InstructorPanelAndNavbarUrlsTest.php
---

# Quick Task Summary: 20261023-instructor-navbar-urls-fix

## Status
Concluído com sucesso (✓). Todos os 91 testes da suíte passaram sem falhas.

## O Que Foi Realizado:
1. **Unificação e Padronização de URLs para `/instrutor`:**
   - Rotas corporativas que antes utilizavam `/empresa/...` foram atualizadas para o prefixo correto `/instrutor/...` (`/instrutor/colaboradores`, `/instrutor/trilhas`, `/instrutor/faturamento`, `/instrutor/sala-de-situacao`, `/instrutor/insights-executivos`).
   - Adicionados redirecionamentos de retrocompatibilidade de `/empresa/*` para `/instrutor/*`.
2. **Harmonização do Papel do Instrutor como a Própria Empresa:**
   - O instrutor possui acesso total ao painel corporativo e aos cursos.
   - Atualizados os rótulos de perfil em [UserRole.php](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Enums/UserRole.php) e middlewares de verificação de papel e suspensão.
3. **Atualização da Barra de Navegação (Navbar):**
   - Em [navigation.blade.php](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/layouts/navigation.blade.php), o menu de instrutor foi unificado com links diretos para Cursos & Conteúdos, Colaboradores & Convites (`/instrutor/colaboradores`), Trilhas Corporativas, Sala de Situação, Insights de IA e Assinatura & Plano.
   - Badge atualizado para exibir `Instrutor`.
4. **Hierarquia de Acesso e Login (Admin, Instrutor, Aluno):**
   - Ajustada a tela de login sandbox em [login.blade.php](file:///c:/Users/Windows%20Lite%20BR/alvora/resources/views/livewire/auth/login.blade.php) para a ordem estrita solicitada: Admin, Instrutor, Aluno.
5. **Cobertura de Testes:**
   - Criado teste automatizado abrangente em [InstructorPanelAndNavbarUrlsTest.php](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/InstructorPanelAndNavbarUrlsTest.php).
   - Todos os 91 testes da aplicação foram validados e aprovados.
