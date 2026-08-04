## Context

A Fase 5 garante a prontidão de produção (production readiness) da API de Blog. Foca em resiliência (rate limiting), transparência auditável (logging estruturado), conformidade com privacidade de dados (Resources sanitizados), documentação pública viva (Scramble OpenAPI) e rigor de qualidade (80%+ cobertura TDD sem alertas PHPStan/Larastan).

## Goals / Non-Goals

**Goals:**
- Definição de RateLimiters específicos (`auth-limiter`, `comments-limiter`, `newsletter-limiter`).
- Canal de log dedicado `audit` em `config/logging.php` direcionando eventos críticos para formato JSON estruturado.
- Sanitização de `UserResource` utilizando `$this->when($this->id === auth()->id() || auth()->user()?->hasRole('ADMIN'), ...)` para e-mail e dados sensíveis.
- Instalação e configuração de `dedoc/scramble` para OpenAPI.
- Verificação automatizada de cobertura em CI com threshold de 80%.

**Non-Goals:**
- Testes de estresse de infraestrutura distribuída em larga escala.

## Decisions

- **Canal de Log `audit`:** Em vez de misturar logs de aplicação com logs de segurança, cria-se o channel `audit` usando o `Monolog\Formatter\JsonFormatter`.
- **Scramble para OpenAPI:** Escolha do pacote `dedoc/scramble` por gerar especificação OpenAPI 3.0 diretamente da inferência de tipos PHP 8, Form Requests e Resources, eliminando anotações manuais redundantes.

## Risks / Trade-offs

- [Falsos Positivos de Rate Limit em Ambientes de Teste] → Mitigação: Desativar ou aumentar thresholds do RateLimiter durante a execução das suítes Pest (`RateLimiter::none()`).
