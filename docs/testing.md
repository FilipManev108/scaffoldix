# Testing

## Backend Test Framework

Pest is installed as the backend test framework, with the Laravel Pest plugin available for feature tests.

Run backend tests from the repository root:

```bash
docker compose exec api ./vendor/bin/pest
```

Laravel and Composer commands should be run inside the `api` container so they use the container PHP extensions and the `api_vendor` volume.

## Existing Smoke Tests

Phase 1 includes small backend smoke tests:

| Test file | What it verifies |
| --- | --- |
| `HealthEndpointTest` | `GET /api/health` returns HTTP 200 and the expected JSON success shape. |
| `DatabaseSmokeTest` | `User::factory()->create()` and `Workspace::factory()->create()` persist records. |
| `SeederSmokeTest` | `DatabaseSeeder` creates demo users, demo workspace, demo project, and `admin.access`. |

These tests are intended to confirm that the backend foundation boots, migrations run, factories persist data, and seeders produce the expected demo baseline.

## Not Tested Yet

The Phase 1 smoke tests intentionally do not test:

- Auth
- Permissions
- Policies
- Controllers
- Request validation
- Business workflows
- Frontend behavior

Those areas are planned for later phases when the corresponding implementation exists.
