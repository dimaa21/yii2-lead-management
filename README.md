# Northline

Internal lead desk for a small sales team. Managers record inbound leads, move them through a short pipeline, and see who owns each conversation. Admins can delete leads and open the staff list.

This is a Yii2 application: session authentication, RBAC, ActiveRecord, migrations, and a JSON API over the same rules as the UI.

## Features

- Sign in, sign out, and self-service registration. New accounts are managers. Admin is seeded.
- Passwords are hashed with `yii\base\Security`. Routes that show data require a session.
- Roles `admin` and `manager`, enforced by Yii RBAC (`DbManager`), not by a check buried in a view.
- Lead list with search, filters, sorting, and pagination.
- Dashboard counts read from the database: total, new, in progress, converted, rejected.
- JSON API for the same lead records, with validation errors and HTTP status codes.
- Server-side validation. The browser checks are only a convenience.

## Stack

- PHP 8.2+
- Yii 2.0.55
- MySQL 8
- Bootstrap 5
- Composer

## Architecture

```
commands/        console seed
components/      status, source, and role enums
config/          web, console, and shared components
controllers/     thin HTTP controllers
controllers/api/ JSON API
migrations/      schema, indexes, RBAC items
models/          ActiveRecord, forms, LeadSearch
services/        lead rules and RBAC sync
views/           Bootstrap UI, no queries
web/             public entry point
```

Controllers stay thin. `LeadService` decides who owns a new lead and who may delete one. `LeadSearch` builds the filtered query with `andFilterWhere()`, so filter values are bound parameters, not concatenated SQL.

`Lead::getManager()` is `hasOne` because `manager_id` points at one user. `User::getLeads()` is the inverse `hasMany`. List pages eager-load these relations (`with()`), so the table does not run a query per row.

The `user.role` column is what the UI shows. Yii's auth manager is what `AccessControl` asks. `RbacService` writes both so they do not drift. There is no `auth_key` column: the remember-me cookie is an HMAC of the user id and the password hash, so a password change signs other browsers out.

A manager can create and edit leads, and is always the owner of a lead they create. They cannot reassign it and they cannot delete it. An admin can reassign and delete, and can open `/users`.

## Requirements

- PHP 8.2 or newer, with `pdo_mysql`, `mbstring`, and `intl`
- Composer 2
- MySQL 8 (or MariaDB 10.5+)

## Installation

```bash
composer install
cp .env.example .env
```

Create the database, then adjust `DB_*` in `.env` if the defaults (`root` with an empty password, database `northline`) do not match your server:

```sql
CREATE DATABASE northline CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

```bash
php yii migrate --interactive=0
php yii seed --interactive=0
php yii serve
```

Open http://localhost:8080

`php yii migrate` also applies the framework RBAC migrations from `@yii/rbac/migrations`. The console app is configured with both paths, so one command is enough.

Set `YII_ENV=prod` and `YII_DEBUG=false` outside local development. In that mode the error page and the API return a generic message for unexpected failures, and the debug toolbar is not registered. `COOKIE_VALIDATION_KEY` must be changed before production; the app refuses to boot in `prod` with the development key.

## Demo accounts

| Email | Password | Role |
| --- | --- | --- |
| admin@northline.test | Admin12345 | admin |
| mark@northline.test | Manager12345 | manager |
| sofia@northline.test | Manager12345 | manager |

Registration at `/register` creates another manager. It cannot create an admin.

The seed loads 21 leads. Dashboard numbers come from those rows. Run `php yii seed` again and it will not duplicate them.

## Application URLs

| URL | Who |
| --- | --- |
| `/login` | guests |
| `/register` | guests |
| `/dashboard` | signed-in staff |
| `/leads` | staff, with search, filters, sort, pagination |
| `/leads/create` | staff |
| `/leads/view/{id}` | staff |
| `/leads/update/{id}` | staff |
| `/users` | admin |

## API

The API uses the same session cookie as the UI. Sign in through the browser, then call the endpoints from that browser. A missing session is `401`. A manager calling delete is `403`.

| Method | Path | Success |
| --- | --- | --- |
| `GET` | `/api/leads` | `200` |
| `GET` | `/api/leads/{id}` | `200` |
| `POST` | `/api/leads` | `201` |
| `PUT` | `/api/leads/{id}` | `200` |
| `DELETE` | `/api/leads/{id}` | `204` |

Query parameters for the collection:

- `name`, `email` — partial match
- `status` — `new`, `in_progress`, `converted`, `rejected`
- `source` — `facebook`, `google`, `telegram`, `website`, `other`
- `manager_id`
- `sort` — for example `name` or `-created_at`. Unknown fields return `400`
- `page`, `per-page` — `per-page` is capped at 100

`POST` and `PUT` take a JSON object (`Content-Type: application/json`). `PUT` updates the fields you send and leaves the others as they are. A manager cannot change `manager_id`.

```json
{
  "name": "John Smith",
  "email": "john@example.com",
  "phone": "+1 555 010 1999",
  "source": "google",
  "status": "new",
  "comment": "Asked for a demo."
}
```

A single lead looks like this:

```json
{
  "id": 1,
  "name": "John Smith",
  "email": "john@example.com",
  "phone": "+1 555 010 1999",
  "source": "google",
  "status": "new",
  "comment": "Asked for a demo.",
  "manager_id": 2,
  "manager": { "id": 2, "name": "Mark Feldman" },
  "created_at": "2026-10-06T12:00:00+03:00",
  "updated_at": "2026-10-06T12:00:00+03:00"
}
```

The collection wraps rows:

```json
{
  "items": [],
  "pagination": { "total": 21, "page": 1, "per_page": 20, "page_count": 2 }
}
```

Validation failures are `422`:

```json
{
  "message": "Validation failed.",
  "status": 422,
  "errors": { "email": ["Email is not a valid email address."] }
}
```

Other statuses: `400` malformed JSON or an unknown sort, `401` no session, `403` the role cannot do that, `404` unknown lead, `500` unexpected failure. `password_hash` is not part of any JSON response.

## Validation

Enforced on the model, for both the form and the API:

- name: required, 2–255 characters
- email: required, email format
- phone: optional, a short phone-shaped string when present
- status and source: only the values listed above
- manager: must be an existing user

## Project layout

```
assets/AppAsset.php
commands/SeedController.php
components/LeadStatus.php
components/LeadSource.php
components/UserRole.php
config/common.php
config/web.php
config/console.php
config/db.php
config/params.php
controllers/SiteController.php
controllers/LeadController.php
controllers/UserController.php
controllers/api/LeadController.php
migrations/m261007_120000_create_user_table.php
migrations/m261007_120001_create_lead_table.php
migrations/m261007_120002_add_indexes.php
migrations/m261007_120003_init_rbac.php
models/User.php
models/Lead.php
models/LeadSearch.php
models/LoginForm.php
models/SignupForm.php
services/LeadService.php
services/RbacService.php
views/
web/index.php
```

`lead.manager_id` references `user.id` with `ON DELETE RESTRICT`. Indexes used by the list: `email`, `status`, `source`, `manager_id`, `created_at`.

## Tests

Unit and functional tests do not need MySQL. Acceptance tests boot the site on port 8080.

```bash
composer tests
```

Static analysis:

```bash
composer static
```
