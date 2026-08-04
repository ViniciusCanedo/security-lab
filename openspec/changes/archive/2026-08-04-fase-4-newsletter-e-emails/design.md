## Context

A Fase 4 introduz a comunicação externa via e-mail. Para evitar gargalos na API e problemas de reputação de IP, a arquitetura separa estritamente os envios transacionais (fila `emails`) dos disparos em massa (fila `newsletter`).

## Goals / Non-Goals

**Goals:**
- Fluxo double opt-in para novas assinaturas de newsletter.
- Unsubscribe simples via token assinado no rodapé das mensagens.
- Disparo de campanha em lotes via `Bus::batch()` utilizando a fila `newsletter`.
- Resiliência com retries ($tries = 3, $backoff = [10, 30, 60]) e gravação de logs em `newsletter_sends`.
- Relatório estatístico básico de campanhas (enviados, falhos, pendentes).

**Non-Goals:**
- Integração com provedores de marketing complexos (ex: Mailchimp/Sendgrid Marketing APIs) — a solução é nativa com Laravel Queues.

## Decisions

- **Separação de Filas:**
  - Fila `emails`: E-mails transacionais de alta prioridade (boas-vindas, reset de senha, confirmação de assinatura).
  - Fila `newsletter`: Campanhas em lote de baixa prioridade.
- **Processamento em Chunks:** A busca de inscritos `confirmed` é realizada em páginas de 100 registros (`NewsletterSubscriber::confirmed()->chunk(100)`), despachando um Job por chunk ligado a um `Bus::batch()`.
- **Rastreamento por Destinatário:** Cada envio gera um registro na tabela `newsletter_sends` com o status do envio (`pending`, `sent`, `failed`), garantindo controle individual de entregas.

## Risks / Trade-offs

- [Sobrecarga do servidor SMTP em envios em massa] → Mitigação: Rate limiting por segundo nos workers de fila do Redis e estratégia de retry granular por destinatário.
