# Git Flow

## Branches

The repository follows a lightweight Git workflow designed to keep development organized, reviewable, and easy to maintain.

Main branches:

```txt
main
develop
```

## Branch Purpose

### main

Production-ready code.

Only stable, reviewed, tested code should be merged into `main`.

### develop

Active development branch.

Feature branches should usually branch from `develop`.

## Feature Branch Naming

Use clear branch names.

```txt
feature/auth-login
feature/team-management
feature/task-comments
feature/permission-matrix
feature/docker-setup
feature/admin-dashboard
```

## Fix Branch Naming

```txt
fix/sanctum-cors
fix/task-policy-bug
fix/frontend-live-reload
fix/mysql-connection
```

## Documentation Branch Naming

```txt
docs/api-documentation
docs/permission-matrix
docs/setup-guide
docs/deployment-guide
```

## Commit Style

Use conventional commits.

Examples:

```txt
feat: add task assignment endpoint
fix: prevent junior users from assigning tasks
docs: add permission matrix
test: add task policy tests
refactor: extract role hierarchy service
chore: configure docker compose
chore: configure github actions
```

## Commit Types

```txt
feat:     new feature
fix:      bug fix
docs:     documentation only
test:     tests
refactor: code change without behavior change
chore:    setup, tooling, maintenance
style:    formatting only
```

## Pull Requests

Even when working solo, use pull requests.

Each PR should explain:

```txt
What changed
Why it changed
How it was tested
Screenshots, if UI changed
Known limitations
```

## PR Template

```md
## What changed?

-

## Why?

-

## How was it tested?

-

## Screenshots

-

## Known limitations

-
```

## Rules

Do not commit directly to `main`.

Prefer small PRs.

One PR should usually cover one feature or one logical change.

Update documentation when architecture, permissions, setup, or deployment changes.

Add tests when changing backend authorization logic.