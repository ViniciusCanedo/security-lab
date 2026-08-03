## Context

A Fase 2 implementa o core de conteúdo da aplicação: os artigos. A arquitetura deve garantir que a regra de permissões entre `COMMON`, `PUBLISHER` e `ADMIN` seja inviolável, com forte separação entre o ciclo de vida privado (rascunho/arquivado) e o consumo público (artigos publicados).

## Goals / Non-Goals

**Goals:**
- Tabela `articles` com suporte a `softDeletes` e chave estrangeira `user_id` (autor).
- Enum PHP 8.1+ `ArticleStatus` (`DRAFT = 'draft'`, `PUBLISHED = 'published'`, `ARCHIVED = 'archived'`).
- `ArticleService` encarregado de validar transições de estado (`draft -> published -> archived`).
- `ArticleRepositoryInterface` e `ArticleRepositoryEloquent` para isolamento de queries Eloquent.
- `ArticlePolicy` cobrindo a matriz completa da Seção 3.1 do `AGENTS.md`.
- Controllers `ArticleController` e `PublicArticleController` para separar endpoints públicos e restritos.

**Non-Goals:**
- Sistema de comentários e curtidas (Fase 3).
- Upload avançado em CDN S3 (salvamento básico de URL ou disco local configurável).

## Decisions

- **Enum de Status e State Machine:** As transições válidas de status são gerenciadas pelo `ArticleService` antes da persistência no Repository, impedindo reativação direta de artigos arquivados sem validação.
- **Isolamento de Queries de Artigos Públicos:** O `PublicArticleController` consulta o repositório utilizando scopes Eloquent padrão (`scopePublished()`), impedindo vazamento acidental de rascunhos ou artigos arquivados.
- **Tratamento de Autor Excluído:** Quando um usuário é removido (soft delete), seus artigos permanecem salvos mantendo a chave `user_id` original para preservação de histórico.

## Risks / Trade-offs

- [Exposição de Rascunhos via API Pública] → Mitigação: Scope global/local obrigatório no Repositório para endpoints públicos e testes cobrindo especificamente a tentativa de acesso direto por ID a artigos `draft` ou `archived`.
