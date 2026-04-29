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

Use Policies for authorization.

Use Services or Actions for business logic.

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