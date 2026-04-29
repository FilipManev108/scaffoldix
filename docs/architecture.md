# Architecture

## Project Name

ScaffoldIX

## Project Type

ScaffoldIX is a full-stack SaaS-style project management application for small software teams.

It is inspired by tools like Jira, Linear, Notion, and GitHub Projects, but the goal is not to clone every feature.

The goal is to demonstrate production-style full-stack development with a strong focus on:

- Authorization
- Team workflows
- Clean architecture
- Testing
- Documentation
- Docker
- Deployment

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

Responsibilities:

- Authentication
- Authorization
- Database models
- Business logic
- API endpoints
- Request validation
- Backend permission enforcement
- Email sending
- Testing

### apps/web

Next.js frontend application.

Responsibilities:

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

## Backend Architecture

Laravel should be structured around thin controllers and clear business logic separation.

Preferred structure:

```txt
app/
  Actions/
  DTOs/
  Enums/
  Http/
    Controllers/
    Requests/
    Resources/
  Models/
  Policies/
  Services/
database/
  migrations/
  seeders/
  factories/
routes/
  api.php
```

## Backend Rules

Controllers should not contain heavy business logic.

A controller may:

- Receive a request
- Authorize the action
- Call an action or service
- Return an API resource

A controller should not:

- Contain complex permission logic
- Contain long database workflows
- Manually format large response structures
- Duplicate business rules

## Frontend Architecture

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

Core planned entities:

```txt
users
workspaces
teams
team_user
projects
tasks
task_statuses
task_priorities
comments
roles
permissions
role_permission
team_user_role
sprints
activity_logs
attachments
invitations
```

The initial MVP may implement fewer entities, but the structure should allow these concepts later.

## Authentication

Authentication will use Laravel Sanctum.

The frontend is a first-party SPA-style client.

In local development:

```txt
Frontend: http://localhost:3000
Backend:  http://localhost:8000
```

In production, the preferred setup is:

```txt
Frontend: https://app.example.com
Backend:  https://api.example.com
```

## Authorization

Authorization must be enforced on the backend using:

- Laravel policies
- Laravel gates where appropriate
- Permission service
- Role hierarchy service

Frontend permission checks are allowed only for UI/UX.

Example:

A Junior user should not see a "Delete Task" button.

But if the Junior manually sends a DELETE request, the Laravel API must still reject it.

## Testing Direction

Testing priority:

1. Backend feature/API tests
2. Auth tests
3. Permission tests
4. Task workflow tests
5. Frontend smoke tests later
6. Browser/E2E tests later

## Deployment Direction

Preferred deployment:

```txt
Frontend: Vercel
Backend: Railway
Database: Railway MySQL
```

## Architectural Principle

The project should be built as if another developer could join later and understand the structure from the repository, documentation, naming, tests, and folder organization.