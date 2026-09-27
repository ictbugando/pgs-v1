# Architecture — PGS v2

Status: Draft v0.1 · Companion to [PRD](PRD.md). Detailed schemas and contracts are in [DESIGN](DESIGN.md).

---

## 1. Architectural drivers

1. **Money correctness over everything.** No lost, duplicated, or unexplained transactions.
2. **Hostile inputs at every edge.** Channels, merchants, and portal users are all untrusted.
3. **Many channels, one core.** Adding a bank must not touch business logic.
4. **Many tenants, one deployment.** Strict isolation.
5. **Small team.** Operational simplicity beats theoretical scalability.

## 2. Architectural style: modular monolith

PGS v2 is **one Laravel application split into strictly bounded modules**, deployed as several **process roles** (web, channel-ingress, workers, scheduler) from the same image.

Why not microservices? The team is small, and the transactional guarantees we need (payment + ledger + outbox in one DB transaction) are much easier inside one database. Module boundaries are enforced in code (Deptrac rules; see RULES §6), so any module can be extracted later if needed.

## 3. System context

```
                ┌──────────────────────────── PGS v2 ────────────────────────────┐
 Merchant  ───► │ api.<domain>        Merchant REST API (HMAC-signed)            │
 systems   ◄─── │                     Webhooks out (signed)                      │
                │                                                                │
 Banks/MNOs ──► │ channels.<domain>   Channel ingress (mTLS / signature / CIDR)  │
            ◄── │                     Status queries, statements, refunds (out)  │
                │                                                                │
 Staff     ───► │ portal.<domain>     Operator & merchant portal (2FA, RBAC)     │
 Payers    ───► │ pay.<domain>        Public invoice lookup & receipt verify     │
                └────────────────────────────────────────────────────────────────┘
                        │ SMS gateway (out)   │ Email (out)   │ Object storage
```

Each hostname has its own ingress rules, rate limits, and WAF policy. **Channel ingress is isolated** so that portal or API traffic spikes, or a portal deploy, cannot block payment callbacks.

## 4. Modules

```
app/Modules/
  Tenancy/          tenants, branches, settings, feature flags
  Identity/         users, roles, permissions, 2FA, API credentials
  Payers/           payer registry per tenant
  Invoicing/        invoices, line items, control numbers, bulk jobs
  Channels/         adapter contract + one adapter per channel (Crdb, Nmb, Mkcb, Mpesa, ...)
  Payments/         payment intake, allocation, state machine, reversals, suspense
  Ledger/           accounts, journal entries, balance checks
  Notifications/    outbox, webhook dispatcher, SMS, email
  Reconciliation/   statement import, matching engine, exceptions
  Refunds/          maker-checker refund workflow
  Reporting/        read models, exports
  Audit/            append-only audit log
  Shared/           Money value object, IDs, clock, errors, crypto helpers
```

**Dependency direction** (enforced):
```
Channels ──► Payments ──► Invoicing ──► Payers ──► Tenancy
                 │            │
                 └──► Ledger ◄┘
Notifications, Audit, Reporting: consume domain events; nothing depends on them synchronously.
Shared: everyone may depend on it; it depends on nothing.
```

- Modules talk through **public service interfaces** and **domain events**, never by reaching into each other's models or tables.
- `Channels` knows nothing about invoices. It produces a normalised `PaymentNotification` / `ValidationRequest` and calls `Payments`.

## 5. Key flows

### 5.1 Invoice creation
```
Merchant ─POST /v1/invoices (HMAC, Idempotency-Key)─► API
  API: authenticate → authorise tenant → validate → Invoicing.create()
    DB TX { insert invoice + items; allocate control number; insert outbox(invoice.created) }
  ◄─ 201 {invoice, control_number}
Worker: outbox → webhook invoice.created → merchant ; SMS control number → payer
```

### 5.2 Channel validation (inquiry)
```
Bank ─► channels/{crdb}/validate
  Adapter.authenticate(request)        // signature/mTLS/CIDR; fail → 401 + security event
  Adapter.parseValidation(request)     // → ValidationRequest{control_number, amount?}
  Payments.validate(req)               // read-only: invoice exists? status? amount due? policy?
  Adapter.renderValidation(result)     // channel-specific response format
```

