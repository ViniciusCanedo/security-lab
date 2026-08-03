## Why

Implementar a fase de fortalecimento de segurança, auditoria completa, proteção contra abuso via rate limiting avançado, ocultação rigorosa de dados sensíveis em Resources, documentação interativa da API via Scramble/OpenAPI e auditoria de cobertura de testes (mínimo 80% em Services/Controllers).

## What Changes

- **Rate Limiting Avançado**:
  - Configuração de limitadores de taxa semânticos via `RateLimiter::for()` no `AppServiceProvider` para rotas de autenticação, comentários, curtidas e submissões de formulário.
- **Auditoria de Ações Sensíveis**:
  - Log estruturado (JSON) com dados contextuais (`actor_id`, `action`, `ip`, `payload`, `timestamp`) para operações críticas: exclusão de artigo, elevação/alteração de papéis, remoção de usuários e concessão de permissões avulsas.
- **Mascaramento de Dados Sensíveis nos Resources**:
  - Uso rigoroso de `$this->when()` nos API Resources garantindo que e-mails de outros usuários e metadados de auditoria só sejam expostos para o próprio usuário ou para papéis `ADMIN`.
- **Documentação OpenAPI/Swagger (Scramble)**:
  - Instalação e geração de OpenAPI 3.0 via `dedoc/scramble` em `/docs/api`.
- **Validação de Cobertura e Qualidade**:
  - Auditoria completa de cobertura Pest/PHPUnit atingindo >= 80% em `app/Services` e `app/Http/Controllers`, sem alertas do Larastan Nível 5+.

## Capabilities

### New Capabilities
- `hardening-and-observability`: Middleware de rate limit por rota, logging estruturado de auditoria, sanitização de respostas em API Resources, geração de docs OpenAPI e verificações estritas de cobertura.

### Modified Capabilities

## Impact

- `AppServiceProvider` configurando rate limiters.
- `AuditLogMiddleware` ou listeners de eventos gravando logs estruturados via `Log::channel('audit')`.
- API Resources revisados (`UserResource`, `ArticleResource`, `CommentResource`).
- Adição da rota pública/restrita de documentação Swagger/Scramble (`/docs/api`).
- Configuração de scripts de análise de cobertura em CI.
