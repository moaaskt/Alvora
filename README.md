<div align="center">

# Alvora

**LMS B2B com IA anti-evasão: prevê quem vai abandonar o treinamento e intervém antes que aconteça.**

![Tests](https://img.shields.io/badge/tests-84%20passed%20%C2%B7%20329%20assertions-brightgreen?logo=phpunit&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire-3-FB70A9?logo=livewire&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-blue)

</div>

---

## O problema

A evasão em EAD no Brasil chega a **41,6%**. Plataformas tradicionais só mostram o abandono *depois* que ele acontece. A Alvora calcula um **Score de Risco de Evasão (0–100)** por colaborador e dispara intervenções automáticas via IA **antes** da desistência.

> 📸 Screenshot: Sala de Situação do RH com ranking de colaboradores por risco (`docs/screenshots/situation-room.png`)

## Por que a Alvora é diferente

- **Predição, não relatório:** o score é recalculado continuamente (Job em fila) e o alerta chega em tempo real via WebSocket (Reverb) quando o colaborador cruza o limiar de risco alto (≥ 70).
- **Intervenção ativa:** score alto aciona o `EvasionInterventionService`, que gera uma mensagem personalizada por IA, sem depender de ação manual do RH.
- **IA resiliente:** chat em baixa latência via Groq; análises e geração de currículo via OpenRouter com **fallback em cascata** (DeepSeek R1 → Llama 3.3 → Qwen 3). Um provedor fora do ar não derruba o produto.

## Como o score é calculado

| Fator | Peso | Sinal |
|---|---|---|
| Inatividade | 35% | Dias sem acesso (0 → 14+ dias) |
| Estagnação | 25% | Dias sem concluir aula |
| Desempenho em quiz | 20% | `100 − nota média` |
| Desvio de pares | 20% | Distância abaixo da média de conclusões da empresa |

Faixas: **baixo** 0–39 · **médio** 40–69 · **alto** 70–100. Cada cálculo persiste o `breakdown` por fator em `risk_scores`, tornando o score auditável.
Implementação: [`EvasionRiskCalculatorService`](app/Services/EvasionRiskCalculatorService.php).

## Stack

![Laravel](https://img.shields.io/badge/Laravel_12-FF2D20?logo=laravel&logoColor=white)
![Livewire](https://img.shields.io/badge/Livewire_3-FB70A9?logo=livewire&logoColor=white)
![Tailwind](https://img.shields.io/badge/Tailwind_CSS-06B6D4?logo=tailwindcss&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-646CFF?logo=vite&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL_8-4479A1?logo=mysql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis_7-DC382D?logo=redis&logoColor=white)
![Horizon](https://img.shields.io/badge/Laravel_Horizon-FF2D20?logo=laravel&logoColor=white)
![Reverb](https://img.shields.io/badge/Laravel_Reverb-FF2D20?logo=laravel&logoColor=white)
![Octane](https://img.shields.io/badge/Octane_+_FrankenPHP-FF2D20?logo=laravel&logoColor=white)
![Docker](https://img.shields.io/badge/Docker_multi--stage-2496ED?logo=docker&logoColor=white)
![Groq](https://img.shields.io/badge/Groq_API-F55036)
![OpenRouter](https://img.shields.io/badge/OpenRouter-6467F2)
![Mercado Pago](https://img.shields.io/badge/Mercado_Pago-00B1EA?logo=mercadopago&logoColor=white)
![PHPUnit](https://img.shields.io/badge/PHPUnit-3776AB?logo=php&logoColor=white)

DomPDF para certificados.

## Funcionalidades

**Admin (plataforma)**
- Gestão de faturamento e assinaturas das empresas clientes
- Checkout e webhooks via Mercado Pago
- Health check da infraestrutura (`/api/health`)

**RH (empresa)**
- **Sala de Situação:** painel em tempo real com score de risco e alertas por colaborador
- Convite e gestão de colaboradores (aceite por token)
- Trilhas de aprendizado por equipe
- **Insights executivos por IA** sobre engajamento da empresa
- Faturamento e plano contratado

**Instrutor**
- Editor de cursos, módulos e aulas (curriculum builder)
- **Gerador de currículo por IA** a partir de um tema
- Editor de quizzes com tentativas e nota

**Aluno**
- Dashboard com progresso e cursos matriculados
- Player de aulas com registro de conclusão
- Quizzes com correção automática
- Certificado em PDF (DomPDF)
- **Tutor IA** (drawer de chat em tempo real via Groq)

**IA**
- Score de risco de evasão com breakdown auditável
- Intervenção automática em risco alto (broadcast + mensagem gerada)
- Fallback em cascata entre modelos ([`OpenRouterResilientService`](app/Services/OpenRouterResilientService.php))
- Chat de baixa latência ([`GroqAiService`](app/Services/GroqAiService.php))

> 📸 Screenshot: Dashboard do aluno e Tutor IA (`docs/screenshots/student-dashboard.png`)
> 📸 Screenshot: Gerador de currículo por IA no painel do instrutor (`docs/screenshots/curriculum-ai.png`)

## Arquitetura

```
 Browser (Blade + Livewire 3 + Tailwind)
        │  HTTP / WebSocket
        ▼
 ┌──────────────────────────────┐        ┌──────────────────┐
 │ app: Octane + FrankenPHP     │◄──────►│ MySQL 8          │
 │  Livewire · Services · Models│        └──────────────────┘
 └──────┬───────────────┬───────┘        ┌──────────────────┐
        │ dispatch      │ broadcast      │ Redis 7          │
        ▼               ▼                │ cache·fila·sessão│
 ┌─────────────┐  ┌───────────┐          └──────────────────┘
 │ Horizon     │  │ Reverb    │
 │ (workers)   │  │ (WS)      │
 └──────┬──────┘  └───────────┘
        │ CalculateEvasionRiskJob
        ▼
 EvasionRiskCalculatorService ──► score ≥ 70
        │                              │
        │                   EvasionRiskAlertEvent (WS)
        │                   EvasionInterventionService
        ▼                              ▼
   risk_scores              OpenRouter (cascata) / Groq
```

```
app/
├── Livewire/{Admin,Auth,Company,Instructor,Student}   # UI por papel
├── Services/    # Risco, Intervenção, Groq, OpenRouter, MercadoPago, Certificado
├── Jobs/        # CalculateEvasionRiskJob
├── Events/      # EvasionRiskAlertEvent, NewInterventionDispatchedEvent
└── Models/ · Enums/ · Traits/
```

Papéis controlados por middleware `role:` (aluno, instrutor, `company_admin`, admin).

## Rodando localmente

Pré-requisitos: Docker e Docker Compose.

```bash
git clone https://github.com/moaaskt/alvora.git && cd alvora
cp .env.example .env
# gere uma APP_KEY (ex.: base64:...) e preencha no .env
docker compose up -d --build
```

O container `app` roda as migrations no boot (`RUN_MIGRATIONS=true`).
Serviços: `app` (:8000), `mysql`, `redis`, `horizon`, `reverb`.

- App: http://localhost:8000
- Health: http://localhost:8000/api/health

Testes:

```bash
docker compose exec app php artisan test
# Tests: 84 passed (329 assertions)
```

## Variáveis de ambiente

Além das padrão do Laravel (`.env.example`), adicione:

```dotenv
APP_KEY=                       # obrigatória (php artisan key:generate --show)
APP_URL=http://localhost:8000

# Banco / Redis (defaults do docker-compose)
DB_DATABASE=alvora
DB_USERNAME=alvora
DB_PASSWORD=secret
DB_ROOT_PASSWORD=root_secret

# Reverb (WebSocket)
REVERB_APP_ID=alvora-app
REVERB_APP_KEY=alvora-key
REVERB_APP_SECRET=alvora-secret

# IA
GROQ_API_KEY=
GROQ_MODEL=llama-3.3-70b-versatile
OPENROUTER_API_KEY=

# Pagamentos
MERCADO_PAGO_ACCESS_TOKEN=
MERCADO_PAGO_PUBLIC_KEY=
MERCADO_PAGO_WEBHOOK_SECRET=
```

Sem as chaves de IA/pagamento a aplicação sobe normalmente; os recursos dependentes degradam ou usam valores de teste.

## Licença

[MIT](LICENSE) © Alvora
