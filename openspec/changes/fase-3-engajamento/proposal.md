## Why

Implementar as funcionalidades de engajamento da comunidade (RF04 e RF05): curtidas com alternância (toggle) idempotente e sistema de comentários com suporte a 1 nível de resposta aninhada, assegurando que interações ocorram estritamente em artigos no estado `published`.

## What Changes

- **RF04 — Curtidas**:
  - `POST /api/v1/articles/{id}/like`: Alternar curtida (curtir se não curtiu, descurtir se já curtiu) para o usuário autenticado.
  - Exposição da contagem total de curtidas e do booleano `user_has_liked` no `ArticleResource`.
- **RF05 — Comentários e Respostas**:
  - `POST /api/v1/articles/{id}/comments`: Adicionar comentário em artigo publicado.
  - `POST /api/v1/comments/{id}/replies`: Responder a um comentário existente (aninhamento de 1 nível de profundidade).
  - `PUT /api/v1/comments/{id}`: Editar conteúdo do próprio comentário (apenas autor do comentário).
  - `DELETE /api/v1/comments/{id}`: Excluir comentário (autor exclui o seu; ADMIN exclui/modera qualquer comentário).
  - `GET /api/v1/articles/{id}/comments`: Listagem paginada de comentários com respostas inclusas via `CommentResource`.
- **Regras de Negócio e Proteções**:
  - Rejeição imediata (422/403) para qualquer tentativa de curtir ou comentar em artigos `draft` ou `archived`.

## Capabilities

### New Capabilities
- `article-engagement`: Sistema de curtidas (toggle) e comentários aninhados de 1 nível com moderação por ADMIN e bloqueio em artigos não publicados.

### Modified Capabilities

## Impact

- Tabelas `likes` (`user_id`, `article_id`, constraint `unique(user_id, article_id)`) e `comments` (`user_id`, `article_id`, `parent_id`, `content`, `softDeletes`).
- `LikeService`, `CommentService`, `LikeRepositoryInterface`, `CommentRepositoryInterface`.
- `CreateCommentDTO`, `UpdateCommentDTO`.
- `CommentResource` incluindo array aninhado de respostas.
- `CommentPolicy` para checagens de propriedade e autorização de ADMIN.
