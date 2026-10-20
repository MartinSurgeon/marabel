-- ============================================================
-- Migration: Speed Tests & Mock Exams (WAEC-graded assessments)
-- Uaddara Basic School — SBA Management System
-- Run this once in phpMyAdmin or MySQL console (safe to re-run)
-- ============================================================

CREATE TABLE IF NOT EXISTS `assessments` (
  `id`               int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `term_id`          int(10) UNSIGNED NOT NULL,
  `assessment_type`  enum('speed_test','mock') NOT NULL,
  `title`            varchar(100)     NOT NULL,
  `assessment_date`  date             DEFAULT NULL,
  `max_score`        decimal(6,2)     NOT NULL DEFAULT 100.00,
  `is_locked`        tinyint(1)       NOT NULL DEFAULT 0,
  `created_by`       int(10) UNSIGNED DEFAULT NULL,
  `created_at`       timestamp        NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_term_type` (`term_id`, `assessment_type`),
  CONSTRAINT `fk_assessments_term` FOREIGN KEY (`term_id`) REFERENCES `terms` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `assessment_scores` (
  `id`                int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `assessment_id`     int(10) UNSIGNED NOT NULL,
  `student_id`        int(10) UNSIGNED NOT NULL,
  `class_subject_id`  int(10) UNSIGNED NOT NULL,
  `raw_score`         decimal(6,2)     DEFAULT NULL,   -- out of assessments.max_score
  `percentage`        decimal(5,2)     DEFAULT NULL,   -- raw ÷ max × 100
  `grade`             tinyint(3) UNSIGNED DEFAULT NULL, -- WAEC 1–9
  `entered_by`        int(10) UNSIGNED DEFAULT NULL,
  `updated_at`        timestamp        NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_assessment_score` (`assessment_id`, `student_id`, `class_subject_id`),
  CONSTRAINT `fk_as_assessment`    FOREIGN KEY (`assessment_id`)    REFERENCES `assessments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_as_student`       FOREIGN KEY (`student_id`)       REFERENCES `students` (`id`),
  CONSTRAINT `fk_as_class_subject` FOREIGN KEY (`class_subject_id`) REFERENCES `class_subjects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
