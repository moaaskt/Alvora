# 02. Multi-Tenancy e Segurança

## 🏢 Arquitetura Multi-Tenant: Single Database com Segregação Lógica

O **ALVORA** atende múltiplas empresas corporativas utilizando a abordagem de **Banco de Dados Único com Isolamento Lógico** (Single Database Multi-Tenancy). 

Essa escolha oferece o melhor equilíbrio entre:
* Custo e complexidade operacional (uma única migração gerencia todos os clientes).
* Escalabilidade do pool de conexões com o MySQL.
* Isolamento garantido por design em nível de ORM (Eloquent) e banco de dados.

---

## 🛡️ O Mecanismo `BelongsToTenant`

A proteção central contra o vazamento cruzado de dados entre empresas (Cross-Tenant Data Leak) é implementada pelo Trait [`BelongsToTenant`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Traits/BelongsToTenant.php):

```php
namespace App\Traits;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // 1. Aplica o escopo global para restringir SELECTs
        static::addGlobalScope(new TenantScope());

        // 2. Preenche automaticamente o company_id no evento creating
        static::creating(function ($model) {
            if (empty($model->company_id) && auth()->check()) {
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

### Comportamento do `TenantScope`:
1. Quando um usuário autenticado realiza qualquer consulta (`Model::all()`, `Model::find()`, etc.), o escopo injeta automaticamente:
   ```sql
   WHERE `company_id` = {auth()->user()->company_id}
   ```
2. **Super Admin Bypass:** Usuários com a role `admin_alvora` (administradores globais da plataforma) possuem isenção para visualizar dados consolidados entre empresas nos dashboards analíticos da Alvora.
3. **Escrita Segura:** O evento `creating` impede que um usuário forje a chave estrangeira de outra empresa através de formulários ou chamadas de API maliciosas.

---

## 👥 Matriz de Papéis e Permissões (`UserRole`)

O sistema define quatro papéis rigorosamente delimitados através do Enum [`App\Enums\UserRole`](file:///c:/Users/Windows%20Lite%20BR/alvora/app/Enums/UserRole.php):

| Papel (`role`) | Descrição e Escopo de Acesso | Permissões Principais |
| :--- | :--- | :--- |
| `admin_alvora` | **Super Administrador da Plataforma Alvora** (Visão Global) | Gerenciar planos de faturamento, visualizar MRR global, auditar infraestrutura, acessar qualquer tenant. |
| `company_admin` | **Gestor de RH / Diretor da Empresa Cliente** | Sala de Situação (risco de evasão), convite de colaboradores, gestão de trilhas, faturamento Mercado Pago. |
| `instructor` | **Criador de Conteúdo / Treinador da Empresa** | Criação de cursos, módulos, aulas, upload de materiais e criação de quizzes interativos. |
| `student` | **Colaborador / Aluno Matriculado** | Consumo de aulas, player interativo, execução de quizzes, chat com Tutor IA, emissão de certificados. |

---

## 🔒 Middlewares de Proteção

1. **`EnsureUserHasRole` (`role:xxx,yyy`):**
   * Valida se a sessão autenticada possui o papel requerido.
   * Lança `403 Forbidden` imediatamente se o colaborador tentar acessar rotas administrativas ou de instrutor.
2. **`CheckSubscriptionStatus` (`subscription.active`):**
   * Avalia a saúde da assinatura corporativa da empresa do usuário em tempo real.
   * **Se a empresa estiver suspensa ou inadimplente:**
     * Gestores de RH são redirecionados automaticamente para `/instrutor/faturamento` para regularização.
     * Alunos recebem uma tela de aviso informando que a capacitação corporativa está temporariamente suspensa pela empresa contratante, bloqueando novas aulas e chamadas à API de IA.

---

## 📡 Isolamento de WebSockets (Broadcast Channels)

Em [`routes/channels.php`](file:///c:/Users/Windows%20Lite%20BR/alvora/routes/channels.php), cada canal privado exige validação criptográfica de autorização via cookie de sessão:

* **Canal `company.{companyId}`:** Exclusivo para os gestores da respectiva empresa. Alertas de risco de evasão e relatórios nunca trafegam em canais públicos ou de empresas vizinhas.
* **Canal `user.{userId}`:** Exclusivo para o colaborador específico receber notificações personalizadas de intervenção.

---

## 🛡️ Conformidade com a LGPD

* **Mascaramento de PII:** Parâmetros confidenciais (tokens, senhas, CPF/CNPJ de colaboradores e cartões) são sanitizados antes de qualquer envio ao Sentry ou gravação no Telescope.
* **Transparência do Score de IA:** O cálculo do risco de evasão salva o detalhamento dos 4 fatores na tabela `risk_scores` (campo `breakdown` em JSON), permitindo auditoria clara de porque um colaborador recebeu determinado índice, sem efeito "caixa-preta".
