# Tasks Breakdown: JST Multi-Tenant Recruitment Portal

**Input**: Design documents from `/specs/001-jst-recruitment-portal/`  
**Prerequisites**: `spec.md`, `research.md`, `data-model.md`, `plan.md`  

---

## Phase 1: Setup & Core Infrastructure

**Purpose**: Scaffolding project architecture, multi-tenant middleware, and database foundations.

- [x] T001 Initialize database migrations for `tenants`, `users`, `announcements`, `candidate_applications`, `application_attachments`, `evaluations_formal`, `evaluations_merit`, `protocols`, and `audit_logs` per `specs/001-jst-recruitment-portal/data-model.md`.
- [x] T002 Implement `Tenant` model in `app/Models/Tenant.php` and `TenantScope` global scope in `app/Models/Scopes/TenantScope.php`.
- [x] T003 Create `TenantResolverMiddleware` in `app/Http/Middleware/TenantResolverMiddleware.php` to resolve tenant from domain/subdomain or request header.
- [x] T004 Implement `User` model with roles (`superadmin`, `moderator`, `recruiter`) in `app/Models/User.php`.
- [x] T005 Setup Two-Factor Authentication (2FA) middleware and email token generator in `app/Http/Middleware/TwoFactorAuthMiddleware.php` and `app/Services/TwoFactorService.php`.
- [x] T006 Implement structured Audit Logger middleware `app/Http/Middleware/AuditLoggerMiddleware.php` to record user ID, tenant ID, IP address, and payload diffs in `audit_logs`.

---

## Phase 2: User Story 1 - Candidate Job Search & Public Portal (Priority: P1 - MVP)

**Goal**: Enable anonymous candidates to search, filter, and view recruitment announcements.

- [ ] T007 [P] Implement `Announcement` model and repository methods in `app/Models/Announcement.php`.
- [ ] T008 [P] Implement public announcement listing controller `app/Http/Controllers/Candidate/PublicAnnouncementController.php` with filters for JST, location, category, contract type, and publication dates.
- [ ] T009 Create public announcements list view `resources/views/candidate/announcements/index.blade.php` with full WCAG 2.1 AA compliant styling, ARIA landmarks, and keyboard navigation.
- [ ] T010 Create public announcement detail view `resources/views/candidate/announcements/show.blade.php` displaying formal/merit requirements, duties, list of required documents, and countdown to submission deadline.
- [ ] T011 [P] Implement public announcement search test `tests/Feature/Candidate/PublicAnnouncementSearchTest.php`.

---

## Phase 3: User Story 2 - Online Application Submission & Tracking (Priority: P1 - MVP)

**Goal**: Enable candidates to submit applications online with attachments, tracking links, and withdrawal capabilities.

- [ ] T012 Create `CandidateApplication` and `ApplicationAttachment` models in `app/Models/CandidateApplication.php` and `app/Models/ApplicationAttachment.php`.
- [ ] T013 Implement multi-step application submission controller `app/Http/Controllers/Candidate/ApplicationSubmissionController.php` supporting attachment uploads (CV, cover letter, certificates) and RODO consent logging.
- [ ] T014 Implement multi-step application Blade view `resources/views/candidate/applications/form.blade.php` featuring a visual progress bar, draft saving, and WCAG AA form validation error states.
- [ ] T015 Implement unique `tracking_token` generator and automated email dispatch `app/Mail/ApplicationConfirmationMail.php` sent upon submission.
- [ ] T016 Implement candidate tracking controller `app/Http/Controllers/Candidate/ApplicationTrackingController.php` allowing applicants to check status or withdraw their application before the deadline via a secure token link.
- [ ] T017 Write feature tests for candidate submission and withdrawal in `tests/Feature/Candidate/ApplicationSubmissionTest.php`.

---

## Phase 4: User Story 3 - Recruiter Job Posting & Candidate Evaluation (Priority: P1 - MVP)

**Goal**: Provide JST recruiters with management tools to create job postings, conduct formal checks, and evaluate merit.

- [ ] T018 Implement recruiter job announcement controller `app/Http/Controllers/Recruiter/AnnouncementController.php` for creating, editing, publishing, versioning, and cancelling postings.
- [ ] T019 Build Recruiter JST Dashboard and announcement management UI in `resources/js/Pages/Recruiter/Announcements/` (Inertia/React or Livewire).
- [ ] T020 Implement Formal Evaluation module (`app/Models/EvaluationFormal.php` and `app/Http/Controllers/Recruiter/FormalEvaluationController.php`) with configurable checklist items and mandatory justification notes.
- [ ] T021 Implement Merit Evaluation module (`app/Models/EvaluationMerit.php` and `app/Http/Controllers/Recruiter/MeritEvaluationController.php`) allowing scoring and confidential interview feedback notes.
- [ ] T022 Write unit and feature tests for recruiter evaluation workflows in `tests/Feature/Recruiter/EvaluationWorkflowTest.php`.

---

## Phase 5: User Story 4 & 5 - Protocol Generation, Results Disclosure & Moderator Approval (Priority: P2)

**Goal**: Automate statutory recruitment protocol creation, public result publishing, and optional pre-publication moderation.

- [ ] T023 Implement protocol PDF generator service `app/Services/ProtocolGeneratorService.php` generating compliant recruitment protocols.
- [ ] T024 Implement public recruitment results controller and view `resources/views/candidate/results/index.blade.php` displaying historical outcomes as legally mandated.
- [ ] T025 Implement Moderator workflow middleware `app/Http/Middleware/ModeratorApprovalMiddleware.php` and moderation queue controller `app/Http/Controllers/Moderator/ApprovalController.php` for JST units requiring approval.

---

## Phase 6: User Story 6 - Superadmin & Multi-Tenant Management (Priority: P2)

**Goal**: Enable system administrators to provision JST instances, manage global dictionaries, and monitor system activity.

- [ ] T026 Implement Tenant provisioning controller `app/Http/Controllers/Admin/TenantManagementController.php` for creating, suspending, or configuring JST units.
- [ ] T027 Implement global settings controller `app/Http/Controllers/Admin/GlobalSettingsController.php` for announcement templates, email templates, and default RODO retention days.
- [ ] T028 Implement system audit log viewer UI `resources/js/Pages/Admin/AuditLogs/Index.jsx` for superadmins to inspect audit trails across all tenants.

---

## Phase 7: User Story 7 - Security, RODO Automated Purging & WCAG Verification (Priority: P1 - Core)

**Goal**: Automated compliance logic for RODO data purging, 2FA enforcement, multi-tenant isolation verification, and WCAG accessibility.

- [ ] T029 Implement scheduled console command `app/Console/Commands/PurgeExpiredRodoData.php` that identifies applications exceeding the tenant's `retention_days` and permanently deletes applicant PII and files.
- [ ] T030 Write multi-tenant boundary security test `tests/Feature/Security/TenantIsolationTest.php` proving Recruiter from Tenant A cannot view or manipulate data belonging to Tenant B.
- [ ] T031 Run automated accessibility audit on all candidate portal views using `axe-core` / lighthouse CLI.

---

## Phase 8: Exports & Final Polish

- [ ] T032 Implement candidate data export to XLSX/CSV `app/Exports/CandidatesExport.php` for recruiters.
- [ ] T033 Add white-label styling injector `app/Http/Middleware/InjectTenantBranding.php` applying custom logos and primary colors per JST.
- [ ] T034 Complete end-to-end regression testing.
