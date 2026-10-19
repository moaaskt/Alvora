<!-- GSD:project-start source:PROJECT.md -->

## Project

**ALVORA — Plataforma de Cursos B2B com IA**

Alvora é uma plataforma de treinamento corporativo (B2B) onde empresas contratam acesso para capacitar seus times através de cursos, trilhas de aprendizado e acompanhamento de desempenho. O diferencial central é um sistema de IA com intervenção ativa que prevê risco de evasão do colaborador em tempo hábil e age de forma proativa para retenção e engajamento.

**Core Value:** Combater a evasão no treinamento corporativo através do cálculo contínuo do Score de Risco de Evasão e intervenção preditiva automatizada via IA antes da desistência do colaborador.

### Constraints

- **Tech Stack**: Monolito Laravel 12 + Livewire 3 + Blade + MySQL 8 + Redis.
- **IA Fallback**: Groq API para chat de baixa latência; OpenRouter para análise/geração com fallback não hardcoded (`deepseek/deepseek-r1:free`, `meta-llama/llama-3.3-70b-instruct:free`, `qwen/qwen3-235b-a22b:free`).
- **Idiomático**: Código e documentação orientados em PT-BR.
- **Infraestrutura**: Orquestração com Docker e monitoramento de filas com Horizon.

<!-- GSD:project-end -->

<!-- GSD:stack-start source:STACK.md -->

## Technology Stack

Technology stack not yet documented. Will populate after codebase mapping or first phase.
<!-- GSD:stack-end -->

<!-- GSD:conventions-start source:CONVENTIONS.md -->

## Conventions

Conventions not yet established. Will populate as patterns emerge during development.
<!-- GSD:conventions-end -->

<!-- GSD:architecture-start source:ARCHITECTURE.md -->

## Architecture

Architecture not yet mapped. Follow existing patterns found in the codebase.
<!-- GSD:architecture-end -->

<!-- GSD:skills-start source:skills/ -->

## Project Skills

No project skills found. Add skills to any of: `.agents/skills/`, `.agents/skills/`, `.cursor/skills/`, `.github/skills/`, or `.codex/skills/` with a `SKILL.md` index file.
<!-- GSD:skills-end -->

<!-- GSD:workflow-start source:GSD defaults -->

## GSD Workflow Enforcement

Before using Edit, Write, or other file-changing tools, start work through a GSD command so planning artifacts and execution context stay in sync.

Use these entry points:

- `/gsd-quick` for small fixes, doc updates, and ad-hoc tasks
- `/gsd-debug` for investigation and bug fixing
- `/gsd-execute-phase` for planned phase work

Do not make direct repo edits outside a GSD workflow unless the user explicitly asks to bypass it.
<!-- GSD:workflow-end -->

<!-- GSD:profile-start -->

## Developer Profile

> Profile not yet configured. Run `/gsd-profile-user` to generate your developer profile.
> This section is managed by `generate-claude-profile` -- do not edit manually.
<!-- GSD:profile-end -->
