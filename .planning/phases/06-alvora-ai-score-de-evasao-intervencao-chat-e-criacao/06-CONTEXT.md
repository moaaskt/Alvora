# Contexto da Fase 06: Alvora AI — Score de Evasão, Intervenção, Chat e Criação

## Visão Geral e Decisões de Arquitetura

Nesta fase construímos o diferencial competitivo central do **ALVORA**: o sistema de inteligência preditiva contra evasão de colaboradores em treinamentos corporativos, intervenções automatizadas, tutoria conversacional de baixa latência e criação de cursos por IA com fallback resiliente.

---

## 1. Motor do Score de Risco de Evasão (EVAS-01, EVAS-02)

- **Fórmula Ponderada (0 a 100):**
  - **Dias sem login (35%):** Penalização progressiva conforme o tempo desde a última atividade ou autenticação.
  - **Estagnação de aulas (25%):** Dias decorridos sem concluir uma nova aula em cursos matriculados.
  - **Desempenho em Quizzes (20%):** Média ponderada das notas obtidas em `quiz_attempts` (notas baixas ou reprovações aumentam o risco).
  - **Desvio em relação à turma/empresa (20%):** Comparativo do ritmo de conclusão do aluno versus a média dos colaboradores da mesma organização (`companies`).
- **Classificação de Risco:**
  - `Baixo Risco`: 0 a 39
  - `Médio Risco`: 40 a 69
  - `Alto Risco`: 70 a 100
- **Persistência e Histórico:**
  - Tabela `risk_scores` contendo `user_id`, `company_id`, `score`, `level` (low, medium, high), `breakdown` (JSON com os 4 fatores pontuados) e timestamps.
  - Job agendado (`CalculateEvasionRiskJob`) executado em background para recomputar pontuações periodicamente de todos os alunos ativos.

---

## 2. Sistema de Intervenção Ativa Proativa (EVAS-03, EVAS-04)

- **Gatilho Autônomo:**
  - Quando um colaborador atinge ou ultrapassa a faixa de **Alto Risco (>70)**, o sistema dispara imediatamente uma intervenção proativa.
- **Ações da Intervenção:**
  - **Mensagem Personalizada de Incentivo & Reforço:** IA gera um texto empático e motivacional, sugerindo a próxima aula ideal ou revisão de conteúdo que o aluno teve dificuldade.
  - **Caixa de Entrada / Notificações do Aluno:** Persistido em tabela de intervenções/notificações (`interventions` ou `notifications`).
  - **Alerta para o Gestor de RH:** Registro com indicador visual na **Sala de Situação** do RH (`company_admin`).
- **Sala de Situação (Heatmap do RH):**
  - Tela Livewire com visão analítica e visual (Heatmap / Grid cromático) dividindo os times por departamentos e criticidade de risco.
  - Filtros rápidos por nível de risco, departamento e status da intervenção.
  - Ações manuais disponíveis: RH pode disparar mensagens adicionais personalizadas ou marcar o caso como acompanhado.

---

## 3. Alvora AI — Chat do Aluno em Tempo Real (CHAT-01, CHAT-02)

- **Interface:**
  - **Widget Flutuante / Drawer Global:** Presente no layout do aluno (dashboard, catálogo e player de aulas), expansível com 1 clique.
- **Injeção de Contexto Automática:**
  - Quando aberto dentro do **Player de Aulas**, o chat carrega no prompt do sistema: curso atual, módulo, aula atual, duração, anotações do aluno e status da aula.
  - No dashboard geral, carrega progresso geral e trilhas atribuídas.
- **Engine:**
  - **Groq API** com streaming/resposta ultra-rápida (baixa latência) para diálogo fluido do colaborador.
- **Histórico:**
  - Armazenado na tabela `ai_conversations` e `ai_messages`, isolado por colaborador e por sessão/aula.

---

## 4. Geração de Cursos por IA e Roteador com Fallback (CURS-03, INST-01, INST-02, INST-03)

- **Estratégia de Provedores:**
  - **Groq API:** Reservado com exclusividade para o **Chat do Aluno** (priorizando tempo de resposta inferior a 1s).
  - **OpenRouter com Roteador de Fallback:** Destinado a tarefas de raciocínio pesado e estruturação (gerador de grade curricular, diagnósticos executivos e insights do RH).
- **Lista de Modelos em Fallback (OpenRouter):**
  1. `deepseek/deepseek-r1:free` (ou versão principal configurada)
  2. `meta-llama/llama-3.3-70b-instruct:free`
  3. `qwen/qwen3-235b-a22b:free`
  - Caso o modelo primário retorne erro de quota, indisponibilidade ou timeout (8s), o roteador chaveia transparentemente para o próximo modelo da lista e registra log.
- **Gerador de Grade Curricular (`CurriculumGenerator`):**
  - Permite ao instrutor digitar um prompt temático (ex: *"Curso avançado de Segurança da Informação para Bancos"*).
  - A IA retorna JSON estruturado com título, descrição, lista de módulos e aulas sugeridas.
  - O instrutor revisa e aplica diretamente ao `CurriculumBuilder` já existente.
