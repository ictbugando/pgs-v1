# Architecture — PGS v2

Status: Draft v0.1 · Companion to [PRD](PRD.md). Detailed schemas and contracts are in [DESIGN](DESIGN.md).

---

## 1. Architectural drivers

1. **Money correctness over everything.** No lost, duplicated, or unexplained transactions.
2. **Hostile inputs at every edge.** Channels, integrating systems, and portal users are all untrusted.
3. **Many channels, one core.** Adding a bank must not touch business logic.
4. **One codebase, one installation per organisation.** Differences between organisations come from configuration, never code (D-009).
5. **Small team.** Operational simplicity beats theoretical scalability.
6. **Easy to install, upgrade, and support at many sites**, including on-premises servers run by the organisation.

## 2. Architectural style: modular monolith

PGS v2 is **one Go program (`pgs`) split into strictly bounded packages** (D-016). It compiles to a single static binary that runs as several **process roles** from the same image: `pgs serve api`, `pgs serve ingress`, `pgs serve portal`, `pgs worker` (background jobs and schedules), and the `pgs` admin CLI (install, upgrade, migrate, doctor, channel). On small installations every role can run in one process (`pgs serve all`).

Why not microservices? The team is small, and the transactional guarantees we need (payment + ledger + outbox in one DB transaction) are much easier inside one database. Module boundaries are enforced in code (Go `internal/` packages plus `depguard` import rules; see RULES §6), so any module can be extracted later if needed.

## 3. System context

```
                ┌──────────────────────────── PGS v2 ────────────────────────────┐
 Org apps  ───► │ api.<domain>        Integration REST API (HMAC-signed)         │
 systems   ◄─── │                     Webhooks out (signed)                      │
                │                                                                │
 Banks/MNOs ──► │ channels.<domain>   Channel ingress (mTLS / signature / CIDR)  │
            ◄── │                     Status queries, statements, refunds (out)  │
                │                                                                │
 Staff     ───► │ portal.<domain>     Organisation portal (2FA, RBAC)            │
 Payers    ───► │ pay.<domain>        Public invoice lookup & receipt verify     │
                └────────────────────────────────────────────────────────────────┘
                        │ SMS gateway (out)   │ Email (out)   │ Object storage
```

The box above is **one organisation's installation**. `<domain>` is that organisation's domain (e.g., `pay.<school>.ac.tz`), or a vendor subdomain for vendor-hosted installs (e.g., `<org>.<vendor-domain>`). Every organisation gets its own copy of this whole picture.

### 3.1 Deployment model: one codebase, many installations

```
                 ┌──────────── one Git repo, one main branch ────────────┐
                 │  tagged releases  →  signed container image vX.Y.Z   │
                 └───────────────┬──────────────────────────────────────┘
                                 │ same image everywhere
     ┌───────────────────────────┼───────────────────────────┐
     ▼                           ▼                           ▼
 ┌────────────┐            ┌────────────┐              ┌────────────┐
 │ BMC        │            │ School A   │              │ SACCO B    │   each: own DB, secrets,
 │ install    │            │ install    │              │ install    │   domain, channel accounts,
 │ org.yaml   │            │ org.yaml   │              │ org.yaml   │   backups
 └─────┬──────┘            └─────┬──────┘              └─────┬──────┘
       └──── health telemetry only (no personal data) ───────┘──► Vendor fleet dashboard
```

