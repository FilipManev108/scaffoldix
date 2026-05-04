# Setup

## Docker Basics

Start containers:

```bash
docker compose up -d
```

Rebuild and start containers:

```bash
docker compose up -d --build
```

Stop containers:

```bash
docker compose stop
```

Start stopped containers:

```bash
docker compose start
```

## Running Backend Commands

Run Laravel commands inside the `api` container:

```bash
docker compose exec api php artisan migrate:fresh --seed
```

Run Composer commands inside the `api` container:

```bash
docker compose exec api composer install
```

Run backend tests inside the `api` container:

```bash
docker compose exec api ./vendor/bin/pest
```

## API Container Volumes

The API service uses these important mounts:

```yaml
./apps/api:/var/www/html
api_vendor:/var/www/html/vendor
```

`./apps/api:/var/www/html` bind-mounts the Laravel application from the repository into the container, so local file edits are visible to PHP immediately.

`api_vendor:/var/www/html/vendor` stores Composer dependencies in a Docker named volume. This keeps container-installed dependencies separate from the host filesystem and avoids relying on host PHP extensions.

## Common Commands

```bash
docker compose up -d
docker compose up -d --build
docker compose stop
docker compose start
docker compose exec api composer install
docker compose exec api php artisan migrate:fresh --seed
docker compose exec api ./vendor/bin/pest
```

## Health Check

After the API container is running:

```bash
curl http://localhost:8000/api/health
```

## Authentication Setup Notes

Backend authentication uses Laravel Sanctum cookie/session auth.

For SPA-style local requests, call the CSRF cookie endpoint before state-changing authenticated requests:

```txt
GET http://localhost:8000/sanctum/csrf-cookie
```

Auth endpoint details are documented in `docs/auth.md`.

## Mailpit

The Docker API service sends local auth emails through Mailpit:

```txt
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

Inspect verification and password reset emails in the browser:

```txt
http://localhost:8025
```
