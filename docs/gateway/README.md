# PGS v2 — Documentation

Planning docs for **PGS v2**, a multi-sector payment routing gateway that succeeds the Bugando PGS in this repository.

| Doc | Purpose | Read when |
|---|---|---|
| [PRD.md](PRD.md) | What we're building and why: goals, users, requirements, releases | Scoping and prioritising |
| [ARCHITECTURE.md](ARCHITECTURE.md) | System shape: modules, flows, data, security, deployment | Before designing any feature |
| [DESIGN.md](DESIGN.md) | Detail: schema, ledger, control numbers, channel adapters, API, state machines, threat model, UI | While implementing |
| [RULES.md](RULES.md) | Mandatory engineering rules and Definition of Done | Always |
| [TASKS.md](TASKS.md) | Phased backlog with acceptance criteria | Picking up work |
| [MEMORY.md](MEMORY.md) | Decisions, assumptions, open questions, legacy lessons, session log | Start and end of every session |

Key decisions: **we route payments and never hold funds** (MEMORY D-002), and **each organisation gets its own installation of one shared codebase; there are no tenants** (MEMORY D-009).