### 5.3 Payment notification (the critical path)
```
Bank ─► channels/{crdb}/notify
  1. Adapter.authenticate()                         reject early, never touch DB if invalid
  2. Store raw request (encrypted) in channel_messages   ← ALWAYS, before any processing
  3. Adapter.parseNotification() → PaymentNotification{channel, channel_txn_id, control_number,
                                                        amount_minor, currency, payer_msisdn, paid_at}
  4. Payments.ingest(notification):
       DB TX (SERIALIZABLE or SELECT ... FOR UPDATE on invoice) {
         insert payment ON CONFLICT (channel_id, channel_txn_id) → return existing result (idempotent)
         allocate per invoice policy → POSTED | SUSPENSE
         Ledger.post(balanced entries)
         update invoice paid_amount/status
         insert outbox events (payment.received, invoice.paid|partially_paid|suspense.created)
         insert audit record
       }
  5. Adapter.renderAck(result) → 200 to bank          p95 ≤ 500 ms
Worker (async): outbox → webhooks, SMS receipt
```
If step 4 fails with an unexpected error, the raw message from step 2 stays in `channel_messages` with status `FAILED_PROCESSING`. It raises an alert and can be replayed. The channel receives the error code that its spec says will make it retry.

### 5.4 Webhook delivery (outbox pattern)
```
outbox_events (written in the same TX as the domain change)
   └─► Relay worker (polls every 1 s, SKIP LOCKED) → creates webhook_deliveries per subscribed endpoint
          └─► Dispatcher worker: POST signed payload
                2xx → DELIVERED
                other/timeout → retry with backoff: 10s, 1m, 5m, 30m, 2h, 6h, 12h, 24h (8 attempts ≈ 45h)
                exhausted → DEAD, alert tenant + operator; manual/API redelivery possible
```

### 5.5 Daily reconciliation
```
Scheduler 02:00 EAT → per channel: fetch statement (API/SFTP) or await manual upload
  → normalise lines → match against payments (by channel_txn_id, then fuzzy: amount+time+ref)
  → results: MATCHED | MISSING_INTERNAL | MISSING_AT_CHANNEL | AMOUNT_MISMATCH | DUPLICATE
  → exceptions → recon_exceptions (open) → report email → dashboard
MISSING_INTERNAL → auto-create payment via Payments.ingest(source=RECON), flagged for review
```

## 6. Data architecture

- **PostgreSQL 16+**, a single cluster. Primary plus a streaming replica (reports and read APIs may use the replica; money writes never do).
- **Multi-tenancy:** shared schema with a `tenant_id` column on every tenant-scoped table. Enforced in two layers:
  1. an application global scope bound from the authenticated context;
  2. **Postgres Row-Level Security** policies using `current_setting('app.tenant_id')`, as defence in depth.
- **IDs:** ULIDs (sortable, non-guessable) as public IDs, with a prefix per type (`inv_`, `pay_`, `pyr_`, `evt_`).
- **Money:** `BIGINT amount_minor` + `CHAR(3) currency`. There is no float or decimal arithmetic in PHP code; a `Money` value object is used everywhere.
- **Time:** `timestamptz`, stored in UTC.
- **Personal data:** name, phone, and email are encrypted at the application level (AES-256-GCM, key from KMS/Vault), with a blind index (HMAC) where search is needed.
- **Partitioning:** `channel_messages`, `audit_logs`, `webhook_deliveries`, and `ledger_entries` are partitioned by month from day one.
- **Redis:** queues, rate limiting, locks, cache. **Never the source of truth.**
- **Object storage (S3-compatible, e.g., MinIO):** statements, bulk upload files, PDFs, exports. Private buckets, signed URLs.

## 7. Security architecture

| Layer | Controls |
|---|---|
| Edge | TLS 1.2+/HSTS; WAF; per-host rate limits; channel host allows only whitelisted CIDRs at the firewall **and** the app |
| Channel auth | Per-channel strategy: mTLS client cert, request signature (HMAC/RSA), or payload encryption (AES-GCM for MKCB). CIDR check is always **in addition to**, never instead of, these |
| Merchant auth | HMAC-SHA256 request signing with key ID, timestamp (±300 s), nonce (replay cache 10 min); optional mTLS; per-key scopes and IP allow-list |
| Portal auth | Session cookies (Secure, HttpOnly, SameSite=Lax), CSRF, TOTP 2FA, lockout and rate limit, password policy (length ≥ 12, breached-password check) |
| Authorisation | RBAC with permissions; tenant scoping; maker-checker for refunds, suspense allocation, and payout/bank-account changes |
| Secrets | HashiCorp Vault (or cloud KMS) → injected at runtime. Nothing in git or images. Per-channel/per-tenant credentials, rotatable |
| Data | Personal data column encryption, encrypted backups, DB access only from the app subnet, no public DB port |
| Audit | Append-only `audit_logs` (DB role has INSERT only; hash-chained rows for tamper evidence) |
| Supply chain | Composer lock, `composer audit`, Dependabot/Renovate, image scanning (Trivy), signed images |

