# project-foundation Specification

## Purpose
TBD - created by archiving change fase-0-fundacao. Update Purpose after archive.
## Requirements
### Requirement: Architecture and Layer Separation
The application MUST strictly separate concerns across HTTP Controllers, Form Requests, DTOs, Services, Repositories, Models, and JSON Resources. Controllers SHALL NOT query the database directly or execute business logic.

#### Scenario: Controller delegates to Service layer via DTO
- **WHEN** an HTTP request is received by a Controller
- **THEN** the request parameters are validated by a Form Request, mapped into an immutable DTO, passed to a Service layer, and returned through a JSON Resource.

### Requirement: Role-Based Access Control (RBAC) Seeding
The system MUST initialize default roles (`ADMIN`, `PUBLISHER`, `COMMON`) and assign granular permission sets using Spatie Laravel-Permission.

#### Scenario: Seeding roles and permissions on initial setup
- **WHEN** the DatabaseSeeder is executed
- **THEN** the roles `ADMIN`, `PUBLISHER`, and `COMMON` are created in the database alongside their mapped permissions matrix.

### Requirement: Asynchronous Queue Separation
The application MUST configure dedicated queue channels for transactional emails (`emails`) and mass newsletters (`newsletter`).

#### Scenario: Queue routing configuration
- **WHEN** a job for transactional email or newsletter dispatch is queued
- **THEN** the job is assigned to its respective queue (`emails` or `newsletter`) without blocking HTTP request execution.

### Requirement: Static Analysis and Code Quality Automated Checks
The repository MUST enforce strict linting with Laravel Pint, static type checking with Larastan, and automated tests via Pest in CI.

#### Scenario: Running automated quality pipeline
- **WHEN** the CI pipeline runs on codebase changes
- **THEN** Pint checks formatting, Larastan validates type safety without errors, and Pest executes all test suites.

