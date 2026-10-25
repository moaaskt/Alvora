---
phase: "06"
plan: "06-01"
title: "Motor do Score de Risco de Evasão e Job em Segundo Plano"
status: "completed"
completed_at: "2026-10-20T15:10:00-03:00"
requirements:
  - EVAS-01
  - EVAS-02
files_created:
  - database/migrations/2026_10_20_000015_create_risk_scores_table.php
  - app/Models/RiskScore.php
  - app/Services/EvasionRiskCalculatorService.php
  - app/Jobs/CalculateEvasionRiskJob.php
  - tests/Feature/EvasionRiskCalculationTest.php
files_modified:
  - app/Models/User.php
---

# Plan 06-01 Summary: Motor do Score de Risco de Evasão e Job em Segundo Plano

## Accomplished
1. **Modelagem e Persistência de Risco de Evasão (EVAS-01, EVAS-02)**:
   - Migration `2026_10_20_000015_create_risk_scores_table.php` criando a tabela `risk_scores` com `company_id`, `user_id`, `score` (0-100), `level` ('low', 'medium', 'high'), `breakdown` (JSON com os 4 fatores) e `calculated_at`.
   - Model Eloquent `App\Models\RiskScore` com trait `BelongsToTenant`, casts tipados e métodos de conveniência (`isHighRisk`, `isMediumRisk`, `isLowRisk`).
   - Relacionamento `riskScores()` e helper `latestRiskScore()` adicionados em `App\Models\User`.
2. **Motor Ponderado de Evasão (`EvasionRiskCalculatorService`)**:
   - Implementada a fórmula exata com os 4 pilares:
     - Inatividade (Dias sem login): peso 35%
     - Estagnação em aulas (Dias sem concluir aula): peso 25%
     - Desempenho em Quizzes (Média de notas): peso 20%
     - Desvio em relação à turma/empresa: peso 20%
   - Classificação em faixas de criticidade:
     - 0 a 39: Baixo Risco (`low`)
     - 40 a 69: Médio Risco (`medium`)
     - 70 a 100: Alto Risco (`high`)
   - Breakdown detalhado persistido para rastreabilidade e explicabilidade da IA.
3. **Job em Fila (`CalculateEvasionRiskJob`)**:
   - Job com `ShouldQueue` processando cálculos por usuário específico, por empresa ou em lotes para toda a base de colaboradores.
4. **Verificação Automatizada**:
   - Suíte `tests/Feature/EvasionRiskCalculationTest.php` aprovada com 4 testes e 19 asserções cobrindo alunos engajados, alunos de alto risco por inatividade, isolamento multi-tenant e processamento do job.
   - Suíte geral de testes mantida em 100% de aprovação (64 testes, 239 asserções).
