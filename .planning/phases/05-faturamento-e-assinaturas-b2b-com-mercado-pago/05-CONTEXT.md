# Phase 5: Faturamento e Assinaturas B2B com Mercado Pago - Context

**Gathered:** 2026-10-20
**Status:** Ready for planning

<domain>
## Phase Boundary

Esta fase implementa a monetização da plataforma Alvora com planos de assinatura para empresas contratantes (B2B), integração completa com a API do Mercado Pago para assinaturas recorrentes e cobranças avulsas, processamento seguro e resiliente de webhooks para ativação e bloqueio de tenants, além dos painéis de faturamento e visualização de contratos para o gestor de RH (`company_admin`) e para o super admin (`admin_alvora`).
</domain>

<decisions>
## Implementation Decisions

### Modalidade de Checkout do Mercado Pago
- **D-01:** Adoção do **Checkout Pro (Redirect Seguro)** do Mercado Pago para contratação dos planos corporativos. As empresas são redirecionadas para o ambiente seguro hospedado do Mercado Pago (com suporte nativo a Pix, Boleto e Cartão de Crédito corporativo) com retorno automático para a Alvora (`back_urls` para sucesso, pendente e falha). Isso elimina a necessidade de manipular dados sensíveis de cartão na aplicação (PCI compliance) e garante máxima confiabilidade na conversão. — **Reversibility:** costly — mudar para checkout transparente exigiria integrar os SDKs de frontend (Bricks) e tokens de cartão.

### Recorrência e Meios de Cobrança
- **D-02:** **Modelo de Faturamento Híbrido**:
  - Assinatura Automática no Cartão de Crédito via **Preapproval API** do Mercado Pago para empresas que cadastram cartão corporativo, realizando débitos automáticos mensais recorrentes sem intervenção manual.
  - Faturas Mensais sob demanda via **Checkout Pro** com emissão de cobrança a cada ciclo de 30 dias para empresas que optam por pagamento via Pix ou Boleto bancário. — **Reversibility:** costly — arquitetura que acomoda tanto recorrência nativa quanto faturas avulsas periódicas.

### Política de Inadimplência e Carência
- **D-03:** **Carência de 5 dias corridos com bloqueio em dois estágios**:
  - Ao receber notificação de falha de cobrança, cancelamento ou atraso via webhook, a empresa entra em período de tolerância de 5 dias corridos (`status = overdue`).
  - Durante os 5 dias de carência, os colaboradores continuam com acesso pleno às aulas e trilhas para não interromper os treinamentos em andamento, enquanto um banner persistente de pendência é exibido no painel do RH (`company_admin`).
  - Após 5 dias sem regularização financeira, o status da empresa torna-se suspenso (`status = suspended`), bloqueando o player e quizzes para os colaboradores.
  - O gestor (`company_admin`) mantém acesso restrito exclusivo à tela de Faturamento para efetuar a quitação e reativar a empresa imediatamente via webhook. — **Reversibility:** reversible — parametrização de dias de tolerância e middleware de bloqueio de tenant.

### Regras de Upgrade de Plano e Vagas
- **D-04:** **Upgrade com cobrança integral e reinício de ciclo de 30 dias**:
  - Ao realizar upgrade (ex: Starter para Pro + IA) ou contratar mais vagas de colaboradores, a assinatura/plano anterior é encerrado e uma nova cobrança do valor integral do novo plano é gerada no Mercado Pago.
  - Uma vez confirmado o pagamento, o ciclo de 30 dias é reiniciado a partir daquela data e os novos limites de colaboradores e recursos de IA são liberados imediatamente na organização, simplificando a conciliação financeira e evitando cálculos fragmentados de pro-rata. — **Reversibility:** reversible — lógica de troca de plano no controller/service de faturamento.

