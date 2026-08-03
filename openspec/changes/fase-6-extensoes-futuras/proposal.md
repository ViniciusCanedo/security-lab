## Why

Mapear a arquitetura e especificações das extensões futuras (backlog pós-lançamento da v1) identificadas na Fase 6 do PRD. Este módulo estabelece o design prévio para notificações ao autor por novos comentários, múltiplos provedores sociais (GitHub, Facebook), busca full-text em artigos/tags, métricas/analytics de leitura e sistema de webhooks assíncronos acionados por eventos do blog.

## What Changes

- **RF07.4 — Notificação ao Autor por Novo Comentário**:
  - Envio assíncrono de e-mail ao autor do artigo quando um novo comentário ou resposta for publicado.
- **Múltiplos Provedores Sociais**:
  - Expansão do Socialite para suportar GitHub e Facebook mantendo vinculação por e-mail na tabela `social_accounts`.
- **Busca Full-Text e Taxonomia Avançada**:
  - Integração de busca full-text (Laravel Scout com Meilisearch/Postgres) e suporte avançado a categorias e tags hierárquicas.
- **Analytics e Métricas de Leitura**:
  - Rastreamento de visualizações únicas de artigos, tempo médio de leitura e métricas de engajamento acumuladas.
- **Webhooks de Eventos**:
  - Sistema de subscrição de webhooks para integrar plataformas externas quando eventos ocorrem (`article.published`, `article.archived`, `comment.created`).

## Capabilities

### New Capabilities
- `future-extensions-backlog`: Extensões de notificações ao autor, autenticação social estendida (GitHub/Facebook), busca full-text (Scout), estatísticas de leitura e disparo de webhooks assíncronos.

### Modified Capabilities

## Impact

- Módulo extensível de webhooks (`webhook_subscriptions`, `webhook_deliveries`).
- Driver de busca Laravel Scout.
- Novos drivers Socialite em `config/services.php`.
- Eventos de domínio adicionais (`ArticlePublished`, `CommentPosted`).
