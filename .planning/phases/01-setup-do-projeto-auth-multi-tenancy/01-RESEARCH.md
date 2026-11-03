# Phase 1: Setup do Projeto, Auth & Multi-Tenancy - Research

**Researched:** 2026-10-19
**Domain:** Laravel 12, Livewire 3 / Volt, Multi-tenancy Scoping, Sanctum Auth
**Confidence:** HIGH

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions
- **D-01:** Resolução de Tenant transparente via Global Scope no Eloquent baseado na sessão e no usuário autenticado (`company_id`).
- **D-02:** Um usuário pertence estritamente a 1 empresa por vez (simplificado e direto para o modelo corporativo B2B).
- **D-03:** Acesso global irrestrito para `admin_alvora` (dono da plataforma faz bypass automático do escopo de tenant).
- **D-04:** Trait `BelongsToTenant` com Global Scope aplicada a todos os models com tenant, auto-injetando `company_id` no evento `saving()`.
- **D-05:** Mecanismo híbrido de convites: disparo de e-mail com token temporário + botão no painel do RH para copiar o link direto.
- **D-06:** Validade padrão do token de convite fixada em 7 dias, com botões de "Reenviar" e "Revogar" no painel do RH.
- **D-07:** Gestor da empresa seleciona no formulário de convite se o novo usuário terá perfil `student` ou `instructor`.
- **D-08:** Formulário unitário com suporte a múltiplos e-mails colados em textarea separados por vírgula na Fase 1.
- **D-09:** Foco de conversão voltado para demonstração B2B (CTA principal "Solicitar Demonstração").
- **D-10:** Apresentação de 3 níveis corporativos de planos (Starter, Pro, Enterprise) com destaque para IA de evasão.
- **D-11:** Identidade visual dark moderna corporativa com acentos índigo e esmeralda.
- **D-12:** Processamento de leads salvando em tabela própria no banco de dados (`leads`).
- **D-13:** Scaffolding inicial baseado em Laravel Breeze com Livewire/Volt customizado e adaptado em PT-BR.
- **D-14:** Estrutura de layouts dedicados por perfil (`admin_alvora`, `company_admin`, `student`/`instructor`).
- **D-15:** Biblioteca de ícones baseada em Heroicons.
- **D-16:** Notificações e feedback de usuário usando toasts reativos em Livewire disparados via eventos (`$dispatch('toast', ...)`).

### the agent's Discretion
- Estrutura exata das migrations de tabelas acessórias (`invitations`, `leads`).
- Implementação detalhada da trait `BelongsToTenant` e do `TenantScope`.
</user_constraints>

<research_summary>
## Summary

A Fase 1 entrega a espinha dorsal de todo o produto Alvora. Para evitar vazamento de dados entre empresas (o risco #1 em aplicações B2B multi-tenant), o padrão do ecossistema Laravel consiste em combinar:
1. Uma trait `BelongsToTenant` que registra um Eloquent Global Scope (`TenantScope`).
2. O `TenantScope` verifica se há um usuário autenticado. Se o usuário tiver a role `admin_alvora`, a cláusula `where('company_id', ...)` não é aplicada. Se for qualquer outro usuário corporativo, a query é automaticamente restrita ao `auth()->user()->company_id`.
3. Ao persistir novos registros (`creating`/`saving`), o modelo injeta automaticamente o `company_id` da sessão se não tiver sido especificado explicitamente.

Para o fluxo de convites, a modelagem com uma tabela `invitations` contendo token SHA-256 hasheado, e-mail, role pretendida, `company_id` e `expires_at` é padrão e à prova de ataques de enumeração.

**Primary recommendation:** Centralizar a lógica de isolamento na trait `BelongsToTenant` e utilizar Livewire 3 com componentes Blade dedicados para garantir segurança rígida e reatividade limpa.
</research_summary>

<standard_stack>
## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| Laravel | 12.x | Framework monolítico base | Recursos nativos de segurança, migrações, Eloquent e roteamento |
| Livewire | 3.x | Componentes reativos full-stack | Reatividade sem necessidade de SPA desacoplado |
| Laravel Sanctum | 4.x | Autenticação por sessão e tokens | Solução oficial leve para autenticação web e futura API |
| Tailwind CSS | 3.x / 4.x | Estilização utilitária | Padrão do ecossistema Laravel com suporte a dark mode nativo |
| Heroicons | 2.x | Ícones de interface | Leve, integrado aos componentes Blade e sem dependências JS pesadas |
</standard_stack>

<architecture_patterns>
## Architecture Patterns

### Pattern 1: BelongsToTenant Trait & TenantScope
```php
namespace App\Traits;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (auth()->check() && !auth()->user()->isAlvoraAdmin() && empty($model->company_id)) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Company::class);
    }
}
```

### Pattern 2: TenantScope com Bypass para Admin Alvora
```php
namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $user = auth()->user();
            if (!$user->isAlvoraAdmin() && !empty($user->company_id)) {
                $builder->where($model->getTable() . '.company_id', $user->company_id);
            }
        }
    }
}
```
</architecture_patterns>

<common_pitfalls>
## Common Pitfalls

### Pitfall 1: Esquecer company_id em migrations ou queries diretas (DB facade)
**Como evitar:** Nunca usar `DB::table(...)` para consultas sensíveis ao tenant; sempre usar os modelos Eloquent com a trait `BelongsToTenant`.

### Pitfall 2: Falha no isolamento de Jobs em segundo plano
**Como evitar:** Quando um Job assíncrono processar dados de um tenant, o `company_id` deve ser passado explicitamente na carga do Job.
</common_pitfalls>

<validation_architecture>
## Validation Architecture

### Framework
- PHPUnit / Pest PHP nativo do Laravel 12.
- Testes de Feature cobrindo autenticação, isolamento de tenant e convites.

### Comandos de Teste
- `php artisan test --filter=TenantTest` (rápido)
- `php artisan test` (suíte completa)
</validation_architecture>
