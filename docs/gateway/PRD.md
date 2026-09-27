# PRD — PGS v2: Multi-Sector Payment Gateway

| | |
|---|---|
| **Status** | Draft v0.1 |
| **Working name** | PGS v2 (placeholder — see `MEMORY.md` Q-008) |
| **Predecessor** | Bugando PGS (`/ci`, `/public` in this repo) |
| **Related docs** | [ARCHITECTURE](ARCHITECTURE.md) · [DESIGN](DESIGN.md) · [RULES](RULES.md) · [TASKS](TASKS.md) · [MEMORY](MEMORY.md) |

---

## 1. Summary

PGS v2 is a payment collection gateway for any organisation that bills people: schools, hospitals, SACCOs, utilities, churches, event organisers, and SaaS platforms. It is **one product codebase deployed as a separate, single-organisation installation for each customer** (D-009). Each organisation has its own database, domain, secrets and channel setup, and there is no tenant layer. Inside an installation, the organisation creates an **invoice** through the API or portal. The gateway issues a **control number**. The payer pays through any supported bank or mobile-money channel. PGS v2 then verifies the payment, records it in a double-entry ledger, notifies the organisation's systems (HMS, SIS, ERP) by signed webhook, and reconciles every transaction against the channel's statement daily.

It generalises the Bugando PGS (hospital-only, eHMS-coupled) and fixes that system's security and reliability problems.

## 2. Problem statement

Institutions in Tanzania collect money through many channels: several banks, M-Pesa, Mixx by Yas (Tigo Pesa), Airtel Money, HaloPesa, and cash. Today each institution typically:

- integrates each bank/MNO separately, or not at all, and matches payments to payers by hand;
- has no reliable real-time link between "payment received" and its own system (school SIS, hospital HMS, ERP);
- reconciles by spreadsheet, so missing, duplicate, and misallocated payments are found late or never;
- runs home-grown integrations with weak security. The legacy Bugando PGS had committed secrets, unauthenticated callbacks, SQL injection, and debug endpoints in production.

## 3. Goals

| # | Goal | Metric (target) |
|---|---|---|
| G1 | One codebase serves any sector | New organisation **live the same day** it provides its channel credentials: no code changes, no development (D-010) |
| G2 | Payments are never lost or double-counted | **0** unexplained ledger/statement differences after daily recon |
| G3 | The organisation's systems learn of payments in near real time | p95 callback → webhook **≤ 5 s** |
| G4 | Secure by default | 0 critical/high findings in pre-go-live pentest; all callbacks authenticated |
| G5 | High availability for payment ingestion | Channel callback endpoints **≥ 99.9 %** monthly |
| G6 | Automated reconciliation | ≥ **99 %** of transactions auto-matched per day |
| G7 | Migrate Bugando Medical Centre | BMC running on v2 with the legacy system decommissioned |
| G8 | Every installation stays current | 100 % of installations on a supported release; critical security patches offered to every Update Coordinator within 24 h of release and applied within **7 days** of approval (D-012) |

### Non-goals (v1)
- Card acquiring or storing card numbers (avoids PCI-DSS scope).
- **Holding funds, settlement, or payouts.** PGS v2 **routes only**: payers pay directly into each organisation's own collection account at the bank/MNO. PGS v2 never receives, holds, or disburses money. **Confirmed decision** — see `MEMORY.md` D-002.
- Lending, wallets or stored value for payers (the legacy "wallet" concept becomes a prepaid invoice; see §6.3).
- Replacing GePG for government-mandated collections (see Q-003).

## 4. Users & personas

| Persona | Description | Main needs |
|---|---|---|
| **Vendor** (us) | Builds, installs, upgrades and supports the product | Provision new installations, roll out releases, monitor fleet health, time-boxed audited support access |
| **System Admin** | Organisation's IT/finance lead | Configure the installation, users, channels, webhooks, invoice policies; view reports |
| **Update Coordinator** | Designated person at the organisation (D-012) | Receive release notices, approve updates and choose the maintenance window, tell the organisation's staff |
| **Cashier / Clerk** | Front-desk staff | Create invoices and look up payments; reprint receipts |
| **Accountant** | Back office | Reconciliation reports, exports, refunds approval |
| **Integrating System** (machine) | SIS / HMS / ERP | Create invoices via API; receive webhooks; query status |
| **Payer** | Parent, patient, customer | Get a control number, pay by any channel, receive an SMS receipt, verify a receipt |
| **Channel** (machine) | Bank / MNO | Validate a control number; post a payment notification; provide statements |
| **Auditor** | Internal/external | Read-only access to the audit trail, ledger, and recon results |

