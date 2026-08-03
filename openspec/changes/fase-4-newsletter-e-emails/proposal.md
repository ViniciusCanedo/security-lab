## Why

Implementar o módulo completo de Newsletter e Envio Assíncrono de E-mails Transacionais (RF06 e RF07), garantindo que a comunicação em massa seja processada via filas isoladas (`newsletter`), sem impactar as requisições HTTP da API nem os e-mails críticos de autenticação (`emails`).

## What Changes

- **RF06 — Newsletter**:
  - `POST /api/v1/newsletter/subscribe`: Inscrição pública via e-mail com fluxo double opt-in (envio de e-mail com link de confirmação assinado).
  - `GET /api/v1/newsletter/confirm`: Confirmação da inscrição ativando o status do inscrito.
  - `POST /api/v1/newsletter/unsubscribe`: Cancelamento de inscrição utilizando token/link único de rodapé.
  - `POST /api/v1/admin/newsletter/campaigns`: Criação de campanhas de e-mail por PUBLISHER ou ADMIN (assunto, conteúdo em HTML/Markdown, artigo opcional associado).
  - `POST /api/v1/admin/newsletter/campaigns/{id}/send`: Disparo assíncrono em lotes (`Bus::batch` de Jobs `SendNewsletterEmailJob` com chunks de 100 inscritos).
  - `GET /api/v1/admin/newsletter/campaigns/{id}/status`: Relatório de status da campanha (total, enviados, falhos, pendentes).
- **RF07 — Notificações Transacionais Assíncronas**:
  - Centralização de envio de e-mails transacionais (boas-vindas, confirmação de newsletter, magic link de senha) na fila `emails`.

## Capabilities

### New Capabilities
- `newsletter-and-async-mail`: Sistema de newsletter double opt-in, unsubscribe seguro, criação e envio em lotes de campanhas via fila dedicada com resiliência a falhas individuais e relatório de entrega.

### Modified Capabilities

## Impact

- Tabelas `newsletter_subscribers` (`email`, `status: pending/confirmed/unsubscribed`, `confirmation_token`, `subscribed_at`), `newsletter_campaigns` (`title`, `subject`, `content`, `article_id`, `status: draft/sending/completed`), e `newsletter_sends` (`campaign_id`, `subscriber_id`, `status: pending/sent/failed`, `error_message`, `sent_at`).
- `NewsletterService`, `NewsletterRepositoryInterface`, `NewsletterRepositoryEloquent`.
- Jobs `SendNewsletterCampaignBatchJob`, `SendNewsletterEmailJob`, `SendTransactionalEmailJob`.
- Mailables: `WelcomeMail`, `ConfirmSubscriptionMail`, `NewsletterCampaignMail`, `MagicLinkResetMail`.
- Configuração de retry ($tries = 3, $backoff = [10, 30, 60]) e método `failed()` com logs estruturados.