- **Configuration as code:** each installation has an `org.yaml` (non-secret settings: profile, sector, enabled channels, policies, feature flags, SMS templates, control-number org code) in a private **deployments repo**, plus secrets in that installation's own encrypted secret store (D-017). The app validates the config against a schema at boot and refuses to start if it is invalid.
- **No forks:** there are no per-organisation branches or code paths. A need specific to one organisation becomes a feature flag, a configuration option, or an adapter (channel or integration) that any organisation could enable (RULES C9).
- **Releases:** semantic versioning. Each installation runs a tagged release, and its version shows in the portal and the telemetry.
- **Hosting (D-011):** vendor-hosted or on the organisation's own server. The **environment is identical** in both: same signed image, same stack definition, same minimum requirements, and the same integration API/webhooks for the organisation's management system. Only `org.yaml`, secrets, and hostnames differ. Vendor-hosted means **one dedicated VPS per installation** (D-014), never shared between organisations. On-prem sites without registry access get an offline bundle. Requirements are in DESIGN §11.1.
- **Upgrades (D-012):** only after the organisation's **Update Coordinator** approves the version and maintenance window on the installation's Updates page (DESIGN §11.2). `pgs upgrade vX.Y.Z` checks for that approval, then runs pre-flight checks → backup → pull image → expand migrations → rolling restart (channel ingress last, kept up) → smoke tests → report. Rollback restores the previous image. Contract migrations only run in the next release.
- **Channels (D-010):** every supported channel adapter ships in every installation. Enabling a channel for an organisation is data, not code: its credentials go into the secret store and a `channel_accounts` row is activated (DESIGN §5.5).
- **Support access:** vendor staff have no standing access. The System Admin grants a time-boxed support account (2FA, audited, auto-expires).

Each hostname has its own ingress rules, rate limits, and WAF policy. **Channel ingress is isolated** so that portal or API traffic spikes, or a portal deploy, cannot block payment callbacks.

## 4. Modules

```
cmd/pgs/              the only entry point: serve (api|ingress|portal|all), worker, migrate,
                      install, upgrade, rollback, backup, doctor, channel …
internal/
  organization/       organisation profile (single row), branches, settings, feature flags
  identity/           users, roles, permissions, 2FA, API credentials
  payers/             payer registry
  invoicing/          invoices, line items, control numbers, bulk jobs
  channels/           adapter interface + one sub-package per channel (crdb, nmb, mkcb, mpesa, …)
  payments/           payment intake, allocation, state machine, reversals, suspense
  ledger/             accounts, journal entries, balance checks
  notifications/      outbox, webhook dispatcher, SMS, email
  reconciliation/     statement import, matching engine, exceptions
  refunds/            maker-checker refund workflow
  reporting/          read models, exports
  audit/              append-only audit log
  shared/             money, ids, clock, errs, crypto, mask
  platform/           db (pgx + sqlc), jobs (River), httpx (middleware), config, secrets, telemetry
db/
  migrations/         goose migrations (embedded in the binary)
  queries/            hand-written SQL, compiled to type-safe Go by sqlc
web/                  templ templates + static assets (embedded in the binary)
```
Each module package holds its domain types, its service (the public API other modules call), its sqlc-generated store (private to the module), and its HTTP handlers.

