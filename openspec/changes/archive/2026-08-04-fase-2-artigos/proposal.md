## Why

Implementar a gestão completa do ciclo de vida de Artigos (RF03) garantindo rígido controle de permissões por papel (`COMMON`, `PUBLISHER`, `ADMIN`), transições de status válidas (`draft`, `published`, `archived`), e listagens públicas filtradas e paginadas.

## What Changes

- **RF03 — Artigos**:
  - `POST /api/v1/articles`: Criação de artigo por PUBLISHER ou ADMIN (status inicial `draft` ou publicado diretamente se autorizado).
  - `PUT/PATCH /api/v1/articles/{id}`: Edição de artigo (PUBLISHER edita os seus próprios; ADMIN edita qualquer artigo).
  - `POST /api/v1/articles/{id}/archive`: Arquivamento de artigo (PUBLISHER arquiva o seu; ADMIN arquiva qualquer um).
  - `DELETE /api/v1/articles/{id}`: Exclusão física/soft delete definitiva (restrito exclusivamente a ADMIN).
  - `GET /api/v1/articles`: Listagem pública paginada apenas de artigos com status `published` (filtros por tag, categoria, autor, data).
  - `GET /api/v1/articles/{id}`: Visualização detalhada de um artigo publicado.
- **Enum `ArticleStatus`**: Estados estritos `draft`, `published`, `archived`.

## Capabilities

### New Capabilities
- `article-management`: Operações de publicação, edição, transição de status, arquivamento, exclusão e consulta de artigos com matriz de acesso detalhada.

### Modified Capabilities

## Impact

- Tabela `articles` com `title`, `slug`, `summary`, `content`, `cover_image_url`, `status`, `author_id`, `softDeletes`.
- `ArticleService`, `ArticleRepositoryInterface`, `ArticleRepositoryEloquent`.
- `CreateArticleDTO`, `UpdateArticleDTO`, `ArticleFilterDTO`.
- `ArticleResource` e `ArticleCollection`.
- `ArticlePolicy` aplicando autorização granular Spatie/Gates.
