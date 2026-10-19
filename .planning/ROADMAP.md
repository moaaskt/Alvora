# Roadmap: ALVORA

## Overview

A Alvora é construída em 7 fases verticais (Vertical MVP), partindo do alicerce multi-tenant, gestão de conteúdo e player reativo do aluno, expandindo para avaliações, certificados e cobrança B2B, culminando no diferencial central de IA preditiva contra evasão e finalizando com observabilidade de alta performance e mensageria em tempo real.

## Phases

- [ ] **Phase 1: Setup do Projeto, Auth & Multi-Tenancy** - Estrutura base Laravel 12, autenticação Sanctum, isolamento de organizações (`companies`), controle de 4 papéis e landing page B2B.
- [ ] **Phase 2: Gestão de Cursos, Módulos e Aulas** - Painel do instrutor com CRUD estruturado de cursos, módulos, aulas e ordenação de conteúdo.
- [ ] **Phase 3: Player de Aula e Experiência do Colaborador** - Dashboard do aluno, player interativo reativo em Livewire 3 com progresso persistido e bloco de anotações.
- [ ] **Phase 4: Quizzes, Certificados e Trilhas de Aprendizado** - Avaliações de fim de módulo com cálculo de nota, emissão de certificados PDF via DomPDF e trilhas corporativas obrigatórias.
- [ ] **Phase 5: Faturamento e Assinaturas B2B com Mercado Pago** - Gestão de planos empresariais, processamento de pagamentos e tratamento de webhooks do Mercado Pago.
- [ ] **Phase 6: Alvora AI — Score de Evasão, Intervenção, Chat e Criação** - Motor ponderado de risco, intervenções ativas para alunos em risco, sala de situação, assistente via Groq e geração de cursos com fallback OpenRouter.
- [ ] **Phase 7: Infraestrutura, Tempo Real e Observabilidade** - Monitoramento de filas com Horizon, WebSockets nativos com Reverb, health checks, Telescope/Sentry e ambiente Docker com Octane.

## Phase Details

### Phase 1: Setup do Projeto, Auth & Multi-Tenancy

**Goal**: Estabelecer a fundação do monolito Laravel 12 com multi-tenant lógico por empresa, autenticação Sanctum, perfis de usuários e páginas públicas institucionais.
**Mode**: mvp
**Depends on**: Nothing (primeira fase)
**Requirements**: AUTH-01, AUTH-02, AUTH-03, AUTH-04, BILL-01
**Success Criteria**:

  1. Usuário consegue acessar a landing page institucional e página de planos B2B.
  2. Usuário realiza autenticação e mantém sessão ativa de acordo com seu perfil (`admin_alvora`, `company_admin`, `instructor`, `student`).
  3. Dados de uma empresa cliente ficam totalmente isolados de outras empresas.
  4. O gestor da empresa (`company_admin`) consegue visualizar seus colaboradores e enviar convites de acesso.

**Plans**: 3 plans
**UI hint**: yes

Plans:
**Wave 1**

- [ ] 01-01: Bootstrap do Laravel 12, layout base Blade, Livewire 3 e landing page B2B pública.
- [ ] 01-02: Migrations de `companies`, `company_users`, `users`, roles e configuração do Sanctum.

**Wave 2** *(blocked on Wave 1 completion)*

- [ ] 01-03: Telas de autenticação, proteção de rotas por tenant/role e gestão de convites de colaboradores.

### Phase 2: Gestão de Cursos, Módulos e Aulas

**Goal**: Permitir que instrutores construam e gerenciem o catálogo de conteúdo educacional com hierarquia completa.
**Mode**: mvp
**Depends on**: Phase 1
**Requirements**: CURS-01, CURS-02
**Success Criteria**:

  1. Instrutor cria, edita e publica cursos com títulos, slug amigável e capa.
  2. Instrutor cria módulos sequenciais dentro de um curso.
  3. Instrutor cadastra aulas com link de vídeo, duração estimada, materiais anexos e ordenação interativa.

**Plans**: 2 plans
**UI hint**: yes

Plans:

- [ ] 02-01: Modelagem e CRUD de cursos com status de publicação e validações.
- [ ] 02-02: Interface do instrutor para gestão de módulos, aulas com anexo e ordenação de conteúdo.

### Phase 3: Player de Aula e Experiência do Colaborador

**Goal**: Entregar uma interface interativa e reativa para o aluno consumir os cursos com persistência instantânea de progresso.
**Mode**: mvp
**Depends on**: Phase 2
**Requirements**: PLAY-01, PLAY-02, PLAY-03
**Success Criteria**:

  1. Aluno visualiza seu dashboard com lista de cursos matriculados e percentual de progresso.
  2. Player de vídeo exibe a aula selecionada, materiais para download e permite registrar anotações em tempo real.
  3. Progresso do aluno é gravado automaticamente ao concluir uma aula via componentes Livewire 3 sem recarregar a página.

**Plans**: 2 plans
**UI hint**: yes

Plans:

- [ ] 03-01: Dashboard do aluno com resumo de matrículas e cálculo de progresso geral.
- [ ] 03-02: Componente Livewire do Player de Aula com marcação de conclusão, anotações e navegação entre aulas.

### Phase 4: Quizzes, Certificados e Trilhas de Aprendizado