## 5. Key user journeys

1. **School term fees (bulk).** The bursar uploads a CSV or calls the bulk API for 1,200 students. Invoices are created with control numbers and payers get an SMS. Parents pay in instalments (partial payments allowed). The SIS receives an `invoice.partially_paid` / `invoice.paid` webhook for each payment.
2. **Hospital bill (single, exact).** The HMS creates an invoice at discharge. The patient pays by M-Pesa at the window. The HMS receives `invoice.paid` within seconds and releases the patient. (This is today's BMC eHMS flow.)
3. **Open-amount top-up.** A patient deposit or canteen top-up uses an invoice with an open amount (`amount_policy = OPEN`). Any amount is accepted and credited.
4. **Bank-counter payment with validation.** A CRDB teller enters the control number. The gateway returns the payer name and amount due. The teller posts the payment and the gateway acknowledges it.
5. **Wrong or unmatched payment.** A payer pays against an expired or cancelled invoice, or overpays. The payment is **not rejected silently**: it goes to Suspense, the organisation's accountant resolves it with approval, and everything is audited.
6. **Daily reconciliation.** At 02:00 the gateway pulls each channel's statement, matches it against the ledger, and emails an exceptions report. Exceptions stay open until someone resolves them.
7. **Refund.** An accountant requests a refund and a second user approves it (maker-checker). Because PGS v2 never holds funds, the refund is **executed by the organisation's own bank/MNO account** (via the channel's refund API where available, otherwise an exported instruction file). PGS v2 records it and confirms it.

## 6. Functional requirements

Priority: **P0** = MVP/go-live, **P1** = soon after, **P2** = later.

### 6.1 Organisation setup
- **FR-1 (P0)** Each installation serves exactly **one organisation**. Its profile (legal name, sector, TIN, contact, logo) is set at install time and editable by the System Admin.
- **FR-2 (P0)** Isolation between organisations is **physical**: separate installation, database, secrets, domain, and backups. No shared runtime or data between organisations.
- **FR-3 (P0)** Organisation configuration without code changes: invoice expiry default, amount policy default, overpayment policy, SMS templates and sender ID, enabled channels and collection accounts, allowed currencies, feature flags.
- **FR-4 (P1)** Branches/departments within the organisation (e.g., campuses, hospital departments) for reporting and access scoping.

### 6.2 Payers
- **FR-5 (P0)** Payer records (name, phone, email, external reference such as student no. or MRN, metadata) Upsert by `external_ref`.
- **FR-6 (P1)** Payer statement: all invoices and payments for a payer.

### 6.3 Invoices & control numbers
- **FR-7 (P0)** Create an invoice via API or portal with line items, currency, due and expiry dates, amount policy (`EXACT`, `PARTIAL`, `OPEN`), and free-form `metadata`.
- **FR-8 (P0)** A unique, non-guessable numeric **control number** is issued on creation (see DESIGN §4).
- **FR-9 (P0)** Invoice lifecycle: `ISSUED → PARTIALLY_PAID → PAID`, plus `EXPIRED` and `CANCELLED`. A paid invoice can become `REFUNDED` or `PARTIALLY_REFUNDED`.
- **FR-10 (P0)** Amend the amount or expiry of an unpaid invoice, with an audit trail. Cancel an unpaid invoice.
- **FR-11 (P0)** Bulk invoice creation (API with ≤ 5,000 items per job, and CSV upload in the portal), processed asynchronously with a per-row result report.
- **FR-12 (P1)** Recurring invoice templates (e.g., monthly fees).
- **FR-13 (P1)** PDF invoice and receipt with a QR code linking to a public verification page (replaces the legacy `verify/qrcode`).

