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
- Laravel Sanctum
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
docs/permissions.md
docs/git-flow.md
docs/ai-rules.md
```

## Current Status

Phase 0: Planning and repository setup.