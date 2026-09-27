# Project Memory — PGS v2

The project's long-term memory: what has been decided, what is assumed, what is still open, and what we learned. **Humans and AI agents read this before starting work and update it when finishing** (RULES §0). Keep entries short, dated, and never delete them. Supersede entries instead.

---

## 1. Snapshot

| | |
|---|---|
| **What** | Multi-sector payment **routing** gateway (schools, hospitals, any biller) for Tanzania |
| **Deployment** | **Single-tenant:** one codebase, a separate installation per organisation (own DB, secrets, domain). No tenants (D-009) |
| **Model** | Route only. Payers pay straight into each organisation's own collection account. We never hold funds (D-002) |
| **Predecessor** | Bugando PGS — CodeIgniter 4.2.1 + Joomla, hospital-only (this repo: `/ci`, `/public`) |
| **Stack** | PHP 8.3+ / Laravel 12+ / PostgreSQL 16+ / Redis / Docker (D-003, D-004) |
| **Phase** | Documentation drafted (v0.1); build not started. Next: T-0.1, T-4.0, T-8.6 |
| **Docs** | [PRD](PRD.md) · [ARCHITECTURE](ARCHITECTURE.md) · [DESIGN](DESIGN.md) · [RULES](RULES.md) · [TASKS](TASKS.md) |

## 2. Decision log

Format: **ID — decision** (date, status) — why · consequences

- **D-001 — Build a new system (PGS v2) instead of refactoring Bugando PGS** (2026-09-27, accepted)
  Why: the legacy code is hospital-specific (eHMS, patient tables), built on CI 4.2.1 + Joomla auth, has god-classes (`Engine.php`, 3k lines), and has pervasive security issues. Consequence: the legacy system needs emergency hardening meanwhile (T-8.6), and BMC becomes the first installation through a migration (Phase 8).

- **D-002 — Routing only; we never hold, settle, or disburse funds** (2026-09-27, **confirmed by owner**)
  Why: owner's business decision. It also greatly reduces regulatory exposure (no trust account or settlement). Consequences: the ledger is a collections memo sub-ledger (DESIGN §3); refunds are executed from the organisation's own account (DESIGN §8.4); settlement, payouts, and fee deduction are out of scope; each organisation must have its own collection account/paybill at each channel (`channel_accounts`).

- **D-003 — Modular monolith on Laravel + PostgreSQL** (2026-09-27, proposed)
  Why: small team; payment + ledger + outbox need one DB transaction; team PHP experience. Alternatives: microservices (too much ops overhead); Go (slower portal development); keep CodeIgniter (weaker ecosystem for queues, policies, testing). Postgres over MySQL for transactional DDL (safer unattended upgrades), `ON CONFLICT … RETURNING`, partitioning, `SKIP LOCKED`, and stricter semantics.

- **D-004 — Shared schema multi-tenancy with `tenant_id` + Postgres RLS** (2026-09-27, ~~proposed~~ **superseded by D-009**)
  Why: simplest operations; RLS adds defence in depth. Alternative: schema- or DB-per-tenant (harder migrations and reporting).

- **D-005 — Money as integer minor units + `Money` value object; API uses decimal strings** (2026-09-27, accepted)
  Why: the legacy system passed amounts as strings/floats (`"20000.00"`); float errors are unacceptable.

- **D-006 — Random 12-digit Luhn control numbers with a 3-digit org code** (2026-09-27, proposed; org code added with D-009)
  Why: non-enumerable and typo-detecting; the org code keeps numbers unique across separate installations. Needs confirmation that every channel accepts 12 digits (Q-002).

- **D-007 — Transactional outbox for all side effects** (2026-09-27, accepted)
  Why: the legacy system relied on cron re-sync (`getBillsNotSynched`) to catch lost notifications.

- **D-008 — Suspense instead of rejection for unallocatable money** (2026-09-27, accepted)
  Why: once money has moved, rejecting the notification doesn't return it. It just hides it.

- **D-009 — Single-tenant: one codebase, one installation per organisation** (2026-09-27, **confirmed by owner**)
  Why: owner's decision. It gives each organisation physical data isolation and its own domain, bank setup, and hosting choice, matching how BMC runs today. Consequences: no `tenant_id`, tenant scoping, or RLS; an `organization` single-row table plus `org.yaml` config (ARCHITECTURE §3.1); new vendor concerns: installer/upgrade tooling, release discipline, fleet version tracking, patch rollout, time-boxed vendor support access (RULES C9, C10, G5, S12; TASKS T-0.11–T-0.14, T-1.2); control numbers carry an org code (D-006). Main risk: installations drift apart. Mitigated by the no-forks rule and config-only differences.

- **D-010 — Channels are built once; a new organisation only supplies credentials** (2026-09-27, **confirmed by owner**)
  Why: most organisations will use the already-supported banks and mobile-money operators. Consequences: every adapter ships in every installation; onboarding a channel = secure credential intake + Test connection + activation (DESIGN §5.5, T-4.10), so a new organisation goes live the same day (PRD G1); a new, unsupported channel is a shared adapter for everyone, never a customisation. Answers Q-012.