**Dependency direction** (enforced):
```
Channels ──► Payments ──► Invoicing ──► Payers ──► Organization
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
Org system ─POST /v1/invoices (HMAC, Idempotency-Key)─► API
  API: authenticate → authorise (key scopes) → validate → Invoicing.create()
    DB TX { insert invoice + items; allocate control number; insert outbox(invoice.created) }
  ◄─ 201 {invoice, control_number}
Worker: outbox → webhook invoice.created → org system ; SMS control number → payer
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
   └─► Relay job on River (polls every 1 s, SKIP LOCKED) → creates webhook_deliveries per subscribed endpoint
          └─► Dispatcher worker: POST signed payload
                2xx → DELIVERED
                other/timeout → retry with backoff: 10s, 1m, 5m, 30m, 2h, 6h, 12h, 24h (8 attempts ≈ 45h)
                exhausted → DEAD, alert System Admin; manual/API redelivery possible
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
- **Single-tenant:** one database per installation, holding one organisation's data. There is **no `tenant_id` column, no tenant scoping, and no RLS**. Isolation between organisations is physical (separate DB, host/containers, secrets, backups). Branch scoping inside an organisation is ordinary RBAC.
- **IDs:** ULIDs (sortable, non-guessable) as public IDs, with a prefix per type (`inv_`, `pay_`, `pyr_`, `evt_`).
- **Money:** `BIGINT amount_minor` + `CHAR(3) currency`. There is no floating-point arithmetic anywhere; the `money.Amount` type (int64 minor units + currency, checked arithmetic) is used everywhere.
- **Time:** `timestamptz`, stored in UTC.
- **Personal data:** name, phone, and email are encrypted at the application level (AES-256-GCM, key from the installation's secret store), with a blind index (HMAC) where search is needed.
- **Partitioning:** `channel_messages`, `audit_logs`, `webhook_deliveries`, and `ledger_entries` are partitioned by month from day one.
- **No Redis (D-017).** Background jobs, schedules, and the outbox relay run on **River**, a job queue stored in PostgreSQL, so a job is enqueued in the same transaction as the business change. Locks use Postgres advisory locks. The API nonce replay cache is a Postgres table with TTL cleanup. Rate limiting is in-process (token bucket) behind the reverse proxy's per-host limits. That is one less service to run and back up on every VPS.
- **File storage:** statements, bulk upload files, PDFs, exports on an encrypted local volume by default; S3-compatible storage optional via config. Never web-served directly: downloads go through authorised, time-limited links.

## 7. Security architecture

| Layer | Controls |
|---|---|
| Edge | TLS 1.2+/HSTS; WAF; per-host rate limits; channel host allows only whitelisted CIDRs at the firewall **and** the app |
| Channel auth | Per-channel strategy: mTLS client cert, request signature (HMAC/RSA), or payload encryption (AES-GCM for MKCB). CIDR check is always **in addition to**, never instead of, these |
| Integration API auth | HMAC-SHA256 request signing with key ID, timestamp (±300 s), nonce (replay cache 10 min); optional mTLS; per-key scopes and IP allow-list |
| Portal auth | Session cookies (Secure, HttpOnly, SameSite=Lax), CSRF, TOTP 2FA, lockout and rate limit, password policy (length ≥ 12, breached-password check) |
| Authorisation | RBAC with permissions and branch scoping; maker-checker for refunds, suspense allocation, and collection-account changes |
| Secrets | Built-in encrypted secret store (D-017): secrets encrypted with AES-256-GCM (envelope encryption) under a master key delivered to the service as a root-only file/systemd credential, never stored in the DB, image, or git. HashiCorp Vault supported as an optional backend. Nothing in git or images. Per-installation and per-channel credentials (never shared between organisations), rotatable |
| Data | Personal data column encryption, encrypted backups, DB access only from the app subnet, no public DB port |
| Vendor access | No standing vendor access; time-boxed support accounts granted by the System Admin; SSH/host access via bastion with session recording for vendor-hosted installs |
| Audit | Append-only `audit_logs` (DB role has INSERT only; hash-chained rows for tamper evidence) |
| Supply chain | `go.sum` checksums + `go mod verify`, `govulncheck`, Dependabot/Renovate, reproducible builds (`-trimpath`), SBOM, image scanning (Trivy), cosign-signed images |

Threat model summary (STRIDE) and controls are in DESIGN §9.

## 8. Deployment topology

```
                 Internet (443 only)
                        │
          ┌─────────────┴──────────────┐
          │ Caddy reverse proxy (TLS)  │  hosts: api / channels / portal / pay
          └──┬─────────┬──────────┬────┘  per-host rate limits; channels host IP-allow-listed
             │         │          │
      ┌──────▼──┐ ┌────▼────┐ ┌───▼─────┐
      │ pgs     │ │ pgs     │ │ pgs     │   same image, different role
      │ api     │ │ ingress │ │ portal  │   (Large size: ×2 across two app hosts)
      └────┬────┘ └────┬────┘ └────┬────┘
           └───────────┼───────────┘
             ┌─────────┴─────────┐      ┌───────────────────────────────┐
             │ PostgreSQL 16+    │◄─────┤ pgs worker (River queues):    │
             │ data · jobs ·     │      │ callbacks · outbox · webhooks │
             │ outbox · secrets  │      │ sms · recon · reports · cron  │
             └───────────────────┘      └───────────────────────────────┘
  Standard size: everything above on one VPS (Docker Compose), encrypted volume for files, off-host backups.
