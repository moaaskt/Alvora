# Débitos Técnicos, Riscos e Pontos de Atenção — ALVORA

**Mapeamento da Base de Código: Outubro de 2026**

---

## 🔍 Visão Geral

Este documento compila os débitos técnicos conhecidos, pontos de atenção operacional, gargalos de escalabilidade e oportunidades de evolução mapeadas a partir da base de código do **ALVORA**.

---

## ⚠️ 1. Lacunas Funcionais Mapeadas (Gap Analysis)

### Módulo de Certificados
Embora o motor de emissão, geração de PDF via DomPDF e checagem pública (`/validar-certificado/{code}`) estejam 100% funcionais e cobertos por testes, identificou-se os seguintes itens pendentes para versões futuras:
1. **Aba "Meus Certificados" no Portal do Aluno:** Centralizar todos os certificados emitidos em formato de galeria no dashboard do colaborador.
2. **Auditoria de Certificados pelo RH:** Tela no painel da empresa para o gestor consultar data de emissão, colaboradores certificados e exportar relatórios de conformidade de treinamento corporativo.
3. **Customização de Template:** Permitir upload de logotipo da empresa contratante e assinatura digital do responsável pedagógico.
4. **Integração "Adicionar ao LinkedIn":** Geração de URL direta pré-preenchida para adição à seção de certificações da rede social.
5. **Disparo de E-mail Transacional:** Envio automático do PDF anexado por e-mail assim que o colaborador atinge 100% de conclusão.

---

## ⚡ 2. Gargalos de Escalabilidade e Desempenho

### A. Volume de Jobs de Recálculo de Evasão
* **Cenário:** Em momentos de pico corporativo (ex.: campanhas internas de conformidade em grandes empresas), centenas de colaboradores completam lições simultaneamente, enfileirando dezenas de `CalculateEvasionRiskJob`.
* **Atenção:**
  - O cálculo do fator de desvio de pares (`peer_deviation`) realiza queries agregadas (`AVG` e `COUNT`) na base de dados da empresa.
  - **Mitigação Recomendada:** Aplicar cache temporário de curta duração no Redis (ex.: 5 a 15 minutos) para as médias consolidadas de conclusão departamentais da empresa, evitando recalcular a média do tenant a cada job individual.

### B. Consumo de Memória no Redis
* **Cenário:** O histórico de `risk_scores` persiste um objeto JSON com o `breakdown` de cada cálculo para garantir explicabilidade auditável ao RH.
* **Atenção:** Conforme a base de usuários cresce, a tabela `risk_scores` e os eventos no Redis acumularão milhões de registros.
* **Mitigação Recomendada:** Implementar um job de expurgo ou sumarização de scores antigos (ex.: manter detalhamento diário por 90 dias e depois condensar em médias semanais).

### C. Conexões Concorrentes no Laravel Reverb
* **Cenário:** Milhares de colaboradores e gestores com navegadores abertos conectados simultaneamente na porta 8080.
* **Atenção:** Em ambientes de produção de alta escala (acima de 10.000 conexões ativas), o servidor Reverb deve ser configurado com pool Redis escalado e balanceador com suporte a HTTP Upgrade/WebSockets.

---

## 🛡️ 3. Segurança, Segredos e LGPD

### A. Proteção da Vitrine Pública na Home
* A rota `/` consulta cursos da empresa de demonstração identificada por `config('app.demo_tenant_cnpj')` (`12.345.678/0001-90`).
* **Ponto de Atenção:** Deve-se garantir que em ambiente de produção esta variável de ambiente aponte sempre para a empresa demo oficial para nunca expor acidentalmente cursos ou colaboradores de empresas clientes reais para o público não autenticado.

### B. Custos e Quotas de IA (OpenRouter e Groq)
* Embora o serviço possua fallback e utilize tiers gratuitos/econômicos, um aumento drástico de requisições de geração de currículo pode esgotar cotas de rate limit de modelos free.
* **Mitigação Recomendada:** Implementar limitação de taxa por empresa (`RateLimiting`) para ferramentas de geração de conteúdo por IA no painel do instrutor (ex.: máximo de 20 gerações de ementa por dia por empresa).

### C. Rotação de Segredos Críticos
* As seguintes credenciais requerem procedimento documentado de rotação periódica:
  - `MERCADO_PAGO_WEBHOOK_SECRET`
  - `MERCADO_PAGO_ACCESS_TOKEN`
  - `OPENROUTER_API_KEY`
  - `GROQ_API_KEY`
  - `APP_KEY`
