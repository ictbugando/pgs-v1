# Technical Design — PGS v2

Status: Draft v0.1 · Implements [PRD](PRD.md) within [ARCHITECTURE](ARCHITECTURE.md), under [RULES](RULES.md).

> **Deployment model (D-009):** everything below describes **one installation serving one organisation**. There are no tenants and no `tenant_id`. Each organisation runs its own installation of the same codebase.
>
> **Business model reminder (D-002):** PGS v2 **routes** payments. Money moves from the payer directly into the organisation's own collection account at the bank/MNO. We never hold or disburse funds. Everything below, especially the ledger and refunds, follows from that.

---

## 1. Domain model

```
Organization (single row) 1─* Branch
User ─*─* Role (optionally scoped to Branch)
ApiCredential (one per integrating system, e.g. eHMS, SIS, ERP)
ChannelAccount (channel enabled + the organisation's collection account/credentials at that channel)
Payer 1─* Invoice 1─* InvoiceItem
Invoice 1─1 ControlNumber
Invoice 1─* Allocation *─1 Payment *─1 Channel
Payment 1─* ChannelMessage (raw inbound/outbound)
Payment 1─1 Journal 1─* LedgerEntry *─1 LedgerAccount
Payment 0─1 SuspenseItem
Invoice/Payment 1─* Refund
OutboxEvent 1─* WebhookDelivery *─1 WebhookEndpoint
ReconRun 1─* StatementLine 0─1 ReconMatch / ReconException
AuditLog (append-only, references any entity)
```

## 2. Data model (key tables)

Single-tenant: no table has `tenant_id` (RULES D2). Every table has `created_at` and `updated_at` (`timestamptz`). Public IDs are prefixed ULIDs.

### organization (exactly one row, enforced by `CHECK (id = 1)`)
| column | type | notes |
|---|---|---|
| id | smallint PK | always 1 |
| org_code | char(3) | numeric issuer code allocated by the vendor, unique across all installations; used in control numbers and telemetry (§4) |
| legal_name, display_name | text | |
| sector | enum | `SCHOOL, HOSPITAL, SACCO, UTILITY, RELIGIOUS, EVENTS, OTHER` |
| tin | varchar(20) | |
| logo_path | text | |

Non-secret settings (expiry default, amount/overpayment policies, SMS sender and templates, locale, feature flags) come from `org.yaml` (ARCHITECTURE §3.1), validated at boot by the `OrgConfig` DTO. Settings that admins may change at runtime are stored in `settings` (key, value jsonb, updated_by) and audited.

### branches
`id brn_…, code, name, status` — campuses, departments, or cash points.

### channel_accounts
Which channels the organisation accepts, and **its own collection account** at each.
| column | type | notes |
|---|---|---|
| channel_id | FK | unique (one collection account per channel; extend to many if an organisation has several accounts at one bank) |
| collection_account | text (encrypted) | organisation's bank account / MNO paybill/till number |
| channel_biller_ref | text | biller ID the channel uses for this organisation |
| credentials_ref | text | Vault path, never the secret itself |
| status | enum | `PENDING_UAT, ACTIVE, DISABLED` |

### payers
`id pyr_…, external_ref (unique), name_enc, phone_enc, phone_bidx, email_enc, email_bidx, metadata jsonb`

### invoices
| column | type | notes |
|---|---|---|
| id | `inv_…` | |
| branch_id?, payer_id | FK | |
| external_ref | varchar(64) | bill number from the integrating system; unique |
| control_number | varchar(20) unique | see §4 |
| currency | char(3) | |
| amount_minor | bigint | null when `amount_policy = OPEN` |
| paid_minor | bigint default 0 | sum of allocations minus reversals |
| refunded_minor | bigint default 0 | |
| amount_policy | enum | `EXACT, PARTIAL, OPEN` |
| overpayment_policy | enum | `REJECT_AT_VALIDATION, SUSPENSE, ACCEPT` |
| status | enum | §8.1 |
| issued_at, due_at, expires_at | timestamptz | |
| description | text | shown to payer and channel |
| metadata | jsonb | sector-specific (student class, MRN, …) |
| version | int | optimistic lock counter |

