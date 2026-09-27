# Tasks — PGS v2

Backlog derived from [PRD](PRD.md) and [DESIGN](DESIGN.md). Every task follows [RULES](RULES.md) and its Definition of Done (RULES §11).

**Status:** `[ ]` todo · `[~]` in progress · `[x]` done · `[!]` blocked (reason in MEMORY)
**Size:** S ≤ 1 day · M 2–3 days · L ~1 week
**Release:** R0 foundations · R1 MVP/pilot · R2 GA · R3 later

Agents: pick the lowest-numbered unblocked `[ ]` task in the current release, mark it `[~]`, and reference its ID in branch and commit names.

---

## Phase 0 — Foundations (R0)

- [ ] **T-0.1 Repository & skeleton** · S · R0
  New repo `pgs-v2` (separate from the legacy repo). Laravel 12+, PHP 8.3, `declare(strict_types=1)`, module folders per ARCHITECTURE §4.
  *AC:* `composer install && php artisan test` passes; README with local setup; `.gitignore` covers `.env`, `storage/`, `vendor/`, logs.
- [ ] **T-0.2 Local dev environment** · S · R0 · deps T-0.1
  Docker Compose: app, nginx, Postgres 16, Redis, MinIO, Mailpit, Vault (dev mode).
  *AC:* `make up` brings up a working stack; `.env.example` has placeholders only.
- [ ] **T-0.3 CI pipeline** · M · R0 · deps T-0.1
  GitHub Actions: Pint, PHPStan L8 (Larastan), Deptrac, Pest with a Postgres service, `composer audit`, gitleaks, coverage report.
  *AC:* All jobs are required checks on `main`; a seeded secret fails the build.
- [ ] **T-0.4 Forbidden-pattern checks** · S · R0 · deps T-0.3
  Custom PHPStan rules or a grep job for: `dd(`, `dump(`, `var_dump(`, `print_r(`, `die(`, `exit(`, interpolated `*Raw(` SQL, `verify => false`, `env(` outside `config/`, and file names matching `*Old|*Bk|*Backup`.
  *AC:* Each pattern has a failing fixture test.
- [ ] **T-0.5 Shared kernel** · M · R0 · deps T-0.1
  `Money` (integer minor units, ISO-4217 exponent table, `parse`, `format`, arithmetic, currency guard), prefixed ULID generator, `Clock` interface, base domain exception, masking helpers.
  *AC:* Property tests for `Money::parse` (rejects floats with extra decimals, negatives, junk); 100 % coverage of `Money`.
- [ ] **T-0.6 Error handling & problem+json** · S · R0 · deps T-0.5
  Global exception handler → RFC 9457 with `code` + `trace_id`; no stack traces when `APP_DEBUG=false`; boot guard that refuses production with debug on.
  *AC:* Tests for each error class in DESIGN §6.3; boot guard test.
- [ ] **T-0.7 Observability baseline** · M · R0 · deps T-0.1
  JSON logging with `trace_id`/`org_code`/`app_version` processor, OpenTelemetry tracing, `/health/live` and `/health/ready`, Prometheus metrics endpoint (internal only), Sentry with personal-data scrubbing.
  *AC:* A request produces correlated log + trace; the metrics endpoint is not reachable publicly.
- [ ] **T-0.8 Secrets management** · M · R0 · deps T-0.2
  Vault integration (KV v2) for channel keys, webhook secrets, personal-data encryption keys; key-rotation helper.
  *AC:* No secret in config files; rotating a channel key requires no deploy.
- [ ] **T-0.9 Route-protection test** · S · R0 · deps T-0.1
  Test that fails if any route lacks one of `channel.auth`, `api.hmac`, `portal.auth`, `public` middleware groups (RULES S9).
  *AC:* Adding an unprotected route fails CI.
- [ ] **T-0.10 Environments & deploy** · L · R0 · deps T-0.3
  Container image build, Trivy scan, deploy to `sandbox` and `staging` (Compose/k3s via Ansible/Terraform), zero-downtime deploy, migrations step.
  *AC:* A merge to `main` deploys to staging automatically; production deploy is manual with approval.
- [ ] **T-0.11 `org.yaml` config schema & boot validation** · M · R0 · deps T-0.1
  JSON-Schema for per-organisation config (profile, sector, org code(s), channels, policies, feature flags, SMS templates); typed `OrgConfig`; app refuses to boot on invalid config; every setting has a default.
  *AC:* Invalid config fails boot with a clear message; schema documented; example configs for a school and a hospital.
