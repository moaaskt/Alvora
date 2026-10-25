# Stack Tecnológica — ALVORA

**Mapeamento da Base de Código: Outubro de 2026**

---

## 🏛️ Visão Geral da Arquitetura de Software

O **ALVORA** é construído sob o padrão de **Monolito Modular** utilizando o ecossistema Laravel 12. Toda a lógica de negócio, camadas reativas de interface, barramento de eventos, integração com IA e gestão multi-tenant estão unificadas em uma base de código coesa e fortemente tipada.

---

## 🛠️ Tecnologias Principais

### Backend & Runtime
* **PHP:** `^8.3` (Tipagem estrita, JIT habilitado, suporte a match expressions e typed class constants).
* **Framework Web:** `Laravel 12.x` (`laravel/framework: ^13.17` skeleton / 12 core).
* **Camada Reativa Frontend:** `Livewire 4.4 / 3.x` para componentes reativos orientados a estado no servidor sem necessidade de SPA desacoplado.
* **Componentes de UI / Ícones:** `blade-ui-kit/blade-heroicons: ^2.7`.
* **Geração de Documentos:** `barryvdh/laravel-dompdf: ^3.1` (PDFs de certificados com layout paisagem A4).
* **Autenticação de API / Tokens:** `laravel/sanctum: ^4.3`.
* **Runtime de Alta Performance:** `Laravel Octane` executado sobre `FrankenPHP` em contêineres Docker, mantendo o framework em memória entre requisições.

### Frontend & Build Tooling
* **CSS Framework:** `Tailwind CSS 4.0` (`@tailwindcss/vite: ^4.0.0`).
* **Bundler & Dev Server:** `Vite 8.0` (`laravel-vite-plugin: ^3.1`).
* **Gerenciador de Processos de Desenvolvimento:** `concurrently: ^10.0.3`.
* **Design & Tipografia:** Inter / Sans-serif moderna, micro-animações, design system corporativo responsivo.

### Armazenamento de Dados & Mensageria
* **Banco de Dados Principal:** `MySQL 8.0` (Transacional ACID, isolamento multi-tenant lógico via coluna `company_id` e índices compostos).
* **Cache, Sessões e Filas:** `Redis 7-alpine` (Armazenamento in-memory com persistência RDB/AOF para sessions, driver de cache e backend de jobs assíncronos).
* **Supervisor de Filas:** `Laravel Horizon 5.x` com múltiplos supervisores para paralelismo de jobs e interface web de telemetria.
* **WebSockets em Tempo Real:** `Laravel Reverb 1.x` rodando na porta `8080` (compatível com protocolo Pusher, sem dependência de serviços SaaS de terceiros).

### Containerização & Orquestração
* **Docker Multi-Stage:** Base única com `php:8.3-cli-alpine` / `FrankenPHP` e Node.js para compilação estática de assets.
* **Docker Compose:** Orquestração de 5 microsserviços interdependentes:
  1. `alvora_mysql` (:3306)
  2. `alvora_redis` (:6379)
  3. `alvora_app` (:8000)
  4. `alvora_horizon` (Background worker)
  5. `alvora_reverb` (:8080 WebSockets)

---

## 🤖 Ecossistema de Inteligência Artificial (Dual-Engine)

| Motor | Provedor / API | Modelos | Aplicação no Sistema | Latência Típica |
| :--- | :--- | :--- | :--- | :--- |
| **Edge / Baixa Latência** | **Groq Cloud API** | `llama-3.3-70b-versatile` | Tutor de IA no player do aluno (`AiTutorDrawer`) | 800ms – 1.4s |
| **Resiliente em Cascata** | **OpenRouter API** | Tier 1: `deepseek/deepseek-r1:free`<br>Tier 2: `meta-llama/llama-3.3-70b-instruct:free`<br>Tier 3: `qwen/qwen3-235b-a22b:free` | Gerador de Ementas Curriculares, Mensagens de Intervenção Anti-Evasão, Diagnósticos de RH | 4s – 12s (Async) |

---

## 💳 Meio de Pagamento & Faturamento

* **Gateway:** `Mercado Pago API v1`.
* **Modalidades:**
  - Assinaturas recorrentes via débito em cartão (`/preapproval`).
  - Checkout Pro para compras avulsas e quitação de pendências (`/checkout/preferences`).
* **Segurança de Webhooks:** Validação criptográfica HMAC-SHA256 do cabeçalho `x-signature` com segredo de webhook em `POST /api/webhooks/mercadopago`.

---

## 🧪 Qualidade, Ferramentas de Teste e Linting

* **Framework de Testes:** `PHPUnit 12.5.12` / `Pest`.
* **Cobertura Atual:** 84 testes automatizados aprovados com 329 asserções cobrindo autenticação, multi-tenancy, IA, tempo real e faturamento.
* **Formatador de Código:** `Laravel Pint 1.27` (PSR-12).
* **Depuração & Observabilidade:**
  - `laravel/pail: ^1.2.5` (Streaming de logs em tempo real via CLI).
  - `Laravel Telescope` (Telemetria local de queries e jobs).
  - `Sentry SDK` (Monitoramento de erros e exceções em produção).
