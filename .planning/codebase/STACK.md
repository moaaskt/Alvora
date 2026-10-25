# Stack Tecnológica — ALVORA

**Mapeamento da Base de Código: Outubro de 2026**

---

## 🏛️ Visão Geral da Arquitetura de Software

O **ALVORA** é construído sob o padrão de **Monolito Modular** utilizando o ecossistema Laravel 13. Toda a lógica de negócio, camadas reativas de interface, barramento de eventos, integração com IA e gestão multi-tenant estão unificadas em uma base de código coesa e fortemente tipada.

---

## 🛠️ Tecnologias Principais

### Backend & Runtime
* **PHP:** `^8.3` (Tipagem estrita, suporte a match expressions e typed class constants).
* **Framework Web:** `Laravel 13` (`laravel/framework: v13.37.0` / `^13.17`).
* **Camada Reativa Frontend:** `Livewire 4` (`livewire/livewire: v4.4.7`) para componentes reativos orientados a estado no servidor sem necessidade de SPA desacoplado.
* **Componentes de UI / Ícones:** `blade-ui-kit/blade-heroicons: ^2.7`.
* **Geração de Documentos:** `barryvdh/laravel-dompdf: v3.1.2` (`dompdf/dompdf: v3.1.6`) gerando PDFs de certificados em A4 paisagem sob demanda de forma síncrona, com carregamento remoto desativado (`enable_remote: false`).
* **Autenticação de API / Tokens:** `laravel/sanctum: ^4.3`.
* **Runtime e Execução:** PHP 8.3 padrão. Octane/FrankenPHP consta na configuração do Dockerfile de produção, mas não é dependência instalada no Composer nem requisito de desenvolvimento local.

### Frontend & Build Tooling
* **CSS Framework:** `Tailwind CSS 4` (`@tailwindcss/vite: 4.3.3`, `tailwindcss: 4.3.3` via `@import 'tailwindcss'`).
* **Bundler & Dev Server:** `Vite 8` (`vite: 8.3.4`, `laravel-vite-plugin: ^3.1`).
* **Gerenciador de Processos de Desenvolvimento:** `concurrently: ^10.0.3`.
* **Design & Tipografia:** Inter / Plus Jakarta Sans / Instrument Sans, micro-animações, design system corporativo responsivo.

### Armazenamento de Dados & Mensageria
* **Banco de Dados em Produção:** `MySQL 8.0` (Transacional ACID, isolamento multi-tenant lógico via coluna `company_id` e índices compostos).
* **Banco de Dados em Testes Locais:** `SQLite` (`database/testing.sqlite`).
* **Validação de Concorrência e Restrições:** Testes de concorrência, integridade referencial, restrições e comportamento específico de banco devem executar também em MySQL (via container Docker local ou CI); não presumir que os testes SQLite comprovam o comportamento concorrente em MySQL.
* **Cache, Sessões e Filas:** Driver `database` / `array` localmente. Redis 7 alpine configurado no Docker Compose para ambientes produtivos.
* **Serviços de Tempo Real e Filas:** Laravel Horizon e Laravel Reverb constam como alvos no Docker Compose, mas não são requisitos obrigatórios instalados no runtime atual.

### Containerização & Orquestração
* **Docker Multi-Stage:** Base com `php:8.3-cli-alpine` / `FrankenPHP` e Node.js para compilação estática de assets.
* **Docker Compose:** Configuração de 5 serviços previstos (`alvora_mysql`, `alvora_redis`, `alvora_app`, `alvora_horizon`, `alvora_reverb`).

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
