# 09. Matriz de Rotas, Controladores e Personas

## 🗺️ Visão Consolidada de Rotas e Endpoints

Abaixo está o mapeamento de todas as rotas registradas no sistema, agrupadas por perfil de acesso e escopo de autorização.

---

### 🌐 1. Rotas Públicas (Sem Autenticação)

| Método | URL | Controlador / Ação | Descrição |
| :--- | :--- | :--- | :--- |
| `GET` | `/` | Closure (`routes/web.php`) | Landing page com vitrine dos cursos de demonstração e formulário de leads. |
| `GET` | `/planos` | View `pricing` | Tabela de planos e preços B2B. |
| `GET` | `/validar-certificado/{code}` | `CertificateController@verify` | Validação pública de autenticidade de certificados emitidos. |
| `POST` | `/api/webhooks/mercadopago` | `MercadoPagoWebhookController@handle` | Recepção de webhooks de pagamento com validação HMAC. |
| `GET` | `/api/health` | `HealthCheckController@check` | Sonda de saúde da infraestrutura (MySQL, Redis, Storage). |

---

### 🎓 2. Portal do Aluno / Colaborador (`role: student`)
*Protegido por middlewares `auth` e `subscription.active`.*

| Método | URL | Componente / Controlador | Nome da Rota |
| :--- | :--- | :--- | :--- |
| `GET` | `/aluno/dashboard` | `App\Livewire\Student\Dashboard` | `student.dashboard` |
| `GET` | `/aluno/cursos/{course:slug}/aulas/{lesson?}` | `App\Livewire\Student\LessonPlayer` | `student.player` |
| `GET` | `/aluno/cursos/{course:slug}/certificado` | `CertificateController@download` | `student.certificate` |
| `GET` | `/aluno/quizzes/{quiz}` | `App\Livewire\Student\QuizRunner` | `student.quiz` |

---

### 🏢 3. Portal da Empresa: RH & Gestão Corporativa (`role: instructor, company_admin`)
*Protegido por middlewares `auth`, `subscription.active` e `role:instructor,company_admin`.*

| Método | URL | Componente / Controlador | Nome da Rota | Finalidade |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/dashboard` | Closure (`dashboard.blade.php`) | `dashboard` | Dashboard executivo com contadores e risco médio de evasão. |
| `GET` | `/instrutor/colaboradores` | `Company\EmployeeManager` | `employees.index` | Convite, listagem e gestão de colaboradores da empresa. |
| `GET` | `/instrutor/trilhas` | `Company\TrackManager` | `company.tracks.index` | Criação e atribuição de trilhas de aprendizado por departamento. |
| `GET` | `/instrutor/faturamento` | `Company\CompanyBilling` | `company.billing` | Painel de faturas, plano ativo e atualização de pagamento. |
| `GET` | `/instrutor/sala-de-situacao` | `Company\SituationRoom` | `company.situation-room` | Painel em tempo real com matriz de risco de evasão e alertas. |
| `GET` | `/instrutor/insights-executivos` | `Company\AiExecutiveInsights` | `company.ai-insights` | Relatórios qualitativos gerados por IA para tomadores de decisão. |
| `GET` | `/instrutor/faturamento/sucesso` | `BillingReturnController@success` | `company.billing.success` | Callback de sucesso do Checkout Mercado Pago. |
| `GET` | `/instrutor/faturamento/pendente` | `BillingReturnController@pending` | `company.billing.pending` | Callback de pagamento pendente (boleto/pix). |
| `GET` | `/instrutor/faturamento/erro` | `BillingReturnController@failure` | `company.billing.failure` | Callback de falha no pagamento. |

---

### ✍️ 4. Portal do Instrutor: Gestão Pedagógica de Cursos (`role: instructor, company_admin`)

| Método | URL | Componente Livewire | Nome da Rota | Finalidade |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/instrutor/cursos` | `Instructor\CourseList` | `instructor.courses.index` | Listagem dos cursos da empresa com status e filtros. |
| `GET` | `/instrutor/cursos/novo` | `Instructor\CourseEditor` | `instructor.courses.create` | Cadastro de novo curso (título, descrição, carga horária). |
| `GET` | `/instrutor/cursos/{course}/editar` | `Instructor\CourseEditor` | `instructor.courses.edit` | Edição dos dados cadastrais do curso. |
| `GET` | `/instrutor/cursos/{course}/curriculo` | `Instructor\CurriculumBuilder` | `instructor.courses.curriculum` | Construtor em abas de módulos, aulas e geração via IA. |
| `GET` | `/instrutor/cursos/{course}/quizzes/novo` | `Instructor\QuizEditor` | `instructor.courses.quizzes.create` | Criação de avaliação atrelada ao curso. |
| `GET` | `/instrutor/cursos/{course}/quizzes/{quiz}/editar`| `Instructor\QuizEditor` | `instructor.courses.quizzes.edit` | Edição de perguntas, alternativas e nota de corte. |
| `GET` | `/instrutor/quizzes/{quiz}/editar` | `Instructor\QuizEditor` | `instructor.quizzes.edit` | Rota direta de edição de quiz. |

---

### 👑 5. Super Admin Global Alvora (`role: admin_alvora`)

| Método | URL | Componente Livewire | Nome da Rota | Finalidade |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/admin/faturamento` | `Admin\AdminBilling` | `admin.billing` | Painel financeiro global consolidado com cálculo de MRR de todas as empresas. |
| `GET` | `/horizon` | Laravel Horizon Dashboard | `horizon.index` | Painel de monitoramento de workers, filas Redis e jobs com erro. |
