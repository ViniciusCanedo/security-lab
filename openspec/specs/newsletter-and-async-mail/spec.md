# newsletter-and-async-mail Specification

## Purpose
System for newsletter subscription with double opt-in, one-click unsubscribe, mass campaign async batch dispatch, and transactional emails.

## Requirements

### Requirement: Newsletter Subscription with Double Opt-In
The system MUST allow visitors to subscribe to the newsletter by providing an email. Subscriptions SHALL remain in `pending` state until verified via a double opt-in confirmation link sent asynchronously.

#### Scenario: Visitor submits email for newsletter subscription
- **WHEN** a visitor posts an email to `/api/v1/newsletter/subscribe`
- **THEN** a subscriber record is created with `pending` status and a confirmation email job is pushed to the `emails` queue.

#### Scenario: Subscriber clicks confirmation link
- **WHEN** a user accesses `/api/v1/newsletter/confirm` with a valid confirmation token
- **THEN** the status updates to `confirmed` and a 200 OK success response is returned.

### Requirement: One-Click Unsubscribe
Every newsletter email MUST include a unique unsubscribe link allowing immediate cancellation without login requirements.

#### Scenario: User clicks unsubscribe link
- **WHEN** a GET/POST request is received at `/api/v1/newsletter/unsubscribe` with a valid token
- **THEN** the subscriber status is marked as `unsubscribed` and no further emails are dispatched to them.

### Requirement: Asynchronous Mass Newsletter Campaign Dispatch
`PUBLISHER` and `ADMIN` users MUST be able to create and dispatch newsletter campaigns to all `confirmed` subscribers using batched background jobs (`Bus::batch`) on the dedicated `newsletter` queue.

#### Scenario: Publishing and dispatching a campaign
- **WHEN** an authorized user triggers dispatch for a draft newsletter campaign
- **THEN** the campaign status becomes `sending`, subscribers are chunked into batch jobs, and queued on the `newsletter` queue.

### Requirement: Fault Isolation and Retry Strategy
A failure in dispatching to an individual recipient MUST NOT interrupt or cancel the remaining batch jobs. Failed dispatches SHALL record error details and support individual retry with backoff.

#### Scenario: Single subscriber mail delivery failure
- **WHEN** mail dispatch fails for a single recipient in a batch
- **THEN** the job logs the error in `newsletter_sends`, increments retry attempts according to backoff policy, and allows other batch jobs to complete.

### Requirement: Campaign Delivery Reporting
`PUBLISHER` and `ADMIN` users MUST be able to query campaign execution status reporting count of sent, failed, and pending deliveries.

#### Scenario: Querying campaign status
- **WHEN** an authorized user queries `/api/v1/admin/newsletter/campaigns/{id}/status`
- **THEN** a summary JSON containing total subscribers, sent count, failed count, and pending count is returned.
