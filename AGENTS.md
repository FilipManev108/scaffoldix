# AGENTS.md

## Project

ScaffoldIX is a Laravel + Next.js monorepo for a Jira-inspired project management platform for small software teams.

The project is built as a portfolio application focused on:

- Laravel API architecture
- Next.js frontend architecture
- MySQL database design
- role-based and permission-based authorization
- team, project, task, and comment workflows
- Dockerized local development
- backend testing
- technical documentation
- AI-assisted development workflow

## Repository Structure

- `apps/api` — Laravel API backend
- `apps/web` — Next.js frontend
- `docs` — project documentation
- `docker` — Docker-related files
- `docker-compose.yml` — local development services
- `README.md` — main project overview and setup entry point

## Current Project Status

The Laravel API has implemented authentication and core domain APIs.

Current backend behavior should be verified from the code and documentation, especially:

- `apps/api/AGENTS.md`
- `apps/api/routes/api.php`
- `docs/architecture.md`
- `docs/auth.md`
- `docs/database.md`
- `docs/testing.md`

Do not assume planned features exist unless the code or documentation confirms them.

## General Rules

- Inspect existing files before editing.
- Only edit files relevant to the requested task.
- Keep changes small and focused.
- Prefer simple, readable code over clever abstractions.
- Preserve existing project conventions.
- Do not add packages unless explicitly requested.
- Do not rename files, classes, routes, route parameters, tables, columns, or branches unless explicitly requested.
- Do not make broad architectural changes unless explicitly requested.
- Do not use planning labels in production filenames, test filenames, class names, helper names, or route names.
- Do not commit changes unless explicitly asked.

## Documentation Rules

Update documentation when changing:

- architecture
- setup or Docker workflow
- database structure
- authentication behavior
- authorization behavior
- testing setup or testing strategy
- public API behavior

Relevant documentation locations include:

- `README.md`
- `docs/architecture.md`
- `docs/auth.md`
- `docs/database.md`
- `docs/testing.md`
- `docs/permissions.md`

Do not invent implemented features in documentation.

If something is planned but not built, label it as planned.

## Backend Rules

Laravel backend code lives in:

- `apps/api`

Backend-specific instructions live in:

- `apps/api/AGENTS.md`

Run Laravel, Composer, Artisan, and Pest commands inside the API container.

Use:

```bash
docker compose exec api php artisan ...
docker compose exec api composer ...
docker compose exec api ./vendor/bin/pest
```

Do not assume host PHP or host Composer is the runtime source of truth.

## Frontend Rules

Frontend code lives in:

- `apps/web`

Expected frontend stack:

- Next.js
- TypeScript
- Tailwind CSS
- TanStack Query
- React Hook Form
- Zod

Use Next.js App Router.

Do not add React Router.

Frontend permission checks are only user experience helpers. They are not security.

## Authorization Rules

Backend authorization must be enforced server-side.

Frontend-only permission checks are not sufficient.

Do not add broad role or permission architecture unless explicitly requested.

Do not claim authorization, permission, or policy coverage unless matching tests exist.

## Testing Rules

Backend tests use Pest.

Before finishing backend work, run:

```bash
docker compose exec api ./vendor/bin/pest
```

When route behavior changes, also run:

```bash
docker compose exec api php artisan route:list
```

Do not claim test coverage unless matching tests exist.

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

The API service uses bind-mounted source code and a Docker volume for Composer dependencies.

Run Composer inside the container, not on the host.

## Git Rules

Use small, focused branches.

Example branch names:

```txt
feature/auth-login
feature/team-management
feature/task-comments
fix/sanctum-cors
docs/setup-guide
test/auth-feature-tests
```

Use conventional commits:

```txt
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