- **D-011 — Identical environment whether vendor-hosted or on the organisation's server** (2026-09-27, **confirmed by owner**)
  Why: owner's decision. One environment to build, test, and support. Consequences: same image, stack, requirements, and integration contract for the organisation's management system (DESIGN §11.1, RULES C11, T-0.15). Answers Q-004 (hosting mode).

- **D-012 — Updates go through each organisation's designated personnel (Update Coordinator)** (2026-09-27, **confirmed by owner**)
  Why: owner's decision. The organisation controls when its system changes. Consequences: `update.coordinator` role, Updates page with approval of version + window, `pgs upgrade` blocked without approval, and no forced upgrades (DESIGN §11.2, RULES G6, T-7.8). Answers Q-011 (upgrade approval).

- **D-013 — No Bank of Tanzania licence or registration required** (2026-09-27, **owner decision**)
  Why: owner's position for the routing-only model (D-002): PGS v2 never receives, holds, or disburses funds. Consequences: no licensing workstream; closes Q-001. If the model ever moves towards holding funds, this decision must be revisited first.

- **D-014 — Vendor-hosted installations each run on their own VPS** (2026-09-27, **owner decision**)
  Why: a dedicated VPS per installation removes shared-data-centre concerns and keeps isolation physical (D-009). The provider and region can be chosen per organisation (e.g., in-country when required). Consequences: VPS provisioning baseline (T-0.16); sizing per DESIGN §11.1; closes Q-004.

## 3. Assumptions (verify, then convert to decisions)

- **A-001** Launch channels are the legacy ones: CRDB, NMB, MKCB (banks) first; M-Pesa, Mixx by Yas, Airtel next.
- **A-002** ~~Central hosted deployment~~ (superseded by D-009, then D-011). Now: vendor-hosted and on-prem are equal options with an identical environment.
- **A-003** Currency TZS at launch; USD later.
- **A-004** Peak load for the largest single installation: 50 TPS sustained / 200 TPS burst on channel callbacks (school-fee deadlines). Load test at 4×. Small installations can run on a single host.
- **A-005** Larger organisations integrate their systems (HMS, SIS, ERP) by API + webhooks; small organisations use the portal only.
- **A-006** Each installation sends SMS through the configured provider, using the organisation's own sender ID where registered.
- **A-007** Revenue is billed to each organisation separately (licence/subscription or per-transaction invoice), not deducted from payments (consistent with D-002).
- **A-008** Reference server sizes in DESIGN §11.1 (standard: 1 host with 4 vCPU / 8 GB; large: 2 app + 1 DB host). To be confirmed by load test T-9.1.

## 4. Open questions