- [ ] **T-0.12 Installer & `pgs` CLI** · L · R1 · deps T-0.10, T-0.11
  `pgs install` (new organisation from image + `org.yaml` + secrets), `pgs upgrade vX.Y.Z` (pre-flight → backup → migrate → rolling restart with channel ingress last → smoke test), `pgs rollback`, `pgs backup`/`restore`, `pgs doctor`. Works for vendor-hosted and on-prem, including an offline bundle.
  *AC:* Fresh install on a clean Ubuntu LTS VM in ≤ 30 min; upgrade and rollback proven in CI; `pgs upgrade` refuses to run without an approval record (T-7.8); identical behaviour on vendor-hosted and on-prem hosts (D-011).
- [ ] **T-0.13 Release & upgrade pipeline** · M · R1 · deps T-0.12
  Signed images per tag, changelog + upgrade notes, automated upgrade test from the previous two minor releases with seeded data (RULES G5).
  *AC:* A release cannot be published if the upgrade test fails.
- [ ] **T-0.14 Deployments repo & fleet telemetry** · M · R2 · deps T-0.7
  Private repo holding each installation's `org.yaml` + inventory (no secrets); org-code registry; opt-in health telemetry (allow-listed fields, RULES O5) to a vendor fleet dashboard showing version, health, last backup, recon status.
  *AC:* Telemetry payload test proves no personal or transaction data; dashboard lists all installations and their versions.

- [ ] **T-0.15 Reference environment & requirements** · S · R1 · deps T-0.12
  Publish the hosting requirements (DESIGN §11.1): server sizes, OS, network in/out, DNS/TLS, backups; `pgs doctor` checks a server against them before install.
  *AC:* `pgs doctor` passes on a reference VM and fails with clear messages on an undersized or misconfigured one.

## Phase 1 — Tenancy, identity & audit (R0/R1)

- [ ] **T-1.1 Organisation profile & branches** · S · R0 · deps T-0.5, T-0.11
  Single-row `organization` table (seeded from `org.yaml` on install), `branches`, runtime-editable `settings` with audit.
  *AC:* A second organisation row cannot be inserted; runtime settings are validated and audited.
- [ ] **T-1.2 Vendor support access** · M · R1 · deps T-1.5
  `vendor.support` role; time-boxed grant/revoke by `system.admin` (2FA, reason, ≤ 72 h, auto-expire); alerts and audit on grant and use.
  *AC:* A support account cannot log in after expiry; it cannot refund, approve, or export personal data.
- [ ] **T-1.3 Audit log** · M · R0 · deps T-0.5
  Append-only `audit_logs` (partitioned, hash-chained), `Audit::record()`, DB role with INSERT/SELECT only, chain-verification command.
  *AC:* An UPDATE on audit_logs fails with a permission error; tampering is detected by the verify command.
- [ ] **T-1.4 Portal authentication** · M · R1 · deps T-1.1
  Login, password policy (≥ 12 characters, breached-password check), lockout, TOTP 2FA (required for admins, vendor support, and sensitive roles), session hardening, CSRF.
  *AC:* Brute-force test locked out; 2FA enforced per role.
- [ ] **T-1.5 RBAC** · M · R1 · deps T-1.4
  Roles and permissions per DESIGN §7.1; policies; branch scoping.
  *AC:* Permission matrix test covering each role × action.
- [ ] **T-1.6 Maker-checker framework** · M · R1 · deps T-1.5
  Generic `ApprovalRequest` (maker ≠ checker, 2FA on approve, expiry, audit) reused by refunds, suspense, and bank-account changes.
  *AC:* The same user cannot approve their own request; expired requests cannot be approved.
- [ ] **T-1.7 API credentials & HMAC auth** · M · R1 · deps T-1.1, T-0.8
  Key creation (secret shown once), scopes, IP allow-list, rotation with overlap; `api.hmac` middleware per DESIGN §6.1 (timestamp window, nonce replay cache, `hash_equals`).
  *AC:* Tests for bad signature, skew, replay, revoked key, wrong scope, IP not allowed; a reference client in `tests/Support`.
- [ ] **T-1.8 Idempotency middleware** · S · R1 · deps T-1.7
  `Idempotency-Key` storage per DESIGN §2; replay of the stored response; 409 on reuse with a different body.
  *AC:* Parallel identical requests produce one resource.

## Phase 2 — Payers, invoices & control numbers (R1)

