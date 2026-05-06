# Architecture

## Project

ScaffoldIX is a full-stack project management application for small software teams.

The repository is intentionally documentation-heavy so the current backend status, planned work, and implementation rules stay clear as the project grows.

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
- Workspace, team, team member, project, project member, task status, task, and comment endpoints
- Membership-based domain access checks
- Nested parent-child route validation
- Author-only comment update and delete checks
- Eloquent models and relationships
- Form Requests for auth and domain validation
- API Resources for structured domain responses
- Migrations, factories, and seeders
- Pest smoke, auth, domain endpoint, membership, comment, and domain authorization tests

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

Laravel `apiResource` update routes accept both `PUT` and `PATCH`; this document lists `PATCH` as the preferred partial-update method.

```txt
GET /api/health
POST /api/register
POST /api/login
POST /api/forgot-password
POST /api/reset-password
GET /api/verify-email/{id}/{hash}
POST /api/logout
GET /api/me
POST /api/email/verification-notification
GET /api/workspaces
POST /api/workspaces
GET /api/workspaces/{workspace}
PATCH /api/workspaces/{workspace}
DELETE /api/workspaces/{workspace}
GET /api/workspaces/{workspace}/teams
POST /api/workspaces/{workspace}/teams
GET /api/workspaces/{workspace}/teams/{team}
PATCH /api/workspaces/{workspace}/teams/{team}
DELETE /api/workspaces/{workspace}/teams/{team}
GET /api/workspaces/{workspace}/teams/{team}/members
POST /api/workspaces/{workspace}/teams/{team}/members
DELETE /api/workspaces/{workspace}/teams/{team}/members/{user}
GET /api/workspaces/{workspace}/projects
POST /api/workspaces/{workspace}/projects
GET /api/workspaces/{workspace}/projects/{project}
PATCH /api/workspaces/{workspace}/projects/{project}
DELETE /api/workspaces/{workspace}/projects/{project}
GET /api/workspaces/{workspace}/projects/{project}/members
POST /api/workspaces/{workspace}/projects/{project}/members
DELETE /api/workspaces/{workspace}/projects/{project}/members/{user}
GET /api/projects/{project}/task-statuses
POST /api/projects/{project}/task-statuses
GET /api/projects/{project}/task-statuses/{taskStatus}
PATCH /api/projects/{project}/task-statuses/{taskStatus}
DELETE /api/projects/{project}/task-statuses/{taskStatus}
GET /api/projects/{project}/tasks
POST /api/projects/{project}/tasks
GET /api/projects/{project}/tasks/{task}
PATCH /api/projects/{project}/tasks/{task}
DELETE /api/projects/{project}/tasks/{task}
GET /api/tasks/{task}/comments
POST /api/tasks/{task}/comments
GET /api/tasks/{task}/comments/{comment}
PATCH /api/tasks/{task}/comments/{comment}
DELETE /api/tasks/{task}/comments/{comment}
```

The health route is public. Authenticated auth and domain routes use `auth:sanctum` and `not.disabled`.

Laravel `apiResource` update routes accept both `PUT` and `PATCH`; this document lists `PATCH` as the preferred partial-update method.

Auth endpoint details are documented in `docs/auth.md`.

Domain endpoint details are documented in `docs/domain-api.md`.

### API Responses

`App\Support\ApiResponse` provides shared JSON helpers:

- `success($data, $message, $status)`
- `error($message, $errors, $status)`

The current health, auth, and domain endpoints use the shared response shape.

### Domain API Structure

Domain controllers live in `apps/api/app/Http/Controllers/Api`.

Current domain controllers:

- `WorkspaceController`
- `TeamController`
- `TeamMemberController`
- `ProjectController`
- `ProjectMemberController`
- `TaskStatusController`
- `TaskController`
- `CommentController`

Domain validation lives in `apps/api/app/Http/Requests/Domain`.

Domain response serialization uses API Resources in `apps/api/app/Http/Resources`.

### Current Access Model

Current domain authorization is intentionally simple and explicit:

- Protected domain routes require an authenticated Sanctum user who is not disabled.
- Workspaces are the top-level domain scope.
- A user can access a workspace when they belong to at least one team in that workspace.
- Team, project, task status, task, and comment routes check access through the resource's workspace.
- Nested routes verify parent-child ownership, such as a team belonging to the requested workspace or a task belonging to the requested project.
- Team membership is managed through `team_user`.
- Project membership is managed through `project_user`.
- Comment update and delete require the authenticated user to be the comment author.

The current implementation does not enforce role hierarchy or permission matrix rules.

### Models And Relationships

The backend includes Eloquent models for:

- `User`
- `Workspace`
- `Team`
- `Project`
- `TaskStatus`
- `Task`
- `Comment`
- `Role`
- `Permission`

The implemented model layer covers the main hierarchy:

```txt
Workspace -> Teams
Workspace -> Projects -> Task statuses
Workspace -> Projects -> Tasks -> Comments
```

It also includes role and permission relationships:

```txt
Workspace -> Roles -> Permissions
Team + User + Role assignments through team_user_role
```

See `docs/database.md` for table-level details.

### Factories

Factories exist for the core backend models and support database smoke tests, domain feature tests, and local demo data creation.

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
- Workspace, team, project, task status, task, and comment endpoint behavior
- Team and project membership endpoint behavior
- Domain authentication, disabled-user blocking, workspace access, nested route mismatch, and comment author checks

See `docs/testing.md` for test commands and current coverage.

## Not Implemented Yet

The following pieces are planned but not implemented yet:

- Policies and gates
- Permission service
- Role hierarchy service
- Role and permission matrix enforcement
- Admin-only user management
- Frontend auth pages
- Admin dashboard
- Production deployment
- Frontend dashboard workflows

## Frontend Architecture

The frontend architecture below is planned. Backend auth and the core domain API are implemented, but frontend auth pages and dashboard workflows are not built yet.

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

Implemented backend entities:

```txt
users
workspaces
teams
team_user
projects
project_user
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

Current domain access checks are implemented in controllers through explicit membership and parent-child queries.

Planned role and permission matrix enforcement:

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
4. Domain endpoint tests
5. Membership endpoint tests
6. Comment endpoint tests
7. Domain authorization tests

Planned testing priority:

1. Role and permission matrix tests
2. Policy tests when policies are introduced
3. Frontend smoke tests
4. Browser/E2E tests

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