| ID | Question | Owner | Blocks |
|---|---|---|---|
| Q-001 | ~~BoT registration/approval needed?~~ **Answered → D-013** (no). | — | — |
| Q-002 | Control-number format constraints for each channel (length, prefix, numeric only)? **Not yet decided** (owner, 2026-09-27). Default in DESIGN §4 stands until decided; the format is configurable, so this does not block early work. | Business → channel specs | D-006, T-2.2 (before R1) |
| Q-003 | Will we serve government institutions that are mandated to use GePG? If so, integrate GePG as a channel or exclude them? | Business | T-10.8 |
| Q-004 | ~~Hosting mode / data centre~~ **Answered → D-011, D-014** (identical environment; a dedicated VPS per vendor-hosted installation, region chosen per organisation). | — | — |
| Q-005 | Legal retention period for financial and audit records (assumed ≥ 7 years)? | Compliance | Partition/archival policy |
| Q-006 | Do receipts need TRA EFD/VFD integration, and if so is that the organisation's or our responsibility? | Compliance | T-2.6 |
| Q-007 | Which SMS provider? | Business | T-5.3 |
| Q-008 | Product name and domain (currently "PGS v2"; legacy brand "LipaSwitch")? | Business | Portal, docs site |
| Q-009 | How much BMC history to migrate (all vs last N years + archive)? | BMC + us | T-8.1 |
| Q-010 | Pricing model (licence, subscription, per transaction)? Affects reporting, not the payment path. | Business | Billing reports |
| Q-011 | ~~Who approves upgrades~~ **answered → D-012** (the organisation's Update Coordinator). Still open: may on-prem installations send health telemetry to the vendor? | Business | T-0.14 |
| Q-012 | ~~Separate integration per installation?~~ **answered → D-010** (adapters built once; organisation supplies credentials). Per-channel detail of which credentials and bank-side registrations are needed is captured in T-4.0. | Business → channels | — |
| Q-013 | Emergency security patches: if a `CRITICAL` fix is actively exploited and the Update Coordinator (and deputy) cannot be reached, may the vendor apply it? Proposal: only with the System Admin's approval as fallback, never silently. | Business | T-7.8 |

## 5. Lessons from the legacy system

From the analysis of Bugando PGS on 2026-09-27. RULES references these as **[Lx]**.

- **L1 — Secrets in git.** DB root password in `ci/app/Config/Database.php`, another DB password in `backup.sh`, MKCB AES key in `Constants.php` and `Controllers/Test.php`. → RULES S1–S2. **Rotate them (T-8.6).**
- **L2 — SQL injection.** ~96 raw `->query()` calls in `Engine.php` with interpolated variables, including the login path (`fetchUserName`). → RULES S5.
- **L3 — Auto-routing + partial filters.** `setAutoRoute(true)` while the auth filter covered only 5 controllers, so Test, backup, and old controllers were public. → RULES S9, T-0.9.
- **L4 — Weak callback auth.** MKCB `doCheckPermMalipoApi("6")` always returned TRUE; IP checks stripped the dots and matched prefixes (`41.7`, `13.2`). → RULES S10–S11, DESIGN §5.3.
- **L5 — Debug in production.** `CI_ENVIRONMENT = development`, `var_dump` in endpoints, debugbar dumps committed. → RULES S3, S18.
- **L6 — Lost notifications patched by cron.** eHMS sync failures were retried by cron jobs (`dosynchrepeat`, `getBillsNotSynched`) rather than a durable mechanism. → D-007, RULES I1–I3.
- **L7 — Money as strings/floats.** → D-005, RULES M1–M3.
- **L8 — Personal data everywhere.** Real-looking patient names and phone numbers in code comments; 231 MB of logs committed; `bmclist.txt` in the webroot. → RULES S15–S16.
- **L9 — Backups in the webroot.** `backup.sh` wrote dumps to `public/downloads/database/`. → RULES S17.
- **L10 — Copies as version control.** `EngineOld`, `EngineApril25`, `MalipoBk`, `MalipoApril`, a duplicate `Libraries/Controllers` tree, vendor dirs committed. → RULES S19–S20.
- **L11 — God-classes and channel logic spread through the core.** `Engine.php` (3,044 lines) and `global_helper.php` hold everything; a per-channel `doProcessXTransaction` duplicates business logic. → RULES C3, C6.
- **L12 — Guessable references.** Sequential bill numbers let anyone probing validation endpoints retrieve payer data. → D-006.

## 6. Glossary

| Term | Meaning |
|---|---|
| **Organisation** | The school, hospital, or other biller that owns an installation (formerly "tenant/merchant"; there are no tenants since D-009) |
| **Installation** | One running deployment of PGS v2 for one organisation: own DB, secrets, domain |
| **Org code** | 3-digit issuer code the vendor allocates to each installation; part of every control number |
| **Vendor** | Us: we build, install, upgrade, and support installations |
| **Update Coordinator** | The organisation's designated person (plus deputy) who receives release notices and approves each upgrade and its maintenance window |
| **Secure intake** | The only allowed way to enter channel credentials: portal form or `pgs channel add`, straight into the secret store |
| **Payer** | Person paying an invoice (parent, patient, customer) |
| **Invoice** | A request for payment with line items; the legacy "bill" |
| **Control number** | Unique numeric reference issued per invoice that the payer quotes at any channel |
| **Channel** | A bank or mobile-money operator through which payers pay |
| **Collection account** | The organisation's own account/paybill at a channel into which money lands |
| **Validation / inquiry** | Channel asks "is this control number payable, and for how much?" before taking money |
| **Notification / callback** | Channel tells us a payment happened |
| **Suspense** | Payments received that cannot (yet) be allocated to an invoice |
| **Recon** | Daily matching of our records against the channel statement |
| **Outbox** | DB table of events written in the same transaction as the change, delivered asynchronously |
| **Maker-checker** | One user proposes and a different user approves |
| **Malipo** | Swahili "payments" (legacy controller name) |
| **eHMS** | Bugando's hospital management system (first system integrated with PGS v2) |

## 7. Session log

Newest first. One line per session: date — who — what changed — next.

- 2026-09-27 — Claude (with owner) — Owner decided D-013 (no BoT licence) and D-014 (dedicated VPS per vendor-hosted install); Q-002 control-number format not yet decided. Added T-0.16. — Next: Q-002, Q-013, Q-007, Q-008; start T-0.1, T-4.0, T-8.6.
- 2026-09-27 — Claude (with owner) — Owner confirmed D-010 (credentials-only channel onboarding), D-011 (identical environment either hosting mode), D-012 (updates via the organisation's designated personnel). Added DESIGN §5.5 and §11, RULES S22/C11/G6, tasks T-0.15, T-4.10, T-7.8. — Next: Q-001, Q-002, Q-013; start T-0.1, T-4.0, T-8.6.
- 2026-09-27 — Claude (with owner) — Owner confirmed D-009 (single-tenant, one installation per organisation). Removed tenancy from all docs; added installer/upgrade/fleet/vendor-support design and tasks. — Next: owner answers Q-004, Q-011, Q-012.
- 2026-09-27 — Claude (with owner) — Analysed the legacy Bugando PGS; drafted PRD, ARCHITECTURE, DESIGN, RULES, TASKS, MEMORY v0.1; owner confirmed D-002 (routing only). — Next: owner answers Q-001, Q-002, Q-004, Q-007, Q-008; start T-0.1, T-4.0, T-8.6.
