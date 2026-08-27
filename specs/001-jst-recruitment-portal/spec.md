# Feature Specification: Multi-Tenant JST Recruitment Portal

**Feature ID**: `001-jst-recruitment-portal`  
**Date**: 2026-08-27  
**Status**: Approved / Draft  
**Target Platform**: Web Application (Laravel 13, MySQL/PostgreSQL, Docker)

---

## 1. Executive Summary

The **Multi-Tenant JST Recruitment Portal** (*Portal Rekrutacyjny dla Jednostek Samorządu Terytorialnego*) is a specialized web platform designed for Polish local government entities (commune offices, city halls, starosties, marshal offices, and subordinate units) to manage the recruitment process for vacant civil servant positions in strict accordance with the Act on Local Government Employees (*Ustawa o pracownikach samorządowych*).

The system operates on a **multi-tenant architecture**, serving multiple JST entities on a single infrastructure while ensuring complete data isolation, customized branding, statutory public transparency (BIP integration), RODO compliance, and WCAG 2.1 AA accessibility.

---

## 2. User Roles & Capabilities

### 2.1 Candidate (Public / Anonymous)
- **Search & Browse**: Browse active recruitment announcements with multi-criteria filtering (JST unit, location, position category, contract type, date range).
- **View Listing Details**: Inspect full announcement details (duties, formal/merit requirements, required documents, application deadline, contact details).
- **Online Application**: Complete a multi-step application form with draft saving (progress bar), upload attachments (CV, cover letter, statements, certificates), and confirm RODO consent.
- **Application Tracking & Notifications**: Receive an automated email confirmation with a secure tracking link to view status changes (submitted, formal review, qualified, rejected, interview invitation, final result).
- **Application Withdrawal**: Withdraw application before the submission deadline using a secure link/token.
- **RODO Rights**: Manage consents and request personal data deletion post-retention period.

### 2.2 Recruiter / JST Staff (Authenticated - Panel JST)
- **Job Announcement Management**: Create, edit, publish, version, and withdraw recruitment announcements for their specific JST unit. Customise required documents and application form fields.
- **Candidate Review**: View and download candidate applications and attachments per announcement.
- **Formal Evaluation**: Conduct formal assessment using a configurable checklist (completeness of documents, formal eligibility) with required justification notes.
- **Merit Evaluation**: Conduct merit assessment (scoring, interview feedback/notes).
- **Protocols & Results**: Automatically generate statutory recruitment protocols and publish official recruitment result notices on the public portal.

### 2.3 Moderator / Regional Supervisor (Authenticated - Panel JST)
- **Pre-Publication Approval**: Optional workflow to review and approve job postings before public release (configurable per JST).
- **Quality & Auditing**: Review recruiter activity logs, pause or revoke non-compliant announcements, and generate aggregate regional recruitment reports.

### 2.4 Superadmin (Authenticated - Global Panel)
- **Tenant Management**: Provision, configure, suspend, or decommission JST instances.
- **Global Configuration**: Set global announcement templates, email templates, global dictionaries (positions, categories), and RODO data retention periods.
- **System Monitoring & Audit**: Full access to immutable system audit logs, performance metrics, and security logs.

---

## 3. User Stories & Functional Modules

### US1: Public Portal & Job Search (Priority: P1 - MVP)
- **As a** Candidate,  
- **I want to** search and filter civil servant job postings by JST, location, category, and contract type,  
- **So that** I can easily find relevant job opportunities in local government.

### US2: Online Application Submission & Tracking (Priority: P1 - MVP)
- **As a** Candidate,  
- **I want to** fill out a multi-step application form with required documents and receive email status updates,  
- **So that** I can apply for a position digitally without creating a permanent portal account.

### US3: Recruiter Job Posting & Evaluation Workflow (Priority: P1 - MVP)
- **As a** Recruiter,  
- **I want to** publish job postings, conduct formal checklist reviews, and score candidates,  
- **So that** I can efficiently process applications according to statutory requirements.

### US4: Protocol Generation & Public Results Disclosure (Priority: P2)
- **As a** Recruiter,  
- **I want to** generate the official recruitment protocol and publish the recruitment outcome,  
- **So that** the JST satisfies statutory transparency and public disclosure (BIP) mandates.

### US5: Pre-Publication Approval & Quality Moderation (Priority: P2)
- **As a** Moderator,  
- **I want to** review draft job postings before they go live and audit recruiter actions,  
- **So that** all postings adhere to legal standards.

### US6: Multi-Tenant & Superadmin Operations (Priority: P2)
- **As a** Superadmin,  
- **I want to** manage JST tenants, configure global templates, set RODO retention periods, and view audit logs,  
- **So that** the platform remains secure, compliant, and easy to maintain.

### US7: Security, 2FA, RODO Retention & WCAG Compliance (Priority: P1 - Core)
- **As a** System Auditor / Compliance Officer,  
- **I want** 2FA for administrative logins, automated RODO data purging, immutable audit logs, and WCAG 2.1 AA compliant UI,  
- **So that** the platform fulfills legal, accessibility, and cybersecurity standards for public administration.

---

## 4. Key System Architecture & Requirements

1. **Tech Stack**: Laravel 13, PHP 8.5+, MySQL / PostgreSQL.
2. **Container Scalability**: Architecture separable into 2 Docker containers:
   - **Candidate Portal Container**: High-concurrency, lightweight frontend for public browsing and applications.
   - **Admin/Recruiter Panel Container**: Secure, 2FA-protected administration interface.
3. **Multi-Tenancy**: Isolated tenant data per JST instance using automated tenant scoping middleware.
4. **WCAG 2.1 AA Compliance**: High contrast, screen-reader markup, keyboard navigable, responsive design.
5. **Security & Auditing**: Mandatory 2FA via email token for administrative users, TLS encryption, structured immutable audit log.