Threat model summary (STRIDE) and controls are in DESIGN §9.

## 8. Deployment topology

```
               Internet
                  │
         ┌────────┴────────┐
         │ LB / WAF (TLS)  │  separate listeners: api / channels / portal / pay
         └──┬──────┬──────┬┘
            │      │      │
   ┌────────▼┐ ┌───▼─────┐ ┌▼────────┐
   │ web     │ │ ingress │ │ portal  │   php-fpm + nginx containers (same image, different role)
   │ (api)   │ │(channel)│ │         │   ≥ 2 replicas each
   └────┬────┘ └───┬─────┘ └────┬────┘
        └──────────┼────────────┘
          ┌────────┴─────────┐
          │ PostgreSQL (HA)  │  Redis (sentinel)   MinIO/S3   Vault
          └────────┬─────────┘
        ┌──────────┴──────────────────────────┐
        │ workers: callbacks(high) · outbox · │   Laravel Horizon, separate queues
        │ webhooks · sms · recon · reports    │   + 1 scheduler
        └─────────────────────────────────────┘
```

- **Runtime:** Docker images, deployed to a Tanzania-hosted data centre or cloud (data residency, Q-004). Start with Docker Compose + systemd on 2 hosts, or k3s. Move to Kubernetes only when needed.
- **Environments:** `local` → `sandbox` (merchant-facing test, channel simulators) → `staging` (real channel UAT) → `production`. There is no shared DB between environments.
- **Deploys:** zero-downtime rolling. DB migrations must be backward compatible (expand → migrate → contract).
- **Backups:** Postgres WAL archiving + nightly base backup to encrypted off-site storage. PITR. Restore drill every quarter.

## 9. Observability

- **Logs:** structured JSON to stdout → Loki/ELK. Mandatory fields: `trace_id`, `tenant_id`, `module`, `event`. Personal data masked.
- **Tracing:** OpenTelemetry. The `trace_id` flows channel request → payment → outbox → webhook, and is included in every API error response.
- **Metrics:** Prometheus. Per channel: requests, auth failures, p95 latency, error rate, "minutes since last payment". Webhook success rate, queue depth, recon exception count, ledger imbalance (must be 0).
- **Alerts:** ledger imbalance ≠ 0 (page); channel auth failures spike; channel silent during business hours; `FAILED_PROCESSING` messages > 0; webhook DLQ growth; queue lag > 60 s; recon exceptions > threshold.
- **Error tracking:** Sentry (with personal-data scrubbing).

## 10. Technology choices

| Concern | Choice | Notes |
|---|---|---|
| Language/framework | PHP 8.3+ / Laravel 12+ | Team familiarity from the legacy system; mature queues, migrations, policies, testing |
| DB | PostgreSQL 16+ | RLS, `ON CONFLICT`, partitioning, `SKIP LOCKED`, strong transactional semantics |
| Queue | Redis + Laravel Horizon | Separate queues and worker pools per concern |
| Portal UI | Laravel + Livewire (or Inertia/Vue) + Tailwind | Server-rendered, CSRF-friendly |
| API docs | OpenAPI 3.1 (source of truth, contract-tested) | |
| Static analysis | PHPStan level 8 (Larastan), Deptrac, PHP-CS-Fixer / Pint | |
| Tests | Pest/PHPUnit, Testcontainers (Postgres), channel simulators | |
| Infra | Docker, Terraform/Ansible, GitHub Actions | |

Alternatives considered are recorded as decisions in [MEMORY.md](MEMORY.md#2-decision-log).

## 11. Evolution path

- **Settlement/aggregator mode is out of scope** (D-002: we route, we never hold funds). The ledger is a merchant-collections sub-ledger. If the business model ever changes, that is a new decision with licensing implications, not an extension.
- **High volume:** extract `Channels` ingress to its own service (it only calls `Payments.ingest`), and add read replicas for reporting.
- **Payer app / checkout page:** builds on the public `pay.` host and the same API.