### the agent's Discretion
- Modelagem detalhada das tabelas `subscriptions` (armazenando `company_id`, `preapproval_id`, `plan`, `status`, `current_period_start`, `current_period_end`) e `invoices` (`subscription_id`, `mp_payment_id`, `amount`, `payment_method`, `status`, `paid_at`).
- Definição do endpoint seguro de webhook (`POST /api/webhooks/mercadopago`) com validação do header de integridade `x-signature` ou consulta de confirmação via API do Mercado Pago para evitar forjamento de notificações.
- Construção dos componentes Livewire e views Blade das telas de faturamento (`/empresa/faturamento` para o gestor e `/admin/faturamento` para o super admin) em estrita conformidade com o contrato de design [05-UI-SPEC.md](.planning/phases/05-faturamento-e-assinaturas-b2b-com-mercado-pago/05-UI-SPEC.md).

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Documentos de Planejamento e Design
- `.planning/phases/05-faturamento-e-assinaturas-b2b-com-mercado-pago/05-UI-SPEC.md` — Contrato de design de interface aprovado (cores, tipografia, escala de espaçamento, copywriting e considerações de UI de faturamento)
- `.planning/REQUIREMENTS.md` § Faturamento & Assinaturas B2B — Requisitos funcionais BILL-02 e BILL-03
- `.planning/ROADMAP.md` § Phase 5 — Metas, dependências e critérios de sucesso da Fase 5
- `.planning/STATE.md` — Estado geral do projeto e histórico de decisões anteriores

### Referências de Integração Técnica
- Mercado Pago Developers Documentation: Checkout Pro Integration & Preferences API
- Mercado Pago Subscriptions: Preapproval API Guide
- Mercado Pago Webhooks: IPN e Notificações de Pagamento v1 com validação de assinatura `x-signature`
</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `app/Models/Company.php`: Já possui os campos `plan`, `status`, `max_users` que devem ser sincronizados diretamente pelas rotinas de faturamento e webhooks.
- `resources/views/pricing.blade.php`: Grade visual com os 3 planos corporativos (Starter por R$ 490/mês para até 20 colaboradores, Pro + IA por R$ 1.290/mês para até 100 colaboradores e Enterprise sob consulta).
- `app/Traits/BelongsToTenant.php`: Trait global de tenant que pode ser associada aos modelos `Subscription` e `Invoice` para manter total isolamento por empresa cliente.

### Established Patterns
- Componentes reativos Livewire 3 no namespace `App\Livewire\Company` e `App\Livewire\Admin`.
- Notificações visuais via dispatch de eventos Blade/Tailwind e feedbacks inline com cores semânticas (`#10B981` para ativo/sucesso, `#EF4444` para suspenso/erro).
- Bypass global automático de escopo de tenant para usuários com role `admin_alvora`.

### Integration Points
- `routes/web.php` e `routes/api.php`: Rotas de retorno do checkout (`/empresa/faturamento/sucesso`, `/empresa/faturamento/pendente`, `/empresa/faturamento/erro`) e rota pública de webhook (`POST /api/webhooks/mercadopago`).
- Middleware de verificação de status da empresa (bloqueando alunos caso `company->status === 'suspended'`).
</code_context>

<specifics>
## Specific Ideas

- O gestor da empresa no painel `/empresa/faturamento` deve poder visualizar claramente a quantidade de vagas ocupadas vs total contratado (ex: `14 de 20 vagas ocupadas`) com alerta visual caso tente convidar além da capacidade.
- O histórico de faturas deve permitir download ou visualização de recibo/comprovante com detalhes da transação do Mercado Pago.
</specifics>

<deferred>
## Deferred Ideas

- Emissão automática de Nota Fiscal de Serviço eletrônica (NFS-e via eNotas/PlugNotas) — postergado para marcos posteriores de automação fiscal.
- Parcelamento de anuidade com desconto em 12x no cartão corporativo — manter mensalidade recorrente padrão na v1.
</deferred>

---

*Phase: 05-Faturamento e Assinaturas B2B com Mercado Pago*
*Context gathered: 2026-10-20*
