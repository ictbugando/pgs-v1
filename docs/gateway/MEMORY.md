# Project Memory — PGS v2

The project's long-term memory: what has been decided, what is assumed, what is still open, and what we learned. **Humans and AI agents read this before starting work and update it when finishing** (RULES §0). Keep entries short, dated, and never delete them. Supersede entries instead.

---

## 1. Snapshot

| | |
|---|---|
| **What** | Multi-tenant, multi-sector payment **routing** gateway (schools, hospitals, any biller) for Tanzania |
| **Model** | Route only. Payers pay straight into each merchant's own collection account. We never hold funds (D-002) |
| **Predecessor** | Bugando PGS — CodeIgniter 4.2.1 + Joomla, hospital-only (this repo: `/ci`, `/public`) |
| **Stack** | PHP 8.3+ / Laravel 12+ / PostgreSQL 16+ / Redis / Docker (D-003, D-004) |
| **Phase** | Documentation drafted (v0.1); build not started. Next: T-0.1, T-4.0, T-8.6 |
| **Docs** | [PRD](PRD.md) · [ARCHITECTURE](ARCHITECTURE.md) · [DESIGN](DESIGN.md) · [RULES](RULES.md) · [TASKS](TASKS.md) |

## 2. Decision log

Format: **ID — decision** (date, status) — why · consequences

- **D-001 — Build a new system (PGS v2) instead of refactoring Bugando PGS** (2026-09-27, accepted)
  Why: the legacy code is hospital-specific (eHMS, patient tables), built on CI 4.2.1 + Joomla auth, has god-classes (`Engine.php`, 3k lines), and has pervasive security issues. Consequence: the legacy system needs emergency hardening meanwhile (T-8.6), and BMC becomes the first tenant through a migration (Phase 8).

- **D-002 — Routing only; we never hold, settle, or disburse funds** (2026-09-27, **confirmed by owner**)
  Why: owner's business decision. It also greatly reduces regulatory exposure (no trust account or settlement). Consequences: the ledger is a merchant-collections memo sub-ledger (DESIGN §3); refunds are executed from the merchant's own account (DESIGN §8.4); settlement, payouts, and fee deduction are out of scope; each merchant must have its own collection account/paybill at each channel (`tenant_channels`).

- **D-003 — Modular monolith on Laravel + PostgreSQL** (2026-09-27, proposed)
  Why: small team; payment + ledger + outbox need one DB transaction; team PHP experience. Alternatives: microservices (too much ops overhead); Go (slower portal development); keep CodeIgniter (weaker ecosystem for queues, policies, testing). Postgres over MySQL for RLS, `SKIP LOCKED`, partitioning, and stricter semantics.

- **D-004 — Shared schema multi-tenancy with `tenant_id` + Postgres RLS** (2026-09-27, proposed)
  Why: simplest operations; RLS adds defence in depth. Alternative: schema- or DB-per-tenant (harder migrations and reporting).

- **D-005 — Money as integer minor units + `Money` value object; API uses decimal strings** (2026-09-27, accepted)
  Why: the legacy system passed amounts as strings/floats (`"20000.00"`); float errors are unacceptable.

- **D-006 — Random 12-digit Luhn control numbers** (2026-09-27, proposed)
  Why: non-enumerable and typo-detecting. Needs confirmation that every channel accepts 12 digits (Q-002).

- **D-007 — Transactional outbox for all side effects** (2026-09-27, accepted)
  Why: the legacy system relied on cron re-sync (`getBillsNotSynched`) to catch lost notifications.

- **D-008 — Suspense instead of rejection for unallocatable money** (2026-09-27, accepted)
  Why: once money has moved, rejecting the notification doesn't return it. It just hides it.

## 3. Assumptions (verify, then convert to decisions)

- **A-001** Launch channels are the legacy ones: CRDB, NMB, MKCB (banks) first; M-Pesa, Mixx by Yas, Airtel next.
- **A-002** Central hosted deployment (not on-prem per client), in Tanzania.
- **A-003** Currency TZS at launch; USD later.
- **A-004** Peak load 50 TPS sustained / 200 TPS burst on channel callbacks (school-fee deadlines). Load test at 4×.
- **A-005** Merchants integrate by API + webhooks; small merchants use the portal only.
- **A-006** SMS is sent by us on behalf of the tenant, with tenant sender IDs where registered.
- **A-007** Revenue is billed to merchants separately (monthly invoice), not deducted from payments (consistent with D-002).

## 4. Open questions

