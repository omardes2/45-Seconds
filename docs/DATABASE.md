# Database — 45 Seconds

Production target: **MySQL 8**. Local/dev/tests: **SQLite**. Money is always
`DECIMAL(10,2)` — never float. Timestamps everywhere; soft deletes only where
they make sense (products, landing pages — **not** orders).

## Tables

### Identity & access
- **users** — Laravel default (name, email, password).
- **roles** — `name` (super_admin | staff), `display_name`.
- **permissions** — `name`, `display_name`.
- **permission_role**, **role_user** — pivots.

### Platform
- **settings** — `group`, `key`, `value` (longText), `is_encrypted`. Unique
  `(group,key)`. Cached; secrets encrypted at rest.
- **audit_logs** — `user_id`, `action`, `subject_type`, `subject_id`,
  `metadata` (json), `ip_address`, `created_at`.

### Catalog
- **products** — `name`, `slug` (unique), `sku`, `description`,
  `base_price` DECIMAL, `compare_at_price` DECIMAL, `currency` (ILS|USD),
  `status` (draft|active|archived), `main_image`. Soft deletes.
- **product_media** — `product_id`, `type` (image|video), `disk`, `path`,
  `url`, `mime`, `size`, `sort_order`.

### Landing pages
- **landing_pages** — `product_id`, `name`, `slug` (unique), `status`
  (draft|published|paused|archived), `title`, SEO/OG fields, `published_at`,
  `settings` (json), **`published_snapshot`** (longText json), `created_by`,
  `updated_by`. Soft deletes.
- **page_sections** — `landing_page_id`, `type`, `position`, `is_enabled`,
  `settings` (json). Indexed `(landing_page_id, position)`.
- **offers** — `landing_page_id`, `name`, `quantity`, `price` DECIMAL,
  `compare_at_price` DECIMAL, `badge_text`, `is_default`, `is_active`,
  `sort_order`.
- **testimonials** — `landing_page_id`, `customer_name`, `customer_image`,
  `rating`, `text`, `video_url`, `is_verified`, `is_active`, `sort_order`.
- **faqs** — `landing_page_id`, `question`, `answer`, `sort_order`, `is_active`.

### Orders
- **orders** — `order_number` (unique), `landing_page_id`, `product_id`,
  `offer_id` (nullable), customer fields (`full_name`, `phone`, `city`,
  `area`, `address`, `notes`), `quantity`, `unit_price`/`subtotal`/`total`
  DECIMAL, `currency`, `payment_method` (cod), `status`, attribution keys
  (`visitor_id`, `session_id`, `user_agent`, `ip_address`). Indexed on
  `status`, `phone`, `created_at`, `visitor_id`. **No soft deletes / no hard
  delete from UI.**
- **order_status_histories** — `order_id`, `from_status`, `to_status`,
  `user_id`, `note`, `created_at` (append-only, never overwritten).
- **order_attributions** — one-to-one with an order: `utm_*`, `fbclid`,
  `ttclid`, `referrer`, `landing_page_id`, `visitor_id`, `session_id`.

### Tracking
- **visitors** — `visitor_id` (uuid, unique), `first_seen_at`, `last_seen_at`,
  `user_agent`, `ip_address`.
- **visits** — `session_id` (uuid, unique), `visitor_id`, `landing_page_id`,
  seen timestamps, `referrer`, `utm_*`, `fbclid`, `ttclid`.
- **tracking_events** — `landing_page_id`, `visitor_id`, `session_id`, `type`
  (page_view|view_content|demo_interaction|offer_selected|checkout_opened|
  order_created), `event_id` (unique, for dedup), `metadata` (json),
  `created_at`.

## Referential integrity
Foreign keys with `cascadeOnDelete` for owned children (sections, offers,
testimonials, faqs, media, status histories, attributions) and `nullOnDelete`
for optional references (offer on order, users on audit/history).

## Indexes of note
Unique: product/landing-page slugs, order_number, settings `(group,key)`,
tracking `event_id`, visitor/session uuids. Composite: page sections/offers/
testimonials/faqs `(landing_page_id, position|sort_order)`, orders
`(status)`/`(created_at)`/`(phone)`.
