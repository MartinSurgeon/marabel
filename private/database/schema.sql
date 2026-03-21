-- ============================================================
-- Uaddara Basic School — SBA Management System
-- Database Schema v1.0 | March 2026
-- Run: mysql -u root uaddara_sba < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS uaddara_sba
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE uaddara_sba;

-- ============================================================
-- ACADEMIC STRUCTURE
-- ============================================================

CREATE TABLE academic_years (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  year_name   VARCHAR(20) NOT NULL,            -- e.g. "2025/2026"
  is_active   TINYINT(1) NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_year_name (year_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE terms (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  academic_year_id    INT UNSIGNED NOT NULL,
  term_number         TINYINT UNSIGNED NOT NULL,   -- 1, 2, or 3
  name                VARCHAR(50) NOT NULL,          -- e.g. "Term One"
  start_date          DATE,
  end_date            DATE,
  total_school_days   SMALLINT UNSIGNED DEFAULT 60, -- "Out of: X" on report card
  next_term_begins    DATE,
  is_active           TINYINT(1) NOT NULL DEFAULT 0,
  created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_year_term (academic_year_id, term_number),
  FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE school_levels (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(80) NOT NULL,            -- e.g. "Lower Primary"
  code        VARCHAR(10) NOT NULL,            -- e.g. "LP", "UP", "JHS"
  sort_order  TINYINT UNSIGNED DEFAULT 0,
  UNIQUE KEY uq_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE classes (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  level_id          INT UNSIGNED NOT NULL,
  class_name        VARCHAR(20) NOT NULL,      -- e.g. "B1A", "B8B"
  section           VARCHAR(5),               -- e.g. "A", "B"
  academic_year_id  INT UNSIGNED NOT NULL,
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_class_year (class_name, academic_year_id),
  FOREIGN KEY (level_id) REFERENCES school_levels(id),
  FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE class_teachers (
  class_id      INT UNSIGNED NOT NULL,
  teacher_id    INT UNSIGNED NOT NULL,
  PRIMARY KEY (class_id, teacher_id),
  FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE subjects (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  level_id      INT UNSIGNED NOT NULL,
  subject_name  VARCHAR(100) NOT NULL,
  subject_code  VARCHAR(20),
  sort_order    TINYINT UNSIGNED DEFAULT 0,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (level_id) REFERENCES school_levels(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE class_subjects (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  class_id    INT UNSIGNED NOT NULL,
  subject_id  INT UNSIGNED NOT NULL,
  teacher_id  INT UNSIGNED,               -- assigned subject/class teacher
  term_id     INT UNSIGNED NOT NULL,
  is_locked   TINYINT(1) NOT NULL DEFAULT 0,
  locked_at   TIMESTAMP NULL,
  UNIQUE KEY uq_class_subj_term (class_id, subject_id, term_id),
  FOREIGN KEY (class_id)   REFERENCES classes(id),
  FOREIGN KEY (subject_id) REFERENCES subjects(id),
  FOREIGN KEY (term_id)    REFERENCES terms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- USERS
-- ============================================================

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(200) NOT NULL,
  email         VARCHAR(150),
  phone         VARCHAR(20),              -- required for parents
  password_hash VARCHAR(255),            -- NULL for parents (OTP only)
  role          ENUM('admin','teacher','parent','student') NOT NULL,
  is_active     TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at TIMESTAMP NULL,
  created_by    INT UNSIGNED,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_email (email),
  INDEX idx_phone (phone),
  INDEX idx_role  (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- STUDENTS
-- ============================================================

CREATE TABLE students (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id_number VARCHAR(50) NOT NULL,
  full_name         VARCHAR(200) NOT NULL,
  surname           VARCHAR(100),
  date_of_birth     DATE,
  gender            ENUM('Male','Female','Other'),
  photo_path        VARCHAR(500),
  current_class_id  INT UNSIGNED,
  academic_year_id  INT UNSIGNED,
  pin_hash          VARCHAR(255),          -- 4-digit PIN (bcrypt hashed)
  status            ENUM('active','inactive','transferred') DEFAULT 'active',
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_student_id_number (student_id_number),
  FOREIGN KEY (current_class_id)  REFERENCES classes(id),
  FOREIGN KEY (academic_year_id)  REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE student_parents (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id      INT UNSIGNED NOT NULL,
  parent_user_id  INT UNSIGNED NOT NULL,
  relationship    VARCHAR(50),
  is_primary      TINYINT(1) DEFAULT 1,
  UNIQUE KEY uq_student_parent (student_id, parent_user_id),
  FOREIGN KEY (student_id)     REFERENCES students(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SCORING
-- ============================================================

-- Raw component scores entered by teacher
CREATE TABLE sba_component_scores (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id        INT UNSIGNED NOT NULL,
  class_subject_id  INT UNSIGNED NOT NULL,
  term_id           INT UNSIGNED NOT NULL,
  -- The 4 components (each max 15)
  individual_test   DECIMAL(5,2) DEFAULT NULL,
  group_work        DECIMAL(5,2) DEFAULT NULL,
  class_test        DECIMAL(5,2) DEFAULT NULL,
  project           DECIMAL(5,2) DEFAULT NULL,
  -- Computed on save
  sub_total         DECIMAL(6,2) DEFAULT NULL,   -- sum of 4 (max 60)
  class_score       DECIMAL(6,2) DEFAULT NULL,   -- scaled to 50
  entered_by        INT UNSIGNED,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_comp (student_id, class_subject_id, term_id),
  FOREIGN KEY (student_id)       REFERENCES students(id),
  FOREIGN KEY (class_subject_id) REFERENCES class_subjects(id),
  FOREIGN KEY (term_id)          REFERENCES terms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE exam_scores (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id        INT UNSIGNED NOT NULL,
  class_subject_id  INT UNSIGNED NOT NULL,
  term_id           INT UNSIGNED NOT NULL,
  raw_score         DECIMAL(6,2) DEFAULT NULL,   -- entered out of 100
  exam_score        DECIMAL(6,2) DEFAULT NULL,   -- scaled to 50
  entered_by        INT UNSIGNED,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_exam (student_id, class_subject_id, term_id),
  FOREIGN KEY (student_id)       REFERENCES students(id),
  FOREIGN KEY (class_subject_id) REFERENCES class_subjects(id),
  FOREIGN KEY (term_id)          REFERENCES terms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cached computed values (regenerated on publish)
CREATE TABLE computed_scores (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id          INT UNSIGNED NOT NULL,
  class_subject_id    INT UNSIGNED NOT NULL,
  term_id             INT UNSIGNED NOT NULL,
  class_score         DECIMAL(6,2),              -- out of 50
  exam_score          DECIMAL(6,2),              -- out of 50
  overall_total       DECIMAL(6,2),              -- out of 100
  proficiency_level   TINYINT UNSIGNED,          -- 1–5
  subject_position    INT UNSIGNED,              -- rank within section for this subject
  computed_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_comp (student_id, class_subject_id, term_id),
  FOREIGN KEY (student_id)       REFERENCES students(id),
  FOREIGN KEY (class_subject_id) REFERENCES class_subjects(id),
  FOREIGN KEY (term_id)          REFERENCES terms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per-student aggregate for class position
CREATE TABLE student_aggregates (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id        INT UNSIGNED NOT NULL,
  class_id          INT UNSIGNED NOT NULL,
  term_id           INT UNSIGNED NOT NULL,
  aggregate_score   DECIMAL(8,2),               -- Σ overall_totals
  class_position    INT UNSIGNED,               -- dense rank within section
  number_of_subjects TINYINT UNSIGNED,
  computed_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_agg (student_id, class_id, term_id),
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (class_id)   REFERENCES classes(id),
  FOREIGN KEY (term_id)    REFERENCES terms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- REMARKS, ATTENDANCE & PROMOTION
-- ============================================================

CREATE TABLE student_remarks (
  id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id          INT UNSIGNED NOT NULL,
  term_id             INT UNSIGNED NOT NULL,
  conduct_character   TINYINT UNSIGNED,           -- 1–5 rating
  attitude            TINYINT UNSIGNED,           -- 1–5 rating
  teacher_remark      TEXT,
  headmaster_remark   TEXT,
  updated_by          INT UNSIGNED,
  updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_remark (student_id, term_id),
  FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attendance (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id    INT UNSIGNED NOT NULL,
  term_id       INT UNSIGNED NOT NULL,
  days_present  SMALLINT UNSIGNED DEFAULT 0,
  -- total comes from terms.total_school_days
  updated_by    INT UNSIGNED,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_att (student_id, term_id),
  FOREIGN KEY (student_id) REFERENCES students(id),
  FOREIGN KEY (term_id)    REFERENCES terms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE student_promotions (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_id        INT UNSIGNED NOT NULL,
  academic_year_id  INT UNSIGNED NOT NULL,
  term_id           INT UNSIGNED NOT NULL,
  auto_promoted     TINYINT(1),
  manual_override   TINYINT(1) DEFAULT 0,
  promotion_status  ENUM('pending','promoted','repeated') DEFAULT 'pending',
  next_class_name   VARCHAR(20),
  set_by            INT UNSIGNED,
  set_at            TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_promo (student_id, academic_year_id, term_id),
  FOREIGN KEY (student_id)       REFERENCES students(id),
  FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
  FOREIGN KEY (term_id)          REFERENCES terms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- REPORT CARD PUBLISH CONTROL
-- ============================================================

CREATE TABLE report_card_locks (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  class_id      INT UNSIGNED NOT NULL,
  term_id       INT UNSIGNED NOT NULL,
  is_published  TINYINT(1) NOT NULL DEFAULT 0,
  published_at  TIMESTAMP NULL,
  published_by  INT UNSIGNED,
  sms_sent      TINYINT(1) NOT NULL DEFAULT 0,
  sms_sent_at   TIMESTAMP NULL,
  UNIQUE KEY uq_lock (class_id, term_id),
  FOREIGN KEY (class_id) REFERENCES classes(id),
  FOREIGN KEY (term_id)  REFERENCES terms(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SMS
-- ============================================================

CREATE TABLE otp_tokens (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  phone       VARCHAR(20) NOT NULL,
  token_hash  VARCHAR(64) NOT NULL,
  expires_at  TIMESTAMP NOT NULL,
  used_at     TIMESTAMP NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sms_logs (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  recipient_phone  VARCHAR(20),
  message          TEXT,
  sms_type         ENUM('otp','report_card','broadcast','reminder') NOT NULL,
  sent_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status           VARCHAR(50),
  response_data    TEXT,
  INDEX idx_phone (recipient_phone),
  INDEX idx_type  (sms_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- AUDIT LOG
-- ============================================================

CREATE TABLE audit_log (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED,
  action      VARCHAR(100),
  table_name  VARCHAR(100),
  record_id   INT UNSIGNED,
  old_value   TEXT,
  new_value   TEXT,
  ip_address  VARCHAR(45),
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user   (user_id),
  INDEX idx_table  (table_name),
  INDEX idx_record (table_name, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- LOGIN ATTEMPTS (rate limiting)
-- ============================================================

CREATE TABLE login_attempts (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  identifier   VARCHAR(200) NOT NULL,   -- email or phone
  ip_address   VARCHAR(45),
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_identifier (identifier)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
