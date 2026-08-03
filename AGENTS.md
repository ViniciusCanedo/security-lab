# AGENTS.md — Blog API (Laravel)

> Este documento define as regras **inegociáveis** de arquitetura, qualidade e processo do projeto.
> Qualquer Pull Request que viole um item marcado como `[MUST]` deve ser rejeitado no code review,
> independentemente de "funcionar".

---

## 1. Princípios Fundamentais

1. **TDD é obrigatório (`[MUST]`).** Nenhuma funcionalidade é implementada sem que o teste correspondente exista primeiro e falhe (Red → Green → Refactor). PRs sem testes cobrindo os casos felizes **e** de erro/autorização não são aceitos.
2. **Separação estrita de camadas (`[MUST]`).** Controllers nunca contêm regra de negócio, nunca fazem query direta ao Eloquent e nunca fazem validação manual de input.
3. **Nenhuma lógica de negócio no Model (`[MUST]`).** Models contêm apenas relacionamentos, casts, scopes simples, accessors/mutators e regras de mass assignment. Regras de negócio vivem na camada de Service.
4. **Fail-safe por padrão em permissões (`[MUST]`).** Na dúvida, o sistema nega acesso. Toda rota protegida deve ter policy/gate/permission explícita — não existe rota "esquecida como pública".
5. **Consistência de contrato de API (`[MUST]`).** Toda resposta de sucesso e erro segue o mesmo formato (ver seção 7). Nenhum endpoint retorna Models ou Collections "cru".

---

## 2. Arquitetura em Camadas

Fluxo de uma requisição (obrigatório, nesta ordem):

```
Rota → Middleware (auth/permissão) → Form Request (validação) → Controller (fino)
     → Service (regra de negócio) → Repository (acesso a dados) → Model/Eloquent
     → DTO (transporte de dados entre camadas)
     → API Resource (formatação da resposta)
```

### 2.1 Controllers (`app/Http/Controllers`)
- `[MUST]` Apenas orquestram: recebem o Form Request já validado, chamam o Service, retornam um Resource.
- `[MUST]` Não contêm `if/else` de regra de negócio, nem `try/catch` de lógica de domínio (exceptions de domínio são tratadas no `Handler` global ou em Service).
- `[SHOULD]` Ter no máximo ~15 linhas por método.
- `[MUST NOT]` Injetar Models diretamente para manipulação — apenas Services.

### 2.2 Form Requests (`app/Http/Requests`)
- `[MUST]` Toda entrada de dado externo passa por um Form Request dedicado (um por ação: `StorePostRequest`, `UpdatePostRequest`, etc.).
- `[MUST]` O método `authorize()` do Form Request pode delegar para Policies, mas a decisão final de autorização de negócio granular fica também garantida por Policy/Gate na camada de Service quando aplicável (defesa em profundidade).
- `[MUST]` Mensagens de erro em português, padronizadas.

### 2.3 DTOs (`app/DTOs`)
- `[MUST]` Toda troca de dados entre Controller → Service → Repository usa DTOs imutáveis (readonly properties, PHP 8.1+).
- `[MUST]` DTOs são construídos a partir do Form Request (`::fromRequest()`) e nunca expõem o `Request` para camadas internas.
- `[MUST NOT]` Passar arrays soltos (`$data['title']`) entre Service e Repository — sempre um DTO tipado.

### 2.4 Services (`app/Services`)
- `[MUST]` Contêm toda a regra de negócio: regras de publicação/arquivamento, granularidade de permissões, orquestração de jobs assíncronos, disparo de eventos.
- `[MUST]` Um Service por domínio/agregado (`ArticleService`, `CommentService`, `NewsletterService`, `AuthService`, `PermissionService`, `UserManagementService`).
- `[MUST]` Services dependem de **interfaces** de Repository, nunca de implementações concretas (Dependency Inversion via Service Container).
- `[MUST]` Lançam Exceptions de domínio customizadas (`ArticleAlreadyArchivedException`, `InsufficientPermissionException`) — nunca `abort()` direto dentro do Service.

### 2.5 Repositories (`app/Repositories`)
- `[MUST]` Toda query Eloquent vive exclusivamente dentro de Repositories.
- `[MUST]` Contrato definido por Interface (`app/Repositories/Contracts`), implementação concreta com Eloquent (`app/Repositories/Eloquent`), registrado no `RepositoryServiceProvider`.
- `[MUST]` Repository retorna Models ou Collections do Eloquent — a conversão para DTO/Resource acontece fora dele (no Service ou Controller, conforme o caso).
- `[MUST NOT]` Conter regra de negócio (ex.: "só pode arquivar se for autor" fica no Service, não no Repository).

