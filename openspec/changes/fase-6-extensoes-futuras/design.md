## Context

A Fase 6 representa o backlog de evolução futura pós-MVP. Suas especificações e escolhas de design garantem que a arquitetura construída nas Fases 0 a 5 suporte extensões sem necessidade de reescritas estruturais.

## Goals / Non-Goals

**Goals:**
- Mapear a arquitetura de Notificação de Comentários, Provedores Sociais adicionais, Busca Full-Text (Scout), Analytics e Webhooks.
- Garantir desacoplamento via Eventos e Listeners nativos do Laravel (`Event::dispatch()`).

**Non-Goals:**
- Execução obrigatória das tarefas da Fase 6 antes do lançamento da v1 da API.

## Decisions

- **Arquitetura Orientada a Eventos:** O disparo de webhooks e notificações por comentário utiliza os eventos nativos `CommentCreated` e `ArticleStatusChanged`.
- **Laravel Scout para Busca:** Integração transparente com Meilisearch/Algolia via Scout mantendo o repositório imune a queries complexas de `LIKE %...%`.

## Risks / Trade-offs

- [Falha no Envio de Webhooks Externos] → Mitigação: Gravar tentativas em `webhook_deliveries` com retry exponencial e mecanismo de desativação por circuito aberto (circuit breaker) caso a URL falhe repetidamente.