`invoice_items`: `invoice_id, line_no, code, description, quantity (numeric(12,3)), unit_minor, discount_minor, tax_minor, total_minor`. The DB enforces `Σ total_minor = invoice.amount_minor` with a CHECK or deferred trigger for `EXACT` and `PARTIAL`.

### channels
`id chn_…, code (crdb|nmb|mkcb|mpesa|mixx|airtel|halopesa), name, type (BANK|MNO), auth_strategy, ip_allowlist cidr[], status, config jsonb (non-secret)`

### payments
| column | type | notes |
|---|---|---|
| id | `pay_…` | |
| channel_id | FK | |
| channel_txn_id | varchar(100) | **UNIQUE (channel_id, channel_txn_id)** — idempotency (RULES I1) |
| channel_receipt | varchar(100) | receipt shown to payer |
| control_number_raw | varchar(40) | exactly as received |
| invoice_id | FK nullable | null if unallocated |
| amount_minor, currency | | |
| payer_msisdn_enc, payer_name_enc | | from the channel |
| paid_at | timestamptz | channel time |
| received_at | timestamptz | our time |
| source | enum | `CALLBACK, STATUS_QUERY, RECON, MANUAL` |
| status | enum | §8.2 |
| response_snapshot | jsonb | normalised result returned to the channel (replayed on duplicates) |
| trace_id | varchar(32) | |

### channel_messages (partitioned monthly)
Raw inbound and outbound messages, **written before processing** (RULES E5).
`id, channel_id, direction (IN|OUT), endpoint, http_status, headers_enc, body_enc, remote_ip, auth_result (OK|FAIL:<reason>), processing_status (RECEIVED|PROCESSED|FAILED_PROCESSING|REJECTED), payment_id?, trace_id, created_at`

### ledger_accounts / journals / ledger_entries
See §3.

### suspense_items
`id sus_…, payment_id, reason (UNKNOWN_CONTROL_NUMBER|INVOICE_EXPIRED|INVOICE_CANCELLED|INVOICE_ALREADY_PAID|OVERPAYMENT|CURRENCY_MISMATCH|UNDERPAYMENT_EXACT), amount_minor, status (OPEN|PENDING_APPROVAL|RESOLVED), resolution (ALLOCATED|CREDIT|REFUND), resolved_by, approved_by, note`

### refunds
`id ref_…, payment_id, invoice_id?, amount_minor, reason, status (§8.4), requested_by, approved_by, executed_via (CHANNEL_API|ORG_BANK_FILE), channel_ref, executed_at`

### outbox_events
`id evt_…, type, aggregate_type, aggregate_id, payload jsonb, occurred_at, relayed_at?`

### webhook_endpoints / webhook_deliveries
Endpoints: `id whe_…, url (https only), secret_ref, event_types text[], status, failure_streak`.
Deliveries (partitioned): `id, event_id, endpoint_id, attempt, status (PENDING|DELIVERED|FAILED|DEAD), next_attempt_at, response_code, response_ms, last_error`.

### recon_runs / statement_lines / recon_exceptions
Runs: `channel_id, channel_account_id, statement_date, source (API|SFTP|UPLOAD), status, counts jsonb`.
Lines: normalised statement rows with `channel_txn_id, amount_minor, value_date, raw jsonb`.
Exceptions: `type (MISSING_INTERNAL|MISSING_AT_CHANNEL|AMOUNT_MISMATCH|DUPLICATE), payment_id?, statement_line_id?, status (OPEN|INVESTIGATING|RESOLVED), assignee, resolution_note`.

### audit_logs (append-only, partitioned, hash-chained)
`id, actor_type (USER|API_KEY|CHANNEL|SYSTEM|VENDOR_SUPPORT), actor_id, action, entity_type, entity_id, before jsonb, after jsonb, ip, user_agent, trace_id, prev_hash, hash`. The app DB role has `INSERT` + `SELECT` only.

