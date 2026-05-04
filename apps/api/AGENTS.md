# AGENTS.md

## Scope

This file applies to Laravel API work in `apps/api`.

Phase 2 backend scope is authentication only:

- Laravel Sanctum login/logout flow
- `GET /api/me` authenticated user endpoint
- backend validation, responses, authorization checks, and tests for auth

Out of scope for Phase 2:

- frontend work
- team/project/task CRUD
- admin dashboard
- unrelated refactors

## Laravel API Conventions

- Keep controllers thin.
- Put request validation in Form Request classes when validation is introduced.
- Use `App\Support\ApiResponse` for consistent JSON responses when it fits the task.
- Use API Resources where structured response transformation is useful.
- Keep business logic out of controllers when it grows beyond simple orchestration.
- Preserve existing namespaces, route style, model names, and project conventions.

## Authentication And Permissions

- Do not assume auth, policies, gates, or permission enforcement already exist unless the code shows they do.
- Enforce authorization on the backend. Frontend checks are only user experience helpers.
- Any auth or permission behavior change must include relevant Pest tests.
- Do not add broad permission architecture unless explicitly requested.

## Testing

- Backend tests use Pest.
- Add focused feature or unit tests for new backend behavior.
- Do not claim auth, permission, policy, or workflow coverage unless matching tests exist.
- Before finishing backend work, run:

```bash
docker compose exec api ./vendor/bin/pest
```

## Docker Commands

Run Laravel, Composer, Artisan, and Pest commands inside the `api` container.

Use:

```bash
docker compose exec api php artisan ...
docker compose exec api composer ...
docker compose exec api ./vendor/bin/pest
```

Do not rely on host PHP or host Composer as the runtime source of truth.
