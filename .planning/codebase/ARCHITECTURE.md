# Arquitetura do Sistema — ALVORA

**Mapeamento da Base de Código: Outubro de 2026**

---

## 🏛️ Filosofia: Monolito Modular de Alta Concorrência

O **ALVORA** é arquitetado como um **Monolito Modular** em Laravel 13 / PHP 8.3.

A aplicação rejeita a complexidade operacional precoce de microsserviços em favor de um sistema coeso que oferece:
1. **Transações Atômicas:** Garantia ACID nativa no MySQL para operações financeiras, matrículas e concessão de certificados.
2. **Separação por Contextos Delimitados:** Módulos lógicos (`Admin`, `Company`, `Instructor`, `Student`, `AI`, `Billing`).
3. **Desenvolvimento e Testes Ágeis:** Execução local simples com SQLite e testes em MySQL para validação de integridade referencial.

```mermaid
graph TD
    Client["Navegador / Dispositivo (Tailwind 4 + Livewire 4)"]
    
    subgraph "Camada de Rede & Aplicação"
        Web["Servidor Web HTTP (Porta 8000)"]
    end
    
    subgraph "Camada de Aplicação Laravel 13"
        AuthMiddleware["Middlewares (auth, role, subscription.active)"]
        LivewireComponents["Livewire 4 (UI Reativa no Servidor)"]
        Controllers["Controladores Web & API"]
        DomainServices["Domain Services (EvasionRisk, MercadoPago, AI, Certificate)"]
        ORM["Eloquent ORM (BelongsToTenant Scope)"]
    end
    
    subgraph "Camada Assíncrona & Armazenamento"
        Redis["Redis 7 (Filas, Cache, Sessões, PubSub)"]
        Horizon["Laravel Horizon Workers"]
        MySQL[("MySQL 8.0 Multi-Tenant Database")]
    end
    
    Client <-->|HTTP / HTTPS| Proxy
    Client <-->|WSS WebSockets| ReverbServer
    Proxy --> AuthMiddleware --> LivewireComponents & Controllers
    LivewireComponents & Controllers --> DomainServices --> ORM
    DomainServices -->|Dispatch Job| Redis
    Redis --> Horizon --> DomainServices
    ORM <--> MySQL
    Horizon -->|Broadcast Event| Redis --> ReverbServer
```

---

## 🏢 1. Arquitetura Multi-Tenant: Single Database com Segregação Lógica

O sistema atende múltiplos clientes corporativos compartilhando um único banco de dados relacional (MySQL 8.0), garantindo isolamento através de regras de software nativas do ORM Eloquent.

### A. Trait [`BelongsToTenant`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Traits/BelongsToTenant.php)
Todos os models vinculados a uma empresa implementam este trait:
1. **Injeção de Escopo Global:** Aplica automaticamente o [`TenantScope`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Models/Scopes/TenantScope.php) em todas as queries (`SELECT`), adicionando `WHERE company_id = {user.company_id}`.
2. **Gravação Segura:** No evento `creating`, preenche automaticamente o campo `company_id` com o id da empresa do usuário logado, impedindo a injeção acidental ou maliciosa de IDs de outros tenants.

### B. Bypass de Super Admin
Usuários com o papel `admin_alvora` têm isenção no `TenantScope`, permitindo visualização consolidada entre empresas para auditoria, suporte e métricas globais de MRR.

---

## 👥 2. Modelo de Autorização e Matriz de Papéis (RBAC)

O controle de acesso é regulado pelo enum [`App\Enums\UserRole`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Enums/UserRole.php) e pelo middleware [`EnsureUserHasRole`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Http/Middleware/EnsureUserHasRole.php):

| Papel | Escopo | Capacidades Principais |
| :--- | :--- | :--- |
| `admin_alvora` | **Global (Super Admin)** | Faturamento global, monitoramento do Horizon, acesso unificado a todos os tenants. |
| `company_admin` | **Tenant (RH / Diretoria)** | Sala de Situação em tempo real, gestão de colaboradores, atribuição de trilhas, faturamento da empresa. |
| `instructor` | **Tenant (Pedagógico)** | Gestão de cursos, módulos, aulas, upload de materiais, quizzes e gerador de ementas por IA. |
| `student` | **Tenant (Colaborador)** | Dashboard de estudos, player de aulas, interação com Tutor IA, quizzes e emissão de certificados. |

---

## ⏳ 3. Ciclo de Vida da Assinatura e Middleware Gatekeeper

O middleware [`CheckSubscriptionStatus`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Http/Middleware/CheckSubscriptionStatus.php) atua como barreira de proteção do faturamento B2B:

```mermaid
stateDiagram-v2
    [*] --> Active: Pagamento Confirmado
    Active --> PastDue: Falha na Cobrança Recorrente
    PastDue --> Active: Regularização em até 5 dias
    PastDue --> Suspended: 5 dias esgotados (Inadimplência)
    Suspended --> Active: Fatura Quitada
    Active --> Cancelled: Cancelamento
    Suspended --> Cancelled: Cancelamento
```

