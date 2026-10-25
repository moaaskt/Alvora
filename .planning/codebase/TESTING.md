# Estratégia de Testes e Garantia de Qualidade — ALVORA

**Mapeamento da Base de Código: Outubro de 2026**

---

## 🎯 Visão Geral da Suíte de Testes

O ALVORA adota uma política rigorosa de testes de integração e comportamento (Feature Tests). Toda a funcionalidade crítica — incluindo segurança multi-tenant, validação de assinaturas em webhooks, algoritmo de predição de evasão e resiliência de IA — é validada por testes automatizados.

* **Status Atual:** ✅ **84 testes aprovados (100% de sucesso)** com **329 asserções**.
* **Framework:** `PHPUnit 12.5.12` integrado com o ecossistema de testes do Laravel.

---

## 📂 Organização dos Testes em `tests/Feature/`

A suíte contém 21 arquivos de teste em `tests/Feature/` organizados por responsabilidade de domínio:

| Arquivo de Teste | Área Coberta | Principais Cenários Validados |
| :--- | :--- | :--- |
| [`TenantScopeTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/TenantScopeTest.php) | **Segregação Multi-Tenant** | Isolamento de queries entre empresas, bloqueio de vazamento de dados e bypass do Super Admin. |
| [`EvasionRiskCalculationTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/EvasionRiskCalculationTest.php) | **Motor Anti-Evasão** | Cálculo exato dos 4 fatores (inatividade, estagnação, quizzes e desvio de pares) e gravação de breakdown. |
| [`SituationRoomAndInterventionTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/SituationRoomAndInterventionTest.php) | **Sala de Situação & IA** | Filtros de criticidade no Livewire e disparo de intervenções preditivas personalizadas. |
| [`AiTutorChatTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/AiTutorChatTest.php) | **Tutor IA (Groq)** | Injeção de contexto da lição no system prompt e gravação de histórico de mensagens. |
| [`OpenRouterFallbackAndCurriculumGeneratorTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/OpenRouterFallbackAndCurriculumGeneratorTest.php) | **Resiliência LLM** | Cascata de 3 tiers (Tier 1 ➔ Tier 2 ➔ Tier 3), chaveamento transparente após HTTP 429/500. |
| [`BillingWebhookAndDashboardTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/BillingWebhookAndDashboardTest.php) | **Webhooks Mercado Pago** | Validação de assinatura HMAC `x-signature`, rejeição de payloads forjados e esteira de carência (5 dias). |
| [`MercadoPagoCheckoutTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/MercadoPagoCheckoutTest.php) | **Checkout B2B** | Geração de preferências de checkout e planos de assinatura recorrente. |
| [`CertificateTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/CertificateTest.php) | **Certificação** | Bloqueio para progresso < 100%, emissão em PDF e checagem na rota pública `/validar-certificado/{code}`. |
| [`RealtimeAndQueueEventsTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/RealtimeAndQueueEventsTest.php) | **WebSockets & Filas** | Despacho de `CalculateEvasionRiskJob` e transmissão no canal privado `company.{id}` via Reverb. |
| [`HealthCheckAndObservabilityTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/HealthCheckAndObservabilityTest.php) | **Sondas de Infraestrutura** | Validação das sondas ativas de MySQL, Redis e disco no endpoint `/api/health`. |
| [`CourseTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/CourseTest.php) | **Gestão de Cursos** | CRUD de cursos, módulos, permissões de instrutor e contadores de aulas. |
| [`LessonPlayerTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/LessonPlayerTest.php) | **Player do Aluno** | Marcação de conclusão, salvamento de anotações privadas e download de materiais anexos. |
| [`QuizTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/QuizTest.php) | **Quizzes & Avaliações** | Cadastro de perguntas com alternativas, submissão de respostas e cálculo de nota com nota de corte. |
| [`LearningTrackTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/LearningTrackTest.php) | **Trilhas Corporativas** | Vinculação de cursos a trilhas de departamentos e restrição por empresa. |
| [`AuthenticationTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/AuthenticationTest.php) | **Autenticação & RBAC** | Login, logout, proteção de rotas com base no enum `UserRole`. |
| [`InvitationTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/InvitationTest.php) | **Onboarding de Alunos** | Geração de convites por e-mail com token e aceitação com cadastro de senha. |
| [`LandingPageTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/LandingPageTest.php) | **Vitrine Pública** | Isolamento restrito ao tenant de demonstração e captura de formulário de leads. |
| [`StudentDashboardTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/StudentDashboardTest.php) | **Portal do Aluno** | Exibição de trilhas matriculadas e métricas de progresso. |
| [`CurriculumTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/CurriculumTest.php) | **Construtor Curricular** | Adição e reordenação de módulos e aulas. |
| [`InstructorPanelAndNavbarUrlsTest.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/tests/Feature/InstructorPanelAndNavbarUrlsTest.php) | **Navegação** | Consistência de links do menu de navegação e rotas corporativas. |

---

## 🎭 Padrões de Mocking e Isolamento

### 1. APIs Externas (`Http::fake()`)
Nos testes de integração de IA e pagamentos, nunca são feitas chamadas reais para a internet:
```php
// Mock do OpenRouter simulando indisponibilidade do Tier 1 e sucesso no Tier 2:
Http::fake([
    'https://openrouter.ai/*' => Http::sequence()
        ->push(['error' => 'Rate limit exceeded'], 429)
        ->push([
            'choices' => [
                ['message' => ['content' => json_encode(['modules' => []])]]
            ]
        ], 200),
]);
```

### 2. Eventos e Filas (`Event::fake()`, `Queue::fake()`)
Garante que eventos de WebSockets e Jobs pesados possam ser auditados de forma atômica:
```php
Event::fake([EvasionRiskAlertEvent::class]);
Queue::fake([CalculateEvasionRiskJob::class]);

// Asserção
Queue::assertPushed(CalculateEvasionRiskJob::class, fn ($job) => $job->userId === $user->id);
```

---

## 🚀 Como Executar os Testes

### Ambientes de Banco de Dados nos Testes
* **Testes Rápidos Locais (Host):** Utilizam `SQLite` em memória ou arquivo (`database/testing.sqlite` conforme `phpunit.xml`) para feedback ágil durante o desenvolvimento.
* **Testes Críticos de Concorrência e Integridade:** Testes que validam condições de corrida (ex: emissões simultâneas de certificados), índices únicos sob concorrência e comportamento de chaves estrangeiras (`restrict`/`cascade`) **devem rodar também em MySQL 8** (via Docker Compose ou pipeline de CI).
* **Atenção:** Nunca presumir que testes passando no SQLite comprovam ausência de concorrência no MySQL, visto que o SQLite serializa operações de escrita.

### Execução via Docker (Ambiente Completo com MySQL)
```bash
docker compose exec app php artisan test
```

### Execução Direta no Host (SQLite)
```bash
php artisan test
```

### Execução de Suíte Específica
```bash
php artisan test --filter=CertificateTest
```

### Com Paralelismo e Verificação de Cobertura
```bash
php artisan test --parallel
```