### 2.6 JSON Resources (`app/Http/Resources`)
- `[MUST]` Toda resposta HTTP de sucesso passa por um Resource (`ArticleResource`, `CommentResource`, `UserResource`, etc.), inclusive em listagens paginadas (`Resource::collection()`).
- `[MUST]` Resources controlam exposição condicional de campos por permissão (`$this->when(...)`), ex.: e-mail do usuário só visível para ADMIN ou o próprio usuário.

### 2.7 Models (`app/Models`)
- `[MUST]` `$fillable` explícito (nunca `$guarded = []`).
- `[MUST]` Soft Deletes em `Article`, `Comment`, `User` (auditoria e "arquivamento" ≠ exclusão física).
- `[MUST]` Enums nativos do PHP para `role` (`UserRole`), `status` do artigo (`ArticleStatus`), etc.

---

## 3. Autenticação e Autorização

1. `[MUST]` Autenticação via **Laravel Sanctum** (tokens pessoais para API stateless — SPA/mobile).
2. `[MUST]` Login social via **Laravel Socialite** (Google), com criação/vínculo automático de conta por e-mail.
3. `[MUST]` Reset de senha via **magic link** assinado (`URL::temporarySignedRoute`) enviado por e-mail, com expiração configurável (padrão 60 min) e uso único (invalidação de token após uso).
4. `[MUST]` Autorização em duas camadas complementares:
   - **Gates/Policies** do Laravel para autorização por recurso (`ArticlePolicy`, `CommentPolicy`, `UserPolicy`).
   - **Sistema de permissões granular** (RBAC + permission-based) usando um pacote como `spatie/laravel-permission`, permitindo permissões finas (`article.create`, `article.edit.own`, `article.edit.any`, `article.archive`, `article.delete`, `user.invite`, `user.remove`, `user.promote`, `comment.moderate`, etc.) atribuíveis por role e, opcionalmente, override individual por usuário.
5. `[MUST]` As 3 roles (`ADMIN`, `PUBLISHER`, `COMMON`) são **seeds** de conjuntos de permissões, não hardcode de `if ($user->role === 'admin')` espalhado pelo código — a checagem é sempre via `can()`/`authorize()`.
6. `[MUST]` Elevação de permissão (mudança de role ou concessão de permissão avulsa) é ação exclusiva de `ADMIN`, auditada (log de quem, quando, o quê).
7. `[MUST NOT]` Nenhuma rota de escrita (`POST/PUT/PATCH/DELETE`) sem middleware de autenticação Sanctum, exceto as explicitamente públicas (registro, login, callback social, request de reset).

## 3.1 Matriz de Permissões (referência rápida)

| Ação | COMMON | PUBLISHER | ADMIN |
|---|---|---|---|
| Ver artigos publicados | ✅ | ✅ | ✅ |
| Curtir artigo | ✅ | ✅ | ✅ |
| Comentar / responder comentário | ✅ | ✅ | ✅ |
| Criar artigo | ❌ | ✅ | ✅ |
| Editar artigo (próprio) | ❌ | ✅ | ✅ |
| Editar artigo (de outros) | ❌ | ❌ | ✅ |
| Arquivar artigo | ❌ | ✅ (próprio) | ✅ (qualquer) |
| Excluir artigo | ❌ | ❌ | ✅ |
| Moderar/excluir comentário de terceiro | ❌ | ❌ | ✅ |
| Adicionar/remover usuário | ❌ | ❌ | ✅ |
| Elevar permissões | ❌ | ❌ | ✅ |
| Gerenciar newsletter (campanhas) | ❌ | ✅ | ✅ |

---

## 4. Jobs, Filas e E-mails

1. `[MUST]` Todo envio de e-mail é assíncrono via `Queueable`/`ShouldQueue` — nenhum `Mail::send()` síncrono em request HTTP.
2. `[MUST]` Fila dedicada para e-mails transacionais (`emails`) separada da fila de newsletter em massa (`newsletter`), para não travar reset de senha atrás de milhares de envios de campanha.
3. `[MUST]` Jobs de newsletter em massa são despachados em chunks (`Bus::batch` ou `chunk()` + dispatch individual) para evitar sobrecarga e permitir retry granular.
4. `[MUST]` Jobs implementam `$tries`, `$backoff` e tratamento de falha (`failed()`) com log estruturado.
5. `[SHOULD]` Usar `database` ou `redis` como driver de fila (Redis recomendado em produção).

