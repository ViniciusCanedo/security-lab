## 1. Migracoes, Models e Enums

- [x] 1.1 Criar enum `ArticleStatus` (`draft`, `published`, `archived`) com métodos auxiliares de validação de transição
- [x] 1.2 Criar migration da tabela `articles` com `user_id`, `title`, `slug`, `summary`, `content`, `cover_image_url`, `status` e `softDeletes`
- [x] 1.3 Criar `Article` Model com relacionamentos e mass assignment `$fillable` explícito

## 2. DTOs, Contrato e Repositorio

- [x] 2.1 Criar `CreateArticleDTO`, `UpdateArticleDTO` e `ArticleQueryDTO` imutáveis
- [x] 2.2 Criar contrato `ArticleRepositoryInterface` e implementação `ArticleRepositoryEloquent`

## 3. Camada de Servico e Policy

- [x] 3.1 Criar `ArticleService` aplicando regras de transição de status e integrando com o Repositório
- [x] 3.2 Criar `ArticlePolicy` cobrindo a matriz completa de permissões para `COMMON`, `PUBLISHER` e `ADMIN`
- [x] 3.3 Criar `ArticleResource` e `ArticleCollection` com formatação padronizada

## 4. Controllers e Suite TDD

- [x] 4.1 Criar `PublicArticleController` para endpoints públicos (`/api/v1/articles` e `/api/v1/articles/{id}`)
- [x] 4.2 Criar `ArticleController` para endpoints autenticados de criação, edição, arquivamento e exclusão
- [x] 4.3 Escrever bateria de testes Pest cobrindo 100% da matriz de permissões e transições de estado
