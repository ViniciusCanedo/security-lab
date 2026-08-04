# article-management Specification

## Purpose
TBD - created by archiving change fase-2-artigos. Update Purpose after archive.
## Requirements
### Requirement: Article Creation and Initial Status
PUBLISHER and ADMIN users MUST be able to create articles with title, content, summary, cover image, and initial status `draft` or `published`. COMMON users SHALL be denied creation access.

#### Scenario: Publisher creates article draft
- **WHEN** an authenticated `PUBLISHER` posts valid article payload to `/api/v1/articles`
- **THEN** the system persists the article assigned to the publisher with status `draft` and returns 201 Created.

#### Scenario: Common user attempt to create article
- **WHEN** a `COMMON` user attempts to post to `/api/v1/articles`
- **THEN** the request fails with 403 Forbidden.

### Requirement: Article Ownership-Based Editing
The system MUST allow `PUBLISHER` users to edit only their own articles, while `ADMIN` users can edit any article.

#### Scenario: Publisher edits own article
- **WHEN** a `PUBLISHER` updates an article authored by themselves
- **THEN** the changes are saved and returned in an `ArticleResource`.

#### Scenario: Publisher attempts to edit another author's article
- **WHEN** a `PUBLISHER` attempts to update an article authored by another user
- **THEN** the request is rejected with a 403 Forbidden status code.

### Requirement: Article Archiving and Status Transitions
Valid status transitions MUST strictly follow `draft -> published -> archived` or `published -> archived`. Archived articles MUST NOT be returned in public listings.

#### Scenario: Archiving a published article
- **WHEN** an authorized user archives a `published` article
- **THEN** the article status transitions to `archived` and it no longer appears in public index results.

### Requirement: Admin-Only Article Deletion
Only `ADMIN` users SHALL be permitted to perform soft/hard deletion of articles.

#### Scenario: Non-admin user attempts deletion
- **WHEN** a `PUBLISHER` or `COMMON` user sends a DELETE request to `/api/v1/articles/{id}`
- **THEN** the request is denied with 403 Forbidden.

#### Scenario: Admin deletes an article
- **WHEN** an `ADMIN` sends a DELETE request for an existing article
- **THEN** the system executes soft deletion and returns 204 No Content.

### Requirement: Public Article Listing and Retrieval
The public API MUST return paginated listings of `published` articles with support for filtering by author, tag, and publication date.

#### Scenario: Unauthenticated visitor retrieves published articles list
- **WHEN** a request is made to GET `/api/v1/articles`
- **THEN** a 200 OK paginated collection containing only `published` articles is returned.

