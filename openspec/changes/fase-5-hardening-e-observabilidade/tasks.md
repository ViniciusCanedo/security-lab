## 1. Rate Limiting e Seguranca

- [ ] 1.1 Configurar rate limiters semânticos (`auth-limiter`, `comments-limiter`, `newsletter-limiter`) em `AppServiceProvider`
- [ ] 1.2 Aplicar middleware `throttle` nas rotas sensíveis de `routes/api.php`

## 2. Observabilidade e Logs Estruturados de Auditoria

- [ ] 2.1 Configurar canal `audit` com `JsonFormatter` em `config/logging.php`
- [ ] 2.2 Criar serviço/listener de auditoria disparado em exclusões de artigo, remoções de usuários e alterações de papel/permissão

## 3. Privacidade nos Resources e Documentacao OpenAPI

- [ ] 3.1 Revisar `UserResource`, `ArticleResource`, `CommentResource` aplicando `$this->when()` para vazamento zero de e-mails de terceiros
- [ ] 3.2 Instalar e configurar `dedoc/scramble` para servir documentação OpenAPI em `/docs/api`

## 4. Auditoria de Cobertura e CI

- [ ] 4.1 Rodar Pest com flag `--coverage --min=80` validando cobertura mínima em `app/Services` e `app/Http/Controllers`
- [ ] 4.2 Rodar Larastan e Pint garantindo zero violações antes da homologação final