* **Período de Carência (5 dias):** Empresas com pagamento pendente entram em `past_due`. Os colaboradores continuam assistindo aulas para não interromper programas de treinamento corporativo.
* **Suspensão:**
  - Gestores de RH/Instrutores são redirecionados compulsoriamente para `/instrutor/faturamento`.
  - Alunos têm o acesso a aulas e chamadas de IA bloqueados com mensagem explicativa para contatar o RH.

---

## 🧠 4. Motor Anti-Evasão com IA Preditiva

Diferente de sistemas que registram o abandono tardiamente, o ALVORA calcula o risco continuamente antes da desistência do aluno.

### Algoritmo Matemático de Risco:
$$\text{Score Total} = (0.35 \times I) + (0.25 \times E) + (0.20 \times Q) + (0.20 \times D)$$

1. **Inatividade ($I$ - 35%):** Dias corridos sem autenticação (0 a 14+ dias escalonados até 100 pontos).
2. **Estagnação ($E$ - 25%):** Dias sem registro de conclusão de aulas em `lesson_progress`.
3. **Desempenho em Quizzes ($Q$ - 20%):** Dificuldade calculada como $\max(0, 100 - \text{Média das Notas})$.
4. **Desvio de Pares ($D$ - 20%):** Atraso de ritmo relativo à média do departamento na mesma empresa.

### Fluxo Reativo em Cascata:
1. Conclusão de aula ou quiz dispara `CalculateEvasionRiskJob::dispatch($userId)`.
2. O worker do Horizon calcula e persiste o score na tabela `risk_scores` com o payload JSON auditável de breakdown.
3. Se o score atingir **Zona Crítica (Score ≥ 70)**:
   - Dispara `EvasionRiskAlertEvent` via broadcast WebSocket para a **Sala de Situação** do RH.
   - O serviço `EvasionInterventionService` é acionado para gerar uma mensagem personalizada via OpenRouter e sugerir a próxima lição ao colaborador.

---

## 📜 5. Módulo de Certificação e Auditoria

### Diretrizes Técnicas Aprovadas:
 1. **Estabilização Prévia à Evolução:** O fluxo existente de emissão e validação deve ser corrigido e blindado com testes de regressão antes da introdução do editor visual.
 2. **Preservação de Contratos Públicos e Rotas:**
    - Manter as rotas `student.certificate` (`/aluno/cursos/{course:slug}/certificado`) e `certificates.verify` (`/validar-certificado/{code}`).
    - Preservar o UUID e o formato legível único `ALV-YYYY-XXXXXX`.
    - Manter os requisitos de elegibilidade (progresso 100% e aprovação em todos os quizzes).
 3. **Ponto Único de Entrada para Renderização:**
    - `CertificateService::generatePdf(Certificate $certificate)` é o ponto centralizador.
    - Se `certificate->template_version_id` for nulo: utiliza o renderizador legado (`certificates.template`).
    - Se `certificate->template_version_id` estiver associado a uma versão: utiliza o novo renderizador alimentado pelo schema JSON daquela versão.
 4. **Geração Síncrona e Sob Demanda:**
    - Os arquivos PDF são gerados no momento da solicitação (tempo medido ~81ms via Dompdf); não há requisito de armazenamento permanente de binários.
    - O carregamento remoto de recursos no Dompdf é mantido desativado (`enable_remote = false`) para prevenir vulnerabilidades de SSRF. Assets locais são servidos via storage privado/data URIs.
 5. **Snapshots Históricos e Integridade:**
    - Os dados do certificado são preservados em snapshot no momento da emissão, evitando que futuras alterações em cursos, usuários ou empresas alterem retroativamente um certificado já emitido.
    - Certificados antigos cujos snapshots forem preenchidos a partir de dados atuais devem ser identificados explicitamente como reconstruídos, sem alegar precisão histórica não comprovada.
    - Exclusões de cursos ou usuários não podem apagar silenciosamente certificados históricos.
 6. **Editor Visual com Schema JSON como Fonte da Verdade:**
    - O layout visual é representado por um schema JSON versionado (coordenadas em mm, tipografia aprovada, elementos fixos, variáveis permitidas, imagens e QR Code).
    - O servidor valida o schema JSON antes de qualquer publicação.
    - A prévia interativa no editor utiliza o mesmo renderizador de backend responsável pelo PDF final, garantindo fidelidade absoluta.
 7. **Decisões de Negócio Não Presumidas:**
    - Decisões pendentes de produto (tais como política de reemissão, retenção/anonimização em exclusão, escopo do template por empresa vs global, e mascaramento de dados pessoais na validação pública) não podem ser inventadas pelo agente de código e requerem validação prévia.
