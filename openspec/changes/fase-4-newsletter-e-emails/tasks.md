## 1. Migracoes, Models e Mailables

- [ ] 1.1 Criar migrations para `newsletter_subscribers`, `newsletter_campaigns` e `newsletter_sends`
- [ ] 1.2 Criar Models `NewsletterSubscriber`, `NewsletterCampaign` e `NewsletterSend`
- [ ] 1.3 Criar Mailables `ConfirmSubscriptionMail`, `NewsletterCampaignMail`, `WelcomeMail` e `MagicLinkResetMail`

## 2. Repositorios, DTOs e Servicos

- [ ] 2.1 Criar `SubscribeDTO`, `CreateCampaignDTO` imutáveis
- [ ] 2.2 Criar contratos e implementações de `NewsletterRepository`
- [ ] 2.3 Criar `NewsletterService` gerenciando double opt-in, unsubscribe, criação de campanha e cálculo de relatórios de entrega

## 3. Jobs de Fila Assincronos

- [ ] 3.1 Criar `SendNewsletterCampaignBatchJob` despachando lotes de envio via `Bus::batch()` na fila `newsletter`
- [ ] 3.2 Criar `SendNewsletterEmailJob` com lógica de retry, backoff e gravação de erros em `newsletter_sends`
- [ ] 3.3 Configurar `SendTransactionalEmailJob` na fila `emails`

## 4. Controllers e Suite TDD

- [ ] 4.1 Criar `NewsletterController` para endpoints públicos (`subscribe`, `confirm`, `unsubscribe`)
- [ ] 4.2 Criar `AdminNewsletterCampaignController` para gestão e disparo de campanhas
- [ ] 4.3 Escrever testes Pest com `Mail::fake()`, `Queue::fake()`, `Bus::fake()` testando resiliência de lotes e retries
