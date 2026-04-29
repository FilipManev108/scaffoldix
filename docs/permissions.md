# Permissions System

## Purpose

The permission system is the central technical feature of ScaffoldIX.

The goal is to support:

- Default roles
- Custom roles
- Role hierarchy
- Scoped permissions
- Team/project-based authorization
- Backend-enforced security
- Frontend permission-aware UI

## Core Rule

Every important permission must be enforced on the backend.

Frontend checks only control what the user sees.

They do not provide security.

## Default Roles

```txt
Admin
Team Lead
Senior
Mid
Junior
Viewer
```

## Role Levels

Each role has an authority level.

```txt
Admin:     100
Team Lead: 80
Senior:    60
Mid:       40
Junior:    20
Viewer:    10
```

Role levels help with hierarchy-based rules.

Example:

```txt
A Senior can assign tasks to Mid or Junior users.
A Mid can assign unassigned tasks to himself or to Junior users.
A Junior cannot assign tasks.
```

## Permission Naming

Permissions should use dot notation.

Examples:

```txt
team.create
team.update
team.delete
team.view

project.create
project.update
project.delete
project.view

task.create
task.update
task.delete
task.assign
task.change_status
task.view

comment.create
comment.update_own
comment.delete_own
comment.delete_any

status.create
status.update
status.delete

role.create
role.update
role.delete
role.assign

admin.access
user.disable
```

## Backend Enforcement

Backend enforcement should use:

```txt
Policies
Gates
PermissionService
RoleHierarchyService
```

Example policy classes:

```txt
TaskPolicy
ProjectPolicy
CommentPolicy
TeamPolicy
RolePolicy
```

## Frontend Permission Usage

Frontend permissions may be used to:

- Hide buttons
- Disable form fields
- Hide navigation items
- Show access-denied states
- Improve UX

Frontend permissions must not be trusted as security.

## Admin

Admin has global access.

Admin can:

- Access admin panel
- View all users
- Disable users
- View all teams
- View all projects
- View all tasks
- Manage roles
- Manage permissions
- Enter any project
- Perform all project actions

## Team Lead

Team Lead has high control within owned teams/projects.

Team Lead can:

- Create teams
- Add users to teams
- Assign projects to teams
- Create projects
- Create tasks
- Assign tasks
- Change task statuses
- Comment on tasks
- Delete comments from other users within owned projects
- Create custom roles
- Configure custom permissions
- Create custom task statuses
- Delete owned custom roles/statuses

## Senior

Senior can:

- Create tasks
- Assign tasks to allowed users
- Change statuses on allowed tasks
- Comment on tasks
- View project content

Senior cannot:

- Delete comments from other users unless granted custom permission
- Manage roles
- Manage team settings
- Access admin panel

## Mid

Mid can:

- Assign unassigned tasks to himself
- Assign allowed tasks to Junior users
- Comment on tasks assigned to himself or Junior users
- Edit own comments
- Delete own comments
- Change status of tasks assigned to himself or Junior users

Mid cannot:

- Create tasks by default
- Assign tasks to Senior or Team Lead
- Delete other users' comments
- Manage roles
- Manage project settings
- Access admin panel

## Junior

Junior can:

- View tasks assigned to him
- Comment on tasks assigned to him
- Edit own comments
- Delete own comments

Junior cannot:

- Create tasks
- Assign tasks
- Change other users' tasks
- Delete other users' comments
- Manage statuses
- Manage roles
- Access admin panel

## Viewer

Viewer can:

- View allowed project resources

Viewer cannot:

- Create tasks
- Edit tasks
- Assign tasks
- Comment
- Change statuses
- Manage users
- Manage roles
- Access admin panel

## Custom Roles

Custom roles should support:

- Custom title
- Custom permission set
- Custom authority level
- Scope to workspace/team/project where appropriate

Custom roles should not be allowed to exceed the creator's own authority level.

Example:

```txt
A Team Lead should not be able to create a custom role stronger than Team Lead.
```

## Permission Testing Requirements

Permission changes must include tests.

Required test examples:

```txt
Team Lead can create task
Senior can create task
Mid cannot create task
Junior cannot create task

Senior can assign task to Mid
Mid cannot assign task to Senior
Junior cannot assign task

Junior can comment on assigned task
Junior cannot comment on unassigned task

Junior can delete own comment
Junior cannot delete another user's comment

Team Lead can delete comments in owned project
Viewer cannot comment
Admin can access admin routes
Non-admin cannot access admin routes
```