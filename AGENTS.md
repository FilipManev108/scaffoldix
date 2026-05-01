# AGENTS.md

## Project

ScaffoldIX is a Laravel + Next.js monorepo for a Jira-inspired project management platform for small software teams.

The project is built as a portfolio application focused on:

- Laravel API architecture
- Next.js frontend architecture
- MySQL database design
- role-based and permission-based authorization
- team/project/task workflows
- Dockerized local development
- testing
- technical documentation
- AI-assisted development workflow

## Repository Structure

- `apps/api` — Laravel API backend
- `apps/web` — Next.js frontend
- `docs` — project documentation
- `docker` — Docker-related files
- `docker-compose.yml` — local development services
- `README.md` — main project overview and setup entry point

## Current Phase Status

Phase 1 backend foundation is complete.

Implemented backend foundation includes:

- Laravel API route foundation
- `GET /api/health`
- `App\Support\ApiResponse`
- core database migrations
- Eloquent models and relationships
- factories
- seeders
- Pest setup
- backend smoke tests
- Phase 1 documentation

Not implemented yet:

- authentication
- Laravel Sanctum login/logout flow
- controllers for domain resources
- form requests
- API resources
- policies
- permission enforcement
- frontend dashboard workflows

Do not assume these planned pieces already exist.

## General Rules

- Inspect existing files before editing.
- Only edit files relevant to the requested task.
- Do not make broad architectural changes unless explicitly requested.
- Do not add new packages unless explicitly requested.
- Do not rename existing files, classes, branches, tables, or routes unless explicitly requested.
- Prefer simple, readable code over clever abstractions.
- Preserve existing project conventions.
- Keep changes small and focused.
- Do not commit changes unless explicitly asked.
- Ask for clarification when a task is ambiguous instead of guessing.

## Documentation Rules

When changing architecture, update:

- `docs/architecture.md`

When changing database structure, update:

- `docs/database.md`

When changing setup or Docker workflow, update:

- `README.md`
- `docs/setup.md`

When changing testing setup or test strategy, update:

- `docs/testing.md`

When changing permissions or authorization rules, update:

- `docs/permissions.md`

Do not invent implemented features in documentation. If something is planned but not built, label it as planned.

## Backend Rules

Laravel backend lives in:

- `apps/api`

Run Laravel, Composer, and Pest commands inside the API container.

Use:

```bash
docker compose exec api php artisan ...
docker compose exec api composer ...
docker compose exec api ./vendor/bin/pest
```

Do not assume host PHP or host Composer is the runtime source of truth.

## Laravel Code Rules

- Keep controllers thin.
- Use Form Request classes for validation when request validation is introduced.
- Use API Resources for structured resource responses when domain endpoints are introduced.
- Use Policies/Gates for authorization when permissions are introduced.
- Do not put complex business logic in controllers.
- Do not duplicate response formatting manually when `App\Support\ApiResponse` fits the task.
- Do not implement frontend-only security.
- Backend authorization must be enforced server-side.

## Database Rules

- Do not edit already-merged migrations unless explicitly requested.
- For schema changes after merge, create a new migration.
- Keep workspace scoping in mind.
- Roles are workspace-scoped.
- Permissions are global permission definitions.
- Team/user/role assignment is represented through `team_user_role`.
- Do not add soft deletes to pivot tables unless explicitly requested.

## Testing Rules

Before finishing backend work, run:

```bash
docker compose exec api ./vendor/bin/pest
```

Current smoke tests cover:

- API health endpoint
- basic factory/database persistence
- demo seeders

Do not claim auth, permission, policy, controller, or business workflow coverage until those tests exist.

## Frontend Rules

Frontend lives in:

- `apps/web`

Planned frontend stack:

- Next.js
- TypeScript
- Tailwind CSS
- TanStack Query
- React Hook Form
- Zod

Use Next.js App Router. Do not add React Router.

Frontend permission checks are only for user experience. They are not security.

## Docker Rules

Use Docker for runtime commands.

Common commands:

```bash
docker compose up -d
docker compose up -d --build
docker compose stop
docker compose start
docker compose exec api composer install
docker compose exec api php artisan migrate:fresh --seed
docker compose exec api ./vendor/bin/pest
```

The API service uses:

```yaml
./apps/api:/var/www/html
api_vendor:/var/www/html/vendor
```

Meaning:

- Laravel source code is bind-mounted from the host.
- Composer dependencies live in a Docker named volume.
- Run Composer inside the container, not on the host.

## Git Rules

Use small, focused branches.

Branch examples:

```
feature/auth-login
feature/team-management
feature/task-comments
fix/sanctum-cors
docs/setup-guide
test/auth-feature-tests
```

Use conventional commits:

```
feat: add login endpoint
fix: correct role permission pivot
docs: update setup guide
test: add auth smoke tests
refactor: extract permission service
chore: update Docker config
```

Do not commit automatically unless asked.

After editing, summarize:

- files changed
- what changed
- commands run
- remaining risks or follow-up work