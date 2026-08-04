# article-engagement Specification

## Purpose
System for article likes (toggle) and nested single-level comments with admin moderation and strict state checks.

## Requirements

### Requirement: Idempotent Article Liking (Toggle)
Authenticated users MUST be able to toggle likes on `published` articles. Sending consecutive toggle requests SHALL alternate between liked and unliked states cleanly without duplicate records.

#### Scenario: User likes a published article
- **WHEN** an authenticated user sends POST to `/api/v1/articles/{id}/like` for an unliked published article
- **THEN** the system adds a like record and returns 200 OK with updated like counts.

#### Scenario: User unlikes a previously liked article
- **WHEN** an authenticated user sends POST to `/api/v1/articles/{id}/like` for an already liked article
- **THEN** the system removes the like record and returns 200 OK with updated like counts.

#### Scenario: Attempting to like draft or archived article
- **WHEN** a user attempts to like a `draft` or `archived` article
- **THEN** the request fails with 422 Unprocessable Entity or 403 Forbidden.

### Requirement: Article Comments and Single-Level Replies
Authenticated users MUST be able to comment on `published` articles and reply to existing top-level comments up to 1 level of nesting depth.

#### Scenario: Posting a top-level comment
- **WHEN** an authenticated user submits a valid comment payload for a published article
- **THEN** the system persists the comment and returns 201 Created.

#### Scenario: Replying to a top-level comment
- **WHEN** an authenticated user submits a reply payload referencing a top-level comment ID (`parent_id`)
- **THEN** the reply is attached with `parent_id` and nested in responses.

#### Scenario: Attempting to reply to a child reply (depth > 1)
- **WHEN** a user attempts to send a reply referencing a comment that already has a `parent_id`
- **THEN** the system rejects the request enforcing maximum 1 level of nested thread depth.

### Requirement: Comment Moderation and Ownership Editing
Authors MUST be able to edit and soft-delete their own comments. `ADMIN` users SHALL be able to soft-delete or moderate any comment.

#### Scenario: Author edits their own comment
- **WHEN** the author of a comment submits an update to `/api/v1/comments/{id}`
- **THEN** the comment text is updated and returned.

#### Scenario: Non-author common user attempts to delete another user's comment
- **WHEN** a user who is not the author or an `ADMIN` attempts to delete a comment
- **THEN** the request is rejected with 403 Forbidden.

#### Scenario: Admin deletes any comment
- **WHEN** an `ADMIN` sends a DELETE request for any comment ID
- **THEN** the comment is soft-deleted and a 204 No Content response is returned.
