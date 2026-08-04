## Context

A Fase 3 cuida do engajamento social em torno dos artigos. As funcionalidades incluem curtidas (likes) e comentários com 1 nível de aninhamento (respostas). O ponto crítico de design é proibir qualquer interação em artigos que não estejam no estado `published`.

## Goals / Non-Goals

**Goals:**
- Tabela `likes` com chave composta/única (`user_id`, `article_id`) garantindo consistência no banco.
- Tabela `comments` com `parent_id` nulo para comentários topo de linha e preenchido para respostas de 1º nível.
- `LikeService` com método `toggle(userId, articleId)` executado dentro de uma transação de banco de dados (`DB::transaction`).
- `CommentService` com validações de profundidade de aninhamento e status do artigo.
- `CommentPolicy` para checagens de autorização de edição/exclusão.

**Non-Goals:**
- Aninhamento infinito de comentários (limitado a 1 nível de profundidade por simplicidade e performance).
- Notificação assíncrona ao autor por e-mail quando receber um comentário (reservado para fase futura).

## Decisions

- **Constraint Única para Likes:** A tabela `likes` utiliza índice único `(user_id, article_id)`. O `LikeService::toggle()` tenta deletar se existir ou criar se não existir dentro de `firstOrCreate`/`delete`, evitando race conditions.
- **Validação de Aninhamento de Comentários:** Se `parent_id` for informado no payload de resposta, o `CommentService` verifica se o comentário pai possui `parent_id == null`. Caso já seja uma resposta (`parent_id != null`), a criação é rejeitada com erro 422.

## Risks / Trade-offs

- [N+1 Query em Listagem de Comentários com Respostas] → Mitigação: O `CommentRepository` faz o carregamento antecipado das respostas (`with(['replies.user', 'user'])`) na query paginada do comentário principal.
