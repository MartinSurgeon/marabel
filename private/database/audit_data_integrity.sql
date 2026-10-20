-- ============================================================
-- Data-integrity audit (READ-ONLY — changes nothing)
-- Uaddara Basic School — SBA Management System
--
-- Run in phpMyAdmin (SQL tab) on the live database BEFORE
-- fix_data_integrity.sql. Each query lists problem rows; an
-- empty result means that check is clean.
-- ============================================================

-- 1. Sections merged by earlier year transitions:
--    promoted students whose class is not the one chosen at promotion
SELECT s.id, s.student_id_number, s.full_name,
       chosen.class_name AS chosen_class, chosen.section AS chosen_section,
       actual.class_name AS actual_class, actual.section AS actual_section
FROM student_promotions sp
JOIN students s       ON s.id = sp.student_id AND s.status = 'active'
JOIN classes chosen   ON chosen.id = sp.target_class_id
JOIN classes actual   ON actual.id = s.current_class_id
WHERE sp.promotion_status = 'promoted'
  AND s.academic_year_id = chosen.academic_year_id
  AND s.current_class_id <> sp.target_class_id;

-- 2. Classes with two subjects of the same name in one term
--    (e.g. Lower Primary AND JHS "English Language" in BASIC 9 — reports
--    show it twice and the WAEC aggregate counts only one)
SELECT c.class_name, c.section, t.name AS term, s.subject_name,
       GROUP_CONCAT(cs.id ORDER BY cs.id) AS class_subject_ids,
       GROUP_CONCAT(sl.code ORDER BY cs.id) AS subject_levels
FROM class_subjects cs
JOIN classes c        ON c.id = cs.class_id
JOIN subjects s       ON s.id = cs.subject_id
JOIN school_levels sl ON sl.id = s.level_id
JOIN terms t          ON t.id = cs.term_id
GROUP BY cs.class_id, cs.term_id, s.subject_name
HAVING COUNT(*) > 1;

-- 3. Students whose academic year differs from their class's year
SELECT s.id, s.student_id_number, s.full_name, s.academic_year_id, c.class_name, c.section,
       c.academic_year_id AS class_year
FROM students s JOIN classes c ON c.id = s.current_class_id
WHERE s.academic_year_id <> c.academic_year_id AND s.status = 'active';

-- 4. Attendance above the term's school days
SELECT a.student_id, s.full_name, t.name AS term, a.days_present, t.total_school_days
FROM attendance a JOIN terms t ON t.id = a.term_id JOIN students s ON s.id = a.student_id
WHERE a.days_present > t.total_school_days;

-- 5. Conduct / attitude ratings outside 1-5 (0 = a cleared value, fixed automatically by the migration)
SELECT r.student_id, s.full_name, r.term_id, r.conduct_character, r.attitude
FROM student_remarks r JOIN students s ON s.id = r.student_id
WHERE r.conduct_character NOT BETWEEN 0 AND 5 OR r.attitude NOT BETWEEN 0 AND 5;

-- 6. Scores outside their allowed range
SELECT 'component' AS kind, student_id, class_subject_id, term_id,
       individual_test, group_work, class_test, project
FROM sba_component_scores
WHERE individual_test NOT BETWEEN 0 AND 15 OR group_work NOT BETWEEN 0 AND 15
   OR class_test NOT BETWEEN 0 AND 15 OR project NOT BETWEEN 0 AND 15;
SELECT 'exam' AS kind, student_id, class_subject_id, term_id, raw_score
FROM exam_scores WHERE raw_score NOT BETWEEN 0 AND 100;

-- 7. Scores saved under a different term than their subject's term
SELECT 'exam' AS kind, e.student_id, e.class_subject_id, e.term_id AS saved_term, cs.term_id AS subject_term
FROM exam_scores e JOIN class_subjects cs ON cs.id = e.class_subject_id WHERE e.term_id <> cs.term_id
UNION ALL
SELECT 'component', sc.student_id, sc.class_subject_id, sc.term_id, cs.term_id
FROM sba_component_scores sc JOIN class_subjects cs ON cs.id = sc.class_subject_id WHERE sc.term_id <> cs.term_id;

-- 8. Remarks or attendance for a student in a term where they have no results
--    (possible signs of edits from the wrong class)
SELECT r.student_id, s.full_name, r.term_id, LEFT(r.teacher_remark, 60) AS teacher_remark
FROM student_remarks r JOIN students s ON s.id = r.student_id
WHERE NOT EXISTS (SELECT 1 FROM exam_scores e WHERE e.student_id = r.student_id AND e.term_id = r.term_id)
  AND NOT EXISTS (SELECT 1 FROM sba_component_scores c WHERE c.student_id = r.student_id AND c.term_id = r.term_id);

-- 9. Graduates still labelled 'inactive' (relabelled by the migration)
SELECT COUNT(*) AS graduates_marked_inactive
FROM students s JOIN student_promotions sp ON sp.student_id = s.id
WHERE s.status = 'inactive' AND sp.promotion_status = 'promoted' AND sp.next_class_name = 'Graduated';
