# Railway Deploy Checklist (Laravel Test)

## 1) Services

Create these services in one Railway project:

- `web` (from this repo)
- `queue` (from this repo)
- `scheduler` (from this repo)
- `mysql` (Railway MySQL plugin)

`web` uses `Dockerfile` and serves HTTP on `$PORT`.

## 2) Environment variables

Set in all three app services (`web`, `queue`, `scheduler`):

- `APP_NAME=Cafe Manager`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://<your-railway-domain>`
- `APP_KEY=<generated-key>`
- `LOG_CHANNEL=stack`
- `LOG_LEVEL=info`

Database (from Railway MySQL variables):

- `DB_CONNECTION=mysql`
- `DB_HOST=<mysql host>`
- `DB_PORT=<mysql port>`
- `DB_DATABASE=<mysql database>`
- `DB_USERNAME=<mysql user>`
- `DB_PASSWORD=<mysql password>`

Queue/session/cache:

- `QUEUE_CONNECTION=database`
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`

Optional Swagger host override:

- `L5_SWAGGER_CONST_HOST=https://<your-railway-domain>`

## 3) Start commands

Set per service:

- `web`: use Dockerfile default command (no override needed)
- `queue`: `php artisan queue:work --tries=3 --timeout=90`
- `scheduler`: `sh -lc "while true; do php artisan schedule:run --no-interaction; sleep 60; done"`

## 4) One-time release commands (run in web shell)

```bash
php artisan key:generate --show
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan l5-swagger:generate --all
```

## 5) Smoke tests

- Open `/api/documentation`
- Call `POST /api/login`
- Use token to call `GET /api/me`
- Trigger checkout and ensure queue worker logs jobs

## 6) Notes

- Storage on Railway containers is ephemeral. For durable files, move to S3-compatible storage.
- If credentials were ever exposed locally, rotate them before go-live.
