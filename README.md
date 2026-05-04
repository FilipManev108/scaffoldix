# ScaffoldIX

ScaffoldIX is a Jira-inspired project management platform for small software teams.

The project is built as a full-stack portfolio application focused on:

- Laravel API architecture
- Next.js frontend architecture
- MySQL database design
- Authentication
- Role-based and permission-based authorization
- Team and project workflows
- Dockerized local development
- Testing
- CI/CD
- Deployment
- Technical documentation
- AI-assisted development workflow

## Tech Stack

### Backend

- Laravel
- PHP
- MySQL
- Laravel Sanctum for authentication
- Pest / PHPUnit

### Frontend

- Next.js
- TypeScript
- Tailwind CSS
- TanStack Query
- React Hook Form
- Zod

### Infrastructure

- Docker Compose
- GitHub Actions
- Vercel
- Railway
- Mailpit
- Adminer

## Local Development

This project is intended to be developed inside the Ubuntu filesystem using WSL2.

Recommended location:

```bash
~/docker/portfolio/scaffoldix
```

Do not develop the project inside:

```bash
/mnt/c/...
```

That can cause slower performance and unreliable frontend file watching.

## Local URLs

After starting Docker:

```txt
Frontend: http://localhost:3000
Backend:  http://localhost:8000
Adminer:  http://localhost:8080
Mailpit:  http://localhost:8025
```

## Setup

Clone the repository:

```bash
git clone git@github.com:YOUR_USERNAME/scaffoldix.git
cd scaffoldix
```

Create Laravel environment file:

```bash
cp apps/api/.env.example apps/api/.env
```

Create frontend environment file:

```bash
cp apps/web/.env.example apps/web/.env.local
```

Start containers:

```bash
docker compose up -d --build
```

Generate Laravel app key:

```bash
docker compose exec api php artisan key:generate
```

Run migrations:

```bash
docker compose exec api php artisan migrate
```

For the fuller local setup guide, see `docs/setup.md`.

## Backend Commands

Start the local Docker services:

```bash
docker compose up -d
```

Reset the backend database:

```bash
docker compose exec api php artisan migrate:fresh
```

Reset and seed demo data:

```bash
docker compose exec api php artisan migrate:fresh --seed
```

Run backend tests:

```bash
docker compose exec api ./vendor/bin/pest
```

Check the API health endpoint:

```bash
curl http://localhost:8000/api/health
```

## Demo Users

Seeded demo users use the password `password`:

```txt
admin@demo.test
teamlead@demo.test
senior@demo.test
mid@demo.test
junior@demo.test
viewer@demo.test
```

## Authentication

Phase 2 backend authentication is implemented with Laravel Sanctum session authentication.

Implemented API auth includes registration, login, logout, `GET /api/me`, disabled-user handling, email verification, password reset, and auth feature tests.

SPA clients should call the Sanctum CSRF endpoint before state-changing authenticated requests:

```txt
GET /sanctum/csrf-cookie
```

Local auth emails can be inspected in Mailpit:

```txt
http://localhost:8025
```

For endpoint details and behavior notes, see `docs/auth.md`.

## Docker Services

The local Docker environment includes:

- `api` — Laravel backend
- `web` — Next.js frontend
- `mysql` — MySQL database
- `adminer` — database browser
- `mailpit` — local email testing

## Adminer Credentials

```txt
System:   MySQL
Server:   mysql
Username: scaffoldix
Password: secret
Database: scaffoldix
```

## Frontend Live Reload

The frontend container uses polling-based file watching to avoid common Docker + WSL2 live reload problems.

Relevant environment values:

```env
WATCHPACK_POLLING=true
CHOKIDAR_USEPOLLING=true
CHOKIDAR_INTERVAL=1000
```

The Next.js config also enables polling in development mode.

## Documentation

See:

```txt
docs/architecture.md
docs/auth.md
docs/database.md
docs/testing.md
docs/setup.md
docs/permissions.md
docs/git-flow.md
docs/ai-rules.md
```

## Current Status

Phase 1 backend foundation and Phase 2 backend authentication are complete.

Implemented:

- Laravel API route foundation with `GET /api/health`
- Shared API response helper
- Laravel Sanctum session authentication
- Registration, login, logout, current user, email verification, password reset, and disabled-user auth handling
- Core Eloquent models and relationships
- Database migrations for users, workspaces, teams, projects, tasks, comments, roles, permissions, statuses, and pivots
- Factories and seeders for backend smoke data
- Pest tests for health, database factories, seeders, and auth behavior

Planned later:

- Frontend auth pages
- Team/project/task CRUD
- Policies and backend permission enforcement
- Frontend application workflows
