## 1. Notificacoes e Provedores Sociais Adicionais

- [x] 1.1 Criar Listener `SendCommentNotificationToAuthor` acionado pelo evento `CommentPosted`
- [x] 1.2 Adicionar provedores Socialite `github` e `facebook` no `AuthService` com vinculação por e-mail

## 2. Busca Full-Text e Analytics

- [x] 2.1 Configurar Laravel Scout no model `Article` habilitando busca por termo
- [x] 2.2 Implementar contador assíncrono de visualizações e cálculo de tempo de leitura

## 3. Sistema de Webhooks para Eventos

- [x] 3.1 Criar migrations para `webhook_subscriptions` e `webhook_deliveries`
- [x] 3.2 Implementar Job `DispatchWebhookJob` com tratamento de retries e logs de status de entrega
