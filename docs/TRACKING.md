# Tracking — 45 Seconds

Two layers: **first-party internal analytics** (always on) and **vendor pixels /
Conversions APIs** (Meta, TikTok — configured in Settings → Tracking, off by
default).

## First-party (internal)

`TrackVisitor` middleware on the public routes:
- Resolves a **visitor** from a first-party cookie (`45s_vid`, 180 days,
  httpOnly, SameSite=Lax), creating one if absent.
- Resolves a **visit/session** stored in the Laravel session; on creation it
  captures `referrer` and UTM params + `fbclid` / `ttclid`.
- Records a `page_view` event on landing views.

`VisitorTracker::recordEvent()` writes `tracking_events`, deduplicated by
`event_id`. A public, CSRF-exempt, rate-limited beacon (`POST /t/event`) ingests
client funnel events from an allow-list: `view_content`, `demo_interaction`,
`offer_selected`, `checkout_opened`. `order_created` is recorded server-side.

Only what's needed for attribution/funnel analytics is stored — no third-party
identifiers.

## Vendor architecture (`TrackingManager` + drivers)

```
TrackingManager
 ├─ BrowserTracker (contract)   MetaBrowserTracker, TikTokBrowserTracker
 └─ ServerTracker  (contract)   MetaServerTracker,  TikTokServerTracker
```

Adding **Google Ads / Snapchat / GA4** later = implement a contract and register
the driver in `TrackingManager`; the public page and order pipeline don't change.

### Browser pixels
- Loaded **only** when the integration is enabled *and* has a pixel id. Pixel
  ids come from settings — **never hardcoded**. If disabled, no vendor script is
  emitted at all.
- A unified `window.fsTrack(internal, data, eventId)` dispatcher maps internal
  event names to each vendor and calls `fbq`/`ttq` when present.
- Event mapping:

  | Internal          | When                       | Meta             | TikTok           |
  |-------------------|----------------------------|------------------|------------------|
  | (base)            | page load                  | PageView         | ttq.page()       |
  | ViewContent       | landing load               | ViewContent      | ViewContent      |
  | InitiateCheckout  | checkout sheet opens       | InitiateCheckout | InitiateCheckout |
  | Purchase          | **thank-you page only**    | Purchase         | Purchase         |

- **Purchase fires only on the thank-you page**, i.e. only after the order was
  created server-side. It carries `value`, `currency`, `content_ids`,
  `content_name`, `num_items`, and `eventID = order_{id}`.

### TikTok event-name note
Verified against TikTok's April 2026 pixel docs: `Purchase` is a current
standard event and `PlaceAnOrder` is **soft-deprecated until 2027**. We map the
completed-order event to **`Purchase`** for both vendors (kept in each driver's
`eventMap()` so it's a one-line change if a business prefers `PlaceAnOrder`).

## Conversions API (server-side)

Prepared and implemented behind `capi_enabled` flags (off by default):
- `SendServerConversion` (queued, dispatched after commit) sends the
  server-side `Purchase` via Meta CAPI (`graph.facebook.com`) and/or TikTok
  Events API with the **same `event_id`** as the browser pixel for
  deduplication, and hashed (SHA-256) phone as user data.
- **Access tokens** are stored **encrypted** in settings, never rendered to the
  browser, and never logged. Failures log status/order id only — never secrets.

Because live delivery needs real vendor credentials, the CAPI path ships fully
implemented but **disabled by default**; enable it per-integration in Settings.

## Configuration
Settings → Tracking (permission `manage_tracking`). Global integrations for the
MVP; per-page overrides are on the roadmap (`docs/ROADMAP.md`).