### 6.4 Channels & payments
- **FR-14 (P0)** Channel adapters for the channels the legacy system already uses: **CRDB, NMB, MKCB**. **(P1)** M-Pesa, Mixx by Yas, Airtel Money. **(P2)** HaloPesa, other banks, GePG (if applicable). Every supported channel is built into every installation, so enabling one for an organisation needs only that organisation's credentials (FR-44).
- **FR-15 (P0)** Every channel request is authenticated (signature or mTLS, plus a CIDR allow-list). Unauthenticated requests are rejected and logged.
- **FR-16 (P0)** Validation (inquiry) endpoint returning the payer name, amount due, and whether payment is allowed, where the channel supports it.
- **FR-17 (P0)** Payment notification is **idempotent**. A repeated channel transaction ID returns the original result and never creates a second payment.
- **FR-18 (P0)** Allocation rules applied per invoice policy: exact, partial, open, overpayment, and payment after expiry or cancellation. Anything that cannot be allocated goes to **Suspense**.
- **FR-19 (P0)** Every posted payment creates balanced ledger entries.
- **FR-20 (P1)** Channel-initiated reversals are supported and reflected in the ledger and the invoice.
- **FR-21 (P2)** Push-to-pay (USSD push / STK push) initiated by the organisation for MNO channels.

### 6.5 Notifications to integrating systems
- **FR-22 (P0)** Signed webhooks for invoice, payment, refund, and suspense events, delivered with at-least-once semantics, exponential backoff retries, and a dead-letter queue.
- **FR-23 (P0)** The organisation can list events and redeliver any event (API and portal).
- **FR-24 (P0)** SMS receipt to the payer on payment (configurable); SMS with the control number on invoice creation.
- **FR-25 (P1)** Email notifications and a daily summary email.

### 6.6 Reconciliation & suspense
- **FR-26 (P0)** Daily automated statement fetch for each channel that has an API (manual file upload otherwise), matched against the ledger.
- **FR-27 (P0)** Exceptions: missing internally, missing at the channel, amount mismatch, duplicate. Each has a status, assignee, resolution note, and audit trail.
- **FR-28 (P0)** Suspense resolution: allocate to an invoice, create a credit, or mark for refund. Requires maker-checker.

### 6.7 Refunds
- **FR-29 (P1)** Refund request → approval (a second user) → execution **from the organisation's own collection account** (channel refund API where available, otherwise an exported instruction file for the organisation's bank) → confirmation recorded in the ledger. PGS v2 never disburses funds itself.

### 6.8 Reporting
- **FR-30 (P0)** Collections by day, channel, and branch; invoice aging; suspense aging; recon status. Export to CSV and XLSX.
- **FR-31 (P1)** System health dashboard: volume, success rate, and latency per channel, webhook failure rate, queue and recon status.

### 6.9 Portal & access
- **FR-32 (P0)** Web portal for the organisation's staff with RBAC (roles in DESIGN §7).
- **FR-33 (P0)** TOTP 2FA required for admins, vendor support accounts, and roles that can refund, resolve suspense, or change configuration.
- **FR-34 (P0)** Immutable audit log of every state-changing action (who, what, when, before/after, IP).
- **FR-35 (P1)** Public payer page: look up an invoice by control number plus a verification factor, and view or download the receipt.

### 6.10 Developer experience
- **FR-36 (P0)** Versioned REST API (`/v1`) with an OpenAPI 3.1 spec, a sandbox installation, and test credentials for the organisation's integrating systems.
- **FR-37 (P1)** Sandbox channel simulator for triggering test payments.
- **FR-38 (P2)** Client SDKs (PHP, JS) and a WooCommerce/Moodle plugin.

### 6.11 Installation & fleet management (vendor)
- **FR-39 (P0)** Scripted, repeatable installation of a new organisation from one released image plus a per-organisation configuration file. No code changes and no per-organisation branches.
- **FR-40 (P0)** Versioned releases with safe, scripted upgrades (backup → migrate → health check → rollback path). Every installation reports its running version. An upgrade is applied **only after the organisation's Update Coordinator approves** the version and window (FR-45).
- **FR-41 (P1)** Optional fleet health telemetry sent to the vendor: version, uptime, queue lag, error rates, recon status. **No personal or payment-detail data** leaves the installation.
- **FR-42 (P0)** Vendor support access is disabled by default. The System Admin can grant it time-boxed, and every action is audited.
- **FR-43 (P0)** Hosting-agnostic (D-011): vendor-hosted or on the organisation's own server, the **environment is identical** (same image, stack, and minimum requirements), and so are the requirements for connecting to the organisation's management system (same integration API and webhooks). Offline upgrade bundle for on-prem sites without registry access.
- **FR-44 (P0)** Channel activation by credentials only (D-010): the organisation supplies its credentials for each bank/MNO; they are entered through a secure intake (portal form or `pgs channel add`) straight into the installation's secret store; **Test connection** verifies them and shows the callback URL and outbound IP to give the bank; maker-checker activation; payments accepted immediately.
- **FR-45 (P0)** Updates page and approval (D-012): the installation shows available releases with release notes and severity, the Update Coordinator approves a version and maintenance window (2FA, audited), and the upgrade result is reported back to them.