---

## 5. Testes (TDD)

1. `[MUST]` Ciclo Red-Green-Refactor obrigatório para toda feature nova.
2. `[MUST]` Cobertura mínima por funcionalidade:
   - Teste de **sucesso** (happy path).
   - Teste de **validação** (Form Request rejeita input inválido).
   - Teste de **autorização** (usuário sem permissão recebe 403; não autenticado recebe 401).
   - Teste de **regra de negócio** (Service) isolado, com Repository mockado/fake quando fizer sentido.
3. `[MUST]` Testes de feature (HTTP) usam `RefreshDatabase` + Factories, nunca dados fixos manuais no banco.
4. `[MUST]` Repositories têm testes de integração (Feature/Unit com banco em memória/sqlite ou banco de teste dedicado).
5. `[MUST]` Services têm testes unitários com Repositories mockados (interfaces facilitam isso).
6. `[SHOULD]` Cobertura mínima de 80% em `app/Services` e `app/Http/Controllers`.
7. `[MUST]` Testes de e-mail/jobs usam `Mail::fake()`, `Queue::fake()`, `Bus::fake()` — nunca disparam e-mail real em CI.
8. `[MUST]` Testes de login social (Socialite) usam `Socialite::shouldReceive()` mockado — nunca chamada real ao Google em testes.

---

## 6. Estrutura de Diretórios (referência)

```
app/
├── DTOs/
├── Enums/
├── Events/
├── Exceptions/
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Requests/
│   ├── Resources/
│   └── Middleware/
├── Jobs/
├── Listeners/
├── Mail/
├── Models/
├── Policies/
├── Providers/
├── Repositories/
│   ├── Contracts/
│   └── Eloquent/
└── Services/
tests/
├── Feature/
└── Unit/
```

---

## 7. Contrato de Resposta da API

**Sucesso:**
```json
{
  "data": { },
  "meta": { }
}
```

**Erro:**
```json
{
  "message": "Descrição legível do erro",
  "errors": { "campo": ["mensagem de validação"] }
}
```

- `[MUST]` Códigos HTTP semânticos: `200/201/204` sucesso, `401` não autenticado, `403` não autorizado, `404` não encontrado, `422` validação, `429` rate limit, `500` erro interno (nunca vazando stack trace em produção).

---

## 8. Versionamento e API

1. `[MUST]` Rotas versionadas: `routes/api.php` sob prefixo `/api/v1`.
2. `[MUST]` Todas as rotas de mutação sujeitas a rate limiting (`throttle`).
3. `[SHOULD]` Documentação de API via OpenAPI/Swagger (ex.: `l5-swagger` ou `scramble`), gerada a partir das rotas/Form Requests/Resources.

---

## 9. Padrão de Commits e PRs

1. `[MUST]` Commits seguem Conventional Commits (`feat:`, `fix:`, `test:`, `refactor:`, `docs:`).
2. `[MUST]` PR só é aberto com os testes da feature já passando localmente e no CI.
3. `[MUST]` Nenhum PR mistura duas fases do roadmap (ver `PRD.md`) sem justificativa explícita.
4. `[MUST]` Uma tarefa só deve ser considerado concluída quando todos os testes passarem e nào houver erros reportados pelo Larastan.
5. `[MUST]` Nunca execute os comandos `git commit` ou `git push` sem aprovação.

---

## 10. Stack Técnica Fixada

| Camada | Tecnologia |
|---|---|
| Framework | Laravel 12.x (PHP 8.3+) |
| Autenticação API | Laravel Sanctum |
| OAuth Social | Laravel Socialite (Google) |
| Permissões | spatie/laravel-permission |
| Fila | Redis (produção) / database (dev) |
| Banco de dados | MySQL 8 / PostgreSQL 15 |
| Testes | Pest |
| Documentação API | Scramble |
| Formatação de código | Laravel Pint |
| Análise estática | Larastan (PHPStan) |

Este documento é vivo, mas alterações nas regras `[MUST]` exigem consenso explícito da equipe e atualização deste arquivo no mesmo PR.