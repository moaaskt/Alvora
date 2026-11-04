# ALVORA — Plataforma de Cursos B2B com IA

## What This Is

Alvora é uma plataforma de treinamento corporativo (B2B) onde empresas contratam acesso para capacitar seus times através de cursos, trilhas de aprendizado e acompanhamento de desempenho. O diferencial central é um sistema de IA com intervenção ativa que prevê risco de evasão do colaborador em tempo hábil e age de forma proativa para retenção e engajamento.

## Core Value

Combater a evasão no treinamento corporativo através do cálculo contínuo do Score de Risco de Evasão e intervenção preditiva automatizada via IA antes da desistência do colaborador.

## Business Context

- **Customer**: Empresas contratantes (Gestores de RH, T&D e C-level buscando capacitação de equipes).
- **Revenue model**: Assinatura B2B por planos empresariais via Mercado Pago (gestão de contratos e usuários por organização).
- **Success metric**: Redução mensurável na taxa de evasão de treinamentos e aumento na conclusão de trilhas corporativas.
- **Strategy notes**: Abordagem B2B multi-tenant com foco em ROI de treinamento para o RH e experiência fluida para instrutores e alunos.

## Requirements

### Validated

- [x] **AUTH & TENANCY**: Multi-tenancy organizacional (`companies`, `company_users`) com 4 papéis (`admin_alvora`, `company_admin`, `instructor`, `student`) usando Laravel Sanctum.
- [x] **CURSOS & CONTEÚDO**: Gestão completa de cursos, módulos, aulas com vídeo/materiais/anotações e geração assistida por IA para instrutores, com WYSIWYG e sanitização HTML.
- [x] **PLAYER & EXPERIÊNCIA DO ALUNO**: Player reativo com Livewire, anotações, progresso de aulas em tempo real e histórico com porcentagem de conclusão.
- [x] **AVALIAÇÕES & TRILHAS**: Quizzes ao final de módulos, trilhas de aprendizado corporativas com acompanhamento em tempo real e atribuição departamental.
- [x] **CERTIFICADOS VERSIONADOS & EDITOR VISUAL**: Motor completo Dompdf baseado em schema JSON, editor visual interativo, isolamento por tenant, backgrounds privados e verificação pública.
- [x] **FATURAMENTO & ASSINATURA**: Integração de pagamentos e assinaturas com Mercado Pago via webhook HMAC-SHA256 para empresas clientes.
- [x] **ALVORA AI - EVASÃO**: Motor de Score de Risco de Evasão (0 a 100 ponderado por inatividade, tempo parado, notas de quiz e ritmo) com classificação (baixo/médio/alto).
- [x] **ALVORA AI - INTERVENÇÃO ATIVA**: Ações automatizadas ao detectar risco elevado (incentivo personalizado por LLM, sugestão de reforço e alerta para company_admin via WebSocket/email).
- [x] **ALVORA AI - CHAT & ASSISTENTE**: Chat de suporte em tempo real com baixa latência via Groq para tirar dúvidas sobre o conteúdo e progresso do aluno.
- [x] **ALVORA AI - CRIAÇÃO & INSIGHTS**: Criação de estrutura de cursos a partir de prompts em linguagem natural e monitoramento inteligente com relatórios executivos para RH e Admin Alvora (OpenRouter com array de fallback).
- [x] **ADMINISTRAÇÃO & SALA DE SITUAÇÃO**: Painel da empresa com filtros avançados e heatmap de risco por colaborador, além de painel global Admin Alvora com gestão de usuários, categorias e cursos.
- [x] **INFRAESTRUTURA & TEMPO REAL**: Laravel Horizon (Redis), Laravel Reverb (WebSockets nativos), Sonda de observabilidade `/api/health` e suporte a Docker.

### Active

(Nenhum item pendente do Milestone v1.0 e da extensão de certificados — pronto para o Milestone v2.0)

### Out of Scope

- Modelo de Machine Learning proprietário em v1 — Na Fase 1 o score de evasão é calculado via motor de regras ponderadas determinísticas; o treinamento de ML supervisionado fica reservado para a Fase 2 após acúmulo de dados históricos.
- Venda B2C avulsa para aluno final — O modelo é estritamente B2B (empresas contratam planos para seus colaboradores).
- Aplicativo Mobile Nativo (iOS/Android) — Aplicação focada em Web responsiva no monolito Laravel + Livewire 4.

## Context

- Taxa média de evasão em EAD no Brasil ultrapassa 40% no setor privado; as soluções atuais operam como repositórios passivos ("mais um LMS").
- Stack baseada no ecossistema moderno do Laravel 13 (PHP 8.3+) com Livewire 4, Redis, MySQL 8 e containers Docker.
- Convenção de código do projeto em PT-BR para regras de negócio, métodos, variáveis, comentários e commits.

## Constraints

- **Tech Stack**: Monolito Modular Laravel 13 + Livewire 4 + Blade + Tailwind CSS 4 + MySQL 8 + Redis.
- **IA Fallback**: Groq API para chat de baixa latência; OpenRouter para análise/geração com fallback não hardcoded (`deepseek/deepseek-r1:free`, `meta-llama/llama-3.3-70b-instruct:free`, `qwen/qwen3-235b-a22b:free`).
- **Idiomático**: Código e documentação orientados em PT-BR.
- **Infraestrutura**: Orquestração com Docker e monitoramento de filas com Horizon.

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Monolito Laravel 13 + Livewire 4 | Reduz a complexidade de manter API e SPA separados, oferecendo reatividade nativa e rápida iteração | Validated ✅ |
| Multi-tenancy via company_id | Isolamento lógico claro e simples sem necessidade de esquemas de banco separados por tenant na v1 | Validated ✅ |
| Score Ponderado em v1 vs ML | Permite lançar imediatamente e colher telemetria real sem depender de dataset prévio para treinar modelos | Validated ✅ |
| Provedores de IA Groq + OpenRouter | Baixa latência no chat direto com o aluno (Groq) e flexibilidade econômica com fallback para tarefas batch/gerativas (OpenRouter) | Validated ✅ |
| Templates Versionados de Certificado | Garante integridade histórica dos certificados emitidos mesmo após alterações visuais pelo instrutor/empresa | Validated ✅ |

---
*Last updated: 2026-11-02 após sincronização completa da base de código*
