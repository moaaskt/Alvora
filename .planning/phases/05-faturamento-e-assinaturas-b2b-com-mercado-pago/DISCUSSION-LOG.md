# Discussion Log: Phase 05 — Faturamento e Assinaturas B2B com Mercado Pago

**Date:** 2026-10-20
**Mode:** Interactive discuss-phase

---

## Areas Discussed

### 1. Modalidade de Checkout
- **Question:** Qual modalidade de integração do Mercado Pago devemos adotar para a contratação dos planos B2B?
- **Options Presented:**
  1. Checkout Pro (Redirect Seguro) [Recommended]
  2. Checkout Transparente (Payment Brick no Livewire)
- **User Selection:** Checkout Pro (Redirect Seguro)
- **Notes:** Elimina manipulação de dados sensíveis de cartão de crédito no servidor (PCI compliance), garante suporte imediato e robusto a múltiplos meios de pagamento e retorno automático para rotas da Alvora.

### 2. Recorrência e Meios de Cobrança
- **Question:** Como deve funcionar o modelo de renovação e recorrência das assinaturas corporativas?
- **Options Presented:**
  1. Faturamento Híbrido: Assinatura Automática no Cartão (Preapproval) + Fatura Mensal Avulsa para Pix e Boleto [Recommended]
  2. Apenas Assinatura Recorrente Automática no Cartão (Preapproval pura)
  3. Faturamento Mensal por Ciclo (Checkout Pro sob demanda)
- **User Selection:** Faturamento Híbrido: Assinatura Automática no Cartão (Preapproval) + Fatura Mensal Avulsa para Pix e Boleto
- **Notes:** Cartão corporativo desfruta de renovação contínua automática sem fricção; empresas que optam por Pix ou Boleto recebem cobranças por ciclo de 30 dias.

### 3. Política de Inadimplência e Carência
- **Question:** Qual deve ser a política de tolerância/carência e suspensão de acesso quando houver falha de pagamento ou atraso?
- **Options Presented:**
  1. Carência de 5 dias corridos com bloqueio em dois estágios [Recommended]
  2. Bloqueio imediato no Webhook
  3. Tolerância flexível de 10 dias com bloqueio de novos convites
- **User Selection:** Carência de 5 dias corridos: colaboradores mantêm acesso durante a carência com banner de aviso no painel do RH; após 5 dias sem regularização, o acesso dos alunos é suspenso e o gestor é direcionado apenas para a regularização financeira.
- **Notes:** Protege a experiência de aprendizagem dos colaboradores contra falhas temporárias de cartão ou atraso bancário, enquanto alerta o gestor da empresa para regularizar.

### 4. Regras de Upgrade e Vagas
- **Question:** Como deve funcionar o cálculo financeiro e a liberação de vagas quando a empresa faz upgrade de plano?
- **Options Presented:**
  1. Upgrade imediato com pro-rata da diferença [Recommended]
  2. Upgrade com cobrança integral e novo ciclo de 30 dias
  3. Upgrade agendado para o próximo ciclo
- **User Selection:** Upgrade com cobrança integral e novo ciclo de 30 dias: cancela o plano anterior, cobra o valor total do novo plano e reinicia a vigência de 30 dias a partir do pagamento.
- **Notes:** Modelo direto, sem complicação contábil de frações de dias, cancelando a recorrência antiga e iniciando o novo plano imediatamente após confirmação.
