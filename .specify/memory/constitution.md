# JST Recruitment Portal Constitution

## Core Principles

### I. Multi-Tenant Data Isolation (NON-NEGOTIABLE)
Every JST (Jednostka Samorządu Terytorialnego) operates in a strictly isolated data space. Cross-tenant data leakage is a critical security violation. All database queries outside superadmin operations must enforce tenant scoping via automated middleware or global model scopes.

### II. Statutory Compliance & Public Transparency
The application must strictly comply with the Polish Act on Local Government Employees (*Ustawa o pracownikach samorządowych*). Public recruitment announcements, requirements, protocols, and recruitment results must be accessible publicly without authentication, ensuring transparency and compliance with public information disclosure standards (BIP).

### III. Accessibility First (WCAG 2.1 AA Mandatory)
All public-facing views and candidate application workflows must strictly comply with WCAG 2.1 level AA standards as mandated for Polish public administration entities. Keyboard navigation, screen-reader compatibility, contrast ratios, and alternative text are mandatory quality gates.

### IV. Privacy & Security by Design (RODO / KRI)
Data privacy is paramount. Personal data of job applicants must be handled with explicit consent tracking, strict retention periods, and automated data purging schedules. Two-Factor Authentication (2FA) is mandatory for all administrative roles (Recruiter, Moderator, Superadmin). Audit logging of all administrative actions is immutable and required by KRI (*Krajowe Ramy Interoperacyjności*).

### V. Modular & Scalable Container Architecture
The architecture must allow decoupling the public-facing Candidate Portal from the administrative JST/Admin Panel into separate Docker containers. This enables independent horizontal scaling of public application traffic without risking administrative backend stability.

## Security & Compliance Constraints

- **Encryption**: TLS in transit, sensitive fields encrypted at rest.
- **Authentication**: 2FA token via email for administrative access; local network access constraint support for JST panel logins.
- **Audit Logging**: Structured log containing timestamp, user_id, tenant_id, action, IP address, and payload diffs for every state-changing action.

## Development Workflow & Quality Gates

1. **Spec-Driven Development**: Every epic and feature must have a corresponding specification in `specs/`.
2. **Automated Testing**: Unit tests for tenant isolation, RODO purging logic, and evaluation workflow; Contract/Feature tests for API endpoints.
3. **WCAG Audits**: Automated and manual accessibility checks on candidate-facing forms before release.

**Version**: 1.0.0 | **Ratified**: 2026-08-27 | **Last Amended**: 2026-08-27
