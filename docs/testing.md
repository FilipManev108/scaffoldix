# Testing

## Backend Test Framework

Pest is installed as the backend test framework, with the Laravel Pest plugin available for feature tests.

Run backend tests from the repository root:

```bash
docker compose exec api ./vendor/bin/pest
```

Laravel and Composer commands should be run inside the `api` container so they use the container PHP extensions and the `api_vendor` volume.

## Existing Tests

The backend includes smoke tests, auth tests, domain endpoint tests, membership tests, comment tests, and domain authorization tests:

| Test file | What it verifies |
| --- | --- |
| `HealthEndpointTest` | `GET /api/health` returns HTTP 200 and the expected JSON success shape. |
| `DatabaseSmokeTest` | `User::factory()->create()` and `Workspace::factory()->create()` persist records. |
| `SeederSmokeTest` | `DatabaseSeeder` creates demo users, demo workspace, demo project, and `admin.access`. |
| `AuthRoutesTest` | Registration, login, logout, current user, protected routes, disabled users, email verification, password reset, validation errors, and sensitive-field exclusions. |
| `DomainRoutesTest` | Representative domain routes require Sanctum auth and block disabled authenticated users. |
| `WorkspaceRoutesTest` | Workspace create, list, show, update, soft delete, validation, duplicate slug handling, and inaccessible workspace rejection. |
| `TeamRoutesTest` | Team create, list, show, update, soft delete, validation, duplicate slug handling, and inaccessible workspace/team rejection. |
| `TeamMemberRoutesTest` | Team member list, add, remove, validation, duplicate membership rejection, inaccessible team rejection, and safe user serialization. |
| `ProjectRoutesTest` | Project create, list, show, update, soft delete, validation, duplicate slug handling, and inaccessible workspace/project rejection. |
| `ProjectMemberRoutesTest` | Project member list, add, remove, validation, duplicate membership rejection, inaccessible project rejection, and safe user serialization. |
| `TaskStatusRoutesTest` | Task status create, list, show, update, soft delete, validation, duplicate slug handling, and inaccessible project/status rejection. |
| `TaskRoutesTest` | Task create, list, show, update, soft delete, validation, project-scoped status validation, and inaccessible project/task rejection. |
| `CommentRoutesTest` | Comment create, list, show, author update/delete, non-author rejection, validation, inaccessible task/comment rejection, and safe author serialization. |
| `DomainAuthorizationTest` | Unauthenticated rejection, disabled-user blocking, inaccessible workspace/resource rejection, nested route mismatch rejection, and comment author enforcement. |

These tests confirm that the backend foundation boots, migrations run, factories persist data, seeders produce the expected demo baseline, auth behavior works, and the current membership-based domain access model is enforced.

Run only auth tests:

```bash
docker compose exec api ./vendor/bin/pest tests/Feature/AuthRoutesTest.php
```

Run only domain authorization tests:

```bash
docker compose exec api ./vendor/bin/pest tests/Feature/DomainAuthorizationTest.php
```

## Not Tested Yet

The current backend tests intentionally do not test:

- Role and permission matrix enforcement
- Policies and gates
- Frontend behavior
- Browser/E2E workflows

Those areas are planned for later work when the corresponding implementation exists.
