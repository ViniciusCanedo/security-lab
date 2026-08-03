## ADDED Requirements

### Requirement: User Management by ADMIN
The system MUST allow `ADMIN` users to list, filter, invite/create, and soft-delete user accounts.

#### Scenario: Admin listing users with filters
- **WHEN** an authenticated `ADMIN` queries `/api/v1/admin/users` with role/search parameters
- **THEN** a paginated JSON response containing matching users is returned.

#### Scenario: Non-admin attempt to list users
- **WHEN** a `COMMON` or `PUBLISHER` user attempts to access `/api/v1/admin/users`
- **THEN** the system denies access with a 403 Forbidden status code.

### Requirement: Role Elevation and Audit Logging
The system MUST restrict role modification to `ADMIN` users and produce an audit log entry for every role transition.

#### Scenario: Admin updates user role
- **WHEN** an `ADMIN` changes a user's role from `COMMON` to `PUBLISHER` via `/api/v1/admin/users/{id}/role`
- **THEN** the role is updated, permissions are refreshed, and an audit record is logged with actor, target, timestamp, old role, and new role.

### Requirement: Granular Permission Overrides
The system MUST permit `ADMIN` users to grant or revoke explicit individual permissions for a specific user.

#### Scenario: Assigning custom permission override
- **WHEN** an `ADMIN` grants an explicit permission to a user
- **THEN** the user gains that effective permission independently of their assigned role.
