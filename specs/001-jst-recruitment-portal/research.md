# Technical Research & Architecture Decisions: JST Recruitment Portal

**Feature ID**: `001-jst-recruitment-portal`  
**Date**: 2026-08-27  

---

## 1. Technical Stack & Foundation

| Layer | Technology Selected | Rationale / Benefits |
|---|---|---|
| **Framework** | Laravel 13 (PHP 8.5+) | Modern PHP framework, robust ORM (Eloquent), built-in queuing, scheduling, and security primitives. |
| **Database** | PostgreSQL / MySQL | Relational DB with JSON support, strong ACID guarantees, indexed tenant columns. |
| **Caching & Queues** | Redis | High-speed cache for job listings and async queue processing for email notifications & background RODO cleanup. |
| **Frontend - Candidate** | Blade / Tailwind / Vanilla JS | Fast page load times (< 2s), server-rendered HTML compliant with WCAG 2.1 AA screen readers. |
| **Frontend - Admin** | Laravel Inertia.js (React) or Livewire | Rich, dynamic UI for complex evaluation forms, checklist reviews, and dashboard metrics. |
| **Exports** | Browsershot / Dompdf & PhpSpreadsheet | PDF protocol generation and XLSX/CSV reporting exports. |

---

## 2. Architectural Decisions

### 2.1 Multi-Tenancy Strategy
- **Approach**: Single database with `tenant_id` column + global Eloquent scope (`BelongsToTenant`).
- **Isolation Enforcement**: Middleware resolves tenant from domain/subdomain or URL prefix (e.g. `gmina-krakow.portal-jst.pl` or `portal-jst.pl/urzad-gminy-x`).
- **Security Scope**: Global scope automatically injects `WHERE tenant_id = ?` into all Eloquent queries. Superadmin bypasses scope via explicit `withoutTenancy()` scope.

### 2.2 Decoupled 2-Container Deployment Model
- **Container 1 (Public Candidate App)**:
  - Serves public endpoints (`GET /announcements`, `POST /applications`).
  - Read-heavy, heavily cached, optimized for high traffic concurrency.
- **Container 2 (JST & Superadmin Panel)**:
  - Serves authenticated endpoints (`/panel`, `/admin`).
  - State-heavy, protected by 2FA middleware, local IP restrictions, and strict session limits.

### 2.3 2FA (Two-Factor Authentication) Implementation
- Email-based 2FA tokens generated upon password login for Recruiter, Moderator, and Superadmin roles.
- Tokens expire in 10 minutes and are invalidated after single use.
- Option for local network / IP range restriction check per JST configuration.

### 2.4 RODO Retention & Automated Data Erasure
- Configurable retention period per JST (default: 3 months post-recruitment conclusion).
- Scheduled console command (`artisan rodo:purge-expired`) runs daily via Laravel Scheduler.
- Candidate files and database records are permanently erased, leaving only anonymized statistical data.

### 2.5 WCAG 2.1 AA Accessibility Guidelines
- Color contrast ratio minimum 4.5:1 for normal text.
- Full keyboard focus handling (`:focus-visible` states, skip links).
- Proper ARIA landmarks (`role="main"`, `aria-live` for form validation errors).
- Automated accessibility testing via `axe-core` in CI/CD pipeline.
