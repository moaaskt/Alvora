# Requirements: ALVORA

**Defined:** 2026-10-19
**Core Value:** Combater a evasão no treinamento corporativo através do cálculo contínuo do Score de Risco de Evasão e intervenção preditiva automatizada via IA antes da desistência do colaborador.

## v1 Requirements

### Autenticação & Multi-tenancy

- [x] **AUTH-01**: Usuário pode se cadastrar e efetuar login com e-mail e senha via Laravel Sanctum com sessões e tokens de API.
- [x] **AUTH-02**: O sistema isola dados por organização (`companies`) garantindo que colaboradores vejam apenas dados da sua empresa.
- [x] **AUTH-03**: O sistema suporta 4 perfis de acesso com permissões granulares: `admin_alvora`, `company_admin`, `instructor` e `student`.
- [x] **AUTH-04**: O `company_admin` pode convidar colaboradores para sua empresa e gerenciar o status dos usuários.

### Cursos & Conteúdo do Instrutor

- [x] **CURS-01**: Instrutor pode criar, editar e excluir cursos com título, slug, descrição e status de publicação.
- [x] **CURS-02**: Instrutor pode estruturar cursos em módulos e aulas ordenadas com suporte a URLs de vídeo, duração e materiais complementares.
- [x] **CURS-03**: Instrutor pode gerar a estrutura preliminar de um curso por comando em linguagem natural via IA (módulos, títulos e ementa) para revisão antes da publicação.

### Experiência do Aluno & Player

- [x] **PLAY-01**: Aluno visualiza seu dashboard com cursos matriculados, porcentagem de conclusão e trilhas ativas.
- [x] **PLAY-02**: Aluno assiste às aulas em um player interativo com suporte a anotações pessoais persistidas e download de materiais.
- [x] **PLAY-03**: O progresso da aula (`completed_at`) é computado e sincronizado de forma reativa via Livewire 3 conforme o aluno avança.

### Avaliações, Trilhas & Certificados

- [x] **EVAL-01**: Instrutor pode cadastrar perguntas de quiz (múltipla escolha) vinculadas a aulas e módulos.
- [x] **EVAL-02**: Aluno realiza tentativas de quiz com correção imediata, armazenamento de histórico de notas e checagem de aproveitamento mínimo.
- [x] **EVAL-03**: Aluno gera e faz download de certificado em PDF via DomPDF automaticamente ao atingir 100% de conclusão de um curso.
- [x] **EVAL-04**: Gestor (`company_admin`) pode criar trilhas de aprendizado vinculando cursos com indicação de obrigatoriedade por departamento ou cargo.

### Faturamento & Assinaturas B2B

- [x] **BILL-01**: O sistema disponibiliza página pública institucional e página de planos de contratação B2B.
- [x] **BILL-02**: Integração com Mercado Pago para cobrança de assinaturas de empresas com processamento de webhooks para ativação e bloqueio de acesso.
- [x] **BILL-03**: `admin_alvora` e `company_admin` podem visualizar status de contrato, faturamento e planos ativos.

### Alvora AI - Score de Risco de Evasão & Intervenção

- [x] **EVAS-01**: Motor de cálculo calcula score de risco de 0 a 100 ponderando dias sem acesso, tempo parado na aula, notas em quizzes e ritmo em relação à turma.
- [x] **EVAS-02**: O sistema classifica colaboradores nos níveis baixo, médio e alto risco com registro de histórico em `risk_scores`.
- [x] **EVAS-03**: O sistema dispara intervenções ativas automáticas para alunos em risco alto (mensagem de incentivo gerada por IA e recomendação de reforço).
- [x] **EVAS-04**: O gestor da empresa (`company_admin`) recebe alertas em tempo real e visualiza uma Sala de Situação com heatmap de risco de evasão de seu time.

### Alvora AI - Chat & Suporte ao Colaborador

