# Feralde Ecom

Monorepo containing five services:

| Service                | Port | Purpose                                       |
| ---------------------- | ---- | --------------------------------------------- |
| feralde_auth           | 8100 | Auth: login, register, sessions, permissions  |
| feralde_ecom_api       | 8101 | Public catalog: active products only, no auth |
| feralde_ecom_admin_api | 8102 | Admin API: full product CRUD, auth required   |
| feralde_ecom_frontend  | 3000 | Customer storefront: browse active products   |
| feralde_ecom_admin     | 3001 | Admin panel: full product management          |

---

## Running all services

All five services run on different ports and can be started simultaneously.

### Option A — single command (from monorepo root)

```bash
cd /home/ferl/feralde

php artisan serve --port=8100 --chdir=feralde_auth &
php artisan serve --port=8101 --chdir=feralde_ecom_api &
php artisan serve --port=8102 --chdir=feralde_ecom_admin_api &
npm run dev --prefix feralde_ecom_frontend &
npm run dev --prefix feralde_ecom_admin
```

To stop all background services: `kill %1 %2 %3 %4`

### Option B — separate terminals

```bash
# Terminal 1 — Auth (start first)
cd feralde_auth && php artisan serve --port=8100

# Terminal 2 — Public catalog API
cd feralde_ecom_api && php artisan serve --port=8101

# Terminal 3 — Admin API
cd feralde_admin_api && php artisan serve --port=8102

# Terminal 4 — Storefront
cd feralde_ecom_frontend && npm run dev

# Terminal 5 — Admin panel
cd feralde_admin && npm run dev --port=3001
```

Open in browser:
- Storefront: http://localhost:3000
- Admin panel: http://localhost:3001

---

## First-time setup

```bash
# 1. feralde_auth
cd /home/ferl/feralde/feralde_auth
composer install
php artisan migrate
php artisan db:seed   # creates admin user and roles

# 2. feralde_ecom_api
cd /home/ferl/feralde/feralde_ecom_api
composer install
php artisan migrate

# 3. feralde_ecom_admin_api
cd /home/ferl/feralde/feralde_admin_api
composer install
# no migrate needed — shares feralde_ecom database with feralde_ecom_api

# 4. feralde_ecom_frontend
cd /home/ferl/feralde/feralde_ecom_frontend
npm install

# 5. feralde_ecom_admin
cd /home/ferl/feralde/feralde_admin
npm install
```

---

## API endpoints

### feralde_auth (port 8100)

| Route                   | Auth    | Description                |
| ----------------------- | ------- | -------------------------- |
| POST /api/auth/login    | None    | Login                      |
| POST /api/auth/register | None    | Register                   |
| POST /api/auth/logout   | Session | Logout                     |
| GET  /api/user          | Session | Current user + permissions |

### feralde_ecom_api (port 8101) -- public, no auth

| Route                 | Auth | Description                   |
| --------------------- | ---- | ----------------------------- |
| GET /api/catalog      | None | List active products (public) |
| GET /api/catalog/{id} | None | Get active product (public)   |

### feralde_ecom_admin_api (port 8102) -- admin, session required

| Route                     | Auth  | Description       |
| ------------------------- | ----- | ----------------- |
| GET    /api/products      | Admin | List all products |
| POST   /api/products      | Admin | Create product    |
| GET    /api/products/{id} | Admin | Get any product   |
| PUT    /api/products/{id} | Admin | Update product    |
| DELETE /api/products/{id} | Admin | Delete product    |

---

## Database

| Service                | Database     | Host            | Port |
| ---------------------- | ------------ | --------------- | ---- |
| feralde_auth           | feralde_auth | 127.0.0.1       | 7777 |
| feralde_ecom_api       | feralde_ecom | 127.0.0.1       | 7777 |
| feralde_ecom_admin_api | feralde_ecom | 127.0.0.1       | 7777 |

feralde_ecom_api and feralde_ecom_admin_api share the same database.
Only feralde_ecom_api needs to run migrations for the ecom database.

---

## Frontend proxy routes

| Frontend              | Path prefix                                               | Backend target       |
| --------------------- | --------------------------------------------------------- | -------------------- |
| feralde_ecom_frontend | /api/auth/*, /api/user                                    | http://localhost:8100 |
| feralde_ecom_frontend | /api/catalog                                              | http://localhost:8101 |
| feralde_ecom_admin    | /api/auth/*, /api/user, /api/users, /api/roles, /api/permissions | http://localhost:8100 |
| feralde_ecom_admin    | /api/products                                             | http://localhost:8102 |

---

## Prerequisites

- PHP 8.2+ with extensions: pdo_mysql, mbstring, openssl, tokenizer, xml, ctype, json
- Composer
- Node.js 18+
- npm
- MySQL running on host 127.0.0.1, port 7777, user `sociadev`
