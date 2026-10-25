---
wave: 1
depends_on: []
files_modified:
  - routes/web.php
  - resources/views/layouts/navigation.blade.php
  - resources/views/dashboard.blade.php
  - resources/views/livewire/auth/login.blade.php
  - app/Enums/UserRole.php
  - app/Models/User.php
  - app/Http/Middleware/CheckSubscriptionStatus.php
  - app/Services/MercadoPagoService.php
  - tests/Feature/SituationRoomAndInterventionTest.php
  - tests/Feature/MercadoPagoCheckoutTest.php
  - tests/Feature/BillingWebhookAndDashboardTest.php
  - tests/Feature/InvitationTest.php
  - tests/Feature/LearningTrackTest.php
status: complete
---

# Quick Task: 20261023-instructor-navbar-urls-fix

## Objective
Corrigir a estrutura de URLs e papéis do sistema conforme especificado pelo usuário:
1. O instrutor representa a própria empresa (tenant corporativo), enquanto o ADMIN administra os instrutores e o ALUNO consome os conteúdos. A hierarquia de logins/acessos é estritamente: Admin, Instrutor, Aluno.
2. Alterar as rotas da empresa de `/empresa/...` para `/instrutor/...` (ex: `/instrutor/colaboradores`, `/instrutor/trilhas`, `/instrutor/faturamento`, `/instrutor/sala-de-situacao`, `/instrutor/insights-executivos`).
3. Manter aliases e redirecionamento de `/empresa/*` para `/instrutor/*` para retrocompatibilidade.
4. Unificar na Navbar o menu de navegação do Instrutor com todos os links corporativos (Cursos, Colaboradores & Convites, Trilhas, Sala de Situação, Insights de IA, Assinatura).
5. Ajustar a tela de login (Sandbox) para listar na ordem exata: Admin, Instrutor, Aluno.
6. Validar que todos os testes passem com 100% de sucesso.
