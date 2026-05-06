# Domain API

## Overview

The core domain API is implemented in the Laravel backend under `apps/api`.

Domain routes are registered in `apps/api/routes/api.php` and are protected by:

- `auth:sanctum`
- `not.disabled`

Responses use the shared `App\Support\ApiResponse` JSON shape and domain resources from `apps/api/app/Http/Resources`.

Laravel `apiResource` update routes accept both `PUT` and `PATCH`; the endpoint tables below list `PATCH` as the preferred partial-update method.

## Current Access Model

The current backend access model is membership-based:

- Workspaces are the top-level domain scope.
- A user can access a workspace when they belong to at least one team in that workspace.
- Team, project, task status, task, and comment routes check access through the resource's workspace.
- Nested routes verify parent-child ownership.
- Team membership is stored in `team_user`.
- Project membership is stored in `project_user`.
- Comment update and delete are limited to the comment author.

Role hierarchy, permission matrix enforcement, policies, gates, and permission services are planned and not implemented yet.

## Workspaces

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/workspaces` | List workspaces accessible through team membership. |
| `POST` | `/api/workspaces` | Create a workspace and a default team for the authenticated user. |
| `GET` | `/api/workspaces/{workspace}` | Show an accessible workspace. |
| `PATCH` | `/api/workspaces/{workspace}` | Update an accessible workspace. |
| `DELETE` | `/api/workspaces/{workspace}` | Soft delete an accessible workspace. |

## Teams

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/workspaces/{workspace}/teams` | List teams for an accessible workspace. |
| `POST` | `/api/workspaces/{workspace}/teams` | Create a team in an accessible workspace. |
| `GET` | `/api/workspaces/{workspace}/teams/{team}` | Show a team that belongs to the workspace. |
| `PATCH` | `/api/workspaces/{workspace}/teams/{team}` | Update a team that belongs to the workspace. |
| `DELETE` | `/api/workspaces/{workspace}/teams/{team}` | Soft delete a team that belongs to the workspace. |

## Team Members

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/workspaces/{workspace}/teams/{team}/members` | List users attached to a team. |
| `POST` | `/api/workspaces/{workspace}/teams/{team}/members` | Attach a user to a team through `team_user`. |
| `DELETE` | `/api/workspaces/{workspace}/teams/{team}/members/{user}` | Detach a user from a team. |

Team member responses use `UserResource` and do not expose sensitive user fields.

## Projects

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/workspaces/{workspace}/projects` | List projects in an accessible workspace. |
| `POST` | `/api/workspaces/{workspace}/projects` | Create a project in an accessible workspace. |
| `GET` | `/api/workspaces/{workspace}/projects/{project}` | Show a project that belongs to the workspace. |
| `PATCH` | `/api/workspaces/{workspace}/projects/{project}` | Update a project that belongs to the workspace. |
| `DELETE` | `/api/workspaces/{workspace}/projects/{project}` | Soft delete a project that belongs to the workspace. |

Project creation records the authenticated user as `created_by`.

## Project Members

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/workspaces/{workspace}/projects/{project}/members` | List users attached to a project. |
| `POST` | `/api/workspaces/{workspace}/projects/{project}/members` | Attach a user to a project through `project_user`. |
| `DELETE` | `/api/workspaces/{workspace}/projects/{project}/members/{user}` | Detach a user from a project. |

Project member responses use `UserResource` and do not expose sensitive user fields.

## Task Statuses

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/projects/{project}/task-statuses` | List task statuses for an accessible project. |
| `POST` | `/api/projects/{project}/task-statuses` | Create a task status for an accessible project. |
| `GET` | `/api/projects/{project}/task-statuses/{taskStatus}` | Show a task status that belongs to the project. |
| `PATCH` | `/api/projects/{project}/task-statuses/{taskStatus}` | Update a task status that belongs to the project. |
| `DELETE` | `/api/projects/{project}/task-statuses/{taskStatus}` | Soft delete a task status that belongs to the project. |

Task status creation uses the project's workspace for `workspace_id`.

## Tasks

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/projects/{project}/tasks` | List tasks for an accessible project. |
| `POST` | `/api/projects/{project}/tasks` | Create a task for an accessible project. |
| `GET` | `/api/projects/{project}/tasks/{task}` | Show a task that belongs to the project. |
| `PATCH` | `/api/projects/{project}/tasks/{task}` | Update a task that belongs to the project. |
| `DELETE` | `/api/projects/{project}/tasks/{task}` | Soft delete a task that belongs to the project. |

Task creation records the authenticated user as `created_by`. Task status validation is scoped to the current project.

## Comments

| Method | Path | Purpose |
| --- | --- | --- |
| `GET` | `/api/tasks/{task}/comments` | List comments for an accessible task. |
| `POST` | `/api/tasks/{task}/comments` | Create a comment for an accessible task. |
| `GET` | `/api/tasks/{task}/comments/{comment}` | Show a comment that belongs to the task. |
| `PATCH` | `/api/tasks/{task}/comments/{comment}` | Update an authored comment. |
| `DELETE` | `/api/tasks/{task}/comments/{comment}` | Soft delete an authored comment. |

Comment creation records the authenticated user as `user_id`. Comment responses include safe author data through `UserResource`.

## Validation And Tests

Domain request validation lives in `apps/api/app/Http/Requests/Domain`.

Current backend tests cover domain endpoints, membership behavior, nested resource mismatches, inaccessible resources, disabled-user blocking, and comment author-only modification. See `docs/testing.md` for the test file list and commands.
