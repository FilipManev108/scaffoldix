# AGENTS.md

## Scope

This file applies to Laravel API work in `apps/api`.

The root repository instructions in `../../AGENTS.md` also apply.

## Current API Status

The Laravel API currently includes:

- health endpoint
- shared API response helper
- Sanctum authentication
- register, login, logout, authenticated user, email verification, and password reset endpoints
- disabled-user access blocking
- workspace endpoints
- team endpoints
- team membership endpoints
- project endpoints
- project membership endpoints
- task status endpoints
- task endpoints
- comment endpoints
- domain authorization feature tests

Do not assume planned behavior exists unless the code or documentation confirms it.

## Access Model

Current protected routes use:

- `auth:sanctum`
- `not.disabled`

Current domain access rules:

- Workspaces are the top-level domain scope.
- A user can access a workspace when they belong to at least one team inside that workspace.
- Teams belong to workspaces.
- Projects belong to workspaces.
- Task statuses belong to projects.
- Tasks belong to projects.
- Comments belong to tasks.
- Nested route access must verify both user access and parent-child ownership.
- Comment update and delete are author-only.
- Team membership is managed through `team_user`.
- Project membership is managed through `project_user`.
- The `team_user_role` pivot is reserved for role assignment work and must not be used for basic membership endpoints.
- Full role and permission matrix enforcement is planned but not implemented yet.

## Laravel API Conventions

- Keep controllers thin.
- Put request validation in Form Request classes.
- Use API Resources for structured resource responses.
- Use `App\Support\ApiResponse` for consistent JSON responses when it fits the task.
- Keep business logic out of controllers when it grows beyond simple orchestration.
- Preserve existing namespaces, route style, model names, and project conventions.
- Do not rename routes, route parameters, models, tables, or columns unless explicitly requested.
- Do not use planning labels in production filenames, test filenames, class names, helper names, or route names.
- Do not add broad service layers, policies, gates, or permission abstractions unless explicitly requested.

## Authentication And Authorization

- Enforce authorization on the backend.
- Frontend checks are only user experience helpers.
- Any authentication or authorization behavior change must include relevant Pest tests.
- Do not implement role hierarchy or permission matrix behavior unless the task specifically asks for it.
- Keep current access rules simple and explicit until the permission system is implemented.
- Do not claim authentication, authorization, permission, policy, or workflow coverage unless matching tests exist.

## Validation And Responses

- Use Form Requests for request validation.
- Keep validation aligned with the current database schema.
- Do not invent request fields that are not supported by the schema.
- Prefer server-controlled values for ownership/scope fields such as `workspace_id`, `created_by`, and authenticated `user_id`.
- Use API Resources for response serialization.
- Do not expose sensitive user fields such as passwords, remember tokens, disabled status, permissions, or internal role data unless explicitly required.

## Database Rules

- Do not edit already-merged migrations unless explicitly requested.
- For schema changes after merge, create a new migration.
- Keep workspace scoping in mind.
- Do not add soft deletes to pivot tables unless explicitly requested.
- Do not use `team_user_role` for basic membership behavior.
- Validate nested resources against their parent scope.

## Testing

Backend tests use Pest.

Add focused feature or unit tests for new backend behavior.

Before finishing backend work, run:

```bash
docker compose exec api ./vendor/bin/pest
```

When routes are added or changed, run:

```bash
docker compose exec api php artisan route:list
```

Do not claim test coverage unless matching tests exist.

## Docker Commands

Run Laravel, Composer, Artisan, and Pest commands inside the `api` container.

Use:

```bash
docker compose exec api php artisan ...
docker compose exec api composer ...
docker compose exec api ./vendor/bin/pest
```

Do not rely on host PHP or host Composer as the runtime source of truth.
