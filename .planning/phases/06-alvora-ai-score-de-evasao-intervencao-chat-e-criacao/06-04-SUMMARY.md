---
phase: "06"
plan: "06-04"
title: "Roteador OpenRouter com Fallback, Gerador de Cursos e Insights Executivos"
status: "completed"
completed_at: "2026-10-20T15:21:00-03:00"
requirements:
  - CURS-03
  - INST-01
  - INST-02
  - INST-03
files_created:
  - app/Services/OpenRouterResilientService.php
  - app/Livewire/Instructor/CurriculumAiGenerator.php
  - resources/views/livewire/instructor/curriculum-ai-generator.blade.php
  - app/Livewire/Company/AiExecutiveInsights.php
  - resources/views/livewire/company/ai-executive-insights.blade.php
  - tests/Feature/OpenRouterFallbackAndCurriculumGeneratorTest.php
files_modified:
  - config/ai.php
  - resources/views/livewire/instructor/curriculum-builder.blade.php
  - routes/web.php
  - resources/views/layouts/navigation.blade.php
---

# Plan 06-04 Summary: Roteador OpenRouter com Fallback, Gerador de Cursos e Insights Executivos

## Accomplished
1. **Roteador Resiliente OpenRouter com Cascata de Modelos (`OpenRouterResilientService` - INST-03)**:
   - Configurada lista de fallback ordenada: `deepseek/deepseek-r1:free` ➔ `meta-llama/llama-3.3-70b-instruct:free` ➔ `qwen/qwen3-235b-a22b:free`.
   - Chaveamento autônomo com registro em log de avisos caso algum modelo falhe ou atinja timeout de 8s.
   - Resposta padronizada com metadados de tentativas (`attempts`) e modelo efetivamente utilizado (`model_used`).
2. **Gerador Inteligente de Grade Curricular (`CurriculumAiGenerator` - CURS-03)**:
   - Componente Livewire e view Blade permitindo ao instrutor digitar um tema e público-alvo para gerar uma grade completa estruturada em JSON (módulos, aulas, tempos estimados e resumos).
   - Ação `applyToCourse` para criar e persistir em lote os modelos `Module` e `Lesson` diretamente no curso, associados ao tenant.
   - Integrado diretamente ao `CurriculumBuilder` do instrutor.
3. **Painel de Diagnósticos e Insights Executivos (`AiExecutiveInsights` - INST-01, INST-02)**:
   - Rota `/empresa/insights-executivos` para gestores de RH e Super Admin.
   - Consolidação de métricas corporativas de retenção: total de colaboradores, aulas concluídas, taxa estimada de conclusão, colaboradores em alto risco e média global de score.
   - Diagnóstico descritivo e interpretado gerado pela IA com recomendações táticas de retenção e ROI para a diretoria.
4. **Verificação Automatizada**:
   - Suíte `tests/Feature/OpenRouterFallbackAndCurriculumGeneratorTest.php` aprovada com 3 testes e 14 asserções validando chaveamento de fallback (500 ➔ sucesso no Llama 3.3), criação em lote de módulos/aulas e painel executivo.
   - Suíte completa de testes da plataforma aprovada com 74 testes e 282 asserções sem nenhuma regressão.
