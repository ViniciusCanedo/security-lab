## ADDED Requirements

### Requirement: User Registration with Default Common Role
The system MUST allow users to register with name, email, and password. New accounts SHALL be assigned the `COMMON` role by default and trigger an asynchronous welcome email.

#### Scenario: Successful local registration
- **WHEN** a valid registration request with name, unique email, and confirmed password is submitted to `/api/v1/auth/register`
- **THEN** the system creates the user, assigns the `COMMON` role, queues a welcome email job, and returns a 201 response with the Sanctum token.

### Requirement: Sanctum Authentication & Token Management
The system MUST authenticate valid credentials, return a bearer token, and support token revocation upon logout.

#### Scenario: Successful login with valid credentials
- **WHEN** correct email and password credentials are posted to `/api/v1/auth/login`
- **THEN** the system issues a Sanctum personal access token with a 200 HTTP status code.

#### Scenario: Logout revokes active token
- **WHEN** an authenticated user sends a POST request to `/api/v1/auth/logout` with a valid Bearer token
- **THEN** the current token is revoked and a 204 No Content response is returned.

### Requirement: Google Social OAuth Login
The system MUST allow users to authenticate using Google OAuth via Laravel Socialite, linking existing accounts by email or creating new `COMMON` accounts.

#### Scenario: Social login creates account if missing
- **WHEN** a user completes Google OAuth callback with a new email address
- **THEN** the system creates a user with `COMMON` role, links the Google provider ID, and issues a Sanctum token.

### Requirement: Password Reset via Signed Magic Link
The system MUST issue temporary signed URL links for password resets dispatched asynchronously via email, enforcing single-use link consumption.

#### Scenario: Requesting password reset magic link
- **WHEN** an existing user requests a password reset link for their email
- **THEN** a signed URL with 60-minute expiration is generated and queued for email delivery without blocking HTTP response.

#### Scenario: Resetting password using magic link
- **WHEN** a valid signed reset token and new password are submitted to `/api/v1/auth/password/reset`
- **THEN** the user's password is updated, the reset token is invalidated, and a success message is returned.

### Requirement: Authenticated User Profile Information (/me)
The system MUST provide an endpoint `/api/v1/me` returning the current user profile alongside their effective permissions.

#### Scenario: Fetching authenticated user info
- **WHEN** an authenticated user calls GET `/api/v1/me`
- **THEN** the system returns user details and permission list in a standardized JSON Resource format.
