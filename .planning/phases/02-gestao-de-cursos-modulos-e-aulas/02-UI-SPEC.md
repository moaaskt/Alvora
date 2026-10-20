---
phase: "2"
slug: "gestao-de-cursos-modulos-e-aulas"
status: approved
created: "2026-10-19"
---

# Phase 2 — UI Design Contract: Gestão de Cursos, Módulos e Aulas

> Visual and interaction contract for the instructor and course management surfaces in Phase 2.

---

## Design System

| Property | Value |
|----------|-------|
| Tool | Tailwind CSS nativo |
| Component library | Livewire 3 + Blade Components customizados |
| Icon library | Heroicons (SVG inline) |
| Font | Plus Jakarta Sans (títulos) / Inter (corpo) |

---

## Color Tokens

| Role | Value | Usage |
|------|-------|-------|
| Dominant (60%) | `#090D16` / `#0F172A` | Background geral e superfícies |
| Secondary (30%) | `#1E293B` / `#334155` | Cards de cursos, modais de aulas, bordas |
| Accent (10%) | `#6366F1` (Índigo) / `#10B981` (Esmeralda) | CTAs principais ("Criar Novo Curso", "Publicar"), status ativo |
| Warning / Draft | `#F59E0B` (Amber) | Badge de curso em "Rascunho" |
| Destructive | `#EF4444` (Rose) | Ações de exclusão com confirmação |

---

## Copywriting Contract

| Element | Copy |
|---------|------|
| Primary CTA (Lista) | "Criar Novo Curso" |
| Primary CTA (Form Curso) | "Salvar Informações do Curso" |
| Primary CTA (Módulo) | "Adicionar Módulo" |
| Primary CTA (Aula) | "Adicionar Aula" |
| Button Publicar | "Publicar Curso" |
| Button Despublicar | "Reverter para Rascunho" |
| Empty State (Lista de Cursos) | "Nenhum curso cadastrado ainda" / "Comece criando o primeiro curso corporativo para capacitar sua equipe." |
| Empty State (Grade Curricular) | "Nenhum módulo cadastrado neste curso" / "Crie o primeiro módulo temático para começar a estruturar as aulas." |
| Feedback Sucesso (Curso Criado) | "Curso criado com sucesso! Agora adicione os módulos e aulas." |
| Feedback Sucesso (Aula Adicionada) | "Aula adicionada com sucesso ao módulo." |
| Feedback Sucesso (Ordem Atualizada) | "Ordem das aulas atualizada com sucesso." |
