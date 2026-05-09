# AI Agent Rules

These rules are for AI-assisted development in ScaffoldIX.

The purpose is to keep AI-generated code consistent with the architecture, security model, and portfolio goals of the project.

## General Rules

Do not make large architectural changes without updating documentation.

Do not silently replace existing patterns.

Before adding a new abstraction, check whether a similar pattern already exists.

Prefer simple, readable code over clever code.

Code should look maintainable by a real development team.

## Backend Rules

Laravel controllers must stay thin.

Controllers may:

- Receive requests
- Authorize actions
- Call actions/services
- Return resources

Controllers must not contain:

- Large business workflows
- Complex permission logic
- Duplicated validation
- Duplicated response formatting

Use Form Request classes for validation.

Use API Resources for structured responses.

Use explicit backend authorization checks for implemented access rules.

Use Policies, Gates, or services when broader permission behavior is introduced.

Use Services or Actions for business logic.

## Authentication Rules

Backend auth is implemented with Laravel Sanctum cookie/session authentication.

When changing auth behavior, update focused tests in:

```txt
apps/api/tests/Feature/AuthRoutesTest.php
```

Auth changes must preserve:

- `App\Support\ApiResponse` JSON response shape
- Form Request validation
- Server-side protected route enforcement
- Disabled-user blocking through `users.disabled_at`
- Safe user responses without passwords or remember tokens

Detailed auth behavior is documented in:

```txt
docs/auth.md
```

## Permission Rules

Never rely on frontend-only permissions.

Every protected action must be enforced by the Laravel backend.

If a permission rule changes, update:

```txt
docs/permissions.md
backend policies/services
backend tests
frontend permission-aware UI if needed
```

Permission-related changes should include tests.

Do not claim authorization, permission, policy, or workflow coverage unless matching backend tests exist.

## Frontend Rules

Use TypeScript.

Use Next.js App Router.

Use file-based routing inside `src/app`.

Do not use React Router.

Do not install `react-router-dom`.

Use feature-based organization.

Keep API calls centralized.

Do not scatter raw fetch/axios calls across random components.

Use reusable components where practical.

Do not hardcode role behavior in many components.

Prefer permission helper functions.

Frontend permission checks are for UX only.

## Docker Rules

Do not remove polling-based file watching from the frontend Docker setup unless a better verified solution is added.

The following values exist because Docker + WSL2 can have unreliable file watching:

```env
WATCHPACK_POLLING=true
CHOKIDAR_USEPOLLING=true
CHOKIDAR_INTERVAL=1000
```

## Documentation Rules

Durable project files must not use planning labels.

Do not use planning labels in production filenames, test filenames, class names, helper names, or route names.

When changing setup steps, update:

```txt
README.md
```

When changing architecture, update:

```txt
docs/architecture.md
```

When changing permissions, update:

```txt
docs/permissions.md
```

When changing Git workflow, update:

```txt
docs/git-flow.md
```

## Testing Rules

Backend auth and permission logic should be tested.

Important tests include:

- Authenticated user access
- Unauthenticated user rejection
- Disabled user rejection
- Role-based task permissions
- Comment permissions
- Admin-only access

Do not mark permission work complete without tests.

## Security Rules

Never commit:

```txt
.env
.env.local
API keys
tokens
passwords
private prompts
personal notes with sensitive information
```

Do not trust user input.

Use Laravel validation.

Use backend authorization.

Use safe file upload rules.

## Portfolio Rule

This is a portfolio project.

Code should demonstrate:

- Clean architecture
- Clear naming
- Secure authorization
- Realistic workflows
- Testable backend logic
- Good documentation
- Professional Git history