### idempotency_keys
`api_credential_id, key, request_hash, response_status, response_body, created_at` — unique `(api_credential_id, key)`, kept 24 h. Same key + different body → `409 idempotency_key_reused`.

## 3. Ledger (collections sub-ledger)

We never hold funds, so the ledger is a **memo sub-ledger**. It records what the organisation has collected through each channel, how that was allocated, and what is unresolved. It is what we reconcile against channel statements and against the organisation's view.

### 3.1 Accounts (created per channel account as needed)
| Account | Normal side | Meaning |
|---|---|---|
| `CHANNEL_COLLECTIONS:{channel_account}` | Debit | Money the channel reports as received into the organisation's collection account |
| `INVOICE_SETTLED` | Credit | Collections allocated to invoices |
| `PAYER_CREDIT` | Credit | Overpayments / credits held for payers (organisation's liability to payer) |
| `SUSPENSE` | Credit | Collections not yet allocated |
| `REFUNDED:{channel_account}` | Credit | Refunds executed from the organisation's collection account |

### 3.2 Postings
| Event | Debit | Credit |
|---|---|---|
| Payment fully allocated | CHANNEL_COLLECTIONS | INVOICE_SETTLED |
| Overpayment (policy ACCEPT) | CHANNEL_COLLECTIONS | INVOICE_SETTLED (due) + PAYER_CREDIT (excess) |
| Unallocatable payment | CHANNEL_COLLECTIONS | SUSPENSE |
| Suspense → invoice | SUSPENSE | INVOICE_SETTLED |
| Suspense → payer credit | SUSPENSE | PAYER_CREDIT |
| Refund executed | INVOICE_SETTLED / PAYER_CREDIT / SUSPENSE | REFUNDED |
| Channel reversal | reverse original journal (mirror entries, `reverses_journal_id`) | |

### 3.3 Invariants (checked by a scheduled job + tests; alert on violation)
1. For every journal: Σ debits = Σ credits, all in one currency.
2. `invoice.paid_minor` = Σ INVOICE_SETTLED credits for that invoice − reversals.
3. Σ CHANNEL_COLLECTIONS for (channel, date) = Σ matched statement lines (after recon).

Tables: `ledger_accounts(id, code, type, currency)`, `journals(id jrn_…, payment_id?, refund_id?, suspense_id?, reverses_journal_id?, description, posted_at)`, `ledger_entries(journal_id, account_id, direction D|C, amount_minor > 0)`.

## 4. Control numbers

- **Format (default):** 12 digits = `E` (1-digit environment: `9` prod, `8` sandbox) + `OOO` (3-digit **org code**, allocated by the vendor and unique across all installations) + 7 random digits + 1 **Luhn** check digit. Where a channel allows 14 digits, use 9 random digits.
- **Why the org code:** installations are separate, so without it two organisations could issue the same number. That becomes dangerous if a channel, aggregator, or bank biller ever resolves by control number alone. The org code also tells support staff at a glance which organisation a number belongs to.
- **Capacity:** 7 random digits = 10M numbers per org code. When usage passes 50 %, allocate a second org code to the installation (the config allows a list).
- **Generation:** CSPRNG → check uniqueness via the unique index → retry on conflict (max 5, then alert). Random, not sequential, so numbers cannot be enumerated to scrape payer data. **[L12]**
- **Validation at ingress:** length + prefix + Luhn check before any DB lookup. Typos are caught without a DB hit, and the adapter returns "invalid reference".
- **Lifetime:** one control number per invoice. It is never reused, even after cancellation.
- **Per-channel constraints:** if a channel needs a different length or prefix, the adapter maps the format (documented in `docs/channels/<code>.md`). The core number stays the same.
- **Legacy BMC numbers:** the migration keeps old bill references in `invoices.external_ref`. Old references can be looked up during the transition window through a `legacy_references` table (T-8.3).

## 5. Channel integration

### 5.1 Adapter contract
```php
interface ChannelAdapter
{
    public function code(): string;

    /** Throw ChannelAuthException on failure. Never returns false. */
    public function authenticate(Request $request): void;

    public function parseValidation(Request $request): ValidationRequest;
    public function renderValidation(ValidationResult $result): Response;

    public function parseNotification(Request $request): PaymentNotification;
    public function renderNotificationAck(IngestResult $result): Response;

    /** Optional capabilities, declared via capabilities(). */
    public function queryStatus(string $channelTxnId): ?PaymentNotification;   // STATUS_QUERY
    public function fetchStatement(CarbonImmutable $date, ChannelAccount $account): iterable; // RECON
    public function refund(Refund $refund, ChannelAccount $account): RefundResult;   // REFUND

    /** @return list<Capability> VALIDATION|NOTIFICATION|STATUS_QUERY|STATEMENT_API|REFUND_API|PUSH */
    public function capabilities(): array;
}
```

Normalised DTOs (in `Modules/Channels/Domain`):
```php
final readonly class PaymentNotification {
    public function __construct(
        public string $channelCode,
        public string $channelTxnId,
        public ?string $channelReceipt,
        public string $controlNumber,
        public Money $amount,
        public ?string $payerMsisdn,
        public ?string $payerName,
        public CarbonImmutable $paidAt,
        public array $extra = [],   // adapter-specific, never read by the core
    ) {}
}
```

Routes are generated from registered adapters: `POST /channels/{code}/validate`, `POST /channels/{code}/notify`, each behind `channel.auth:{code}` middleware, which calls `authenticate()`.

### 5.2 Ingest algorithm (`Payments::ingest`)
```
1. Luhn/format check on control number → if invalid: result=INVALID_REFERENCE (still record payment → SUSPENSE if money moved)
2. BEGIN
3.   INSERT payment … ON CONFLICT (channel_id, channel_txn_id) DO NOTHING RETURNING id
     → if no row: SELECT existing; COMMIT; return existing.response_snapshot   (duplicate, idempotent)
4.   SELECT invoice WHERE control_number = ? FOR UPDATE
5.   decision = AllocationPolicy::decide(invoice, amount)        // pure function, §8.5
6.   Ledger::post(journal for decision)
7.   update invoice.paid_minor / status via InvoiceStateMachine
8.   create SuspenseItem if needed
9.   Outbox::record(events…); Audit::record(…)
10.  payment.status = POSTED|SUSPENSE; payment.response_snapshot = result
11. COMMIT
```
Serialization failures or deadlocks → retry the transaction up to 3 times. Other failures → `FAILED_PROCESSING` + alert (RULES E6).

### 5.3 Channel authentication strategies
| Strategy | Used when | Implementation |
|---|---|---|
| `MTLS` | Channel supports client certs | LB terminates and verifies against the channel's CA; passes the verified subject DN; the app checks the DN against config |
| `HMAC_SIGNATURE` | Channel signs requests with a shared secret | Canonical string per channel spec; `hash_equals`; timestamp window |
| `RSA_SIGNATURE` | Channel signs with a private key | Verify with the channel's public cert (rotatable, 2 active keys) |
| `PAYLOAD_ENCRYPTION` | e.g. MKCB AES-GCM | Decrypt with a per-channel key from Vault; the GCM tag gives integrity; also require a timestamp/nonce inside the payload where available |
| `CIDR` | **Always, in addition** | `IpUtils::checkIp($clientIp, $allowlist)` using the trusted-proxy-resolved IP |

**Compensating controls** when a channel supports only CIDR (no crypto):
- Site-to-site VPN/IPsec or a private link to the channel, so the channel host is not public.
- Mandatory `validate` round-trip before `notify` where the channel supports it. Notifications whose amounts don't match the preceding validation are flagged.
- Always call the channel's status-query API to confirm the transaction before posting, if the channel has one.
- Anomaly alerts: unusual amounts, velocity per control number, requests outside the channel's hours.
- Written risk acceptance in `MEMORY.md`, signed off by the owner.

### 5.4 Channel notes (to be completed from official specs in `docs/channels/`)
| Channel | Legacy endpoint | Legacy auth (flawed) | v2 target |
|---|---|---|---|
| CRDB | `malipo/crdbverify`, `crdbpost` | IP prefix `172.18.171.` | Validation + notify; CIDR + signature/mTLS per CRDB spec; recon via statement |
| NMB | `nmbbillrequest`, `nmbprocesscallback`, `nmbrecon` | single IP (dots stripped) | Validation + notify + recon API |
| MKCB | `mkcbfetchcontrol`, `mkcbprocesscontrol`, `mkcbrecon` | **none (always allowed)** | AES-GCM payload encryption with a **new rotated key** + CIDR + status confirmation |
| M-Pesa / Mixx / Airtel | `aipros*` (XML) | IP prefix (Airtel `41.7`, `13.2`) | P1 — per the MNO's current API (C2B/B2C) |

## 6. Integration API

Base: `https://api.<domain>/v1` · JSON · UTF-8 · OpenAPI 3.1 at `/v1/openapi.json`.

### 6.1 Authentication (HMAC request signing)
Headers:
```
Authorization: PGS-HMAC-SHA256 KeyId=key_01J…, Signature=<base64>
X-PGS-Timestamp: 2026-09-27T10:15:30Z
X-PGS-Nonce: 5f1c9b2e-…               (unique per request, ≤ 64 chars)
Idempotency-Key: <uuid>               (mutating requests)
```
Canonical string:
```
METHOD \n PATH \n CANONICAL_QUERY (sorted, url-encoded) \n TIMESTAMP \n NONCE \n HEX(SHA256(body))
```
`Signature = base64(HMAC_SHA256(secret, canonical))`. The server rejects requests where the timestamp is off by more than 300 s, the nonce was seen in the last 10 min (Redis), the key is unknown or revoked, the source IP is outside the key's allow-list (if set), or the key lacks the required scope.

Key scopes: `invoices:read`, `invoices:write`, `payments:read`, `refunds:write`, `webhooks:manage`, `reports:read`. A key's secret is shown once at creation; only an encrypted copy is stored, and keys can be rotated with overlap.

### 6.2 Endpoints (v1)
| Method & path | Purpose |
|---|---|
| `POST /invoices` | Create invoice (payer inline or `payer_id`) → returns `control_number` |
| `POST /invoices/bulk` | Async bulk create (≤ 5,000) → `job_id` |
| `GET /bulk-jobs/{id}` | Job status + per-row results |
| `GET /invoices/{id}` · `GET /invoices?external_ref=&status=&created_from=` | Read / list |
| `PATCH /invoices/{id}` | Amend amount/expiry/description (unpaid only) |
| `POST /invoices/{id}/cancel` | Cancel (unpaid only) |
| `GET /control-numbers/{cn}` | Resolve to invoice |
| `GET /payments/{id}` · `GET /payments?invoice_id=&channel=&from=` | Read / list |
| `POST /refunds` · `GET /refunds/{id}` | Request / read refund (approval happens in portal) |
| `PUT /payers/{external_ref}` · `GET /payers/{id}` | Upsert / read payer |
| `POST /webhook-endpoints` · `GET` · `DELETE /{id}` · `POST /{id}/rotate-secret` | Manage webhooks |
| `GET /events` · `POST /events/{id}/redeliver` | Event log / redelivery |
| `GET /reports/collections?from=&to=&group_by=` | Aggregates |

Example — create invoice:
```json
POST /v1/invoices
{
  "external_ref": "SCH-2026-T3-00412",
  "payer": { "external_ref": "STU-00412", "name": "Asha Juma", "phone": "255712000000" },
  "currency": "TZS",
  "amount_policy": "PARTIAL",
  "items": [
    { "code": "TUITION", "description": "Term 3 tuition", "quantity": 1, "unit_amount": "450000" },
    { "code": "TRANSPORT", "description": "School bus", "quantity": 1, "unit_amount": "60000" }
  ],
  "expires_at": "2026-12-31T23:59:59+03:00",
  "metadata": { "class": "Form 2B", "term": "2026-T3" }
}
→ 201
{ "id": "inv_01J…", "control_number": "912345678903", "status": "ISSUED",
  "amount": "510000", "paid": "0", "currency": "TZS", … }
```
Amounts in the API are **decimal strings in major units** (`"510000"`, `"12.50"` for USD). They are converted with `Money::parse` (RULES M3). JSON numbers are never used for money.

### 6.3 Error catalogue (RFC 9457)
```json
{ "type": "https://docs.<domain>/errors/invoice_not_payable", "title": "Invoice is not payable",
  "status": 409, "code": "invoice_not_payable", "detail": "Invoice inv_… is CANCELLED",
  "trace_id": "4bf92f3577b34da6a3ce929d0e0e4736" }
```
| HTTP | code | When |
|---|---|---|
| 400 | `validation_failed` (+ `errors[]` per field) | Schema/rule failure |
| 401 | `authentication_failed` | Bad signature, unknown key, timestamp skew, replayed nonce |
| 403 | `forbidden` / `insufficient_scope` / `ip_not_allowed` | |
| 404 | `not_found` | Also returned when the key's scope/branch does not cover the resource (no existence leak) |
| 409 | `duplicate_external_ref` · `invoice_not_payable` · `invoice_not_editable` · `idempotency_key_reused` | |
| 422 | `amount_invalid` · `currency_not_enabled` · `items_total_mismatch` | Business rule |
| 429 | `rate_limited` (+ `Retry-After`) | Default 50 rps per key |
| 500 | `internal_error` | Always with `trace_id`; never details |
| 503 | `service_unavailable` | Maintenance/circuit open |

Channel-facing error codes are adapter-specific and mapped from the internal result enum `IngestOutcome` (`POSTED, DUPLICATE, SUSPENSE, INVALID_REFERENCE, AUTH_FAILED, MALFORMED, TEMPORARY_FAILURE`). `TEMPORARY_FAILURE` MUST map to the channel's "retry later" code.

### 6.4 Webhooks
Request to the organisation's webhook endpoint:
```
POST <endpoint url>
Content-Type: application/json
X-PGS-Event-Id: evt_01J…
X-PGS-Event-Type: invoice.paid
X-PGS-Signature: t=1790000000,v1=<hex hmac_sha256(secret, t + "." + raw_body)>
```
Body:
```json
{ "id": "evt_01J…", "type": "invoice.paid", "created_at": "…", "org_code": "123",
  "data": { "invoice": { … }, "payment": { … } } }
```
Receiving systems must verify the signature, reject `t` older than 5 min, respond 2xx within 10 s, and dedupe on `id` (delivery is at-least-once, and ordering is not guaranteed; use `invoice.version`).

Event types: `invoice.created`, `invoice.updated`, `invoice.partially_paid`, `invoice.paid`, `invoice.expired`, `invoice.cancelled`, `payment.received`, `payment.reversed`, `suspense.created`, `suspense.resolved`, `refund.requested`, `refund.approved`, `refund.completed`, `refund.failed`, `bulk_job.completed`.

Retry schedule: 10 s, 1 m, 5 m, 30 m, 2 h, 6 h, 12 h, 24 h → `DEAD`. An endpoint with 50 consecutive failures is auto-disabled and the System Admin gets an email.

## 7. Access control

### 7.1 Roles
| Role | Scope | Key permissions |
|---|---|---|
| `system.admin` | Installation | Settings, users, roles, channel accounts (maker), API keys, webhooks, grant vendor support; cannot approve own actions |
| `finance.manager` | Installation | Approve channel-account changes, refunds, suspense allocations (checker) |
| `accountant` | Installation/branch | Reports, recon exceptions, request refunds, suspense allocation (maker) |
| `cashier` | Branch | Create/cancel invoices, view payments, reprint receipts |
| `auditor` | Installation | Read-only incl. audit log |
| `vendor.support` | Installation, **time-boxed** | Granted by `system.admin` for N hours: diagnostics, failed-processing replay, logs. No refunds, approvals, or personal-data export. 2FA; every action audited |

### 7.2 Sensitive actions
| Action | Control |
|---|---|
| Refund | Maker-checker (different users) + 2FA on approve |
| Suspense allocation / credit | Maker-checker |
| Change collection account in `channel_accounts` | Maker-checker (`system.admin` → `finance.manager`) + 2FA + email to all admins |
| Create / rotate API key, webhook secret | 2FA re-confirmation; secret shown once |
| Role changes | 2FA; audit |
| Grant vendor support access | `system.admin` + 2FA; max 72 h; reason required; auto-expires; audited |

## 8. State machines & allocation

### 8.1 Invoice
```
          create
            │
            ▼
        ISSUED ──pay(partial, policy PARTIAL/OPEN)──► PARTIALLY_PAID ──pay(rest)──► PAID
          │  │                                         │                             │
          │  └──pay(full)────────────────────────────────────────────────────────────►│
          │                                                                           │
  expire  │ cancel (unpaid only)                                 refund(part)/refund(all)
          ▼        ▼                                                                  ▼
       EXPIRED  CANCELLED                                          PARTIALLY_REFUNDED / REFUNDED
```
`OPEN`-amount invoices stay `ISSUED` / `PARTIALLY_PAID` until they expire or are closed manually (`CLOSED`).

### 8.2 Payment
`RECEIVED → POSTED` · `RECEIVED → SUSPENSE → POSTED (after allocation)` · `POSTED → REVERSED` · `RECEIVED → FAILED_PROCESSING → (replay) → POSTED|SUSPENSE`

### 8.3 Suspense item
`OPEN → PENDING_APPROVAL → RESOLVED` (or `→ OPEN` if rejected)

### 8.4 Refund
`REQUESTED → APPROVED → EXECUTING → COMPLETED` · `REQUESTED → REJECTED` · `EXECUTING → FAILED → (retry) EXECUTING`
Execution happens from the organisation's own collection account (channel refund API or an exported instruction file). We never disburse.

### 8.5 Allocation policy (`AllocationPolicy::decide`, pure function)
| Invoice state | Policy | Amount vs due | Outcome |
|---|---|---|---|
| not found | — | any | SUSPENSE `UNKNOWN_CONTROL_NUMBER` |
| CANCELLED | — | any | SUSPENSE `INVOICE_CANCELLED` |
| EXPIRED | — | any | SUSPENSE `INVOICE_EXPIRED` (organisation setting may allow `ACCEPT_AFTER_EXPIRY`) |
| PAID | — | any | SUSPENSE `INVOICE_ALREADY_PAID` |
| ISSUED/PARTIAL | EXACT | = due | POSTED → PAID |
| ISSUED/PARTIAL | EXACT | ≠ due | SUSPENSE `UNDERPAYMENT_EXACT` / `OVERPAYMENT` (should have been blocked at validation) |
| ISSUED/PARTIAL | PARTIAL | < due | POSTED → PARTIALLY_PAID |
| ISSUED/PARTIAL | PARTIAL | = due | POSTED → PAID |
| ISSUED/PARTIAL | PARTIAL/OPEN | > due | per `overpayment_policy`: `ACCEPT` → PAID + PAYER_CREDIT; `SUSPENSE` → due part POSTED, excess SUSPENSE |
| ISSUED/PARTIAL | OPEN | any > 0 | POSTED (stays open) |
| any | — | currency ≠ invoice | SUSPENSE `CURRENCY_MISMATCH` |

At **validation** time the same function runs in dry-run mode, so channels that support inquiry can block wrong amounts before money moves.

## 9. Threat model (STRIDE summary)

| Threat | Example | Controls |
|---|---|---|
| **Spoofing** | Forged bank callback credits an invoice | Per-channel crypto auth + CIDR + status-query confirmation; alert on auth failures |
| **Spoofing** | Stolen integration API key | HMAC + nonce + timestamp, key IP allow-list, scopes, rotation, anomaly alerts |
| **Tampering** | Amount altered in transit | TLS + signature/encryption; amount cross-check with validation and status query |
| **Tampering** | Insider edits payment rows | No UPDATE/DELETE on financial rows by the app role; hash-chained audit; maker-checker |
| **Repudiation** | "We never received that payment" | Raw `channel_messages` retained and encrypted; audit trail; signed webhooks with delivery logs |
| **Info disclosure** | Enumerating control numbers to scrape payer data | Random control numbers; validation only for authenticated channels; public page needs a second factor; rate limits |
| **Info disclosure** | Personal data in logs/backups (legacy issue) | Masking, encryption at rest, backups outside the webroot, encrypted |
| **DoS** | Callback flood or fee-deadline spike | Isolated channel ingress, rate limits, queue buffering, autoscaling workers |
| **Elevation** | Cashier approving own refund | RBAC + maker-checker (different user), 2FA |
| **Elevation** | Vendor support account abused | No standing access; time-boxed grant by the organisation; restricted role; full audit; alerts on grant |
| **Elevation** | Known vulnerability left unpatched on some installations | Fleet version reporting; patch SLA (PRD G8); scripted upgrades; on-prem offline bundles |
| **Info disclosure** | Leaking data via fleet telemetry | Allow-listed health metrics only; no personal or transaction data; opt-out |

## 10. Portal UI design

### 10.1 Information architecture
**Organisation portal** (left nav): Dashboard · Invoices (list, create, bulk upload) · Payments · Payers · Suspense · Refunds · Reconciliation · Reports · Developers (API keys, webhooks, event log, sandbox) · Settings (profile, channels, policies, SMS templates, users & roles) · Audit log.

**System admin area** (`system.admin` / `vendor.support`): Organisation profile · Channel accounts (health, allow-lists, keys) · Webhooks (DLQ) · Failed-processing replay · System health (version, queues, last backup, recon status) · Vendor support access (grant/revoke) · Audit.

**Vendor fleet dashboard** (separate vendor tool, not part of the installation): list of installations with org code, version, health, last backup, open recon exceptions count. Health data only.

### 10.2 Key screens
- **Dashboard:** today's collections (total, count), by channel, success rate, open suspense (count/amount), recon status badge per channel, webhook health.
- **Invoice detail:** header (control number, large, copyable + QR), status chip, amounts (due / paid / balance), items, payment timeline, webhook deliveries for the invoice, audit trail tab, actions (amend, cancel, resend SMS, print PDF).
- **Payment detail:** normalised fields, raw channel message (masked, permission-gated), ledger journal, allocation outcome.
- **Suspense queue:** filters by reason and age; "Allocate" opens an invoice search. Submit → pending approval → approver sees a diff and confirms with 2FA.
- **Recon:** calendar grid (channel × day) coloured green / amber / red; drill into exceptions.
- **Bulk upload:** template download → upload → validation preview (row errors inline) → confirm → progress → downloadable results.

### 10.3 UI principles
- Kiswahili and English toggle. Amounts always show the currency and use thousands separators (`TZS 510,000`).
- Destructive and financial actions need a confirmation dialog stating the amount and entity.
- Status colours are consistent: green PAID/POSTED, amber PARTIAL/PENDING, red FAILED/SUSPENSE/EXPIRED, grey CANCELLED.
- Accessibility: WCAG 2.1 AA; keyboard-navigable tables; no colour-only status (always label + colour).
- Works on a 1366×768 office monitor and on a tablet at the cashier window.

### 10.4 SMS templates (defaults, overridable in `org.yaml`)
- Invoice: `{org}: Ankara {external_ref} ya TZS {amount}. Namba ya malipo: {control_number}. Lipa kupitia benki au simu kabla ya {expiry}.`
- Receipt: `{org}: Tumepokea TZS {amount} kwa namba {control_number}. Salio: TZS {balance}. Risiti: {receipt}.`
