---
phase: "06"
plan: "06-02"
title: "Sistema de Intervenção Ativa Proativa e Sala de Situação para o RH"
status: "completed"
completed_at: "2026-10-20T15:15:00-03:00"
requirements:
  - EVAS-03
  - EVAS-04
files_created:
  - database/migrations/2026_10_20_000016_create_ai_interventions_table.php
  - app/Models/AiIntervention.php
  - app/Services/EvasionInterventionService.php
  - app/Livewire/Company/SituationRoom.php
  - resources/views/livewire/company/situation-room.blade.php
  - tests/Feature/SituationRoomAndInterventionTest.php
files_modified:
  - app/Models/User.php
  - app/Services/EvasionRiskCalculatorService.php
  - routes/web.php
  - resources/views/layouts/navigation.blade.php
  - tests/Feature/EvasionRiskCalculationTest.php
---

# Plan 06-02 Summary: Sistema de Intervenção Ativa Proativa e Sala de Situação para o RH

## Accomplished
1. **Modelagem de Intervenções Ativas (EVAS-03)**:
   - Migration `2026_10_20_000016_create_ai_interventions_table.php` criando a tabela `ai_interventions` com `company_id`, `user_id`, `risk_score_id`, `recommended_lesson_id`, `message`, `status` ('sent', 'viewed', 'acknowledged') e `read_at`.
   - Model Eloquent `App\Models\AiIntervention` com `BelongsToTenant` e relacionamentos com `User`, `RiskScore` e `Lesson`.
   - Relacionamento `interventions()` configurado no model `User`.
2. **Serviço de Intervenção Preditiva (`EvasionInterventionService`)**:
   - Disparo autônomo imediato quando colaborador atinge score de alto risco (`>= 70`).
   - Algoritmo de recomendação inteligente que localiza a aula ideal de reforço (aula com quiz não aprovado ou primeira aula pendente da trilha).
   - Mecanismo anti-fadiga com throttling de 48 horas para não sobrecarregar o colaborador com mensagens repetidas.
   - Geração de mensagens humanizadas e empáticas de apoio ao aprendizado.
   - Suporte a intervenções manuais personalizadas enviadas pelo gestor de RH.
3. **Sala de Situação em Tempo Real (`SituationRoom` - EVAS-04)**:
   - Componente Livewire e view Blade em `resources/views/livewire/company/situation-room.blade.php`.
   - Rota `/empresa/sala-de-situacao` protegida por `role:company_admin`.
   - Heatmap visual cromático de engajamento (verde para seguro, amarelo para atenção, vermelho para crítico).
   - KPIs no topo: contagem e % de alto risco, médio risco, estáveis e intervenções disparadas no mês.
   - Filtros dinâmicos por nível de criticidade e busca textual de colaboradores.
   - Modal interativo para envio manual de mensagens de intervenção.
   - Botão para recomputação sob demanda de scores de risco de todos os colaboradores.
4. **Verificação Automatizada**:
   - Suíte `tests/Feature/SituationRoomAndInterventionTest.php` aprovada com 5 testes e 18 asserções cobrindo disparos automáticos, limitação de 48h, autorização de acesso, rendering de KPIs e isolamento multi-tenant.
   - Suíte geral de testes mantida em 100% de aprovação (69 testes, 257 asserções).
