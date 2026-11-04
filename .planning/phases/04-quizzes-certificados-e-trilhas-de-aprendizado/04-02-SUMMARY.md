---
phase: "04"
plan: "04-02"
title: "Template e Motor de Geração de Certificados em PDF via DomPDF"
status: "completed"
completed_at: "2026-10-19T18:10:00-03:00"
requirements:
  - EVAL-03
files_created:
  - database/migrations/2026_10_19_000012_create_certificates_table.php
  - app/Models/Certificate.php
  - app/Services/CertificateService.php
  - app/Http/Controllers/CertificateController.php
  - resources/views/certificates/template.blade.php
  - resources/views/certificates/verify.blade.php
  - tests/Feature/CertificateTest.php
files_modified:
  - composer.json
  - app/Models/Course.php
  - app/Models/User.php
  - resources/views/livewire/student/lesson-player.blade.php
  - routes/web.php
---

# Plan 04-02 Summary: Template e Motor de Geração de Certificados em PDF via DomPDF

## Accomplished
1. **Instalação e Integração do DomPDF (EVAL-03)**:
   - Instalação limpa do pacote `barryvdh/laravel-dompdf` (^3.1).
   - Configuração do pipeline de renderização vetorial de PDF com suporte a orientação paisagem (A4 landscape) e fontes TrueType.
2. **Modelagem de Certificados & Código Único**:
   - Migration `certificates` com `uuid`, `validation_code` único (formato `ALV-YYYY-XXXXXX`), `total_hours_minutes` e `issued_at`.
   - Model `Certificate` com `BelongsToTenant` e gerador autônomo de códigos únicos sem colisão.
3. **Template Corporativo Paisagem & Validação de Elegibilidade**:
   - `CertificateService`: Checagem rigorosa de requisitos (100% das aulas concluídas no `Enrollment` + aprovação em todos os quizzes obrigatórios do curso).
   - `resources/views/certificates/template.blade.php`: Design executivo solene com moldura sofisticada, selos institucionais, nome do colaborador, data, total de horas estimadas e assinaturas.
4. **Verificação Pública de Autenticidade**:
   - Rota pública `/validar-certificado/{code}` permitindo a qualquer empresa ou auditor consultar a autenticidade imediata do certificado sem autenticação.
   - Botão de download direto do PDF integrado no Player de Aula assim que o aluno atinge 100% de progresso.
5. **Verificação Automatizada**:
   - 5 testes de feature em `tests/Feature/CertificateTest.php` aprovados com 15 asserções (bloqueio antes de 100%, download com content-type PDF, validação pública de código válido e inválido, e isolamento multi-tenant).
