# Engineering Rules — PGS v2

These rules are **mandatory** for every contributor, human or AI agent. "MUST" / "MUST NOT" are hard rules: a PR that breaks one is rejected. "SHOULD" rules need a written justification in the PR to deviate.

Many rules exist because the legacy Bugando PGS broke them. The reference in brackets, e.g. **[L1]**, points to the lesson in [MEMORY.md §5](MEMORY.md#5-lessons-from-the-legacy-system).

---

## 0. Rules for AI agents working in this repo

1. Read `MEMORY.md` and the relevant section of `TASKS.md` **before** starting work.
2. Work on one task ID at a time. Reference it in the branch name and commit message (`feat(payments): idempotent ingest [T-3.3]`).
3. When you finish, update the task status in `TASKS.md`. Add any new decision, assumption, or gotcha to `MEMORY.md`.
4. Never invent channel API specifications. If a bank or MNO spec is not in `docs/channels/`, stop and ask.
5. Never weaken a security control to make a test pass. Fix the test or ask.
6. Never commit anything resembling a secret, even a "test" one. Use `.env.example` placeholders.
7. If a rule here conflicts with a request, say so explicitly instead of silently choosing one.

## 1. Money

- **M1** MUST store money as integer **minor units** (`BIGINT amount_minor`) plus an ISO-4217 `currency`. MUST NOT use `float`, `double`, or `DECIMAL` arithmetic in PHP. **[L7]**
- **M2** MUST use the `Money` value object for all arithmetic, comparison, and formatting. Arithmetic across different currencies MUST throw.
- **M3** Amounts from external systems (strings like `"20000.00"`) MUST be parsed by `Money::parse()`, which rejects more decimal places than the currency allows, negatives where not allowed, and non-numeric input.
- **M4** Every money movement MUST go through `Ledger::post()` with balanced entries (Σdebit = Σcredit). Balances are **derived** from entries and MUST NOT be updated in place.
- **M5** Ledger entries are immutable. Corrections are made by **reversing entries**, never by UPDATE or DELETE.
- **M6** A payment MUST NOT be silently dropped or rejected once the money has left the payer. If it cannot be allocated, it goes to **Suspense**.

## 2. Idempotency & consistency

- **I1** Every channel notification MUST be deduplicated by a unique constraint on `(channel_id, channel_txn_id)`. A duplicate returns the **original** response. **[L6]**
- **I2** Every mutating integration API endpoint MUST accept an `Idempotency-Key` header, and MUST require one on `POST /invoices`, `POST /refunds`, and bulk jobs.
- **I3** Domain state change, ledger posting, and outbox event MUST be committed in **one DB transaction**. Never dispatch a webhook, SMS, or queue job directly from inside business logic; write to the outbox.
- **I4** Concurrent updates to an invoice MUST lock it (`SELECT … FOR UPDATE`) or rely on a unique constraint. Never read-modify-write without a lock.
- **I5** Queue jobs MUST be idempotent and safe to run twice.
- **I6** State transitions MUST go through the module's state machine. Direct `status = 'X'` assignments outside it are forbidden.

## 3. Security

### 3.1 Secrets & config
- **S1** MUST NOT commit secrets, passwords, keys, tokens, or certificates, including in comments, tests, docs, or "old" files. Secrets come from Vault or the environment at runtime. **[L1]**
- **S2** `.env`, `storage/`, logs, dumps, and uploads MUST be in `.gitignore`. CI runs a secret scanner (gitleaks) on every push. It is a blocking check.
- **S3** Production MUST run with `APP_ENV=production` and `APP_DEBUG=false`. The app MUST refuse to boot in production if debug is on. **[L5]**
- **S4** Each installation and each channel has its own credentials. Secrets MUST NOT be shared or reused between organisations' installations. Keys MUST be rotatable without a deploy.

### 3.2 Input & data access
- **S5** MUST use the query builder, Eloquent, or bound parameters. String-interpolated SQL is forbidden. CI blocks `DB::raw`, `whereRaw`, and `selectRaw` containing `$` variables unless the line has an allow-list comment reviewed by security. **[L2]**
- **S6** Every request payload is validated by a FormRequest or DTO with explicit rules (type, length, format) before it reaches domain code.
- **S7** Output to HTML MUST be escaped (Blade `{{ }}`). `{!! !!}` is forbidden on user or external data.
- **S8** XML parsing MUST disable external entities (XXE). JSON is decoded with `JSON_THROW_ON_ERROR` and depth limits.

### 3.3 Authentication & authorisation
- **S9** Every route MUST belong to a route group with explicit middleware: `channel.auth:{code}`, `api.hmac`, `portal.auth`, or `public`. A test asserts that no route is unprotected by accident. There is **no auto-routing**. **[L3]**
- **S10** Channel authentication MUST NOT rely on IP alone. The IP check uses full-address/CIDR matching (`IpUtils::checkIp`) against a configured list and uses the proxy-validated client IP. **[L4]**
- **S11** An authentication failure MUST NOT fall through to "allow". Default is deny. There is no `return true` placeholder, even temporarily. **[L4]**
- **S12** Vendor staff MUST NOT have standing access to any installation. Access is granted by the organisation, time-boxed, restricted to `vendor.support`, and audited (DESIGN §7.2).
- **S13** Sensitive actions (refund, suspense allocation, bank account change, API key creation, role change) MUST require maker-checker or 2FA re-confirmation, as specified in DESIGN §7.
- **S14** Signature comparisons MUST use constant-time comparison (`hash_equals`).

### 3.4 Data protection
- **S15** Personal data (names, phone numbers, emails, national IDs) MUST be encrypted at rest (encrypted casts) and MUST NOT appear in logs, exceptions, URLs, or analytics. Use the masking helpers (`2557*****570`). **[L8]**
- **S16** Test fixtures and seeders MUST use synthetic data, never copies of production records. **[L8]**
- **S17** Backups and exports MUST never be written inside a web-served directory. **[L9]**

### 3.5 Forbidden in the codebase
- **S18** No debug or test endpoints in production code: no `Test` controllers, no `var_dump`, `dd`, `dump`, `print_r`, or `die` / `exit` in request paths. Static analysis blocks them. **[L5]**
- **S19** No copies of files as backups (`FooOld.php`, `FooBk.php`, `FooApril.php`). Git is the history. **[L10]**
- **S20** No committed vendor directories, logs, cache, debugbar dumps, or build output. **[L10]**
- **S21** `CURLOPT_SSL_VERIFYPEER` / `verify => false` is forbidden.
- **S22** Channel credentials MUST enter an installation only through the secure intake (portal *Channel accounts* form or `pgs channel add`) and go straight into the secret store. They MUST NOT be sent or stored in email, chat, tickets, documents, `org.yaml`, or the deployments repo, and MUST NOT be shown again after entry.

## 4. Errors & resilience

- **E1** Errors in the integration API use **RFC 9457 problem+json** with a stable machine `code` and a `trace_id` (catalogue in DESIGN §6). No stack traces or SQL in responses.
- **E2** Channel responses MUST follow each channel's spec exactly, including the code that makes the channel retry. That mapping lives in the adapter only.
- **E3** Never swallow exceptions. Catch only to add context, translate to a domain error, or perform a documented fallback. `catch (\Throwable) {}` is forbidden.
- **E4** Every outbound HTTP call MUST set connect and total timeouts (default 5 s / 15 s), use retries with jittered backoff only for idempotent operations, and sit behind a circuit breaker per channel.
- **E5** The raw inbound channel message MUST be persisted **before** processing so it can be replayed (ARCHITECTURE §5.3).
- **E6** Any unexpected processing failure on the payment path MUST raise an alert. No payment failure may be log-only.

## 5. Logging & observability

- **O1** Use structured logging (`Log::info('payment.posted', [...])`), with an event name plus context. No string-concatenated log messages.
- **O2** Every log line and span includes `trace_id`, `org_code`, and `app_version`.
- **O3** Log level discipline: `error` = someone must act; `warning` = degraded but handled; `info` = business events; `debug` = off in production.
- **O4** Every new channel or queue MUST come with metrics and at least one alert rule.
- **O5** Fleet telemetry MUST use an explicit allow-list of health fields. Adding a field needs a privacy review. Personal or transaction-level data MUST NOT leave an installation.

## 6. Code structure

- **C1** Code lives in `app/Modules/<Module>/{Domain,Application,Infrastructure,Http}`. Deptrac enforces the dependency graph in ARCHITECTURE §4.
- **C2** Controllers are thin: validate → call an application service → render. No business logic in controllers, models, or Blade.
- **C3** Channel-specific logic lives **only** in `Modules/Channels/Adapters/<Channel>`. The core never branches on channel code (`if ($channel === 'crdb')` is forbidden outside adapters). **[L11]**
- **C4** Sector-specific data (student class, MRN, etc.) goes in `metadata` JSON. No sector-specific columns or code paths in the core.
- **C5** PHP `declare(strict_types=1);` in every file. PHPStan level 8 with no new baseline entries.
- **C6** Classes ≤ 500 lines, methods ≤ 50 lines (SHOULD). God-classes like the legacy `Engine.php` are forbidden. **[L11]**
- **C7** Use `final` classes by default, readonly DTOs, and enums for statuses. No magic strings for states.
- **C8** Config comes via typed config classes. `env()` is only allowed inside `config/*.php`.
- **C9** **One codebase, no forks.** There MUST NOT be per-organisation branches, forks, or code paths (`if ($org === 'bmc')` is forbidden). A need specific to one organisation becomes a config option, a feature flag, or an adapter that any installation could enable.
- **C10** Every new setting MUST be added to the `org.yaml` schema with a safe default, so existing installations keep working after upgrade without editing their config.
- **C11** Code MUST NOT branch on hosting mode (vendor-hosted vs on-prem). The environment is identical (D-011); anything that genuinely differs is configuration.

## 7. Database

- **D1** Every schema change goes through a migration. Migrations MUST be backward compatible with the currently deployed code (expand/contract). They MUST run unattended on every installation, whatever its data volume: no manual steps, and long backfills run as resumable background jobs.
- **D2** The system is single-tenant: one database per organisation. Do not add `tenant_id` or tenant scoping. Branch scoping uses `branch_id` + RBAC.
- **D3** Foreign keys and `NOT NULL` are used wherever they make sense. Uniqueness is enforced by constraints, not application checks.
- **D4** No `DELETE` on financial tables (`payments`, `ledger_*`, `invoices`, `refunds`, `audit_logs`). Use status changes or reversals.
- **D5** Timestamps are `timestamptz` in UTC. Public IDs are prefixed ULIDs. Never expose auto-increment IDs.

## 8. API

- **A1** The OpenAPI spec is the contract. Change the spec first, then the code. Contract tests run in CI.
- **A2** Breaking changes only in a new version (`/v2`). Additive changes (new optional fields, new event types) are allowed, and receiving systems must ignore unknown fields.
- **A3** Pagination is cursor-based. Maximum page size is 100.
- **A4** Every list endpoint is filterable by `created_at` range and status.

## 9. Testing

- **T1** Every PR that touches Payments, Ledger, Channels, Invoicing, Recon, or Refunds MUST include tests. Coverage on those modules must be ≥ 80 %.
- **T2** Every channel adapter MUST have tests against recorded **real** sample messages (sanitised) for success, duplicate, invalid signature, unknown control number, amount mismatch, and malformed payload.
- **T3** Property-based or fuzz tests for `Money::parse`, allocation rules, and signature verification.
- **T4** Concurrency tests: the same notification delivered twice in parallel results in one payment.
- **T5** Integration tests run against real Postgres (Testcontainers), not SQLite.
- **T6** A ledger invariant check (Σ entries = 0 per journal) runs after every test that posts money.

## 10. Git & review

- **G1** Branch from `main`: `feat/…`, `fix/…`, `chore/…`, with the task ID. Use Conventional Commits.
- **G2** All changes go through a PR with ≥ 1 approving review. Changes to Payments, Ledger, Channels, auth, or crypto need a review from a designated owner (CODEOWNERS).
- **G3** CI must be green: lint, PHPStan, Deptrac, tests, `composer audit`, gitleaks, route-protection test, OpenAPI contract tests.
- **G4** No force-push to `main`. Releases are tagged (`vX.Y.Z`) with a changelog and upgrade notes.
- **G5** Every release MUST pass the upgrade test (install previous release with seeded data → upgrade → smoke tests → rollback) before it is published to installations.
- **G6** No installation is upgraded without a recorded approval for that exact version and window: from its Update Coordinator (D-012), or, only for an actively exploited `CRITICAL` fix when the coordinator and deputy are unreachable for 24 h, from its System Admin (D-015). Tooling MUST enforce this, and there is no bypass flag.

## 11. Definition of Done

A task is done when:
- [ ] Acceptance criteria in `TASKS.md` are met
- [ ] Tests written and passing; CI green
- [ ] OpenAPI / DESIGN docs updated if contracts changed
- [ ] Metrics/alerts added for new flows
- [ ] Audit logging present for state-changing actions
- [ ] `MEMORY.md` updated with any decision or gotcha
- [ ] Reviewed and merged
