<?php
/**
 * Grading Engine
 * Uaddara Basic School — SBA Management System
 *
 * Universal scoring model (confirmed for all levels B1–B9):
 *   Class Score  = (IndivTest + GroupWork + ClassTest + Project) ÷ 60 × 50
 *   Exam Score   = (RawExam ÷ 100) × 50
 *   Overall Total = Class Score + Exam Score  [out of 100]
 */

class GradingEngine {

    /**
     * Compute class score from 4 raw components.
     * Each component max = 15; Sub-total max = 60; scaled to 50.
     *
     * @param float|null $indivTest  Individual Test (max 15)
     * @param float|null $groupWork  Group Work      (max 15)
     * @param float|null $classTest  Class Test      (max 15)
     * @param float|null $project    Project         (max 15)
     * @return array ['sub_total'=>float, 'class_score'=>float]
     */
    public static function computeClassScore(
        ?float $indivTest,
        ?float $groupWork,
        ?float $classTest,
        ?float $project
    ): array {
        // Treat null as 0 (score not yet entered shows as 0)
        $subTotal = (float)$indivTest + (float)$groupWork
                  + (float)$classTest + (float)$project;

        $classScore = ($subTotal / SBA_COMPONENTS_TOTAL) * SBA_CLASS_SCORE_MAX;

        return [
            'sub_total'   => round($subTotal, 0),
            'class_score' => round($classScore, 0),
        ];
    }

    /**
     * Compute scaled exam score.
     * Raw exam entered out of 100; scaled to 50.
     *
     * @param float|null $rawExam  Raw exam score (max 100)
     * @return float  Scaled exam score (max 50)
     */
    public static function computeExamScore(?float $rawExam): float {
        $raw = (float)$rawExam;
        return round(($raw / SBA_EXAM_RAW_MAX) * SBA_EXAM_SCORE_MAX, 0);
    }

    /**
     * Compute overall total (Class Score + Exam Score, out of 100).
     */
    public static function computeOverallTotal(float $classScore, float $examScore): float {
        return round($classScore + $examScore, 0);
    }

    /**
     * Determine Grade Level (1–5 Proficiency or 1–9 WAEC) from overall total percentage.
     *
     * @param float $overallTotal  Score out of 100
     * @param string $system       'proficiency' or 'waec'
     * @return int  Grade level
     */
    public static function getProficiencyLevel(float $overallTotal, string $system = 'proficiency'): int {
        $pct = $overallTotal; // Already out of 100, so percentage = value
        $scale = $system === 'waec' ? WAEC_SCALE : PROFICIENCY_SCALE;
        foreach ($scale as $level => $range) {
            if ($pct >= $range['min'] && $pct <= $range['max']) {
                return $level;
            }
        }
        return $system === 'waec' ? 9 : 5; // Default to worst grade if out of range
    }

    /**
     * Get grade label (e.g. "HIGHLY PROFICIENT" or "EXCELLENT") for a level number.
     */
    public static function getProficiencyLabel(int $level, string $system = 'proficiency'): string {
        $scale = $system === 'waec' ? WAEC_SCALE : PROFICIENCY_SCALE;
        return $scale[$level]['label'] ?? ($system === 'waec' ? 'FAIL' : 'EMERGING');
    }

    /**
     * Get grade abbreviation (e.g. "HP" or "1") for a level number.
     */
    public static function getProficiencyAbbr(int $level, string $system = 'proficiency'): string {
        $scale = $system === 'waec' ? WAEC_SCALE : PROFICIENCY_SCALE;
        return $scale[$level]['abbr'] ?? ($system === 'waec' ? '9' : 'E');
    }

    /**
     * Validate component score. Returns true if within range.
     */
    public static function validateComponent(?float $score, float $max = 15.0): bool {
        if ($score === null) return true; // null = not entered yet
        return $score >= 0 && $score <= $max;
    }

    /**
     * Validate raw exam score.
     */
    public static function validateExam(?float $score): bool {
        if ($score === null) return true;
        return $score >= 0 && $score <= SBA_EXAM_RAW_MAX;
    }

    /**
     * Compute aggregate score for a student (sum of all overall_totals in a term).
     * and return class positions for an entire section.
     *
     * @param array $students  Array of ['student_id'=>int, 'aggregate'=>float]
     * @return array  Same array with 'position' added (dense rank)
     */
    public static function computeClassPositions(array $students): array {
        // Sort descending by aggregate
        usort($students, fn($a, $b) => $b['aggregate'] <=> $a['aggregate']);

        $position   = 0;
        $lastScore  = null;
        $skipCount  = 0;

        foreach ($students as &$student) {
            if ($student['aggregate'] !== $lastScore) {
                $position  += 1 + $skipCount;
                $skipCount  = 0;
                $lastScore  = $student['aggregate'];
            } else {
                $skipCount++;
            }
            $student['position'] = $position;
        }
        unset($student);

        return $students;
    }

    /**
     * Compute positions for a specific subject within a class section
     * (ranks students by their overall_total for that subject).
     *
     * @param array $studentScores  Array of ['student_id'=>int, 'overall_total'=>float]
     * @return array  Same array with 'subject_position' added
     */
    public static function computeSubjectPositions(array $studentScores): array {
        usort($studentScores, fn($a, $b) => $b['overall_total'] <=> $a['overall_total']);

        $position  = 0;
        $lastScore = null;
        $skipCount = 0;

        foreach ($studentScores as &$row) {
            if ($row['overall_total'] !== $lastScore) {
                $position += 1 + $skipCount;
                $skipCount = 0;
                $lastScore = $row['overall_total'];
            } else {
                $skipCount++;
            }
            $row['subject_position'] = $position;
        }
        unset($row);

        return $studentScores;
    }

    public static function computeFull(
        ?float $indivTest,
        ?float $groupWork,
        ?float $classTest,
        ?float $project,
        ?float $rawExam,
        string $system = 'proficiency'
    ): array {
        $cs = self::computeClassScore($indivTest, $groupWork, $classTest, $project);
        $es = self::computeExamScore($rawExam);
        $ot = self::computeOverallTotal($cs['class_score'], $es);
        $pl = self::getProficiencyLevel($ot, $system);

        return [
            'sub_total'        => $cs['sub_total'],
            'class_score'      => $cs['class_score'],
            'exam_score'       => $es,
            'overall_total'    => $ot,
            'proficiency_level'=> $pl,
            'proficiency_label'=> self::getProficiencyLabel($pl, $system),
            'proficiency_abbr' => self::getProficiencyAbbr($pl, $system),
        ];
    }

    /**
     * CSS colour class for a grade level (for UI badge colouring).
     */
    public static function levelColourClass(int $level, string $system = 'proficiency'): string {
        if ($system === 'waec') {
            return match($level) {
                1, 2, 3 => 'level-hp',
                4, 5, 6 => 'level-p',
                7, 8    => 'level-ap',
                9       => 'level-e',
                default => 'level-e',
            };
        }
        return match($level) {
            1 => 'level-hp',
            2 => 'level-p',
            3 => 'level-ap',
            4 => 'level-d',
            5 => 'level-e',
            default => 'level-e',
        };
    }
}
