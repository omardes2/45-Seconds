# Decisions — 45 Seconds

Notable architectural decisions and their rationale.

### D1. Publishing via a stored snapshot
`PageSnapshotService` serializes a fully-resolved page (product, enabled
sections, active offers/testimonials/faqs, media URLs, SEO) into
`landing_pages.published_snapshot`. Public pages render only the snapshot.
**Why:** simplest safe way to isolate visitors from draft edits, and it makes
the public request join-free/fast. The *same* builder feeds the admin preview
(from draft models) so preview is WYSIWYG.

### D2. Section content split: JSON vs. tables
Repeatable, simple content (hero copy, problem/benefit/trust item lists, demo
config) lives in `page_sections.settings` JSON. First-class entities with their
own lifecycle (**offers, testimonials, FAQs**) get tables. **Why:** avoids a
table explosion while keeping sortable/toggleable/queryable content relational.

### D3. Conversion-rate definition
`conversion_rate = orders_created / unique_sessions_on_page` (a session = one
`visits` row). Revenue and AOV exclude `cancelled` and `returned` orders.
Documented so the number is unambiguous.

### D4. Order numbers
`{prefix}{str_pad(order_id + start, pad)}` → default `45` + zero-padded id, e.g.
order id 1 → `450001`. **Why:** human-readable, guaranteed-unique (derived from
the PK), and configurable via `config/fortyfive.php`.

### D5. SQLite for dev/tests, MySQL for production
Migrations avoid MySQL-only features so the identical schema runs on both. The
test suite uses in-memory SQLite for speed. **Why:** frictionless local runs and
CI without a DB server; MySQL 8 remains the documented production target.

### D6. Admin = controllers + Blade + Alpine (Livewire installed)
The admin panel is server-rendered controllers + Form Requests + Blade with
Alpine for light interactivity. Livewire 3 is installed (per the stack) and is
the intended home for richer future admin widgets. **Why:** keeps the critical
order path and the whole panel simple and highly testable in the MVP.

### D7. Relative storage URLs
The `public` disk URL is `/storage` (relative), so published snapshots stay
host/domain independent; OG images are absolutized at render time. **Why:**
snapshots bake asset URLs — relative keeps them portable across domains/ports.

### D8. Tracking driver abstraction
`TrackingManager` + `BrowserTracker`/`ServerTracker` contracts. New ad platforms
are new drivers, not edits to the page/order pipeline. Browser Purchase and CAPI
Purchase share `event_id = order_{id}` for dedup.

### D9. CAPI implemented but disabled by default
Server-side Conversions API is fully implemented (queued, encrypted tokens,
hashed user data) but gated behind `capi_enabled` because live delivery requires
real vendor credentials that can't ship in the repo.

### D10. Authorization via permission Gates (not per-model Policies)
Single-installation, single-business: a small role/permission RBAC with
`Gate::before` for Super Admin and `can:` middleware covers the needs without
per-model Policy classes.