- [ ] **T-2.1 Payers** · S · R1 · deps T-1.2
  Encrypted personal-data columns + blind indexes; upsert by `external_ref`; API `PUT /payers/{external_ref}`, `GET /payers/{id}`.
  *AC:* Personal data is unreadable in the DB dump; search by phone works via the blind index.
- [ ] **T-2.2 Control number generator** · S · R1 · deps T-0.5, T-0.11
  Per DESIGN §4: env digit + org code + 7 CSPRNG digits + Luhn; uniqueness retry; format validator; usage metric and alert at 50 % of the org code's space.
  *AC:* 1M generated numbers are unique with valid Luhn; the validator rejects single-digit typos and transpositions.
- [ ] **T-2.3 Invoice aggregate & state machine** · M · R1 · deps T-2.1, T-2.2
  Invoices + items, item-total CHECK, amount/overpayment policies, `InvoiceStateMachine` (DESIGN §8.1), expiry scheduler job.
  *AC:* Illegal transitions throw; expiry job moves overdue invoices and emits events.
- [ ] **T-2.4 Invoice API** · M · R1 · deps T-2.3, T-1.8
  `POST/GET/PATCH /invoices`, `POST /invoices/{id}/cancel`, `GET /control-numbers/{cn}`, cursor pagination, filters.
  *AC:* OpenAPI spec updated; contract tests pass; out-of-scope IDs return 404.
- [ ] **T-2.5 Bulk invoices** · M · R2 · deps T-2.4
  `POST /invoices/bulk` + CSV upload; async job with per-row results; ≤ 5,000 rows; partial success.
  *AC:* A 5,000-row file processes in < 2 min on staging; row errors are reported without failing the whole job.
- [ ] **T-2.6 Invoice/receipt PDF + verification QR** · S · R2 · deps T-2.3
  *AC:* The QR opens the public verification page (T-7.6).

## Phase 3 — Payments & ledger core (R1)

- [ ] **T-3.1 Ledger** · M · R1 · deps T-0.5, T-1.2
  Accounts, journals, entries per DESIGN §3; `Ledger::post()` enforcing balance and currency; reversal support; invariant checker job + alert.
  *AC:* Unbalanced posting throws; the invariant job reports 0 violations after the full test suite.
- [ ] **T-3.2 Allocation policy** · S · R1 · deps T-2.3
  Pure `AllocationPolicy::decide()` implementing the DESIGN §8.5 table, including dry-run for validation.
  *AC:* Table-driven tests cover every row; property test: posted + suspense = received amount.
- [ ] **T-3.3 Payment ingest (idempotent)** · L · R1 · deps T-3.1, T-3.2
  `Payments::ingest()` per DESIGN §5.2 in one transaction: unique `(channel_id, channel_txn_id)`, invoice row lock, ledger, invoice update, suspense, outbox, audit, response snapshot; serialization retry.
  *AC:* The same notification sent 20× in parallel produces exactly 1 payment and identical responses; the ledger balances.
- [ ] **T-3.4 Payment state machine & reversals** · M · R1 · deps T-3.3
  States per DESIGN §8.2; channel-initiated reversal creates reversing journal entries and updates the invoice.
  *AC:* Reversing a payment on a PAID invoice returns it to PARTIALLY_PAID/ISSUED with correct balances.
- [ ] **T-3.5 Failed-processing replay** · S · R1 · deps T-3.3
  `FAILED_PROCESSING` channel messages can be replayed via an admin command/UI; alert when count > 0.
  *AC:* A simulated DB failure mid-ingest can be replayed to a correct final state.

## Phase 4 — Channels (R1 banks, R2 MNOs)

- [ ] **T-4.0 Collect channel specs** · S · R0 · **owner: business**
  Obtain current official API specs, UAT credentials, IP ranges, and sample messages for CRDB, NMB, MKCB into `docs/channels/<code>.md`. Done **once for all installations** (D-010). Also record, per channel, exactly which credentials an organisation must request from its bank/MNO and whether the bank must register a callback URL or IP. **Blocks all adapter tasks.**
- [ ] **T-4.1 Adapter framework** · M · R1 · deps T-3.3
  `ChannelAdapter` interface + DTOs (DESIGN §5.1), adapter registry, generated routes, `channel.auth:{code}` middleware, raw-message persistence before processing, trusted-proxy IP + CIDR check, per-channel metrics.
  *AC:* A dummy adapter passes a shared adapter conformance test suite.
