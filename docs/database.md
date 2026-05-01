# Database

## Overview

Phase 1 implements the backend database foundation for workspaces, teams, projects, tasks, comments, roles, permissions, task statuses, and membership pivots.

Main hierarchy:

```txt
Workspace -> Teams -> Projects -> Tasks -> Comments
```

Role and permission structure:

```txt
Workspace -> Roles -> Permissions
Team + User + Role assignment through team_user_role
```

## Core Tables

| Table | Responsibility |
| --- | --- |
| `users` | Application users. Demo users are seeded for local testing. |
| `workspaces` | Top-level container for teams, projects, roles, and task statuses. |
| `teams` | Groups of users inside a workspace. |
| `projects` | Work owned by a workspace and assigned to a team. |
| `tasks` | Project work items with creator, optional assignee, status, priority, and due date. |
| `comments` | User comments attached to tasks. |
| `roles` | Workspace-scoped roles with level, system flag, and description. |
| `permissions` | Global permission names grouped by area, such as `task.view` or `admin.access`. |
| `task_statuses` | Workspace/project statuses for task workflow columns. |
| `team_user` | Team membership pivot. |
| `project_user` | Project membership pivot. |
| `role_permission` | Role-to-permission pivot. |
| `team_user_role` | Team-scoped user role assignment pivot. |

Laravel default support tables also exist, including cache, jobs, sessions, and password reset tokens.

## Relationships

`Workspace` owns teams, projects, roles, and task statuses.

`Team` belongs to a workspace, has many projects, and has many users through `team_user`.

`Project` belongs to a workspace, team, and creator. Projects have many tasks and many users through `project_user`.

`Task` belongs to a project, status, creator, and optional assignee. Tasks have many comments.

`Comment` belongs to a task and user.

`Role` belongs to a workspace and has many permissions through `role_permission`.

`Permission` can belong to many roles through `role_permission`.

## Pivot Tables

`team_user` stores basic team membership with a unique `team_id` and `user_id` pair.

`project_user` stores project membership with a unique `project_id` and `user_id` pair.

`role_permission` connects roles to permission records with a unique `role_id` and `permission_id` pair.

`team_user_role` assigns one or more roles to a user within a team. It stores `team_id`, `user_id`, and `role_id` and enforces that combination as unique.

## Soft Deletes

These domain tables use soft deletes:

- `workspaces`
- `teams`
- `projects`
- `tasks`
- `comments`
- `roles`
- `task_statuses`

These tables do not use soft deletes:

- `permissions`
- `team_user`
- `project_user`
- `role_permission`
- `team_user_role`

Pivot rows represent current associations and are removed directly when their parent records are deleted.

## Migration Commands

Run Laravel commands inside the API container:

```bash
docker compose exec api php artisan migrate:fresh
```

Reset and seed demo data:

```bash
docker compose exec api php artisan migrate:fresh --seed
```

## Seeders

`DatabaseSeeder` runs:

- `DemoUserSeeder`
- `PermissionSeeder`
- `DemoWorkspaceSeeder`

The seeders create demo users, the permission catalog, a demo workspace, a demo team, a demo project, roles, task statuses, tasks, comments, memberships, and role-permission links.

Demo credentials:

```txt
admin@demo.test      password
teamlead@demo.test   password
senior@demo.test     password
mid@demo.test        password
junior@demo.test     password
viewer@demo.test     password
```

Seeded demo records include:

- Workspace slug: `scaffoldix-demo`
- Project slug: `scaffoldix-mvp`
- Permission example: `admin.access`
