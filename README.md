# Cafe Manager (Laravel 11)

This is a simple cafe store manager for beginners. It uses one resource: menu items, with basic CRUD.

## 1) Create project with Docker (no local Composer)

Run in the parent folder:

```bash
docker run --rm -v "${PWD}:/app" -w /app composer:2 create-project laravel/laravel laravel_test "^11.0"
```

## 2) Install Sail + MySQL (Docker)

From the project folder:

```bash
docker run --rm -v "${PWD}:/app" -w /app composer:2 php artisan sail:install --with=mysql
./vendor/bin/sail up -d
```

## 3) Configure .env for MySQL in Docker

Use these values:

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=cafe_db
DB_USERNAME=sail
DB_PASSWORD=password
```

## 4) Artisan via Docker

After containers are running:

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker
```

You can also use Docker directly:

```bash
docker compose exec laravel.test php artisan migrate
```

## 5) CRUD features in this project

Model + migration:

```bash
./vendor/bin/sail artisan make:model Category -m
./vendor/bin/sail artisan make:model Product -m
```

Controller:

```bash
./vendor/bin/sail artisan make:controller CategoryController --resource
./vendor/bin/sail artisan make:controller ProductController --resource
```

Routing:

- Resource route is in routes/web.php

## 6) RESTful API + Laravel Sanctum (token-based)

This project now includes API auth endpoints:

- `POST /api/register`
- `POST /api/login`
- `POST /api/logout` (requires Bearer token)
- `GET /api/me` (requires Bearer token)

### Run app + migrations

```bash
docker compose up -d
docker compose exec laravel.test php artisan migrate
```

### Quick test with Postman / FE

1. Register

```http
POST http://localhost:8800/api/register
Content-Type: application/json

{
	"name": "Test User",
	"email": "test@example.com",
	"password": "password123"
}
```

2. Login and copy `token`

```http
POST http://localhost:8800/api/login
Content-Type: application/json

{
	"email": "test@example.com",
	"password": "password123"
}
```

3. Call `me` with Bearer token

```http
GET http://localhost:8800/api/me
Authorization: Bearer <token>
```

4. Logout with Bearer token

```http
POST http://localhost:8800/api/logout
Authorization: Bearer <token>
```

If FE can call `login` and store/use the returned token for `me`, then the requirement "FE can login & get token" is complete.

## 7) Product & Category API (Website)

Public APIs for ReactJS list/detail pages:

- `GET /api/categories` (category list)
- `GET /api/products` (product list)
- `GET /api/products/{id}` (product detail)

Search / Filter / Pagination:

- Category list: `q`, `per_page`
- Product list: `q`, `category_id`, `min_price`, `max_price`, `per_page`

Examples:

```http
GET http://localhost:8800/api/categories?q=coffee&per_page=10
GET http://localhost:8800/api/products?q=latte&category_id=1&min_price=2&max_price=8&per_page=12
GET http://localhost:8800/api/products/1
```

Both list APIs use Laravel API Resource + paginator response (`data`, `links`, `meta`) so FE React can render list page and paging controls directly.

## 8) Order & Checkout API

Learning content covered:

- Order flow
- Transaction
- Order item
- Price validation

APIs (require Bearer token):

- `POST /api/orders/checkout` - create order
- `GET /api/orders` - order history
- `GET /api/orders/{order}` - order detail

### Create order (checkout)

```http
POST http://localhost:8800/api/orders/checkout
Authorization: Bearer <token>
Content-Type: application/json

{
	"items": [
		{ "product_id": 1, "quantity": 2 },
		{ "product_id": 3, "quantity": 1 }
	],
	"client_total": 12.50
}
```

Important backend rules:

- Backend uses DB transaction for checkout.
- Backend calculates price from `products.price` (not from FE).
- `client_total` is optional, but if sent and mismatched, API rejects request.
- Inactive products cannot be ordered.

### Order history

```http
GET http://localhost:8800/api/orders?per_page=10
Authorization: Bearer <token>
```

### Order detail

```http
GET http://localhost:8800/api/orders/1
Authorization: Bearer <token>
```

User can only read their own orders.

## 9) Queue, Job, Email, Export CSV / Excel

Learning content covered:

- Laravel Queue
- Job
- Email
- Export CSV / Excel by Queue

Goals:

- Understand async processing
- Do not block request while heavy work is running

### Start queue worker

```bash
docker compose exec laravel.test php artisan queue:work
```

Keep this command running in a separate terminal.

### Email when creating order

When `POST /api/orders/checkout` succeeds:

- Order is created immediately (HTTP 201)
- A queue job `SendOrderCreatedEmailJob` is dispatched
- Worker sends email in background (no blocking checkout request)

Default mailer is `log`, so you can see email content in log output.

### Export product list using queue

Create export request:

```http
POST http://localhost:8800/api/exports/products
Authorization: Bearer <token>
Content-Type: application/json

{
	"format": "csv"
}
```

`format` supports:

- `csv`
- `xlsx` (default)

Response is `202 Accepted`, because file generation runs async.

Check export status:

```http
GET http://localhost:8800/api/exports/products/{export_id}
Authorization: Bearer <token>
```

Download file when status is `completed`:

```http
GET http://localhost:8800/api/exports/products/{export_id}/download
Authorization: Bearer <token>
```

Security rule: each user can only access their own export.

## Why Eloquent is better than raw SQL here

- One config for all containers: Eloquent reads .env, so you do not hardcode host, user, and password.
- Safer by default: Eloquent uses prepared statements and mass assignment rules.
- Easy migrations: schema changes are tracked and repeatable in Docker.
- Cleaner code: CRUD is simple and readable for beginners.

## 10) Production deploy with Docker Compose

This project now includes a production compose file:

- `compose.prod.yaml`
- `docker/nginx/default.conf`

Services:

- `app`: PHP-FPM Laravel runtime
- `web`: Nginx reverse proxy
- `queue`: queue worker (`php artisan queue:work`)
- `scheduler`: cron-like scheduler loop (`php artisan schedule:run`)
- `mysql`: MySQL 8.4

### Deploy steps

1. Prepare production `.env` values (`APP_ENV=production`, `APP_DEBUG=false`, real DB/mail credentials).
2. Build and start stack:

```bash
docker compose -f compose.prod.yaml up -d --build
```

3. Run one-time release commands:

```bash
docker compose -f compose.prod.yaml exec app php artisan key:generate --force
docker compose -f compose.prod.yaml exec app php artisan migrate --force
docker compose -f compose.prod.yaml exec app php artisan storage:link
docker compose -f compose.prod.yaml exec app php artisan config:cache
docker compose -f compose.prod.yaml exec app php artisan route:cache
docker compose -f compose.prod.yaml exec app php artisan view:cache
docker compose -f compose.prod.yaml exec app php artisan l5-swagger:generate --all
```

4. Verify health:

```bash
docker compose -f compose.prod.yaml ps
docker compose -f compose.prod.yaml logs -f web app queue
```

## 11) Railway deployment

Use the complete checklist in [docs/railway-deploy-checklist.md](docs/railway-deploy-checklist.md).
