# Permissions

## Current Status

ScaffoldIX seeds a permission catalog and demo role-permission assignments for the implemented backend API areas.

Permissions are not enforced yet. Current protected domain routes still use membership-based access checks:

- Workspace access comes from membership in at least one team in the workspace.
- Team membership is stored in `team_user`.
- Project membership is stored in `project_user`.
- Comment update and delete are limited to the comment author.

## Naming Convention

Permissions use predictable dot notation:

```txt
resource.action
```

Resource names are singular. Multi-word resources use snake case, such as `team_member.view` and `task_status.update`.

Actions currently use:

```txt
view
create
update
delete
```

The permissions catalog is global. Roles are scoped to a workspace and receive permissions through `role_permission`.

## Seeded Permission Catalog

```txt
workspace.view
workspace.create
workspace.update
workspace.delete
team.view
team.create
team.update
team.delete
team_member.view
team_member.create
team_member.delete
project.view
project.create
project.update
project.delete
project_member.view
project_member.create
project_member.delete
task_status.view
task_status.create
task_status.update
task_status.delete
task.view
task.create
task.update
task.delete
comment.view
comment.create
comment.update
comment.delete
role.view
role.create
role.update
role.delete
permission.view
```

## Seeded Demo Roles

Seeded role levels:

```txt
Admin:     100
Team Lead: 80
Senior:    60
Mid:       40
Junior:    20
Viewer:    10
```

Role-permission expectations:

| Role | Seeded permissions |
| --- | --- |
| Admin | All seeded permissions. |
| Team Lead | Broad workspace, team, team member, project, project member, task status, task, and comment permissions, plus `role.view` and `permission.view`. Team Lead does not receive `workspace.delete` or role create/update/delete permissions. |
| Senior | Project view plus task status, task, and comment create/update/delete workflow permissions. |
| Mid | Project and task status view, normal task create/update work, and comment create/update/delete work. |
| Junior | Project and task status view, limited task update work, and comment create/update work. |
| Viewer | Read-only domain permissions only. Viewer does not receive role or permission viewing. |

## Planned Enforcement

Next work should add permission resolution and backend enforcement through policies, gates, endpoint checks, or equivalent Laravel authorization boundaries.

Frontend permission checks may improve user experience later, but backend enforcement must remain the source of security.
