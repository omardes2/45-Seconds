# Order Flow — 45 Seconds

## Public checkout

1. Visitor opens `/p/{slug}` (published page, rendered from the snapshot).
   `TrackVisitor` middleware creates/updates the visitor + visit and records a
   `page_view` event.
2. Visitor selects an **offer** (no reload). Alpine updates the sticky price,
   final-CTA summary and checkout summary. An `offer_selected` funnel event is
   beaconed.
3. Visitor taps **Order Now** → the checkout **bottom sheet** opens
   (`checkout_opened` + `InitiateCheckout` pixel).
4. Visitor submits name / phone / city / area / address / notes and the chosen
   `offer_id`. `POST /p/{slug}/order` (rate-limited per IP via `throttle:checkout`).

## Server-side creation (`OrderService::create`)

- `StoreOrderRequest` validates customer fields and that `offer_id` **exists,
  belongs to this page, and is active** (blocks cross-page and disabled offers).
- The controller re-fetches the offer and aborts 404 unless the page is
  Published with a snapshot.
- **Price is recomputed from the DB offer only.** `total = offer.price`,
  `quantity = offer.quantity`, `unit_price = total / quantity`,
  `currency = product.currency`. Any monetary field in the request is ignored.
  → see the price-tampering test in `tests/Feature/OrderTest.php`.
- Everything is wrapped in a DB transaction: create order → assign a
  human-readable `order_number` (`{prefix}{id+start}` → e.g. `450001`) →
  write the initial status history (`null → new`) → snapshot marketing
  attribution onto `order_attributions`.
- After commit, `OrderCreated` fires:
  - `RecordOrderCreatedTrackingEvent` records an `order_created` funnel event
    with a deterministic `event_id = order_{id}`.
  - `QueueServerConversion` queues the CAPI `Purchase` (only when a server
    integration is enabled).
- Visitor is redirected to `/p/{slug}/thank-you`, which fires the **browser
  Purchase** pixel with the same `event_id` (browser⇄CAPI dedup).

## Statuses

`new → contacted → confirmed → preparing → shipped → delivered`, plus
`cancelled` / `returned`. Arabic labels are in `OrderStatus`. Allowed
transitions are enforced by `OrderStatus::canTransitionTo()`; the admin UI only
offers valid next states and the server rejects invalid ones (422). Every change
appends an `order_status_history` row and an audit log entry. Internal notes are
recorded as same-status history entries.

## Admin

`Orders` board: mobile cards, search by number/phone/name, filters by status /
page / date range, order detail with change-status, add-note, attribution and a
full status timeline.

## Payment
COD only in the MVP (`payment_method = cod`). The order schema already carries
`payment_method`, so a gateway can be added later without migration churn.
