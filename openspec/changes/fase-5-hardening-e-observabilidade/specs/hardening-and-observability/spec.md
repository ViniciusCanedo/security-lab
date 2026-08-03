## ADDED Requirements

### Requirement: Rate Limiting Enforcement on Sensitive Routes
The API MUST enforce custom rate limits on sensitive actions (authentication attempts, password reset requests, comment creations, and newsletter subscriptions).

#### Scenario: Exceeding rate limit on login endpoint
- **WHEN** a client sends more than the permitted number of login requests per minute
- **THEN** the system blocks subsequent requests with a 429 Too Many Requests status code.

### Requirement: Structured Audit Logging for Sensitive Actions
The system MUST produce structured JSON logs for all sensitive operations (user deletion, role elevation, article deletion, permission overrides).

#### Scenario: Role elevation produces audit record
- **WHEN** an `ADMIN` changes a user's role
- **THEN** a structured log entry containing actor ID, target user ID, timestamp, IP address, and role change delta is written to the audit log.

### Requirement: Conditional Data Privacy in API Resources
The API Resources MUST obscure private fields (such as user email addresses or internal metadata) unless the requesting party is the owner of the resource or an `ADMIN`.

#### Scenario: Common user views another user's profile or comment author info
- **WHEN** a `COMMON` user requests profile information of another author
- **THEN** sensitive fields like email address are excluded from the returned JSON response.

### Requirement: OpenAPI Interactive Documentation
The system MUST auto-generate up-to-date OpenAPI 3.0 specification documentation accessible at `/docs/api`.

#### Scenario: Accessing API documentation page
- **WHEN** an authorized client accesses `/docs/api`
- **THEN** an interactive OpenAPI documentation interface detailing endpoints, parameters, and responses is rendered.

### Requirement: Minimum Code Coverage Threshold
The test suite MUST enforce a minimum of 80% code coverage across `app/Services` and `app/Http/Controllers`.

#### Scenario: Running test coverage check
- **WHEN** Pest coverage check is run
- **THEN** test coverage for Services and Controllers meets or exceeds the 80% threshold without static analysis errors.