- [x] **CHAT-01**: Aluno acessa assistente Alvora AI em tempo real via Groq integrado ao contexto de seu progresso e dúvidas do curso.
- [x] **CHAT-02**: O sistema mantém histórico de conversas em `ai_conversations` isolado por colaborador e contexto.

### Alvora AI - Monitoramento & Insights Executivos

- [x] **INST-01**: Dashboard executivo do `company_admin` apresenta métricas de ROI de treinamento e taxas de conclusão de trilhas obrigatórias.
- [x] **INST-02**: Alvora AI Monitor gera diagnósticos interpretados para o `admin_alvora` e `company_admin` identificando anomalias de engajamento da plataforma.
- [x] **INST-03**: Roteador de IA implementa array de fallback com modelos OpenRouter para garantir resiliência operacional sem paradas.

### Infraestrutura, Tempo Real & Observabilidade

- [ ] **INFR-01**: Filas assíncronas operam via Redis para processamento de emails, certificados, webhooks e geração de IA com visualização no Horizon.
- [ ] **INFR-02**: Laravel Reverb transmite eventos WebSocket em tempo real para notificações do painel do gestor e do aluno.
- [ ] **INFR-03**: Endpoint de health check (`/api/health`) monitora conectividade de banco de dados, Redis e storage.
- [ ] **INFR-04**: Ambiente conteinerizado com Docker pronto para execução com Laravel Octane (Swoole) e suporte a Telescope (dev) e Sentry (produção).

## v2 Requirements

### Machine Learning Proprietário

- **ML-01**: Modelo de Machine Learning supervisionado treinado com a base histórica de telemetria para refinamento contínuo dos pesos de predição de evasão.

### Aplicativo Mobile

- **MOB-01**: Aplicativo móvel para alunos com consumo de vídeo offline.

## Out of Scope

| Feature | Reason |
|---------|--------|
| Venda B2C avulsa de cursos | Plataforma estritamente voltada a contratos corporativos (B2B). |
| Modelo ML proprietário na v1 | Fase inicial focada em regras ponderadas para validação rápida sem dependência de dados prévios. |
| Chat de voz ao vivo | Complexidade desnecessária para o objetivo de retenção e suporte assíncrono/síncrono textual na v1. |

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| AUTH-01 | Phase 1 | Complete |
| AUTH-02 | Phase 1 | Complete |
| AUTH-03 | Phase 1 | Complete |
| AUTH-04 | Phase 1 | Complete |
| BILL-01 | Phase 1 | Complete |
| CURS-01 | Phase 2 | Complete |
| CURS-02 | Phase 2 | Complete |
| PLAY-01 | Phase 3 | Complete |
| PLAY-02 | Phase 3 | Complete |
| PLAY-03 | Phase 3 | Complete |
| EVAL-01 | Phase 4 | Complete |
| EVAL-02 | Phase 4 | Complete |
| EVAL-03 | Phase 4 | Complete |
| EVAL-04 | Phase 4 | Complete |
| BILL-02 | Phase 5 | Complete |
| BILL-03 | Phase 5 | Complete |
| EVAS-01 | Phase 6 | Complete |
| EVAS-02 | Phase 6 | Complete |
| EVAS-03 | Phase 6 | Complete |
| EVAS-04 | Phase 6 | Complete |
| CHAT-01 | Phase 6 | Complete |
| CHAT-02 | Phase 6 | Complete |
| CURS-03 | Phase 6 | Complete |
| INST-01 | Phase 6 | Complete |
| INST-02 | Phase 6 | Complete |
| INST-03 | Phase 6 | Complete |
| INFR-01 | Phase 7 | Pending |
| INFR-02 | Phase 7 | Pending |
| INFR-03 | Phase 7 | Pending |
| INFR-04 | Phase 7 | Pending |

**Coverage:**
- v1 requirements: 30 total
- Mapped to phases: 30
- Unmapped: 0 ✓

---
*Requirements defined: 2026-10-19*
*Last updated: 2026-10-19 after initial definition*
