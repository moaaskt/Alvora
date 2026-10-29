---
name: trello-sync-skill
description: Sincroniza tarefas e pendências do sistema Alvora com o quadro do Trello de forma automática usando comandos como /trello-sync-skill ou pedidos de anotação/registro no Trello.
---

# Trello Sync Skill

Esta skill gerencia a criação e sincronização de cartões (tasks, pendências, bugs e features) no quadro do Trello do projeto **Alvora**.

## Informações do Quadro e Listas

- **URL do Quadro**: https://trello.com/b/izLyFMLG/alvora
- **Board ID**: `6ae27f2b95a9b79d507f8b4f` (shortLink: `izLyFMLG`)

### Mapeamento de Listas (Colunas)
- **Backlog**: `6ae2810c916523ba6057df2d` (Nome: `📋 Backlog`)
- **Em Progresso / In Progress**: `6ae2810fd9dd10ce3a592559` (Nome: `🔨 Em Progresso`)
- **Revisão / Teste**: `6ae281134508a36afdefc293` (Nome: `🔍 Revisão / Teste`)
- **Concluído**: `6ae281155dfa8062a39b123b` (Nome: `✅ Concluído`)

---

## Procedimento de Execução

Quando o usuário solicitar o registro ou sincronização de uma tarefa (ex: via `/trello-sync-skill` ou pedidos como *"anote no Trello"*, *"crie um card no backlog"*):

1. **Análise do Contexto**:
   - Identifique o que deve ser feito no sistema Alvora (título claro, descrição técnica sucinta, arquivos ou componentes envolvidos).
   - Defina a lista de destino correta (padrão: `📋 Backlog`, a menos que o usuário especifique outra ou a tarefa já esteja em andamento).

2. **Criação do Cartão via MCP Trello**:
   - Utilize a ferramenta MCP `call_mcp_tool`:
     - **ServerName**: `trello`
     - **ToolName**: `add_card_to_list`
     - **Arguments**:
       ```json
       {
         "boardId": "6ae27f2b95a9b79d507f8b4f",
         "listId": "<ID_DA_LISTA>",
         "name": "<Título da Tarefa>",
         "description": "<Descrição objetiva com contexto e arquivos do Alvora>"
       }
       ```

3. **Confirmação ao Usuário**:
   - Retorne uma resposta concisa confirmando:
     - Título do cartão criado;
     - Coluna/Lista onde foi inserido;
     - Link direto ou menção ao quadro Alvora.

