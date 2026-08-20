# Security — 45 Seconds

## Money & integrity
- **No price from the browser.** `OrderService` recomputes `total`, `quantity`,
  `unit_price` and `currency` from the offer row in the DB. Any client-sent
  monetary field is ignored. Covered by an explicit price-tampering test.
- **No arbitrary status changes.** Order status transitions are validated by
  `OrderStatus::canTransitionTo()`; invalid transitions are rejected (422).
- Orders are never hard-deleted from the UI and have no soft-delete either.

## AuthN / AuthZ
- Session auth, **no public registration** (users are seeded or created by an
  admin). Login is throttled (`throttle:login` + per-credential rate limiting).
- Password reset uses Laravel's broker.
- RBAC: `Gate::before` grants Super Admin all abilities; every route is gated by
  `can:<permission>` and Form Requests re-check `authorize()`. Nested resources
  verify ownership (`section/offer/... belongs to page`).

## Input & output
- All writes go through **Form Requests** with explicit rules.
- Blade auto-escapes output. Raw output (`{!! !!}`) is used only for controlled
  pixel bootstrap scripts built from validated settings (Meta pixel id is
  validated numeric; ids are `json_encode`d before injection).
- **Mass-assignment**: every model declares `$fillable`.
- **CSRF**: enabled globally; only the fire-and-forget analytics beacon
  (`/t/event`) is exempt (it writes first-party funnel data only).

## Uploads
- `MediaService` + Form Request rules enforce **MIME type, image validation and
  size limits**; files are stored with random names on the `public` disk and
  raster images are re-encoded to WebP. No executable upload paths.

## Secrets
- CAPI access tokens are **encrypted at rest** (`Crypt`), never sent to the
  browser, and never written to logs (failures log status/order id only).
- `.env.example` contains **no** real credentials; pixel ids and tokens are
  configured in the admin panel, not in git.

## Rate limiting
- `login` (auth), `checkout` (order creation, per IP, configurable), `t/event`
  (60/min). Order creation tolerates spam within a sensible per-IP window.

## Privacy
- First-party cookie only; UTM/click-ids stored for attribution. Phone numbers
  sent to CAPI are SHA-256 hashed. Only data needed for attribution/analytics is
  retained.

## Recommended production hardening
- Serve over HTTPS; set `SESSION_SECURE_COOKIE=true`.
- Run a queue worker so CAPI jobs process off-request.
- Configure a real mail transport for password resets.
- Put the app behind a CDN and enable object storage (S3) for media at scale.
