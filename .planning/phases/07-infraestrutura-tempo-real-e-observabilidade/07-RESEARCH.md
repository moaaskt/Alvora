# Pesquisa da Fase 07: Infraestrutura, Tempo Real e Observabilidade

## Contexto Técnico e Dependências

A Fase 07 entrega a arquitetura de produção do monólito Laravel 12:
1. **INFR-01: Laravel Horizon & Redis**
   - Horizon gerencia filas Redis com supervisores dedicados, balanceamento automático e métricas de execução.
   - O Horizon requer extensões Unix `pcntl` e `posix`. Em ambiente de desenvolvimento Windows sem WSL, ele deve ser orquestrado via container Docker Linux, enquanto o codebase local utiliza conexão compatível via `predis/predis` e filas flexíveis (`database`/`sync`/`redis`).
2. **INFR-02: Laravel Reverb (WebSockets)**
   - Reverb opera como servidor WebSocket de alta concorrência.
   - Eventos de broadcasting `EvasionRiskAlertEvent` e `NewInterventionDispatchedEvent` transmitem via `ShouldBroadcast` nos canais privados autenticados.
3. **INFR-03: Health Check (`/api/health`)**
   - Serviço `HealthCheckService` executando checagens de integridade e medição de latência:
     - Conectividade com DB (query simples e medição de tempo de resposta).
     - Cache / Redis (teste de escrita e leitura de chave temporária).
     - Filesystem / Storage (verificação de escrita e deleção de arquivo temporário).
     - Consumo de memória PHP e status operacional global.
4. **INFR-04: Dockerfile, Octane & Observabilidade**
   - `Dockerfile` multi-stage com PHP 8.3 e extensões completas para produção.
   - `docker-compose.yml` integrando `app` (Octane), `mysql:8.0`, `redis:7-alpine`, `horizon` e `reverb`.
   - Instrumentação configurada para `laravel/telescope` (em ambiente `local`) e `sentry/sentry-laravel` (produção).