```

- **Runtime:** Docker images with Docker Compose + systemd. Vendor-hosted installations each get **their own dedicated VPS** (D-014), and large installations get the multi-host layout in DESIGN §11.1. No Kubernetes; the one-VPS-per-organisation model doesn't need it.
- **Environments:** `local` → `sandbox` (integrator-facing test, channel simulators) → `staging` (real channel UAT) → `production`. There is no shared DB between environments.
- **Deploys:** zero-downtime rolling. DB migrations must be backward compatible (expand → migrate → contract).
- **Backups:** Postgres WAL archiving + nightly base backup to encrypted off-site storage. PITR. Restore drill every quarter.

## 9. Observability

- **Logs:** structured JSON to stdout → Loki/ELK. Mandatory fields: `trace_id`, `org_code` (installation label), `app_version`, `module`, `event`. Personal data masked.
- **Tracing:** OpenTelemetry. The `trace_id` flows channel request → payment → outbox → webhook, and is included in every API error response.
- **Metrics:** Prometheus. Per channel: requests, auth failures, p95 latency, error rate, "minutes since last payment". Webhook success rate, queue depth, recon exception count, ledger imbalance (must be 0).
- **Alerts:** ledger imbalance ≠ 0 (page); channel auth failures spike; channel silent during business hours; `FAILED_PROCESSING` messages > 0; webhook DLQ growth; queue lag > 60 s; recon exceptions > threshold.
- **Error tracking:** Sentry (with personal-data scrubbing), tagged by `org_code` and `app_version`.
- **Fleet view (vendor):** each installation pushes health-only telemetry (version, uptime, queue lag, error rates, recon status, last backup time) to the vendor's fleet dashboard. The payload is allow-listed and contains no personal or transaction-level data. Organisations can opt out, for example on-prem sites with no outbound internet.

## 10. Technology choices

| Concern | Choice | Notes |
|---|---|---|
| Language | **Go** (current stable release) | Compiled, strict static typing, explicit error handling, single static binary, strong standard-library crypto/TLS (D-016) |
| HTTP | `net/http` + `chi` router | Small and standard-library compatible |
| DB | PostgreSQL 16+ | `ON CONFLICT … RETURNING`, transactional DDL (safer upgrades), partitioning, `SKIP LOCKED`, jsonb, partial indexes |
| DB access | `pgx` + **`sqlc`** | Hand-written SQL compiled to type-checked Go at build time; no ORM; parameters always bound |
| Migrations | `goose`, embedded in the binary | Run by `pgs migrate` / `pgs upgrade` |
| Jobs, outbox, schedules | **River** (Postgres-backed) | Transactional enqueue with the business change; separate queues per concern; no Redis (D-017) |
| Portal UI | `templ` + htmx + Tailwind (pre-built CSS), embedded in the binary | Server-rendered, CSRF-friendly, no separate front-end app |
| Config | `org.yaml` + environment → typed structs, JSON-Schema validated at boot | |
| Secrets | Built-in encrypted store (default) or Vault | D-017 |
| Install/upgrade | `pgs` CLI (same Go binary) + Docker Compose; Ansible for the VPS baseline | Same tooling for vendor-hosted and on-prem |
| API docs | OpenAPI 3.1 (source of truth, contract-tested) | |
| Static analysis | `golangci-lint` (errcheck, exhaustive, gosec, depguard, forbidigo, bodyclose, sqlclosecheck, …), `go vet`, `staticcheck` | |
| Tests | `go test` with `-race`, native fuzzing, `testcontainers-go` (Postgres), channel simulators | |
| Supply chain | `govulncheck`, `go mod verify`, SBOM, Trivy, cosign | |
| Infra | Docker, Ansible, GitHub Actions | |

Alternatives considered are recorded as decisions in [MEMORY.md](MEMORY.md#2-decision-log).

## 11. Evolution path

- **Settlement/aggregator mode is out of scope** (D-002: we route, we never hold funds). The ledger is a collections sub-ledger. If the business model ever changes, that is a new decision with licensing implications, not an extension.
- **High volume:** extract `Channels` ingress to its own service (it only calls `Payments.ingest`), and add read replicas for reporting.
- **Payer app / checkout page:** builds on the public `pay.` host and the same API.
