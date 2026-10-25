# Convenções de Código e Padrões de Desenvolvimento — ALVORA

**Mapeamento da Base de Código: Outubro de 2026**

---

## 🧭 Diretrizes Gerais de Linguagem

* **Código-Fonte (Sintaxe e Identificadores):** Em **Inglês** (`Course`, `LessonProgress`, `EvasionRiskCalculatorService`, `calculateForUser`, `$companyId`).
* **Documentação, UI e Comunicação:** Em **Português do Brasil (PT-BR)** (Mensagens na interface, notificações, e-mails, logs explicativos e documentação técnica em `.planning/` e `arquitetura/`).

---

## 🛠️ Padrões de Código PHP (PSR-12 & Laravel Pint)

1. **Tipagem Estrita:** Uso de declarações de tipos rigorosas nos parâmetros e retornos de todos os métodos em serviços, modelos e componentes:
   ```php
   public function calculateForUser(User $user): RiskScore
   ```
2. **Formatação Automatizada:** Padronização com `laravel/pint` configurado para as regras do ecossistema Laravel / PSR-12.
3. **Imutabilidade e Enums:** Preferência por `enum` fortemente tipado (`App\Enums\UserRole`) em substituição a strings soltas no banco para papéis de usuário.

---

## 🏢 Padrões de Multi-Tenancy

1. **Uso Obrigatório de `BelongsToTenant`:**
   Qualquer modelo pertencente ao escopo de uma empresa deve importar:
   ```php
   use App\Traits\BelongsToTenant;
   
   class Course extends Model
   {
       use BelongsToTenant;
   }
   ```
2. **Preenchimento Automático:** O campo `company_id` é injetado no evento `creating` caso o usuário autenticado não seja um Super Admin (`admin_alvora`).
3. **Prevenção de Vazamento de Dados:**
   - Consultas em rotas públicas (como a vitrine na Home) devem resolver explicitamente a empresa autorizada (`demo_tenant_cnpj`) e selecionar apenas campos públicos sem expor dados internos de outros tenants.

---

## 👥 Convenções de Rotas e Autorização

1. **Nomenclatura de Rotas:** Padrão com notação de ponto (`student.dashboard`, `company.situation-room`, `instructor.courses.index`).
2. **Middlewares em Camadas:**
   - Rotas de área logada devem sempre aplicar `middleware(['auth', 'subscription.active'])`.
   - Rotas específicas de perfil devem complementar com `middleware('role:instructor,company_admin')` ou equivalente.
3. **Bypass de Super Admin:** O método `$user->isAlvoraAdmin()` concede isenção de filtros multi-tenant e restrições de papel em operações de suporte e auditoria.

---

## 🤖 Padrões de Integração com IA

1. **Nunca Bloquear o Ciclo HTTP para Tarefas Analíticas:**
   - Tarefas pesadas de IA (estruturação de cursos, insights executivos, mensagens de retenção) devem ser executadas em background jobs via Horizon ou acionadas sob demanda com feedback visual de carregamento (`wire:loading`).
2. **Resiliência e Fallback Obrigatórios:**
   - Nenhuma chamada ao OpenRouter deve utilizar um modelo isolado fixo. Toda chamada deve utilizar a cadeia de fallback do [`OpenRouterResilientService`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Services/OpenRouterResilientService.php).
3. **Tratamento de Chaves Ausentes (Graceful Degradation):**
   - Serviços de IA devem verificar a presença das chaves de API (`GROQ_API_KEY`, `OPENROUTER_API_KEY`) e fornecer payloads de simulação coerentes caso o ambiente local não possua credenciais ativas, permitindo que a aplicação seja testada sem erros fatais.

---

## ⚡ Convenções de Eventos e WebSockets

1. **Isolamento de Canais:**
   - Notificações de empresa devem ser publicadas no canal privado `company.{companyId}`.
   - Mensagens individuais de aluno devem ser publicadas no canal privado `user.{userId}`.
   - Nunca utilizar canais públicos (`Channel`) para dados de negócio, scores de evasão ou dados de colaboradores.
2. **Auditoria em Banco:**
   - Toda emissão de score ou intervenção de IA deve ser gravada no MySQL com seu detalhamento técnico (`breakdown` em JSON) antes do disparo do evento WebSocket.

---

## 💳 Convenções de Pagamento e Webhooks

1. **Validação Criptográfica Incondicional:**
   - O webhook `POST /api/webhooks/mercadopago` deve obrigatoriamente validar o cabeçalho `x-signature` com HMAC-SHA256 antes de ler o corpo da mensagem.
2. **Idempotência:**
   - Eventos de pagamento do Mercado Pago devem checar se a fatura correspondente já foi quitada para evitar processamento duplicado.
