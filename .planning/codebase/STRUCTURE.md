# Estrutura de Diretórios e Organização do Código — ALVORA

**Mapeamento da Base de Código: Outubro de 2026**

---

## 📂 Visão Geral da Árvore de Diretórios

A estrutura do projeto segue o padrão idiomático do Laravel 12 com adições modulares para Livewire 3, serviços de domínio e documentação arquitetural.

```
alvora/
├── app/                        # Núcleo da lógica de negócio e aplicação
│   ├── Enums/                  # Enums fortemente tipados (UserRole)
│   ├── Events/                 # Eventos de domínio e transmissão em tempo real
│   ├── Http/
│   │   ├── Controllers/        # Controladores web, retornos de checkout e APIs
│   │   │   └── Api/            # Controladores REST (HealthCheck, Webhooks)
│   │   └── Middleware/         # Middlewares de autorização e gate de assinatura
│   ├── Jobs/                   # Processamento assíncrono em fila (Horizon)
│   ├── Livewire/               # Componentes reativos full-stack por persona
│   │   ├── Admin/              # Painel Super Admin (Faturamento global)
│   │   ├── Auth/               # Autenticação e aceitação de convite
│   │   ├── Company/            # Portal corporativo (RH, Sala de Situação, Trilhas)
│   │   ├── Instructor/         # Gestão de cursos, currículo, quizzes e IA
│   │   └── Student/            # Portal do colaborador (Player, Quizzes, Tutor IA)
│   ├── Models/                 # Entidades Eloquent ORM
│   │   └── Scopes/             # Escopos globais (TenantScope)
│   ├── Providers/              # Service Providers do framework
│   ├── Services/               # Camada de serviços de domínio puros
│   ├── Traits/                 # Traits reutilizáveis (BelongsToTenant)
│   └── View/                   # View components
├── arquitetura/                # Documentação técnica aprofundada da plataforma
├── bootstrap/                  # Arquivos de inicialização e bootstrapping
├── config/                     # Configurações do ecossistema (ai, horizon, services)
├── database/
│   ├── factories/              # Geradores de modelos fictícios para testes
│   ├── migrations/             # 22 migrações estruturais do banco MySQL
│   └── seeders/                # Seeders de carga inicial (empresas demo, cursos)
├── docker/                     # Configurações específicas de contêineres
├── public/                     # Ponto de entrada web público (index.php, favicon)
├── resources/
│   ├── css/                    # Estilos Tailwind CSS
│   ├── js/                     # Scripts clientes (Echo, Reverb)
│   └── views/                  # Templates Blade (layouts, certificados, landing)
├── routes/                     # Definições de rotas da aplicação
│   ├── api.php                 # Rotas da API e webhooks
│   ├── auth.php                # Rotas de login, logout e convite
│   ├── channels.php            # Autorização de canais WebSocket (Reverb)
│   ├── console.php             # Comandos Artisan agendados
│   └── web.php                 # Rotas de interface web e navegação
├── tests/
│   ├── Feature/                # 21 suítes de testes de integração e comportamento
│   └── Unit/                   # Testes unitários puros
├── .planning/                  # Artefatos do ciclo de vida GSD e planejamento
│   ├── codebase/               # Documentação técnica indexada do codebase
│   ├── milestones/             # Requisitos e roadmap da release ativa
│   └── phases/                 # Planos de implementação por fase
├── docker-compose.yml          # Orquestração de 5 contêineres para desenvolvimento
├── Dockerfile                  # Imagem multi-stage de produção (FrankenPHP)
└── composer.json / package.json# Dependências PHP e Node.js
```

---

## 🧩 Detalhamento dos Componentes em `app/`

### 1. `app/Models/` (Entidades do Domínio)
O ecossistema modela o ciclo de vida completo de uma plataforma LMS corporativa:

| Modelo | Função | Trait Multi-Tenant |
| :--- | :--- | :--- |
| `Company` | Empresa cliente contratante (Tenant) | Raiz do Tenant |
| `User` | Usuário da plataforma (Colaborador, Gestor, Instrutor, Super Admin) | Possui `company_id` |
| `Course` | Curso corporativo cadastrado | `BelongsToTenant` |
| `Module` | Agrupamento de aulas pertencente a um curso | Indireto via `Course` |
| `Lesson` | Aula contendo vídeo, duração e materiais | Indireto via `Module` |
| `LessonProgress` | Registro de visualização e conclusão de cada aula | Vinculado a `User` |
| `LessonAttachment`| Materiais complementares em anexo para download | Indireto via `Lesson` |
| `LessonNote` | Anotações privadas do aluno durante o estudo | Vinculado a `User` |
| `LearningTrack` | Trilha de desenvolvimento por setor da empresa | `BelongsToTenant` |
| `Quiz` / `QuizQuestion` / `QuizOption` | Avaliação de conhecimento e alternativas | `BelongsToTenant` |
| `QuizAttempt` | Tentativa do colaborador com nota e tempo gasto | Vinculado a `User` |
| `Certificate` | Certificado de conclusão com código de autenticidade | `BelongsToTenant` |
| `Subscription` / `Invoice` | Contrato de cobrança e histórico de faturas | `BelongsToTenant` |
| `RiskScore` | Histórico auditável do Score de Evasão (0–100) | `BelongsToTenant` |
| `AiIntervention` | Registro de ações preditivas disparadas pela IA | `BelongsToTenant` |
| `AiConversation` / `AiMessage` | Histórico do chat do Tutor Virtual com o aluno | Vinculado a `User` |
| `Invitation` | Convite para novos colaboradores da empresa | `BelongsToTenant` |
| `Lead` | Leads capturados no formulário da vitrine pública | Global |

---

### 2. `app/Services/` (Lógica de Domínio Pura)
Classes com responsabilidade única desacopladas da camada HTTP:
* **`EvasionRiskCalculatorService`:** Cálculo ponderado dos 4 fatores de evasão e salvamento de breakdown.
* **`EvasionInterventionService`:** Disparo de mensagens persuasivas por IA e recomendação de aula de retomada.
* **`GroqAiService`:** Comunicação de baixa latência com LPU Groq e injeção do contexto da aula no prompt.
* **`OpenRouterResilientService`:** Orquestração de chamadas resilientes com cascata de fallback automático de 3 tiers.
* **`MercadoPagoService`:** Criação de assinaturas recorrentes, links do Checkout Pro e consulta de faturas.
* **`CertificateService`:** Checagem de elegibilidade (100% de progresso) e emissão de certificados.
* **`HealthCheckService`:** Sonda ativa de integridade do MySQL, Redis e armazenamento.

---

### 3. `app/Livewire/` (Componentização da Interface por Persona)
A interface é estritamente segregada por perfis de uso:

* **`Admin/AdminBilling.php`:** Visão gerencial consolidada para o Super Admin com cálculo de MRR.
* **`Company/SituationRoom.php`:** Painel em tempo real para o RH com tabela de colaboradores filtráveis por nível de risco de evasão.
* **`Company/EmployeeManager.php`:** Convite em massa de colaboradores e gestão de acessos.
* **`Company/TrackManager.php`:** Montagem de trilhas corporativas com cursos obrigatórios e eletivos.
* **`Company/CompanyBilling.php`:** Gestão do plano corporativo, download de boletos e histórico financeiro.
* **`Company/AiExecutiveInsights.php`:** Relatório com insights executivos de engajamento gerados por IA.
* **`Instructor/CourseList.php` & `CourseEditor.php`:** Gestão de catálogo de cursos.
* **`Instructor/CurriculumBuilder.php` & `CurriculumAiGenerator.php`:** Construção visual da ementa e geração por IA.
* **`Instructor/QuizEditor.php`:** Construtor dinâmico de perguntas e alternativas com gabarito.
* **`Student/Dashboard.php`:** Painel do colaborador com trilhas atribuídas e cursos em andamento.
* **`Student/LessonPlayer.php`:** Player interativo de vídeo, anotações, materiais e controle de conclusão.
* **`Student/AiTutorDrawer.php`:** Painel retrátil de chat contextual com IA durante a aula.
* **`Student/QuizRunner.php`:** Interface de execução da prova com cronômetro e feedback imediato.

---

## 📐 Regras de Alocação de Código (Code Placement)

1. **Lógica de Domínio Complexa:** Deve ser implementada em `app/Services/`, nunca nos componentes Livewire ou Controllers.
2. **Consultas com Filtro de Tenant:** Models que possuem relação direta com a empresa devem usar o trait `BelongsToTenant`.
3. **Tarefas de Longa Duração:** Todo processamento que envolva chamadas de IA analítica, recálculo de scores ou webhooks deve ser despachado como `Job` assíncrono na fila gerenciada pelo Horizon.
4. **Tratamento de Exceções de IA:** As classes em `app/Services/` que chamam APIs externas devem tratar quedas graciosamente e logar avisos informativos, retornando payloads de contingência sem causar tela 500 para o usuário.
