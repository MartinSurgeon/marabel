<?php
/**
 * Parent Portal Controller
 * Uaddara Basic School — SBA Management System
 *
 * Fetches all children linked to the logged-in parent and their
 * published term scores for context-rich, low-cognitive-load display.
 */

require_once PRIVATE_PATH . '/src/Helpers/DB.php';
require_once PRIVATE_PATH . '/src/Helpers/Session.php';

class PortalController {

    public function handle(): void {
        Session::requireRole('parent');

        $parentUserId = Session::userId();

        // 1. Active academic year & term
        $activeTerm = DB::queryOne(
            "SELECT t.id as term_id, t.name as term_name, t.term_number,
                    t.next_term_begins, t.total_school_days,
                    ay.id as year_id, ay.year_name
             FROM terms t
             JOIN academic_years ay ON t.academic_year_id = ay.id
             WHERE t.is_active = 1 LIMIT 1"
        );

        // 2. All children linked to this parent
        $children = DB::query(
            "SELECT s.id, s.student_id_number, s.full_name, s.surname,
                    s.photo_path, s.gender,
                    c.class_name, c.section, c.id as class_id,
                    sl.name as level_name, sl.code as level_code,
                    sp.relationship
             FROM student_parents sp
             JOIN students s ON s.id = sp.student_id
             LEFT JOIN classes c ON c.id = s.current_class_id
             LEFT JOIN school_levels sl ON sl.id = c.level_id
             WHERE sp.parent_user_id = ?
               AND s.status = 'active'
             ORDER BY s.full_name",
            [$parentUserId]
        );

        // 3. For each child, load published term scores + aggregate
        $childData = [];
        foreach ($children as $child) {
            $sid = $child['id'];

            // All published terms this academic year
            $terms = [];
            if ($activeTerm) {
                $terms = DB::query(
                    "SELECT t.id, t.name, t.term_number
                     FROM terms t
                     WHERE t.academic_year_id = ?
                     ORDER BY t.term_number",
                    [$activeTerm['year_id']]
                );
            }

            $termScores = [];
            foreach ($terms as $term) {
                // Check if class report card is published for this term
                $published = null;
                if ($child['class_id']) {
                    $published = DB::queryOne(
                        "SELECT is_published, published_at
                         FROM report_card_locks
                         WHERE class_id = ? AND term_id = ? AND is_published = 1",
                        [$child['class_id'], $term['id']]
                    );
                }

                if (!$published) {
                    $termScores[$term['id']] = [
                        'term'      => $term,
                        'published' => false,
                        'scores'    => [],
                        'aggregate' => null,
                        'position'  => null,
                        'remarks'   => null,
                        'attendance'=> null,
                    ];
                    continue;
                }

                // Published scores per subject
                $scores = DB::query(
                    "SELECT s.subject_name, s.subject_code,
                            cs2.class_score, cs2.exam_score, cs2.overall_total,
                            cs2.proficiency_level, cs2.subject_position
                     FROM computed_scores cs2
                     JOIN class_subjects cs ON cs.id = cs2.class_subject_id
                     JOIN subjects s ON s.id = cs.subject_id
                     WHERE cs2.student_id = ? AND cs2.term_id = ?
                     ORDER BY s.subject_name",
                    [$sid, $term['id']]
                );

                // Aggregate + class position
                $aggregate = DB::queryOne(
                    "SELECT aggregate_score, class_position, number_of_subjects
                     FROM student_aggregates
                     WHERE student_id = ? AND term_id = ?",
                    [$sid, $term['id']]
                );

                // Teacher remarks
                $remarks = DB::queryOne(
                    "SELECT conduct_character, attitude, teacher_remark, headmaster_remark
                     FROM student_remarks
                     WHERE student_id = ? AND term_id = ?",
                    [$sid, $term['id']]
                );

                // Attendance
                $att = DB::queryOne(
                    "SELECT a.days_present, t2.total_school_days
                     FROM attendance a
                     JOIN terms t2 ON t2.id = a.term_id
                     WHERE a.student_id = ? AND a.term_id = ?",
                    [$sid, $term['id']]
                );

                $termScores[$term['id']] = [
                    'term'       => $term,
                    'published'  => true,
                    'scores'     => $scores,
                    'aggregate'  => $aggregate,
                    'position'   => $aggregate['class_position'] ?? null,
                    'remarks'    => $remarks,
                    'attendance' => $att,
                ];
            }

            $childData[$sid] = [
                'info'       => $child,
                'terms'      => $terms,
                'termScores' => $termScores,
            ];
        }

        // Pass to template
        global $parentActiveTerm, $parentChildren, $parentChildData;
        $parentActiveTerm = $activeTerm;
        $parentChildren   = $children;
        $parentChildData  = $childData;
    }
}
