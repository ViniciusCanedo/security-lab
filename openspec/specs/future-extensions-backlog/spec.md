# future-extensions-backlog Specification

## Purpose
TBD - created by archiving change fase-6-extensoes-futuras. Update Purpose after archive.
## Requirements
### Requirement: Author Notification on New Comment
The system MUST asynchronously notify an article author via email whenever a new comment is posted on their published article, unless the comment was posted by the author themselves.

#### Scenario: Comment triggers email notification to author
- **WHEN** a user posts a comment on an article owned by another author
- **THEN** a `SendCommentNotificationMailJob` is pushed to the `emails` queue addressed to the article author.

### Requirement: Multi-Provider Social OAuth Authentication
The system MUST support GitHub and Facebook social login alongside Google, reusing existing email matching logic without duplicating user accounts.

#### Scenario: User authenticates with GitHub OAuth
- **WHEN** a user completes GitHub OAuth login with an existing email
- **THEN** the system links the GitHub provider record in `social_accounts` and issues a Sanctum token.

### Requirement: Full-Text Search via Laravel Scout
The system MUST support full-text search across article titles, content, and tags using Laravel Scout.

#### Scenario: Full-text search for articles
- **WHEN** a search query is submitted to `/api/v1/articles/search?q=laravel`
- **THEN** relevant published articles matching the search term are returned sorted by relevance.

### Requirement: Article Reading Analytics
The system MUST track article view counts and estimate reading time in minutes based on total word count.

#### Scenario: Fetching article with metrics
- **WHEN** a user views a published article
- **THEN** the system increments view counts asynchronously and includes `reading_time_minutes` in the response payload.

### Requirement: Event Webhooks System
The system MUST allow external services to register webhook URLs to receive HTTP POST notifications upon specified system events (e.g. `article.published`).

#### Scenario: Webhook dispatched on article publication
- **WHEN** an article transitions to `published` status
- **THEN** a background job posts a JSON webhook payload to all active registered endpoints for `article.published`.

