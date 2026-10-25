# Arquitetura do Sistema ALVORA

**ALVORA — Plataforma de Treinamento Corporativo (B2B) com Inteligência Artificial Anti-Evasão**

---

## 🧭 Visão Geral e Índice da Documentação

Esta pasta reúne a documentação arquitetural completa do sistema **Alvora**, cobrindo desde a camada de infraestrutura e orquestração de containers até os modelos de dados, fluxo de inteligência artificial preditiva e segurança multi-tenant.

```mermaid
graph TD
    Client["Navegador Web (Blade + Livewire 3 + Tailwind)"] -->|HTTP / HTTPS| Octane["Laravel Octane / FrankenPHP"]
    Client -->|WebSockets (Pusher Protocol)| Reverb["Laravel Reverb (Porta 8080)"]
    
    Octane --> MySQL[("MySQL 8.0 (Multi-Tenant)")]
    Octane --> Redis[("Redis 7 (Cache, Sessions, Queues)")]
    Octane --> Horizon["Laravel Horizon (Worker Queues)"]
    
    Horizon --> AI_Groq["Groq API (Chat Tutor Baixa Latência)"]
    Horizon --> AI_OR["OpenRouter (Fallback: DeepSeek R1 ➔ Llama 3.3 ➔ Qwen 3)"]
    Horizon --> Reverb
    
    Octane --> MP["Mercado Pago (Checkout Pro & Preapproval)"]
```

---

## 📚 Estrutura dos Módulos Arquiteturais

| Arquivo | Descrição |
| :--- | :--- |
| [01. Visão Geral e Stack Tecnológica](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/01-visao-geral-e-stack.md) | Princípios fundamentais do produto, stack, proposta de valor e monolito modular. |
| [02. Multi-Tenancy e Segurança](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/02-multi-tenancy-e-seguranca.md) | Isolamento estrito de dados por empresa (`BelongsToTenant`), autenticação, permissões e LGPD. |
| [03. Banco de Dados e Modelagem](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/03-banco-de-dados-e-modelagem.md) | Esquema relacional completo, tabelas, models Eloquent e relacionamentos. |
| [04. Motor de IA Anti-Evasão](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/04-motor-de-ia-anti-evasao.md) | Algoritmo do Score de Risco (0-100), breakdown auditável, Sala de Situação e intervenções ativas. |
| [05. Integração com LLMs e Resiliência](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/05-integracao-ia-llm-e-resiliencia.md) | Estratégia híbrida: Groq API para chat de baixa latência e OpenRouter com cascata de fallback. |
| [06. Faturamento e Assinaturas B2B](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/06-faturamento-e-assinaturas-b2b.md) | Mercado Pago, webhooks com HMAC `x-signature`, ciclo de carência de 5 dias e suspensão. |
| [07. Tempo Real e Observabilidade](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/07-tempo-real-e-observabilidade.md) | Laravel Reverb, Laravel Horizon, `/api/health`, Docker multi-stage e observabilidade. |
| [08. Módulo de Certificados e Validação](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/08-modulo-de-certificados-e-validacao.md) | Regras de elegibilidade 100%, renderização PDF via DomPDF, validação pública e oportunidades de expansão. |
| [09. Matriz de Rotas e Personas](file:///c:/Users/Windows%20Lite%20BR/alvora/arquitetura/09-matriz-de-rotas-e-personas.md) | Mapeamento completo de URLs, permissões por perfil (Aluno, RH/Instrutor, Super Admin). |

---

## 🎯 Proposta de Valor e Core Loop

O objetivo primordial do **Alvora** é combater a taxa de evasão em treinamentos corporativos (que chega a mais de 40% no Brasil). Diferente de LMSs convencionais que apenas emitem relatórios retrospectivos após o colaborador desistir, o Alvora:

1. **Monitora continuamente** a jornada do colaborador através de 4 fatores de risco.
2. **Emite alertas em tempo real** para o RH na Sala de Situação via WebSockets quando o score cruza o limiar crítico (≥ 70).
3. **Dispara intervenções preditivas** automáticas com mensagens hiper-personalizadas geradas por IA.
4. **Disponibiliza um Tutor IA** em tempo real no player de vídeo para tirar dúvidas instantâneas e evitar o desengajamento cognitivo.
