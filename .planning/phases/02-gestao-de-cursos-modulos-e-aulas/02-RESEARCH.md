# Phase 2: Gestão de Cursos, Módulos e Aulas - Research

**Researched:** 2026-10-19  
**Domain:** Laravel 12, Livewire 3, Hierarchical Course/Module/Lesson CRUD, File Uploads, Multi-tenancy Scoping  
**Confidence:** HIGH  

---

<user_constraints>
## Requirements & Scope (CURS-01, CURS-02)

### Locked Requirements
- **CURS-01:** Instrutor pode criar, editar e excluir cursos com título, slug, descrição e status de publicação (`draft`, `published`, `archived`).
- **CURS-02:** Instrutor pode estruturar cursos em módulos e aulas ordenadas com suporte a URLs de vídeo, duração estimada em minutos e materiais complementares para download.
- **Tenant Isolation:** Cursos, módulos e aulas pertencem ao tenant (`company_id`) via trait `BelongsToTenant`.
- **Papéis:** Apenas usuários com papel `instructor`, `company_admin` ou `admin_alvora` podem criar e gerenciar cursos.
</user_constraints>

---

<architecture_patterns>
## Architecture Patterns

### Pattern 1: Modelagem Hierárquica com BelongsToTenant
```
Company (Tenant)
  └── Course (title, slug, description, thumbnail, status, instructor_id)
        └── Module (title, description, order)
              └── Lesson (title, description, video_url, duration_minutes, order)
                    └── LessonAttachment (title, file_path, file_size)
```

Cada modelo Eloquent (`Course`, `Module`, `Lesson`, `LessonAttachment`) utiliza a trait `BelongsToTenant`, garantindo que consultas em qualquer nível hierárquico estejam restritas à empresa do usuário logado.

### Pattern 2: Livewire 3 Componentes Modulares
1. `CourseList`: Lista de cursos com filtros por status (`draft`, `published`), busca textual e estatísticas de aulas/módulos.
2. `CourseEditor`: Formulário reativo de criação/edição de curso com geração automática de slug, upload de thumbnail com preview e seleção de instrutor.
3. `CurriculumBuilder`: Gerenciador hierárquico integrado onde o instrutor cria módulos, expande/recolhe aulas, altera a ordem numérica das aulas/módulos e anexa materiais de apoio.
</architecture_patterns>

---

<validation_architecture>
## Validation Architecture

### Framework
- PHPUnit no Laravel 12 com banco de dados de teste MySQL (`alvora_test`).
- Feature tests para CRUD de Cursos, permissões por papel, validação de slug e ordenação de módulos/aulas.

### Comandos de Teste
- `php artisan test --filter=CourseTest`
- `php artisan test --filter=CurriculumTest`
</validation_architecture>