- [ ] **T-4.2 Channel simulator** · M · R1 · deps T-4.1
  Sandbox tool (CLI + portal page) that sends signed validation/notification requests for any adapter, including duplicate, bad-signature, and wrong-amount scenarios.
  *AC:* Used by the integrator sandbox and by the E2E tests.
- [ ] **T-4.3 CRDB adapter** · M · R1 · deps T-4.0, T-4.1
- [ ] **T-4.4 NMB adapter (incl. recon API)** · M · R1 · deps T-4.0, T-4.1
- [ ] **T-4.5 MKCB adapter (AES-GCM, rotated key)** · M · R1 · deps T-4.0, T-4.1
  *AC (each bank adapter):* Conformance suite + recorded sample tests (RULES T2); UAT sign-off by the bank recorded in MEMORY.
- [ ] **T-4.6 M-Pesa adapter** · L · R2 · deps T-4.1
- [ ] **T-4.7 Mixx by Yas adapter** · L · R2 · deps T-4.1
- [ ] **T-4.8 Airtel Money adapter** · L · R2 · deps T-4.1
- [ ] **T-4.9 Status-query confirmation & circuit breakers** · M · R1 · deps T-4.3
  Confirm via status query where available (DESIGN §5.3); per-channel circuit breaker and timeouts (RULES E4).

- [ ] **T-4.10 Channel credential intake & Test connection** · M · R1 · deps T-4.1, T-0.8, T-1.6
  Portal *Channel accounts → Add* form and `pgs channel add` writing straight to the secret store (RULES S22); adapter `healthCheck()`; display of callback URLs and outbound IP; maker-checker activation; per-channel "credentials checklist" for organisations (from T-4.0).
  *AC:* A new organisation goes from credentials to an accepted test payment in the same day with no code change; secrets never appear in logs, DB dumps, or the UI after entry.

## Phase 5 — Notifications (R1)

- [ ] **T-5.1 Outbox relay** · M · R1 · deps T-3.3
  Relay worker (`SKIP LOCKED`, 1 s poll), creates deliveries per subscribed endpoint.
  *AC:* No event is lost when the worker is killed mid-batch (test).
- [ ] **T-5.2 Webhook endpoints & dispatcher** · M · R1 · deps T-5.1
  Endpoint management API, secret rotation, signing per DESIGN §6.4, retry schedule, DEAD state, auto-disable after 50 failures, redelivery API.
  *AC:* Signature verifiable with the documented algorithm; retry timings covered by tests with a fake clock.
- [ ] **T-5.3 SMS** · M · R1 · deps T-5.1
  SMS provider adapter (Q-007), templates from `org.yaml` (sw/en), sender ID, delivery status, rate limiting.
  *AC:* Invoice-created and payment-received SMS sent from outbox events; personal data masked in logs.
- [ ] **T-5.4 Email notifications & daily summary** · S · R2 · deps T-5.1

## Phase 6 — Reconciliation, suspense & refunds (R1/R2)

- [ ] **T-6.1 Statement import** · M · R1 · deps T-4.1
  From adapter `fetchStatement()`, SFTP, or manual upload (CSV/XLSX mapping per channel) → normalised `statement_lines`.
- [ ] **T-6.2 Matching engine** · M · R1 · deps T-6.1
  Exact match by `channel_txn_id`, then fallback (amount + time window + reference); exception types per DESIGN §2; MISSING_INTERNAL auto-ingest with `source=RECON` flagged for review.
  *AC:* ≥ 99 % auto-match on the recorded sample statement set; every unmatched line becomes an exception.
- [ ] **T-6.3 Recon scheduling, report & alerts** · S · R1 · deps T-6.2
  02:00 EAT run per channel; emailed exception report; dashboard status.
- [ ] **T-6.4 Suspense workflow** · M · R1 · deps T-1.6, T-3.3
  Queue, allocate to invoice / credit / refund via maker-checker; ledger postings per DESIGN §3.2.
- [ ] **T-6.5 Refunds** · M · R2 · deps T-1.6, T-3.1
  Request → approve → execute from the **organisation's** collection account (channel API or exported instruction file) → confirm; ledger + events.
  *AC:* PGS never initiates a disbursement from any account of its own (D-002).

## Phase 7 — Portal & reporting (R1/R2)

- [ ] **T-7.1 Portal shell** · M · R1 · deps T-1.5
  Layout, nav per DESIGN §10.1, sw/en i18n, Tailwind, accessibility baseline.