| ID | Question | Owner | Blocks |
|---|---|---|---|
| Q-001 | Does a routing-only gateway need BoT registration/approval under the NPS Act 2015 (e.g., as a payment system provider or technical service provider)? Get a legal opinion. | Business | R2 go-live |
| Q-002 | Control-number format constraints for each channel (length, prefix, numeric only)? | Business → channel specs | D-006, T-2.2 |
| Q-003 | Will we serve government institutions that are mandated to use GePG? If so, integrate GePG as a channel or exclude them? | Business | T-10.8 |
| Q-004 | Hosting provider and data-residency requirements (PDPA 2022)? | Business/Ops | T-0.10 |
| Q-005 | Legal retention period for financial and audit records (assumed ≥ 7 years)? | Compliance | Partition/archival policy |
| Q-006 | Do receipts need TRA EFD/VFD integration, and if so is that the merchant's or our responsibility? | Compliance | T-2.6 |
| Q-007 | Which SMS provider? | Business | T-5.3 |
| Q-008 | Product name and domain (currently "PGS v2"; legacy brand "LipaSwitch")? | Business | Portal, docs site |
| Q-009 | How much BMC history to migrate (all vs last N years + archive)? | BMC + us | T-8.1 |
| Q-010 | Pricing model (per transaction, tiered, monthly)? Affects reporting, not the payment path. | Business | Billing reports |

## 5. Lessons from the legacy system

From the analysis of Bugando PGS on 2026-09-27. RULES references these as **[Lx]**.

- **L1 — Secrets in git.** DB root password in `ci/app/Config/Database.php`, another DB password in `backup.sh`, MKCB AES key in `Constants.php` and `Controllers/Test.php`. → RULES S1–S2. **Rotate them (T-8.6).**
- **L2 — SQL injection.** ~96 raw `->query()` calls in `Engine.php` with interpolated variables, including the login path (`fetchUserName`). → RULES S5.
- **L3 — Auto-routing + partial filters.** `setAutoRoute(true)` while the auth filter covered only 5 controllers, so Test, backup, and old controllers were public. → RULES S9, T-0.9.
- **L4 — Weak callback auth.** MKCB `doCheckPermMalipoApi("6")` always returned TRUE; IP checks stripped the dots and matched prefixes (`41.7`, `13.2`). → RULES S10–S11, DESIGN §5.3.
- **L5 — Debug in production.** `CI_ENVIRONMENT = development`, `var_dump` in endpoints, debugbar dumps committed. → RULES S3, S18.
- **L6 — Lost notifications patched by cron.** Merchant sync failures were retried by cron jobs (`dosynchrepeat`, `getBillsNotSynched`) rather than a durable mechanism. → D-007, RULES I1–I3.
- **L7 — Money as strings/floats.** → D-005, RULES M1–M3.
- **L8 — Personal data everywhere.** Real-looking patient names and phone numbers in code comments; 231 MB of logs committed; `bmclist.txt` in the webroot. → RULES S15–S16.
- **L9 — Backups in the webroot.** `backup.sh` wrote dumps to `public/downloads/database/`. → RULES S17.
- **L10 — Copies as version control.** `EngineOld`, `EngineApril25`, `MalipoBk`, `MalipoApril`, a duplicate `Libraries/Controllers` tree, vendor dirs committed. → RULES S19–S20.
- **L11 — God-classes and channel logic spread through the core.** `Engine.php` (3,044 lines) and `global_helper.php` hold everything; a per-channel `doProcessXTransaction` duplicates business logic. → RULES C3, C6.
- **L12 — Guessable references.** Sequential bill numbers let anyone probing validation endpoints retrieve payer data. → D-006.

## 6. Glossary

| Term | Meaning |
|---|---|
| **Tenant / Merchant** | An organisation collecting payments (school, hospital, …) |
| **Payer** | Person paying an invoice (parent, patient, customer) |
| **Invoice** | A request for payment with line items; the legacy "bill" |
| **Control number** | Unique numeric reference issued per invoice that the payer quotes at any channel |
| **Channel** | A bank or mobile-money operator through which payers pay |
| **Collection account** | The merchant's own account/paybill at a channel into which money lands |
| **Validation / inquiry** | Channel asks "is this control number payable, and for how much?" before taking money |
| **Notification / callback** | Channel tells us a payment happened |
| **Suspense** | Payments received that cannot (yet) be allocated to an invoice |
| **Recon** | Daily matching of our records against the channel statement |
| **Outbox** | DB table of events written in the same transaction as the change, delivered asynchronously |
| **Maker-checker** | One user proposes and a different user approves |
| **Malipo** | Swahili "payments" (legacy controller name) |
| **eHMS** | Bugando's hospital management system (first merchant integration) |

## 7. Session log

Newest first. One line per session: date — who — what changed — next.

- 2026-09-27 — Claude (with owner) — Analysed the legacy Bugando PGS; drafted PRD, ARCHITECTURE, DESIGN, RULES, TASKS, MEMORY v0.1; owner confirmed D-002 (routing only). — Next: owner answers Q-001, Q-002, Q-004, Q-007, Q-008; start T-0.1, T-4.0, T-8.6.