## 7. Non-functional requirements

| Area | Requirement |
|---|---|
| **Security** | OWASP ASVS L2. TLS 1.2+ everywhere. Secrets never in git. Personal data encrypted at rest (column-level for phone/email/name). Full rules in [RULES.md](RULES.md). |
| **Availability** | Channel ingress ≥ 99.9 %. Integration API ≥ 99.5 %. Planned maintenance must never block channel ingress. |
| **Performance** | Channel callback p95 ≤ 500 ms (server-side). Integration API p95 ≤ 300 ms. Sustain **50 TPS**, burst **200 TPS** (school-fee deadline peaks) — assumption A-004. |
| **Durability** | RPO ≤ 5 min, RTO ≤ 1 h. Encrypted off-site backups; quarterly restore drill. |
| **Correctness** | Money stored as integer minor units. Ledger always balances (checked continuously). Idempotency on every write path. |
| **Auditability** | Financial records and audit log retained ≥ 7 years (confirm with compliance, Q-005). Audit log append-only. |
| **Observability** | Structured JSON logs with `trace_id`. Metrics and alerts per channel. Error tracking. |
| **Compliance** | Tanzania Personal Data Protection Act 2022 (PDPC registration as processor). No Bank of Tanzania licence or registration required for the routing-only model (owner decision D-013). TRA/EFD receipt requirements where applicable (Q-006). |
| **Localisation** | English and Kiswahili UI and SMS templates. Currency TZS (plus USD P1). Timezone Africa/Dar_es_Salaam in UI; UTC in storage. |
| **Upgradability** | Any installation upgrades from the previous two minor releases in ≤ 30 min of downtime-free operation (channel ingress kept up); rollback tested for every release |
| **Maintainability** | ≥ 80 % test coverage on the Payments, Ledger, Channels, and Recon modules. No file over 500 lines; import boundaries between modules enforced in CI. |

## 8. Release plan

| Release | Scope | Exit criteria |
|---|---|---|
| **R0 — Foundations** | Repo, CI/CD, environments, auth, tenancy, audit | CI green; staging deployed; security baseline checks pass |
| **R1 — MVP (pilot)** | Invoices, control numbers, CRDB/NMB/MKCB, ledger, webhooks, SMS, basic portal, daily recon | BMC pilot running in parallel with legacy for 2 weeks with 0 unexplained differences |
| **R2 — GA** | MNO channels, suspense workflow, refunds, reports, bulk, payer page | Pentest passed; 3 organisations installed incl. ≥ 1 school; upgrade tooling proven across all three |
| **R3 — Scale** | Push-to-pay, recurring invoices, SDKs, branch accounts | Per roadmap |

## 9. Risks

| Risk | Impact | Mitigation |
|---|---|---|
| Regulatory: being classed as a payment service provider | Low | Routing-only model (D-002): funds never touch our accounts; owner has determined no BoT licence is needed (D-013). Keep the model routing-only; revisit D-013 if that ever changes |
| Bank/MNO integration lead time (contracts, UAT, IP whitelisting) | High (first build) / Low (each new organisation) | Adapters are built and certified **once** for all installations (T-4.0 early, simulator early). A new organisation only supplies credentials (D-010) |
| Update Coordinator slow to approve a critical security patch | Medium | Severity flag, reminders escalating to the System Admin, 72 h approval target; emergency fallback to System Admin approval (D-015) |
| A channel does not support signing or mTLS | High | Compensating controls: strict CIDR, VPN/IPsec tunnel, payload encryption, anomaly alerts (DESIGN §5.3) |
| Duplicate or lost callbacks | High | Idempotency keys, recon, status-query fallback |
| Installations drift (unpatched, customised, or on old versions) | High | One codebase with no per-org forks (RULES C9); config-only differences; fleet version reporting; patch SLA (G8) |
| Migrating BMC data and history | Medium | Dedicated migration task with dry runs and reconciliation of totals (TASKS phase 8) |
| Peak load at fee deadlines | Medium | Queue-based processing, load test at 4× expected peak |

## 10. Open questions
Tracked in [MEMORY.md §4](MEMORY.md#4-open-questions).