- [ ] **T-7.2 Invoices & payments screens** · M · R1 · deps T-7.1, T-2.4
- [ ] **T-7.3 Suspense, recon & refunds screens** · M · R1 · deps T-7.1, T-6.4
- [ ] **T-7.4 Developer screens** · S · R1 · deps T-1.7, T-5.2
  API keys, webhooks, event log with redeliver, sandbox simulator link.
- [ ] **T-7.5 Reports & exports** · M · R2 · deps T-7.1
  Collections, aging, suspense aging, recon status; CSV/XLSX export via background job to object storage with signed URLs.
- [ ] **T-7.6 Public payer page** · S · R2 · deps T-2.6
  Look up an invoice by control number + second factor (e.g., last 4 digits of phone); receipt verification; strict rate limit.
- [ ] **T-7.7 System admin area** · M · R1 · deps T-7.1
  Organisation profile, channel accounts, channel health, webhook DLQ, failed-processing replay, system health (version, queues, last backup), vendor support access grant/revoke.

- [ ] **T-7.8 Updates page & coordinator approval** · M · R1 · deps T-1.5, T-0.13
  *System → Updates*: current and available versions, release/upgrade notes, severity; `update.coordinator` approves version + window (2FA); email notifications and reminders (escalating for `CRITICAL`); `upgrade_approvals` read by `pgs upgrade`; post-upgrade result shown.
  *AC:* Upgrade blocked without approval; approval tied to one version and one window; full audit trail.

## Phase 8 — Bugando (BMC) migration: first installation (R1 pilot → R2 cut-over)

- [ ] **T-8.1 Legacy data audit** · M · R1
  Map legacy `bmc_*` tables to the v2 model; identify data quality issues; decide on history depth (Q-009).
- [ ] **T-8.2 Migration scripts** · L · R1 · deps T-8.1, T-2.3
  Idempotent ETL: patients → payers, bills → invoices, payments → payments + ledger (source=MANUAL, flagged migrated). Totals reconciled per day and per channel against the legacy DB.
  *AC:* Dry run on a copy: totals match to the shilling; report archived.
- [ ] **T-8.3 Legacy reference lookup** · S · R1 · deps T-8.2
  `legacy_references` so old bill numbers still resolve during the transition.
- [ ] **T-8.4 eHMS integration switch** · M · R1 · deps T-2.4, T-5.2
  eHMS moves from legacy `api/postpatbills` + cron sync to the v2 API + webhooks. Coordinate with the eHMS vendor.
- [ ] **T-8.5 Parallel run & cut-over** · M · R2 · deps T-8.2–T-8.4
  2 weeks running in parallel with daily comparison; cut-over runbook; channel endpoint switch at the banks; legacy made read-only then decommissioned.
- [ ] **T-8.6 Legacy emergency hardening** · S · **now** · independent
  Before and while v2 is built: rotate leaked DB/MKCB credentials, set production mode, remove Test/old controllers, require auth on MKCB callbacks, move backups out of the webroot. (See the legacy analysis in MEMORY §5.)

## Phase 9 — Hardening & go-live (R2)

- [ ] **T-9.1 Load test** · M · deps R1 complete
  k6 at 4× assumed peak (A-004): 200 TPS callbacks for 15 min; p95 ≤ 500 ms; 0 lost or duplicated payments.
- [ ] **T-9.2 Backup/restore drill** · S
  PITR restore into a scratch env; RTO/RPO measured and recorded.
- [ ] **T-9.3 External penetration test** · M
  Scope: channel ingress, integration API, portal. 0 critical/high open at go-live.
- [ ] **T-9.4 Runbooks** · M
  Incident response, channel outage, key rotation, failed-processing replay, recon exception handling, DR failover.
- [ ] **T-9.5 Compliance pack** · M · owner: business
  PDPC registration, data processing agreements with each organisation (for vendor-hosted installs and support access), retention policy, legal opinion on BoT position (Q-001).
- [ ] **T-9.6 Integrator documentation site** · M
  Rendered OpenAPI, webhook guide with verification code samples (PHP, JS, Python, Java), sandbox onboarding guide.

## Later (R3)
- [ ] T-10.1 Push-to-pay (MNO STK/USSD push) · [ ] T-10.2 Recurring invoices · [ ] T-10.3 PHP/JS SDKs · [ ] T-10.4 Moodle / WooCommerce plugins · [ ] T-10.5 Branch-level reporting & access · [ ] T-10.6 USD and multi-currency installations · [ ] T-10.7 HaloPesa & additional banks · [ ] T-10.8 GePG integration (if Q-003 says yes)
