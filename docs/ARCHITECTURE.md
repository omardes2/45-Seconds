# Architecture — 45 Seconds

A monolithic Laravel 13 application that builds **single-product, mobile-only
sales pages** structured around a 45-second buying journey. No cart, no catalog,
no desktop storefront — the flow is `Ad → Landing Page → Offer → Order`.

## Stack

- **Laravel 13**, PHP 8.3+ — server-rendered Blade
- **Livewire 3** installed for future rich admin interactions (current admin is
  controller + Blade + Alpine, which keeps the surface testable and light)
- **Alpine.js** (+ `@alpinejs/collapse`) for public-page interactivity
- **Tailwind CSS v4** + **Vite** for assets
- **MySQL 8** in production, **SQLite** for local/dev and the test suite

## High-level layout

```
app/
  Enums/            Currency, statuses, section/demo types, permissions, audit actions
  Models/           Eloquent models (Product, LandingPage, PageSection, Offer, Order, …)
  Http/
    Controllers/    Admin\* (panel) + public (PublicPageController, PublicOrderController, TrackingEventController)
    Requests/       Form Requests (validation + authorization)
    Middleware/     TrackVisitor (first-party analytics)
  Services/
    Pages/          PageBuilder, PageSnapshotService, PagePublisher
    Orders/         OrderService (server-side pricing + status transitions)
    Tracking/       VisitorTracker, TrackingManager, Drivers (Meta/TikTok browser + server)
    Analytics/      AnalyticsService
    SettingsRepository, AuditLogger, MediaService
  Jobs/             SendServerConversion (CAPI, queued)
  Events/Listeners/ OrderCreated → funnel event + queued CAPI
resources/views/
  components/layouts/  admin + guest shells
  admin/**             mobile-first admin screens
  public/landing.blade.php + partials  the 45-second experience
```

## Key domains

### Products (independent of pages)
A `Product` (price, currency, media) can back **many** landing pages, enabling
A/B page variants later without duplicating the product.

### Landing pages & the structured builder
A `LandingPage` owns ordered `page_sections` (one per journey step). Section
*content* that is simple and repeatable (hero copy, problem/benefit/trust item
lists, demo config) lives in `page_sections.settings` (JSON). Content that is a
first-class entity — **offers, testimonials, FAQs** — has its own table.

`SectionType` is an enum/registry (`hero → final_cta`) that is easy to extend
without schema changes.

### Publishing (draft vs. published)
Publishing writes a fully-resolved **snapshot** (`landing_pages.published_snapshot`
JSON) via `PageSnapshotService`. The public page renders **only** the snapshot,
so draft edits never leak to visitors until re-published. The *same* snapshot
builder feeds the live admin **preview** (from draft models), guaranteeing WYSIWYG.
See `docs/DECISIONS.md`.

### Orders
`OrderService` recomputes every monetary value from the offer row in the
database — the client only ever chooses an offer id. Human-readable order
numbers, a status enum with validated transitions, and an append-only status
history. See `docs/ORDER_FLOW.md`.

### Tracking & attribution
A first-party `TrackVisitor` middleware creates visitors/visits, captures
UTM/click-ids, and records funnel events. `TrackingManager` + driver contracts
render Meta/TikTok browser pixels and (optionally) send Conversions API events.
See `docs/TRACKING.md`.

### Analytics
`AnalyticsService` computes overall + per-page metrics with a date range, all
Schema-guarded for a clean zero-state.

## Authorization
Lightweight RBAC: `roles` + `permissions` (+ pivots). A `Gate::before` hook
grants Super Admin everything; every other permission maps to a same-named Gate
ability. Routes are gated with `can:<permission>` middleware and Form Requests
re-check in `authorize()`.

## Performance posture (public page)
- One small CSS + one small JS bundle (Tailwind + Alpine, ~13KB + ~19KB gzip)
- No Livewire on public pages
- Snapshot rendering avoids DB joins at request time
- `loading="lazy"` on below-the-fold imagery; hero image is `fetchpriority=high`
- WebP conversion for uploaded imagery (`MediaService`)
