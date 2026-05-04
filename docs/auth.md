# Authentication

## Overview

Phase 2 backend authentication is implemented in the Laravel API using Laravel Sanctum session authentication.

Implemented auth features:

- User registration
- Login and logout
- Current authenticated user endpoint
- Disabled-user blocking with `users.disabled_at`
- Email verification
- Password reset
- Auth feature tests

Frontend auth pages are not implemented yet.

## Endpoints

| Method | Path | Auth | Purpose |
| --- | --- | --- | --- |
| `POST` | `/api/register` | Public | Register a user and send email verification. |
| `POST` | `/api/login` | Public | Log in with email and password. |
| `POST` | `/api/logout` | Authenticated | Log out and invalidate the session. |
| `GET` | `/api/me` | Authenticated | Return safe current-user data. |
| `GET` | `/api/verify-email/{id}/{hash}` | Signed link | Verify a user's email address. |
| `POST` | `/api/email/verification-notification` | Authenticated | Resend email verification notification. |
| `POST` | `/api/forgot-password` | Public | Send password reset email. |
| `POST` | `/api/reset-password` | Public | Reset password with a valid token. |

Sanctum also exposes:

```txt
GET /sanctum/csrf-cookie
```

SPA clients should call the CSRF cookie endpoint before making state-changing authenticated requests.

## Session Auth

The API uses Sanctum's cookie/session-based SPA authentication flow.

Local development uses:

```txt
Frontend: http://localhost:3000
Backend:  http://localhost:8000
```

The Docker API service configures Sanctum stateful domains for localhost development. Frontend permission checks are only for user experience; backend routes remain the enforcement point.

## Disabled Users

Disabled users are represented by nullable `users.disabled_at`.

Behavior:

- Disabled users cannot log in.
- Already-authenticated users who become disabled are blocked from protected auth routes.
- Disabled authenticated users are logged out when blocked.

## Email Verification

The `User` model uses Laravel's standard `MustVerifyEmail` support.

Behavior:

- Registration sends a verification email.
- Verification links are signed Laravel URLs.
- Valid verification links mark `email_verified_at`.
- Authenticated unverified users can request another verification email.

Local emails are captured by Mailpit:

```txt
http://localhost:8025
```

## Password Reset

Password reset uses Laravel's standard password broker and `password_reset_tokens` table.

Behavior:

- Users can request a password reset email.
- Valid reset tokens update the stored password.
- New passwords are hashed by Laravel.
- Invalid reset tokens are rejected.

## Demo Users

Seeded demo users use the password `password`:

```txt
admin@demo.test
teamlead@demo.test
senior@demo.test
mid@demo.test
junior@demo.test
viewer@demo.test
```

## Tests

Run backend tests from the repository root:

```bash
docker compose exec api ./vendor/bin/pest
```

Auth coverage lives in:

```txt
apps/api/tests/Feature/AuthRoutesTest.php
```

Covered areas include registration, login, logout, `/api/me`, protected-route rejection, disabled-user handling, email verification, password reset, validation errors, and sensitive-field exclusions.

## Out Of Scope

Phase 2 auth does not include:

- Frontend auth pages
- Team/project/task CRUD
- Permission policies
- Admin dashboard
- Production deployment
