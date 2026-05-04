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
