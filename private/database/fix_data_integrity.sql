-- ============================================================
-- Migration: Data-integrity fixes
-- Uaddara Basic School — SBA Management System
-- Run once in phpMyAdmin or MySQL console, AFTER running
-- audit_data_integrity.sql and repairing anything it reports
-- (the range rules in part 2 refuse to install over bad rows).
-- ============================================================

-- ── 1. Graduated status ─────────────────────────────────────
-- Graduates were stored as 'inactive', the same as pupils who left.
ALTER TABLE `students`
  MODIFY `status` ENUM('active','inactive','transferred','graduated') DEFAULT 'active';

-- Re-label existing graduates (promoted with next class "Graduated")
UPDATE `students` s
JOIN `student_promotions` sp ON sp.student_id = s.id
SET s.status = 'graduated'
WHERE s.status = 'inactive'
  AND sp.promotion_status = 'promoted'
  AND sp.next_class_name = 'Graduated';

-- ── 2. Range rules enforced by the database itself ──────────
-- Last line of defence: even a future bug cannot store an impossible value.
-- A rating of 0 was how "cleared" used to be saved; it now means NULL.
UPDATE `student_remarks` SET `conduct_character` = NULL WHERE `conduct_character` = 0;
UPDATE `student_remarks` SET `attitude`          = NULL WHERE `attitude` = 0;

ALTER TABLE `student_remarks`
  ADD CONSTRAINT `chk_conduct_range`  CHECK (`conduct_character` IS NULL OR `conduct_character` BETWEEN 1 AND 5),
  ADD CONSTRAINT `chk_attitude_range` CHECK (`attitude` IS NULL OR `attitude` BETWEEN 1 AND 5);

ALTER TABLE `sba_component_scores`
  ADD CONSTRAINT `chk_sba_ranges` CHECK (
        (`individual_test` IS NULL OR `individual_test` BETWEEN 0 AND 15)
    AND (`group_work`      IS NULL OR `group_work`      BETWEEN 0 AND 15)
    AND (`class_test`      IS NULL OR `class_test`      BETWEEN 0 AND 15)
    AND (`project`         IS NULL OR `project`         BETWEEN 0 AND 15));

ALTER TABLE `exam_scores`
  ADD CONSTRAINT `chk_exam_range` CHECK (`raw_score` IS NULL OR `raw_score` BETWEEN 0 AND 100);

ALTER TABLE `assessment_scores`
  ADD CONSTRAINT `chk_assessment_ranges` CHECK (
        (`percentage` IS NULL OR `percentage` BETWEEN 0 AND 100)
    AND (`grade`      IS NULL OR `grade`      BETWEEN 1 AND 9));
