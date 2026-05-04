# Architecture

## Project

ScaffoldIX is a full-stack project management application for small software teams.

The repository is intentionally documentation-heavy because the project is being built in phases. This file separates implemented backend foundation and auth work from planned later architecture.

## Monorepo Structure

```txt
scaffoldix/
  apps/
    api/
    web/
  docs/
  docker/
  docker-compose.yml
  README.md
```

## Applications

### apps/api

Laravel backend API.

Implemented backend responsibilities:

- API route bootstrap
- `GET /api/health`
- Shared API response helper
- Sanctum-backed registration, login, logout, current-user, email verification, and password reset endpoints
- Disabled-user auth blocking through `users.disabled_at`
- Eloquent models and relationships
- Migrations, factories, and seeders
- Pest smoke and auth feature tests

### apps/web

Next.js frontend application.

Planned responsibilities:

- Public pages
- Authentication UI
- Dashboard UI
- Team UI
- Project UI
- Task UI
- Admin UI
- Permission-aware rendering

Important rule:

Frontend permissions are for user experience only. Real permission enforcement must happen on the Laravel backend.

## Implemented Backend

### API Route Foundation

The backend exposes API routes in `apps/api/routes/api.php`.

```txt
GET /api/health
POST /api/register
POST /api/login
POST /api/logout
GET /api/me
GET /api/verify-email/{id}/{hash}
POST /api/email/verification-notification
POST /api/forgot-password
POST /api/reset-password
```

The health route returns a standardized success response with the app name and current environment.

Auth endpoint details are documented in `docs/auth.md`.

### API Responses

`App\Support\ApiResponse` provides shared JSON helpers:

- `success($data, $message, $status)`
- `error($message, $errors, $status)`

The current health endpoint uses `ApiResponse::success(...)`.

### Models And Relationships

The backend includes Eloquent models for:

- `User`
- `Workspace`
- `Team`
- `Project`
- `Task`
- `Comment`
- `Role`
- `Permission`
- `TaskStatus`

The implemented model layer covers the main hierarchy:

```txt
Workspace -> Teams -> Projects -> Tasks -> Comments
```

It also includes role and permission relationships:

```txt
Workspace -> Roles -> Permissions
Team + User + Role assignments through team_user_role
```

See `docs/database.md` for table-level details.

### Factories

Factories exist for the core backend models and support database smoke tests and future feature tests.

### Seeders

The backend includes seeders for:

- Demo users
- Permissions
- Demo workspace/project/task data

`DatabaseSeeder` runs the demo users, permissions, and workspace seeders in order.

### Tests

Pest is installed for backend testing. Current backend tests cover:

- Health endpoint response shape
- `User` and `Workspace` factory persistence
- Database seeder demo data
- Auth feature behavior

See `docs/testing.md` for test commands and current coverage.

## Not Implemented Yet

The following pieces are planned but not implemented yet:

- Controllers for domain resources
- API resources
- Policies and gates
- Permission service
- Role hierarchy service
- Business workflow endpoints
- Frontend auth pages
- Admin dashboard
- Production deployment
- Frontend dashboard workflows

## Frontend Architecture

The frontend architecture below is planned. Backend auth is implemented, but frontend auth pages and dashboard workflows are not built yet.

Preferred structure:

```txt
src/
  app/
  components/
    ui/
    layout/
    auth/
    dashboard/
    teams/
    projects/
    tasks/
    comments/
    permissions/
  features/
    auth/
    teams/
    projects/
    tasks/
    comments/
    roles/
  lib/
    api.ts
    auth.ts
    permissions.ts
    queryClient.ts
  types/
```

## Frontend Rules

The frontend should:

- Use TypeScript
- Use Next.js App Router
- Use file-based routing inside `src/app`
- Use feature-based folders
- Keep API calls centralized
- Use reusable UI components
- Use permission-aware rendering
- Avoid duplicating business logic from the backend

The frontend should not:

- Use React Router
- Create a `pages/` directory
- Treat hidden buttons as security
- Hardcode permission decisions in many unrelated components
- Call API endpoints directly from random components without using a shared API layer

## Database Direction

Phase 1 implemented entities:

```txt
users
workspaces
teams
team_user
projects
tasks
task_statuses
comments
roles
permissions
role_permission
team_user_role
```

Planned later entities may include sprints, activity logs, attachments, invitations, and richer status/priority configuration.

## Authentication

Backend authentication is implemented with Laravel Sanctum session auth.

Implemented auth includes registration, login, logout, current user, disabled-user handling, email verification, password reset, and tests.

See `docs/auth.md` for endpoint details, CSRF notes, Mailpit usage, and out-of-scope items.

Current local development URLs:

```txt
Frontend: http://localhost:3000
Backend:  http://localhost:8000
```

Planned production direction:

```txt
Frontend: https://app.example.com
Backend:  https://api.example.com
```

## Authorization

Authorization is planned and is not implemented yet.

Planned backend enforcement:

- Laravel policies
- Laravel gates where appropriate
- Permission service
- Role hierarchy service

Frontend permission checks are allowed only for UI/UX.

## Testing Direction

Implemented now:

1. Backend smoke tests
2. Seeder smoke tests
3. Auth feature tests

Planned testing priority:

1. Permission tests
2. Policy tests
3. Controller and API workflow tests
4. Task workflow tests
5. Frontend smoke tests
6. Browser/E2E tests

## Deployment Direction

Deployment is planned, not implemented.

Preferred deployment:

```txt
Frontend: Vercel
Backend: Railway
Database: Railway MySQL
```

## Architectural Principle

The project should be built as if another developer could join later and understand the structure from the repository, documentation, naming, tests, and folder organization.