**Goal**: Fornecer mecanismos de avaliação do aprendizado, comprovação formal via certificado e organização de trilhas obrigatórias por empresa.
**Mode**: mvp
**Depends on**: Phase 3
**Requirements**: EVAL-01, EVAL-02, EVAL-03, EVAL-04
**Success Criteria**:

  1. Instrutor cria perguntas de quiz com opções de múltipla escolha vinculadas a aulas/módulos.
  2. Aluno responde aos quizzes, visualiza correção imediata e tem suas tentativas registradas.
  3. Ao completar 100% do curso e aprovação nos quizzes, o aluno baixa o certificado em PDF gerado pelo DomPDF.
  4. Gestor da empresa cria e vincula trilhas de aprendizado obrigatórias para colaboradores.

**Plans**: 3 plans
**UI hint**: yes

Plans:

- [ ] 04-01: Criação e execução de quizzes com feedback imediato e pontuação mínima.
- [ ] 04-02: Template e motor de geração de certificados em PDF com validação de conclusão.
- [ ] 04-03: Gestão de trilhas de aprendizado e associação a departamentos/cargos.

### Phase 5: Faturamento e Assinaturas B2B com Mercado Pago

**Goal**: Monetização da plataforma com planos de assinatura para empresas contratantes e conciliação automática por webhooks.
**Mode**: mvp
**Depends on**: Phase 1
**Requirements**: BILL-02, BILL-03
**Success Criteria**:

  1. Empresas conseguem assinar planos corporativos através de checkout do Mercado Pago.
  2. Webhook do Mercado Pago processa confirmação de pagamento e ativa o plano da empresa automaticamente.
  3. `admin_alvora` e `company_admin` visualizam relatórios de faturamento, histórico de faturas e status de vigência.

**Plans**: 2 plans
**UI hint**: yes

Plans:

- [ ] 05-01: Integração com SDK/API do Mercado Pago para checkout de assinaturas corporativas.
- [ ] 05-02: Handler seguro de webhooks, atualização de status de tenant e telas de faturamento.

### Phase 6: Alvora AI — Score de Evasão, Intervenção, Chat e Criação

**Goal**: Implementar o diferencial estratégico da plataforma: monitoramento de evasão, ações ativas preditivas, assistente via Groq e geração de cursos com OpenRouter.
**Mode**: mvp
**Depends on**: Phase 4
**Requirements**: EVAS-01, EVAS-02, EVAS-03, EVAS-04, CHAT-01, CHAT-02, CURS-03, INST-01, INST-02, INST-03
**Success Criteria**:

  1. Motor calcula periodicamente o score de risco (0-100) e classifica colaboradores (baixo/médio/alto risco).
  2. Alunos em alto risco recebem intervenções automatizadas (mensagens de incentivo personalizadas e reforço).
  3. Gestor acessa a Sala de Situação com heatmap de risco dos colaboradores em tempo real.
  4. Aluno interage com chat de suporte inteligente via Groq com contexto de seu progresso atual.
  5. Instrutor gera grade completa de cursos por comando em linguagem natural via OpenRouter com fallback automático entre modelos.

**Plans**: 4 plans
**UI hint**: yes

Plans:

- [ ] 06-01: Motor do Score de Risco de Evasão ponderado, jobs agendados e histórico em `risk_scores`.
- [ ] 06-02: Sistema de intervenção ativa proativa e Sala de Situação (heatmap para RH).
- [ ] 06-03: Componente de Chat Alvora AI com integração Groq API de baixa latência e histórico contextual.
- [ ] 06-04: Roteador resiliente com OpenRouter (fallback entre modelos) para geração de cursos e insights executivos.

### Phase 7: Infraestrutura, Tempo Real e Observabilidade

**Goal**: Garantir excelência operacional, alta performance, processamento em segundo plano e monitoramento robusto para produção.
**Mode**: mvp
**Depends on**: Phase 6
**Requirements**: INFR-01, INFR-02, INFR-03, INFR-04
**Success Criteria**:

  1. Filas assíncronas do Redis operam monitoradas com dashboard visual do Laravel Horizon.
  2. Notificações de alerta e risco são transmitidas instantaneamente via Laravel Reverb (WebSockets).
  3. Endpoint `/api/health` retorna a saúde integral dos serviços (DB, Redis, Storage).
  4. Projeto containerizado com Docker, configurado para Laravel Octane e instrumentado com Telescope/Sentry.

**Plans**: 2 plans
**UI hint**: no

Plans:

- [ ] 07-01: Configuração do Laravel Horizon para filas Redis e Laravel Reverb para broadcasting WebSocket em tempo real.
- [ ] 07-02: Endpoint de health check, instrumentação de Telescope/Sentry e orquestração Docker com Octane.

## Progress

**Execution Order:**
Phases execute in numeric order: 1 → 2 → 3 → 4 → 5 → 6 → 7

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Setup do Projeto, Auth & Multi-Tenancy | 0/3 | Not started | - |
| 2. Gestão de Cursos, Módulos e Aulas | 0/2 | Not started | - |
| 3. Player de Aula e Experiência do Colaborador | 0/2 | Not started | - |
| 4. Quizzes, Certificados e Trilhas de Aprendizado | 0/3 | Not started | - |
| 5. Faturamento e Assinaturas B2B com Mercado Pago | 0/2 | Not started | - |
| 6. Alvora AI — Score de Evasão, Intervenção, Chat e Criação | 0/4 | Not started | - |
| 7. Infraestrutura, Tempo Real e Observabilidade | 0/2 | Not started | - |
