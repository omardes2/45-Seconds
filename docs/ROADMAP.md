# Roadmap — 45 Seconds

Deliberately **out of scope** for the MVP (and why the architecture leaves room
for them):

- **A/B testing** — Product is already independent of Landing Page, so multiple
  pages per product are supported; an experiment layer can pick a variant.
- **Per-page tracking overrides** — integrations are global now; `page_sections`
  / `landing_pages.settings` can hold per-page pixel overrides later.
- **Meta CAPI / TikTok Events API at scale** — implemented behind flags; next
  steps are batching, retries/backoff dashboards, and richer user-data matching.
- **Payment gateway** — orders carry `payment_method` (currently `cod`); a
  gateway (Stripe/PayPal/local) can be added as another method.
- **WhatsApp** — order-confirmation button / notifications.
- **AI copy generation** — the structured builder (headline, problem, benefits,
  FAQ, CTA fields) is ready to receive AI-generated suggestions.
- **Google Ads / Snapchat / GA4** — add a `BrowserTracker`/`ServerTracker`
  driver; no pipeline changes.
- **Multi-tenant SaaS** — naming/architecture avoid single-tenant lock-in, but
  organizations/billing are intentionally not built.
- **Queue-based tracking ingestion** — funnel-event writes are synchronous and
  cheap today; move to a queue if volume demands.
- **Additional currencies / i18n** — currency is an enum; the code is prepared
  for more locales without a heavy translation system in v1.
