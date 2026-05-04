# Testing

## Backend Test Framework

Pest is installed as the backend test framework, with the Laravel Pest plugin available for feature tests.

Run backend tests from the repository root:

```bash
docker compose exec api ./vendor/bin/pest
```

Laravel and Composer commands should be run inside the `api` container so they use the container PHP extensions and the `api_vendor` volume.

## Existing Tests

The backend includes smoke tests and focused auth feature tests:

| Test file | What it verifies |
| --- | --- |
| `HealthEndpointTest` | `GET /api/health` returns HTTP 200 and the expected JSON success shape. |
| `DatabaseSmokeTest` | `User::factory()->create()` and `Workspace::factory()->create()` persist records. |
| `SeederSmokeTest` | `DatabaseSeeder` creates demo users, demo workspace, demo project, and `admin.access`. |
| `AuthRoutesTest` | Registration, login, logout, current user, protected routes, disabled users, email verification, password reset, validation errors, and sensitive-field exclusions. |

These tests confirm that the backend foundation boots, migrations run, factories persist data, seeders produce the expected demo baseline, and auth behavior works.

Run only auth tests:

```bash
docker compose exec api ./vendor/bin/pest tests/Feature/AuthRoutesTest.php
```

## Not Tested Yet

The current backend tests intentionally do not test:

- Permissions
- Policies
- Controllers
- Business workflows
- Frontend behavior

Those areas are planned for later phases when the corresponding implementation exists.
