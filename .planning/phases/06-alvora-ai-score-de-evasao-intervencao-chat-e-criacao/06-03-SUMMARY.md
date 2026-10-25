---
phase: "06"
plan: "06-03"
title: "Chat Alvora AI via Groq com Injeção de Contexto"
status: "completed"
completed_at: "2026-10-20T15:18:00-03:00"
requirements:
  - CHAT-01
  - CHAT-02
files_created:
  - database/migrations/2026_10_20_000017_create_ai_conversations_and_messages_tables.php
  - app/Models/AiConversation.php
  - app/Models/AiMessage.php
  - config/ai.php
  - app/Services/GroqAiService.php
  - app/Livewire/Student/AiTutorDrawer.php
  - resources/views/livewire/student/ai-tutor-drawer.blade.php
  - tests/Feature/AiTutorChatTest.php
files_modified:
  - resources/views/layouts/app.blade.php
---

# Plan 06-03 Summary: Chat Alvora AI via Groq com Injeção de Contexto

## Accomplished
1. **Modelagem de Conversas e Mensagens de IA (CHAT-01, CHAT-02)**:
   - Migration `2026_10_20_000017_create_ai_conversations_and_messages_tables.php` com as tabelas `ai_conversations` (`company_id`, `user_id`, `title`, `course_id`, `lesson_id`) e `ai_messages` (`conversation_id`, `sender`, `content`, `tokens_used`).
   - Modelos Eloquent `AiConversation` e `AiMessage` com `BelongsToTenant` garantindo isolamento estrito entre empresas.
2. **Serviço Groq de Baixa Latência (`GroqAiService`)**:
   - Centralização de configurações em `config/ai.php`.
   - Injeção automática e contextual no prompt de sistema: título da aula, ementa do módulo, curso atual e identificação do colaborador.
   - Requisições otimizadas para a Groq API com timeout de 8 segundos e mock gracioso para ambientes locais sem credenciais.
3. **Componente Livewire Drawer do Aluno (`AiTutorDrawer`)**:
   - Botão flutuante sutil no canto inferior direito para acesso rápido em todas as telas do aluno.
   - Painel lateral deslizante (Drawer dark `#090D16`/`#0F172A`) com badge de status, histórico cronológico da conversa, indicador de digitação ("pensando...") e ações de sugestões de prompts.
   - Listeners Livewire para abertura programática com contexto de aula a partir do player de vídeo.
   - Inclusão global em `resources/views/layouts/app.blade.php` restrita a alunos autenticados.
4. **Verificação Automatizada**:
   - Suíte `tests/Feature/AiTutorChatTest.php` aprovada com 2 testes e 11 asserções cobrindo fluxo completo com mock `Http::fake()`, injeção contextual de aula no prompt e isolamento multi-tenant.
   - Suíte geral de testes mantida em 100% de aprovação (71 testes, 268 asserções).
