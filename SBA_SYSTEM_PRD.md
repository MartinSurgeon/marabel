# Product Requirements Document (PRD)
## School-Based Assessment (SBA) Management System
**School:** Uaddara Basic School (Armed Forces Education Unit, Kumasi)  
**SMS Sender ID:** `Uaddara Bsc`  
**Version:** 1.1 (Final — Confirmed)  
**Date:** March 2026  
**Tech Stack:** Core PHP · MySQL · Tailwind CSS · HTML5 · CSS3 · JavaScript  
**SMS Gateway:** SMSOnlineGH / Zenoph Notify PHP SDK v2.25.08  
**Hosting:** Shared Hosting (cPanel / Apache)  
**Brand Colour:** Rebecca Purple — Primary `#9633cc`

> **Design Mandate:** The system MUST rigorously follow HCI, UI/UX principles, and modern web design standards at every layer — from information architecture to micro-interactions. No screen may be shipped that does not meet premium usability and aesthetic standards.

---

## Table of Contents
1. [Executive Summary](#1-executive-summary)
2. [Problem Statement](#2-problem-statement)
3. [Goals & Success Metrics](#3-goals--success-metrics)
4. [User Personas & Roles](#4-user-personas--roles)
5. [Data Architecture & Column Explanations](#5-data-architecture--column-explanations)
6. [Functional Requirements](#6-functional-requirements)
7. [Module Breakdown](#7-module-breakdown)
8. [Grading Engine Specification](#8-grading-engine-specification)
9. [SMS Notification Integration](#9-sms-notification-integration)
10. [Non-Functional Requirements](#10-non-functional-requirements)
11. [Database Schema](#11-database-schema)
12. [Folder & File Structure](#12-folder--file-structure)
13. [UI/UX Guidelines](#13-uiux-guidelines)
14. [Development Phases & Milestones](#14-development-phases--milestones)
15. [Risks & Mitigations](#15-risks--mitigations)
16. [Appendix — SMSOnlineGH API Reference](#16-appendix--smsonlinegh-api-reference)

---

## 1. Executive Summary

This document defines the complete requirements for a web-based **School-Based Assessment (SBA) Management System** for **Uaddara Basic School** (under the Armed Forces Education Unit, Kumasi, Ghana). The system covers three levels: Lower Primary (B1–B3), Upper Primary (B4–B6), and Junior High School / Basic School (B7–B9). The system digitises the full assessment lifecycle — from score entry to report card generation and parent notification — replacing manual Excel-based workflows.

The system will serve students across all levels with multiple class sections per grade (e.g., B8A, B8B), support **four user roles** (Super Admin/Headmaster, Teacher, Parent, Student), and integrate with the **Zenoph SMSOnlineGH PHP SDK v2.25.08** for automated parent notifications. It is built for **single-tenant** deployment on standard **shared hosting** using Core PHP and MySQL without a framework dependency.

> **SHS is explicitly out of scope for this version.** All three active levels use the same unified 4-component SBA scoring model.

---

## 2. Problem Statement

The school currently manages student assessments entirely through Excel spreadsheets (as evidenced by the provided files: `SECOND_TERM_LOWER_PRIMARY_REPORT_UPDATED.xlsx`, `SECOND_TERM_UPPER_PRIMARY_REPORT.xlsx`, and `THIRD_TERM_BS_8A_SBA_UPDATED.xlsx`). This creates the following pain points:

- **Data fragmentation:** Each class teacher maintains a separate file with no central record.
- **Error-prone calculations:** Manual formula errors corrupt grades and totals.
- **No audit trail:** No record of who entered or changed a score and when.
- **Report card bottleneck:** Printing report cards requires manual formatting per student.
- **No parent visibility:** Parents have no access to their child's progress until physical report cards are issued.
- **No historical data:** Past term records are difficult to retrieve or compare.
- **Academic year siloing:** No mechanism to archive, recall, or compare across terms.

---

## 3. Goals & Success Metrics

| Goal | Metric |
|------|--------|
| Centralise all SBA data | 100% of classes entered digitally within 1 term |
| Reduce report card generation time | From days → under 2 hours per term |
| Eliminate calculation errors | 0 formula errors (system auto-calculates) |
| Enable parent access | Parent portal active with SMS OTP login |
| Notify parents automatically | SMS sent within 24hrs of report card publication |
| Provide historical comparison | Admin can view 3 terms back by end of Year 1 |
| Support Excel import | Teachers can import existing sheets without re-typing |

---

## 4. User Personas & Roles

### Role 1: Super Admin (Headmaster / IT Admin)
- Full system access
- Creates and manages academic years, terms, classes (incl. multiple sections e.g. B8A, B8B), subjects
- Creates and manages teacher accounts (admin-controlled only — no self-registration)
- Configures SBA component weights per level
- Sets promotion status per student (automatic threshold OR manual override)
- Publishes/locks report cards per class section
- Views school-wide dashboards and analytics
- Sends broadcast SMS to all parents
- Imports bulk student data

### Role 2: Class Teacher / Subject Teacher
- **B1–B6 (Primary):** Class teacher enters ALL subjects for their assigned class section
- **B7–B9 (JHS):** Subject teachers enter scores ONLY for their assigned subject on their assigned class sections (e.g., English teacher for B8A and B8B)
- Enters 4 SBA component scores per student per subject (manual web grid or Excel import)
- Views class section performance summary
- Adds Conduct/Character, Attitude ratings and free-text remarks per student
- Cannot publish — submits for admin review only

### Role 3: Parent
- Receives SMS notification when child's report card is published
- Logs in via **phone number + SMS OTP only** (no static password)
- One phone number can be linked to multiple children (all shown on dashboard)
- Views their child's report card (current and past terms)
- Cannot edit any data

### Role 4: Student
- Logs in with Student ID + 4-digit PIN
- Views their own report card and performance history
- Views class rankings (configurable by admin)
- Cannot edit any data

---

## 5. Data Architecture & Confirmed Scoring Model

### 5.1 Universal Scoring Model (All Levels: B1–B9)

All three school levels use the **same 4-component SBA scoring structure**, confirmed from the school's existing Excel sheets:

#### 5.1.1 SBA Score Entry Sheet (Input — Long Format)

| Column | Max Raw Score | Role |
|---|---|---|
| No. | — | Row number (alphabetical, surname first) |
| Student Name | — | Full name, surname first |
| **Individual Test** | **15** | SBA Component 1 |
| **Group Work** | **15** | SBA Component 2 |
| **Class Test** | **15** | SBA Component 3 |
| **Project** | **15** | SBA Component 4 |
| **TOTAL** | **60** | Sum of 4 components |
| **60 SCALED TO 50** | **50** | Class Score = (Total ÷ 60) × 50 |
| **End of Term Exams** | **100** | Exam Score (raw) |
| **50%** | **50** | Exam Score = (Raw Exam ÷ 100) × 50 |
| **Overall Total** | **100** | Class Score (50%) + Exam Score (50%) |
| **Position** | — | Rank within class section by aggregate |
| **Level of Proficiency** | — | Grade (1–5 scale, see §8) |

#### 5.1.2 Scaling Formula
```
Class Score  = (IndivTest + GroupWork + ClassTest + Project) ÷ 60 × 50
Exam Score   = (RawExam ÷ 100) × 50
Overall Total = Class Score + Exam Score   [out of 100]
```

#### 5.1.3 Report Card Fields (Wide Format — per student)

| Field | Source |
|---|---|
| Student Name | `students.full_name` |
| Class / Section | e.g., B1A, B8B — `classes.class_name` |
| Position | Computed rank within section |
| No. on Roll | Count of active students in section |
| Year | Academic year |
| Term | Term 1 / 2 / 3 |
| Next Term Begins | Admin-configured date |
| Date (of issue) | Publication date |
| **Subject rows** | Subject \| Class Score \| Exam Score \| Total Score \| Class Grade \| Class Position \| Remarks |
| TOTAL (aggregate) | Sum of Overall Totals across all subjects |
| **Attendance** | Days Present / Total School Days ("Out of: 60" = total school days for that term) |
| Conduct/Character | Teacher-rated (scale TBD) |
| Attitude | Teacher-rated (scale TBD) |
| Class Teacher's Remark | Free text |
| Headmaster's Remark & Signature | Admin-entered |
| Promoted to | Next class — automatic (threshold) or manual override by admin |

> **Attendance Clarification:** The "Out of: 60" field on the report card refers to the **total number of school days** in that term, not a score maximum. This is configured by the admin per term.

### 5.2 Multiple Class Sections

Each grade level can have multiple sections (e.g., B8A and B8B). Each section:
- Has its own assigned teacher(s)
- Maintains its own student roster
- Has its own independent class rankings/positions
- Generates its own set of report cards

### 5.3 Data Storage Strategy
- Store scores in **long format**: one record per student × subject × term
- Pivot to **wide format** only at report card generation / display time
- `computed_scores` table caches final calculated values for performance

---

## 6. Functional Requirements

### 6.1 Authentication & Access Control
- FR-01: Role-based login (Admin, Teacher, Parent, Student)
- FR-02: Parent login via phone number + SMS OTP (no static password)
- FR-03: Student login via Student ID + 4-digit PIN
- FR-04: Teacher/Admin login via email + password (bcrypt hashed)
- FR-05: Session management with 30-minute inactivity timeout
- FR-06: Password reset for teachers/admins via email link
- FR-07: Admin can impersonate / view-as for troubleshooting

### 6.2 Academic Structure Management (Admin Only)
- FR-08: Create, edit, archive Academic Years (e.g. "2025/2026")
- FR-09: Create Terms within each Academic Year (Term 1, 2, 3)
- FR-10: Set active term — all score entry defaults to active term
- FR-11: Create school levels: Lower Primary, Upper Primary, JHS, SHS
- FR-12: Create classes per level (e.g. B1A, B1B, B4A, JHS 1A)
- FR-13: Assign Class Teacher to each class
- FR-14: Create subjects per level
- FR-15: Assign subject teachers to subjects per class (JHS/SHS departmental mode)
- FR-16: Configure SBA component weights per level (e.g. Group Work=10%, Test=15%)

### 6.3 Student Management
- FR-17: Register students individually or bulk-import from Excel template
- FR-18: Assign students to class per academic year
- FR-19: Promote students to next class at end of year (bulk or individual)
- FR-20: Record student repeat/promotion status
- FR-21: Store parent/guardian contact (phone number for SMS)
- FR-22: Upload student passport photo
- FR-23: Deactivate/archive students who have left

### 6.4 Score Entry
- FR-24: Teachers enter SBA component scores via web form per subject per student
- FR-25: System auto-calculates Class Score from components based on configured weights
- FR-26: Teachers enter End-of-Term Exam scores separately
- FR-27: System auto-calculates Grand Total and Grade
- FR-28: Import scores from Excel (template provided for download)
- FR-29: Inline score editing with immediate recalculation
- FR-30: Lock score entry per subject once teacher submits for review
- FR-31: Admin can unlock a locked subject for correction
- FR-32: Score validation: warn if score exceeds maximum allowed per component

### 6.5 Grading & Remarks
- FR-33: Admin configures grading scale in admin panel (fully configurable)
- FR-34: Default scale: 80–100=Excellent(A), 70–79=Very Good(B), 60–69=Good(C), 50–59=Average(D), 40–49=Below Average(E), 0–39=Fail(F)
- FR-35: Each grade band has a descriptor label (configurable)
- FR-36: Teacher adds free-text remark per student
- FR-37: System provides suggested remarks based on grade (configurable templates)
- FR-38: Headmaster can add a global remark per student on the report card

### 6.6 Report Card Generation
- FR-39: System generates individual student report cards as printable HTML (print-to-PDF via browser)
- FR-40: Report card includes: school name/logo, student photo, class, term, year, all subjects with scores and grades, total aggregate, class position, attendance, teacher remark, headmaster remark, and promotion status
- FR-41: Batch print: generate all report cards for a class in one PDF
- FR-42: Report cards are viewable online by parents and students after publication
- FR-43: Admin publishes report cards per class (toggles visibility to parents/students)
- FR-44: Once published, report cards are locked from further editing unless admin unlocks

### 6.7 Attendance Module
- FR-45: Admin sets total school days per term
- FR-46: Teacher records days present per student per term
- FR-47: Attendance appears on report card

### 6.8 Parent Portal
- FR-48: Parents log in via phone number + OTP
- FR-49: Dashboard shows all children linked to their account
- FR-50: View current term report card
- FR-51: View past terms (archived reports)
- FR-52: Simple performance chart (current term scores by subject)
- FR-53: Mobile-responsive UI (most parents use mobile browsers)

### 6.9 Student Portal
- FR-54: Student logs in with Student ID + PIN
- FR-55: View own report card
- FR-56: View performance across terms (graph)
- FR-57: Admin can toggle whether class ranking is visible to students

### 6.10 Dashboard & Analytics
- FR-58: Admin dashboard: school-wide pass rate, subject-wise performance, class rankings
- FR-59: Teacher dashboard: class performance summary, subjects pending submission
- FR-60: Compare class performance across terms (chart)
- FR-61: Subject-level analytics: highest, lowest, average score per subject
- FR-62: Export reports to Excel (admin only)

### 6.11 SMS Notifications (SMSOnlineGH)
- FR-63: Send SMS to parent when child's report card is published
- FR-64: Broadcast SMS to all parents (e.g. school announcements)
- FR-65: Send OTP SMS for parent login
- FR-66: Admin previews SMS message before sending
- FR-67: SMS delivery log: timestamp, recipient, status, message content
- FR-68: Admin configures sender name (school name) in SMSOnlineGH settings

### 6.12 Excel Import / Export
- FR-69: Provide downloadable Excel template per class per subject for score import
- FR-70: Validate imported Excel: flag unknown student names, out-of-range scores
- FR-71: Preview import before confirming
- FR-72: Export full class scores to Excel
- FR-73: Export student list to Excel

---

## 7. Module Breakdown

```
SBA System
├── M1: Auth & Users
├── M2: Academic Structure (Years, Terms, Classes, Subjects)
├── M3: Student Management
├── M4: Score Entry (Manual + Excel Import)
├── M5: Grading Engine
├── M6: Report Card Generator
├── M7: Attendance
├── M8: Parent Portal
├── M9: Student Portal
├── M10: Admin Dashboard & Analytics
├── M11: SMS Notifications (SMSOnlineGH)
└── M12: Settings (Grading Scale, Weights, School Profile)
```

---

## 8. Grading Engine Specification

### 8.1 Score Calculation Flow (All Levels — Confirmed)

```
INPUT COMPONENTS (Raw scores, each out of 15):
  Individual Test    (max 15)
  Group Work         (max 15)
  Class Test         (max 15)
  Project            (max 15)
  ──────────────────────────
  SUB-TOTAL          (max 60)

SCALING:
  Class Score  = (Sub-Total ÷ 60) × 50     → out of 50  [50% weight]
  Exam Score   = (Raw Exam ÷ 100) × 50     → out of 50  [50% weight]
  ──────────────────────────────────────────
  Overall Total = Class Score + Exam Score  → out of 100

RANKING:
  Aggregate = Σ Overall Totals across all subjects (for this student, this term)
  Position  = RANK within class section (ties share same position)

GRADING:
  Overall Total % → Level of Proficiency (see §8.2)
```

### 8.2 Level of Proficiency Grading Scale (CONFIRMED — Fixed, Not Overridable)

This is the official Uaddara Basic School grading scale used across all levels:

| Level | Descriptor | Benchmark (% of Overall Total) |
|-------|-----------|--------------------------------|
| **1** | HIGHLY PROFICIENT (HP) | **80% and above** |
| **2** | PROFICIENT (P) | **68% – 79%** |
| **3** | APPROACHING PROFICIENCY | **54% – 67%** |
| **4** | DEVELOPING | **40% – 53%** |
| **5** | EMERGING | **39% and below** |

> The **Class Grade** column on the report card displays this numeric level (1–5), not a letter grade. Level 1 is the highest; Level 5 is the lowest.

### 8.3 Class Position / Ranking
- **Aggregate** = sum of Overall Totals across all subjects for a student in a term
- **Position** = rank by aggregate **within the same class section** (e.g., B8A students ranked separately from B8B)
- Ties share the same position (dense ranking)
- Computed server-side at report card generation; cached in `computed_scores`

### 8.4 SBA Component Configuration (All Levels — Confirmed Uniform)

| SBA Component | Raw Max | Contribution to Class Score |
|---|---|---|
| Individual Test | 15 | 15/60 of Class Score |
| Group Work | 15 | 15/60 of Class Score |
| Class Test | 15 | 15/60 of Class Score |
| Project | 15 | 15/60 of Class Score |
| **TOTAL → Scaled** | **60 → 50** | **50% of Overall Total** |
| End-of-Term Exam | 100 → 50 | **50% of Overall Total** |

> Component max scores (15 each) and total cap (60) are **fixed** per the school's confirmed structure. The End-of-Term Exam is entered out of 100 and auto-scaled.

### 8.5 Promotion Logic
- **Automatic:** Admin sets an Overall Total threshold (e.g., 40%). Students at or above threshold are auto-marked "Promoted".
- **Manual Override:** Admin can individually change any student's promotion status regardless of score.
- Both modes coexist — auto-runs first, admin can then override specific students.

---

## 9. SMS Notification Integration

### 9.1 Provider: SMSOnlineGH (Zenoph Technologies Ltd)
- **API Base URL:** `https://[api-endpoint]` (obtained from dev.smsonlinegh.com)
- **Protocol:** HTTP REST (JSON)
- **PHP SDK:** Available for download from dev.smsonlinegh.com
- **Authentication:** API key in request headers

### 9.2 SMS Use Cases

| Event | Trigger | Message Template |
|---|---|---|
| Report card published | Admin clicks "Publish" per class | "Dear [Parent], [Student]'s [Term] report card is ready. Log in at [URL] with your phone number to view it." |
| Parent OTP login | Parent requests login OTP | "Your SBA Portal login OTP is [OTP]. Valid for 10 minutes. Do not share." |
| Broadcast | Admin sends school-wide message | Custom message typed by admin |
| Score reminder | Admin triggers reminder | "Dear [Teacher], please submit [Subject] scores for [Class] by [Date]." |

### 9.3 Implementation Notes
- Use the SMSOnlineGH **PHP SDK** (downloadable from dev.smsonlinegh.com/download)
- Store API key in a `.env`-style config file outside the web root
- Log all outbound SMS (recipient, message, timestamp, delivery status) in `sms_logs` table
- Rate-limit OTP requests: max 3 OTP requests per phone number per hour
- OTP validity: 10 minutes, stored as hash in `otp_tokens` table

### 9.4 PHP Integration Snippet
```php
// Using SMSOnlineGH PHP SDK
require_once 'lib/smsonlinegh/SmsOnline.php';

$sms = new SmsOnline();
$sms->setApiKey(SMS_API_KEY);
$sms->setSenderName(SCHOOL_SMS_NAME); // e.g. "GoldenGate"
$sms->setMessage($message);
$sms->addRecipient($phoneNumber);
$response = $sms->send();

// Log response
logSMS($phoneNumber, $message, $response->status);
```

---

## 10. Non-Functional Requirements

### 10.1 Performance
- Page load under 3 seconds on shared hosting for up to 1,000 students
- Score entry form: auto-save every 30 seconds via AJAX
- Excel import: handle files up to 500 rows without timeout (use chunked processing)

### 10.2 Security
- All passwords hashed with `password_hash()` (bcrypt, cost factor 12)
- Prepared statements / PDO for all DB queries (no raw SQL string interpolation)
- CSRF tokens on all POST forms
- Session regeneration on privilege escalation
- File uploads (photos, Excel): validate MIME type and size, store outside web root
- Rate limiting on login (5 failed attempts = 15-minute lockout)
- OTP tokens stored as SHA-256 hash, never plain text

### 10.3 Shared Hosting Compatibility
- PHP 7.4+ (no framework dependencies)
- MySQL 5.7+ (no stored procedures required)
- No shell_exec / exec required
- `.htaccess` for URL routing (mod_rewrite)
- All cron jobs replaceable with manual admin triggers (shared hosting may restrict cron)
- Keep memory usage under 128MB per request (shared hosting limit)
- No Composer required — all dependencies bundled

### 10.4 Browser Support
- Chrome, Firefox, Edge (latest 2 versions)
- Safari (iOS) — critical for parent mobile access
- Minimum viewport: 320px (feature phones)

### 10.5 Accessibility
- Form labels on all inputs
- Color contrast ratio ≥ 4.5:1 (WCAG AA)
- Report cards printable on A4 paper without CSS print media issues

---

## 11. Database Schema

### Core Tables

```sql
-- Academic structure
academic_years       (id, year_name, is_active, created_at)
terms                (id, academic_year_id, term_number, name, start_date, end_date, is_active)
school_levels        (id, name)  -- Lower Primary, Upper Primary, JHS, SHS
classes              (id, level_id, class_name, class_teacher_id, academic_year_id)
subjects             (id, level_id, subject_name, is_active)
class_subjects       (id, class_id, subject_id, teacher_id, term_id)

-- Users
users                (id, full_name, email, phone, password_hash, role, is_active, created_at)
                     -- role: admin | teacher | parent | student

-- Students
students             (id, student_id_number, full_name, date_of_birth, gender, photo_path,
                      current_class_id, academic_year_id, status, created_at)
student_parents      (id, student_id, parent_user_id)  -- many-to-many

-- SBA Components config
sba_components       (id, level_id, component_name, weight_percent, is_active)
                     -- e.g. Group Work=10, Project=10, Test1=15, Test2=15

-- Scores
sba_component_scores (id, student_id, class_subject_id, term_id, component_id, score, entered_by, updated_at)
exam_scores          (id, student_id, class_subject_id, term_id, score, entered_by, updated_at)
computed_scores      (id, student_id, class_subject_id, term_id,
                      class_score, exam_score, grand_total, grade, descriptor, position)

-- Remarks
student_remarks      (id, student_id, term_id, teacher_remark, headmaster_remark, updated_by, updated_at)

-- Attendance
attendance           (id, student_id, term_id, total_days, days_present)

-- Report cards
report_card_locks    (id, class_id, term_id, is_published, published_at, published_by)

-- Grading
grade_scales         (id, min_score, max_score, grade_label, descriptor, academic_year_id)

-- SMS
otp_tokens           (id, phone, token_hash, expires_at, used_at)
sms_logs             (id, recipient_phone, message, sent_at, status, response_data)

-- Audit
audit_log            (id, user_id, action, table_name, record_id, old_value, new_value, ip_address, created_at)
```

---

## 12. Folder & File Structure

```
/public_html/sba/               ← web root (Apache serves this)
├── index.php                   ← front controller / router
├── .htaccess                   ← URL rewriting
├── assets/
│   ├── css/
│   │   └── app.css             ← custom CSS + Tailwind build
│   ├── js/
│   │   ├── app.js
│   │   ├── scores.js           ← AJAX score entry, auto-save
│   │   └── charts.js           ← Chart.js wrappers
│   └── img/
│       └── school-logo.png
├── uploads/                    ← student photos (validated, not executable)
│   └── students/
└── templates/
    ├── layout/
    │   ├── header.php
    │   ├── sidebar.php
    │   └── footer.php
    ├── admin/
    ├── teacher/
    ├── parent/
    ├── student/
    └── report_card/
        └── print.php           ← printer-friendly report card template

/private/                       ← ABOVE web root (not publicly accessible)
├── config/
│   ├── database.php
│   ├── app.php                 ← APP_KEY, SMS_API_KEY, SCHOOL_NAME, etc.
│   └── constants.php
├── src/
│   ├── Auth/
│   │   ├── AuthController.php
│   │   └── OTPService.php
│   ├── Admin/
│   │   ├── AcademicYearController.php
│   │   ├── ClassController.php
│   │   ├── StudentController.php
│   │   └── GradingController.php
│   ├── Teacher/
│   │   ├── ScoreController.php
│   │   └── ImportController.php
│   ├── Parent/
│   │   └── PortalController.php
│   ├── Student/
│   │   └── PortalController.php
│   ├── Reports/
│   │   ├── ReportCardGenerator.php
│   │   └── AnalyticsController.php
│   ├── SMS/
│   │   └── SMSService.php      ← wraps SMSOnlineGH PHP SDK
│   ├── Models/
│   │   ├── Student.php
│   │   ├── Score.php
│   │   ├── Grade.php
│   │   └── ...
│   └── Helpers/
│       ├── DB.php              ← PDO singleton
│       ├── Session.php
│       ├── Validator.php
│       └── ExcelHelper.php     ← PhpSpreadsheet wrapper
├── lib/
│   ├── smsonlinegh/            ← SMS PHP SDK
│   └── phpspreadsheet/         ← Excel import/export (bundled)
└── database/
    ├── schema.sql
    └── seeds/
        └── default_grades.sql
```

---

## 13. UI/UX Guidelines

> **CRITICAL MANDATE:** Every screen must rigorously follow HCI, UI/UX principles, and modern web design standards. The system must feel premium, intuitive, and accessible. No screen may be shipped that does not meet professional usability and aesthetic standards.

### 13.1 Core HCI Principles to Apply
- **Visibility of system status:** Always show loading states, save confirmations, error messages
- **Match between system and real world:** Use school terminology teachers already know
- **User control & freedom:** Easy undo, unlock, draft states throughout
- **Consistency & standards:** Uniform component library across all portals
- **Error prevention:** Real-time validation before submission, not after
- **Recognition over recall:** Labels always visible, contextual help on hover
- **Flexibility & efficiency:** Keyboard-navigable score grid (Tab key between cells)
- **Aesthetic & minimalist design:** Show only relevant information per role

### 13.2 Brand & Colour System (Rebecca Purple)

```css
/* Primary Brand — Rebecca Purple */
--color-primary-50:  #f5ebfa;
--color-primary-100: #ead6f5;
--color-primary-200: #d5adeb;
--color-primary-300: #c085e0;
--color-primary-400: #ab5cd6;
--color-primary-500: #9633cc;   /* Main brand colour */
--color-primary-600: #7829a3;   /* Hover / active states */
--color-primary-700: #5a1f7a;   /* Sidebar, nav backgrounds */
--color-primary-800: #3c1452;   /* Dark headers */
--color-primary-900: #1e0a29;   /* Ultra-dark text on light */
--color-primary-950: #15071d;   /* Page backgrounds (dark mode) */

/* Semantic Colours */
--color-success: #16a34a;    /* Published, submitted, promoted */
--color-warning: #d97706;    /* Pending, draft, approaching deadline */
--color-danger:  #dc2626;    /* Fail grade (Level 5), errors, locked */
--color-info:    #0284c7;    /* Informational badges, tooltips */
--color-neutral: #f8fafc;    /* Page backgrounds */
```

### 13.3 Typography
- **Font:** Inter (Google Fonts) — clean, highly legible at all sizes
- **Headings:** Inter SemiBold / Bold
- **Body:** Inter Regular 14–16px, line-height 1.6
- **Score cells:** Monospace (tabular numbers) for alignment in grids
- **Print (report card):** Fallback to system serif for crisp A4 print

### 13.4 Key Screen Specifications

| Screen | Key UX Requirements |
|---|---|
| **Login Page** | Role-based tabs, OTP timer countdown for parents, clean purple gradient |
| **Score Entry Grid** | Spreadsheet-feel, Tab navigation, auto-save every 30s with visual indicator, colour-coded cells (Level 1–5), sticky header row |
| **Report Card (Print)** | A4 layout, school letterhead, student photo, Level of Proficiency badge per subject, clean signature lines — no browser chrome |
| **Admin Dashboard** | Stat cards with trend arrows, Chart.js bar/doughnut charts (pass rate by class, section comparison), SMS credit widget |
| **Parent Portal** | Mobile-first card layout, child's photo prominent, proficiency level badges, term selector |
| **Student Portal** | Own report card + sparkline trend chart across terms |
| **Import Preview** | Two-column diff (uploaded vs existing), errors flagged in red with row numbers |

### 13.5 Micro-Interactions & Animations
- Button click: subtle scale 0.97 + ripple
- Score save indicator: pulse green tick, fades after 2s
- Grade badge: fade-in on calculation
- Sidebar: smooth slide with 200ms ease
- Alert toasts: slide-in from top-right, auto-dismiss 4s
- Page transitions: 150ms fade

### 13.6 Mobile Responsiveness
- All portals: minimum 320px viewport
- Score entry grid: horizontal scroll on mobile with sticky student name column
- Parent portal: fully optimised for mobile (most parents use phone browsers)
- Report card: readable on mobile; print triggers A4 CSS print stylesheet

### 13.7 Accessibility
- WCAG AA contrast ratio ≥ 4.5:1 throughout
- All form inputs have visible labels (no placeholder-only labels)
- Tab order is logical on all forms
- Focus rings visible on keyboard navigation
- Screen reader friendly: semantic HTML5, aria-labels on icon-only buttons

---

## 14. Development Phases & Milestones

### Phase 1 — Foundation (Weeks 1–3)
- Database schema creation
- Auth system (all 4 roles)
- Academic structure CRUD (years, terms, classes, subjects)
- Student registration + bulk import
- Basic admin UI shell

**Deliverable:** Admin can log in, create academic structure, and register students.

### Phase 2 — Score Entry (Weeks 4–6)
- SBA component configuration per level
- Score entry forms (manual — web grid)
- Auto-calculation engine (Class Score, Grand Total, Grade)
- Score locking / unlock flow
- Remarks entry

**Deliverable:** Teachers can enter and submit scores for their class/subjects.

### Phase 3 — Report Cards (Weeks 7–8)
- Report card template (HTML/CSS print layout)
- Class position/ranking computation
- Batch print (all students in class)
- Publish/unpublish per class
- Attendance recording

**Deliverable:** Admin can generate and publish report cards.

### Phase 4 — Parent & Student Portals (Weeks 9–10)
- Parent portal (OTP login, child report view)
- Student portal (ID+PIN login, own report)
- SMS OTP integration (SMSOnlineGH)
- SMS notification on report card publish

**Deliverable:** Parents receive SMS and can view report cards online.

### Phase 5 — Excel Import/Export (Week 11)
- Excel template generator per class/subject
- Import parser with validation + preview
- Export scores and student lists

**Deliverable:** Teachers can upload their existing Excel score sheets.

### Phase 6 — Analytics & Polish (Week 12)
- Admin dashboard (charts, pass rate, class comparison)
- Teacher dashboard (pending submissions summary)
- Cross-term performance comparison
- UI polish, loading states, error handling
- Security audit (CSRF, XSS, SQL injection review)

**Deliverable:** Fully functional system ready for UAT.

### Phase 7 — UAT & Deployment (Week 13–14)
- User acceptance testing with real teachers and admin
- Data migration (import current Excel data)
- Shared hosting deployment
- Training session for staff
- Go-live

---

## 15. Risks & Mitigations

| Risk | Likelihood | Impact | Mitigation |
|------|---|---|---|
| Teachers resistant to changing from Excel | High | High | Provide Excel import so they keep their workflow; system is additive |
| Shared hosting PHP timeout on large imports | Medium | Medium | Chunked processing, set `max_execution_time = 120` via `.htaccess` |
| SMSOnlineGH API downtime | Low | Medium | Queue failed SMS, retry up to 3x; log all failures |
| Parent phone numbers missing/wrong | High | Medium | Collect and validate phone numbers during student registration |
| Score entry errors by teachers | Medium | High | Validation rules, audit log, admin unlock for corrections |
| Data loss from hosting issues | Medium | High | Daily MySQL dump via cron or manual export; encourage admin to export regularly |
| Multiple teachers editing same class | Low | Medium | Optimistic locking: show warning if another session is active on same class/subject |

---

## 16. Appendix — SMSOnlineGH API Reference

SMSOnlineGH (Zenoph Technologies Ltd, Kumasi) provides a PHP SDK and HTTP REST API for bulk SMS in Ghana, supporting all local networks (MTN, Telecel/Vodafone, AirtelTigo).

**Developer Portal:** https://dev.smsonlinegh.com  
**REST Docs:** https://dev.smsonlinegh.com/docs/v5/http/rest  
**PHP SDK Download:** https://dev.smsonlinegh.com/download  
**Support:** support@smsonlinegh.com | +233 242 053 072

### Key API Features Used
- Send single SMS (OTP delivery)
- Send bulk SMS (report card notifications, broadcasts)
- Check credit balance (admin dashboard widget)
- Delivery status reporting (SMS log)
- Custom sender name (school branding)
- Message scheduling (optional: schedule end-of-term reminders)

### SMS Credit Estimate
With 1,000+ students and assuming ~1 parent per student:
- Report card notification × 3 terms = ~3,000 SMS/year minimum
- OTP messages = variable (estimate 500–1,000/year)
- Recommend admin monitors credit balance via dashboard widget

---

*Document prepared for development team. All functional requirements marked FR-XX are traceable to individual development tasks. This PRD is a living document and should be updated as requirements evolve during development.*
