## 1. Migracoes e Models de Likes e Comentarios

- [x] 1.1 Criar migration da tabela `likes` com `user_id`, `article_id` e restrição `unique(user_id, article_id)`
- [x] 1.2 Criar migration da tabela `comments` com `user_id`, `article_id`, `parent_id` (foreign key nullable), `content` e `softDeletes`
- [x] 1.3 Criar Models `Like` e `Comment` com relacionamentos (`article`, `user`, `replies`, `parent`)

## 2. Repositorios e DTOs

- [x] 2.1 Criar contratos `LikeRepositoryInterface` e `CommentRepositoryInterface` com suas implementações Eloquent
- [x] 2.2 Criar `CreateCommentDTO` e `UpdateCommentDTO`

## 3. Camada de Servico e Policies

- [x] 3.1 Criar `LikeService` com método `toggle()` idempotente e transacional
- [x] 3.2 Criar `CommentService` com validações de status do artigo e trava de profundidade de aninhamento
- [x] 3.3 Criar `CommentPolicy` autorizando edição pelo autor e exclusão pelo autor ou ADMIN
- [x] 3.4 Criar `CommentResource` formatando o autor e as respostas aninhadas

## 4. Controllers e Suite de Testes TDD

- [x] 4.1 Criar `LikeController` (`POST /api/v1/articles/{id}/like`)
- [x] 4.2 Criar `CommentController` para endpoints de criação, resposta, edição, exclusão e listagem
- [x] 4.3 Escrever testes Pest cobrindo toggle de curtidas, bloqueio em artigos `draft`/`archived`, trava de profundidade de respostas e moderação ADMIN
