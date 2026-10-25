# Contexto da Fase 07: Infraestrutura, Tempo Real e Observabilidade

## Visão Geral e Decisões de Arquitetura

Nesta fase consolidamos a fundação de infraestrutura, orquestração e monitoramento em tempo real do **ALVORA**, preparando a plataforma para alta escala, processamento assíncrono com Redis/Horizon, WebSockets com Laravel Reverb, observabilidade e containerização para produção.

---

## 1. Filas Assíncronas, Redis e Laravel Horizon (INFR-01)

- **Arquitetura Dual-Environment:**
  - **Produção / Docker Linux Container:** Orquestração completa com Redis Alpine + Laravel Horizon como daemon de supervisão de filas (`pcntl` + `posix`), processando jobs de cálculo de risco (`CalculateEvasionRiskJob`), envios de certificados PDF, webhooks de faturamento e chamadas pesadas de IA.
  - **Ambiente Local Windows (Desenvolvimento/Testes):** Driver de fila flexível (`sync` ou `database`), com suporte a `predis/predis` para conexões Redis quando disponíveis, sem quebrar ou bloquear o desenvolvimento em máquinas Windows locais.
- **Configuração do Horizon:**
  - `config/horizon.php` estruturado com balanceamento automático (`auto`), timeouts configurados para processamento de IA e autorização do dashboard `/horizon` restrita exclusivamente ao `admin_alvora` via `Horizon::auth()`.

---

## 2. WebSockets e Tempo Real com Laravel Reverb (INFR-02)

- **Engine de Broadcasting:**
  - **Laravel Reverb (`laravel/reverb`)**: Servidor WebSocket nativo de alta performance para Laravel.
- **Eventos em Tempo Real:**
  - **`EvasionRiskAlertEvent`**: Transmitido no canal privado da empresa `company.{company_id}` quando um colaborador entra em Alto Risco (`score >= 70`). A **Sala de Situação** do RH (`SituationRoom`) escuta o evento via Livewire/Echo e atualiza o heatmap cromático e os KPIs instantaneamente sem necessidade de refresh manual.
  - **`NewInterventionDispatchedEvent`**: Transmitido no canal privado do usuário `user.{user_id}` quando uma nova intervenção ativa ou incentivo de IA for gerado. O painel do aluno exibe um Toast visual imediato convidando para a aula recomendada.
- **Canais Privados e Autorização:**
  - Configurados em `routes/channels.php` garantindo isolamento estrito multi-tenant (gestor só escuta canal de sua própria empresa e aluno só escuta seu próprio canal).

---

## 3. Health Check Operacional e Diagnóstico de Serviços (INFR-03)

- **Endpoint `/api/health`:**
  - Retorno em JSON padronizado com HTTP status 200 (se tudo estiver operacional) ou 503 (se houver degradação crítica).
  - **Checagens Detalhadas:**
    1. **Database:** Testa query de conectividade com medição de latência em milissegundos (`db_response_time_ms`).
    2. **Redis / Cache:** Testa operações `set`/`get` com medição de tempo de resposta.
    3. **Storage:** Valida capacidade de leitura/escrita no disco padrão configurado (`local` ou `s3`).
    4. **Memória & Sistema:** Reporta uso de memória PHP (`memory_usage_mb`) e uptime.

---

## 4. Conteinerização Docker, Octane e Observabilidade (INFR-04)

- **Docker & Octane:**
  - `Dockerfile` multi-stage otimizado com PHP 8.3, extensões compiladas (`pcntl`, `posix`, `pdo_mysql`, `redis`), Composer e Node.js.
  - Configuração para execução com **Laravel Octane** (Swoole / FrankenPHP) para máxima taxa de requisições por segundo.
  - `docker-compose.yml` contendo os serviços integrados:
    - `app`: Aplicação Laravel Octane
    - `mysql`: MySQL 8.0 corporativo
    - `redis`: Redis 7 Alpine para cache, filas e Reverb
    - `horizon`: Worker supervisor de filas Redis
    - `reverb`: Servidor WebSocket na porta 8080
- **Observabilidade (Telescope & Sentry):**
  - **Laravel Telescope (`laravel/telescope`)**: Ativo estritamente em ambiente de desenvolvimento local (`APP_ENV=local`), monitorando queries, exceções e requisições no painel `/telescope`.
  - **Sentry**: Integração configurada via DSN (`SENTRY_LARAVEL_DSN`) para captura automática de exceções e telemetria de performance em ambientes de staging/produção, inativo em desenvolvimento local para evitar poluição.
