# 45 Seconds

**45 Seconds** is a platform for building **single-product, mobile-only sales
pages** for Facebook / Instagram / TikTok ad traffic. Each visitor moves through
a short, visually structured 45-second buying journey:

`0s Hook → 5s Problem → 12s Demo → 20s Benefits → 27s Social Proof → 33s Offers → 38s Trust/FAQ → 45s Final CTA / Checkout`

No cart, no catalog, no desktop storefront — just **Ad → Landing Page → Offer →
Order**. The admin panel and the public pages are **mobile-first** and the
interface is **Arabic / RTL** by default.

> Full design notes live in [`docs/`](docs): architecture, database, tracking,
> order flow, security, decisions, roadmap.

## Requirements

- PHP **8.3+** (with `pdo`, `mbstring`, `gd`, `openssl`)
- Composer 2
- Node.js 20+ / npm
- **MySQL 8** for production (or SQLite for a quick local run)

## Installation

```bash
git clone <repo> 45-seconds && cd 45-seconds
composer install
cp .env.example .env
php artisan key:generate
```

### Database — MySQL 8 (production target)

Create a database and set credentials in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=forty_five_seconds
DB_USERNAME=root
DB_PASSWORD=secret
```

```sql
CREATE DATABASE forty_five_seconds CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Database — SQLite (quick local / tests)

```dotenv
DB_CONNECTION=sqlite
```

```bash
touch database/database.sqlite   # DB_DATABASE can stay default
```

### Migrate & seed

```bash
php artisan migrate --seed
php artisan storage:link
```

The seed creates users, roles/permissions, default settings, and a complete
**"Sleep Light"** demo product + published landing page (all sections, 3 offers,
testimonials, trust items, FAQ) so you can see the full experience immediately.

### Build the frontend

```bash
npm install
npm run build      # or: npm run dev
```

### Run

```bash
php artisan serve
```

- Admin panel: <http://localhost:8000/> → `/login`
- Demo landing page: <http://localhost:8000/p/sleep-light>

## Demo credentials

| Role        | Email                     | Password   |
|-------------|---------------------------|------------|
| Super Admin | `admin@45seconds.test`    | `password` |
| Staff       | `staff@45seconds.test`    | `password` |

> Change these before any real deployment.

## Tests

```bash
php artisan test
```

The suite (PHPUnit, in-memory SQLite) covers auth, permissions, products,
landing pages & publishing, offers/content, **server-side pricing &
price-tampering**, order validation & status transitions, tracking &
attribution, pixel settings, analytics, public access and draft protection,
and rate limiting.

## Formatting

```bash
./vendor/bin/pint
```

## Configuration

- **Tracking** (Meta / TikTok pixel ids + optional Conversions API tokens) is
  configured in the admin panel under **Settings → Tracking** — never in code.
  Tokens are encrypted at rest. See [`docs/TRACKING.md`](docs/TRACKING.md).
- App-level knobs (currency default, order-number format, checkout rate limit,
  visitor cookie) live in `config/fortyfive.php`.

## Notes

- MVP payment is **cash on delivery** only.
- For production, run a queue worker (`php artisan queue:work`) so server-side
  conversion jobs process off-request, and serve over HTTPS.
