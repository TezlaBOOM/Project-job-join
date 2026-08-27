# Implementation Plan: JST Multi-Tenant Recruitment Portal

**Branch**: `001-jst-recruitment-portal` | **Date**: 2026-08-27 | **Spec**: [specs/001-jst-recruitment-portal/spec.md](file:///Applications/ServBay/www/Project-job-join/specs/001-jst-recruitment-portal/spec.md)

---

## 1. Executive Summary

Build a multi-tenant recruitment portal for Polish local government units (JST). The application will be developed using **Laravel 13**, **MySQL/PostgreSQL**, **Redis**, and **Tailwind CSS / Inertia.js**, structured to support high-concurrency public applicant traffic and secure, 2FA-enforced administrative workflows for recruiters and superadmins.

---

## 2. Technical Context

- **Framework**: Laravel 13 (PHP 8.5+)
- **Database**: PostgreSQL / MySQL
- **Caching & Queue**: Redis (email notifications, PDF rendering queues, RODO automated purging)
- **Security & Compliance**: Multi-tenant scoping middleware, 2FA email tokens, TLS encryption, immutable audit logging, WCAG 2.1 AA accessibility.
- **Frontend Architecture**:
  - Candidate Portal: Blade templates + Vanilla JS / Tailwind (optimized for WCAG 2.1 AA and fast load times < 2s).
  - JST Admin Panel: Laravel Inertia.js (React) for interactive candidate evaluation checklists and metric dashboards.
- **Testing**: PHPUnit / Pest for unit, integration, and multi-tenant security boundary tests.

---

## 3. Constitution Check

| Gate / Principle | Status | Compliance Method |
|---|---|---|
| **I. Multi-Tenant Data Isolation** | PASSED | `BelongsToTenant` Eloquent global scope + `TenantScopeMiddleware`. |
| **II. Statutory Compliance & Transparency** | PASSED | Public recruitment result pages + statutory PDF protocol export engine. |
| **III. WCAG 2.1 AA Accessibility** | PASSED | Semantic HTML5, high-contrast palette, keyboard focus handlers, ARIA landmarks. |
| **IV. Privacy & Security (RODO / KRI)** | PASSED | Configurable retention periods, automated daily purge job (`artisan rodo:purge`), mandatory 2FA for administrative users. |
| **V. Modular 2-Container Architecture** | PASSED | Separate routing and configuration for Candidate Portal container vs Admin/JST Panel container. |

---

## 4. Project Directory Structure

```text
/Applications/ServBay/www/Project-job-join/
├── app/
│   ├── Enums/                 # Application status, roles, document types
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/         # Superadmin management controllers
│   │   │   ├── Candidate/     # Public job search & application controllers
│   │   │   └── Recruiter/     # JST announcement & evaluation controllers
│   │   └── Middleware/        # TenantResolver, TwoFactorAuth, AuditLogger
│   ├── Jobs/                  # SendEmailNotification, PurgeExpiredRodoData, GenerateProtocolPdf
│   ├── Models/                # Tenant, User, Announcement, CandidateApplication, Evaluation Formal/Merit...
│   ├── Scopes/                # TenantScope
│   └── Services/              # TenantService, EvaluationService, RodoPurgeService, ProtocolService
├── config/                    # Custom jst.php, tenancy.php, rodo.php
├── database/
│   ├── migrations/            # All database schema definitions
│   └── seeders/               # Initial seed data (default categories, demo JST)
├── resources/
│   ├── js/                    # React / Inertia components for Admin Panel
│   └── views/                 # Blade templates for Candidate Public Portal (WCAG AA)
├── routes/
│   ├── admin.php              # Superadmin routes
│   ├── api.php                # API endpoints
│   ├── tenant.php             # Recruiter / JST Panel routes
│   └── web.php                # Public candidate routes
├── specs/001-jst-recruitment-portal/
│   ├── spec.md                # Functional specification
│   ├── research.md            # Technical research & decisions
│   ├── data-model.md          # ER diagram & database schema
│   ├── plan.md                # This implementation plan
│   └── tasks.md               # Task breakdown
└── tests/
    ├── Feature/               # Multi-tenant security tests, application flow
    └── Unit/                  # RODO purge logic, formal evaluation checklist
```
