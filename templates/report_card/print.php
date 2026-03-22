<?php
/**
 * Report Card — Individual Term Report
 * Uaddara Basic School — SBA Management System
 * 
 * PREMIUM VERSION: Optimized for official presentation and print.
 */

global $rc_student, $rc_term, $rc_scores, $rc_aggregate,
       $rc_classSize, $rc_remarks, $rc_attendance, $rc_classTeacher, $rc_isPublished;


$student      = $rc_student      ?? [];
$term         = $rc_term         ?? [];
$scores       = $rc_scores       ?? [];
$aggregate    = $rc_aggregate    ?? null;
$classSize    = $rc_classSize    ?? 0;
$remarks      = $rc_remarks      ?? null;
$attendance   = $rc_attendance   ?? null;
$classTeacher = $rc_classTeacher ?? null;

$base = defined('APP_BASE') ? APP_BASE : '';

function ordinal(int $n): string {
    $s = ['th','st','nd','rd'];
    $v = $n % 100;
    return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
}

$photoSrc = (!empty($student['photo_path']) && file_exists(ROOT_PATH . '/' . ltrim($student['photo_path'], '/')))
  ? $base . '/' . ltrim($student['photo_path'], '/')
  : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Report: <?= htmlspecialchars($student['full_name'] ?? 'Student') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Old+Standard+TT:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #4f1d96;
      --charcoal: #1a1a1b;
      --border: #222;
      --bg-soft: #fcfcfc;
    }
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      font-size: 13px;
      line-height: 1.4;
      color: var(--charcoal);
      margin: 0;
      padding: 30px;
      background: #f0f0f2;
    }
    @media print {
      body { padding: 0; background: #fff; }
      .no-print { display: none; }
      .container { border: none !important; box-shadow: none !important; margin: 0 !important; width: 100% !important; max-width: none !important; }
    }
    
    .container {
      background: #fff;
      max-width: 820px;
      margin: 0 auto;
      padding: 40px;
      border: 1px solid #ddd;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      position: relative;
      overflow: hidden;
    }

    /* Watermark Effect */
    .container::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 400px;
      height: 400px;
      background: url('<?= $base ?>/assets/img/school-logo.png') center/contain no-repeat;
      transform: translate(-50%, -50%) rotate(-15deg);
      opacity: 0.04;
      pointer-events: none;
      z-index: 0;
    }

    /* Draft Watermark */
    .draft-watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%) rotate(-45deg);
      font-size: 150px;
      font-weight: 900;
      color: rgba(200, 0, 0, 0.1);
      text-transform: uppercase;
      letter-spacing: 20px;
      pointer-events: none;
      z-index: 99;
      white-space: nowrap;
    }


    /* Toolbar */
    .toolbar {
      max-width: 820px;
      margin: 0 auto 20px auto;
      display: flex;
      justify-content: space-between;
    }
    .toolbar button, .toolbar a {
      text-decoration: none;
      padding: 10px 20px;
      background: var(--primary);
      color: #fff;
      font-weight: 700;
      border: none;
      cursor: pointer;
      border-radius: 8px;
      font-size: 13px;
      transition: opacity 0.2s;
    }
    .toolbar button:hover, .toolbar a:hover { opacity: 0.9; }

    /* Header Components */
    .school-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 25px;
      position: relative;
      z-index: 1;
    }
    .school-logo { width: 110px; }
    .school-logo img { width: 100%; height: auto; display: block; }
    
    .school-text { text-align: center; flex: 1; padding: 0 15px; }
    .school-text h1 {
      margin: 0;
      font-family: 'Old Standard TT', serif;
      font-size: 26px;
      font-weight: 700;
      color: #111;
      letter-spacing: 0.5px;
    }
    .school-text h2 {
      margin: 8px 0 0 0;
      font-size: 20px;
      font-weight: 800;
      color: #000;
      letter-spacing: -0.2px;
    }
    .pupil-badge {
      display: inline-block;
      margin-top: 15px;
      background: #c00000;
      color: #fff;
      padding: 5px 25px;
      font-size: 14px;
      font-weight: 800;
      letter-spacing: 1px;
      border: 1px solid #000;
      box-shadow: 2px 2px 0 rgba(0,0,0,0.1);
    }
    
    .student-photo {
      width: 110px;
      height: 120px;
      background: #f8f8f8;
      border: 1px solid #000;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .student-photo img { width: 100%; height: 100%; object-fit: cover; }

    /* Layout Tables */
    table { width: 100%; border-collapse: collapse; position: relative; z-index: 1; }
    
    .meta-table td { padding: 6px 10px; }
    .meta-table td.label { width: 15%; font-weight: 700; color: #555; text-align: right; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
    .meta-table td.value { border: 1.5px solid #000; width: 35%; font-weight: 700; font-size: 14px; }
    .meta-table td.label-small { width: 15%; font-weight: 700; color: #555; text-align: right; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
    .meta-table td.value-small { border: 1.5px solid #000; width: 20%; font-weight: 700; font-size: 14px; text-align: center; background: #fafafa; }

    .scores-table { margin-top: 25px; border: 2px solid #000; }
    .scores-table th, .scores-table td { border: 1.2px solid #000; padding: 6px 8px; }
    .scores-table th { background: #fdfdfd; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; height: 35px; }
    .scores-table td.center { text-align: center; }
    .scores-table tr:nth-child(even) td { background-color: #fbfbfb; }
    .subject-name { font-weight: 600; padding-left: 12px !important; }

    .total-row td { background: #f3f3f3 !important; font-weight: 800; font-size: 15px; }
    .remarks-cell { font-size: 12px; font-weight: 600; color: #222; }

    /* Footer Tables */
    .prof-table { width: 70%; margin: 35px auto 0; border: 1.5px solid #000; font-size: 11px; }
    .prof-table th, .prof-table td { border: 1px solid #000; padding: 5px 12px; }
    .prof-table th { background: #eee; font-weight: 800; text-align: left; }
    .prof-table td { font-weight: 700; }

    .sh-label { font-size: 11px; font-weight: 800; text-transform: uppercase; color: #444; margin-bottom: 3px; display: block; }

    /* Signature Line */
    .sig-area { height: 45px; border-bottom: 1.5px solid #000; margin-bottom: 5px; }
  </style>
</head>
<body>

<div class="toolbar no-print">
  <a href="javascript:history.back()">← Return to Dashboard</a>
  <button onclick="window.print()">Print Official Report</button>
</div>

<div class="container">
  <?php if (!$rc_isPublished): ?>
    <div class="draft-watermark">DRAFT PREVIEW</div>
  <?php endif; ?>
  
  <div class="school-header">

    <div class="school-logo">
      <img src="<?= $base ?>/assets/img/school-logo.png" alt="School Badge" onerror="this.style.visibility='hidden'">
    </div>
    
    <div class="school-text">
      <h1>ARMED FORCES EDUCATION UNIT</h1>
      <h2>UADDARA BASIC SCHOOL</h2>
      <div class="pupil-badge">PUPIL'S REPORT FORM</div>
    </div>

    <div class="student-photo">
      <?php if ($photoSrc): ?>
        <img src="<?= htmlspecialchars($photoSrc) ?>" alt="Student">
      <?php else: ?>
        <svg fill="#ddd" viewBox="0 0 24 24" width="64" height="64"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
      <?php endif; ?>
    </div>
  </div>

  <table class="meta-table">
    <tr>
      <td class="label">Name:</td>
      <td class="value" colspan="3"><?= mb_strtoupper(htmlspecialchars($student['full_name'] ?? '')) ?></td>
      <td class="label-small">Position:</td>
      <td class="value-small"><?= $aggregate ? ordinal((int)$aggregate['class_position']) : '—' ?></td>
    </tr>
    <tr>
      <td class="label">Class:</td>
      <td class="value" colspan="3"><?= mb_strtoupper(htmlspecialchars(($student['class_name'] ?? '') . ' ' . ($student['section'] ?? ''))) ?></td>
      <td class="label-small">No on Roll:</td>
      <td class="value-small"><?= $classSize ?></td>
    </tr>
    <tr>
      <td class="label">Year:</td>
      <td class="value" colspan="3"><?= htmlspecialchars($term['year_name'] ?? '') ?></td>
      <td class="label-small">Term:</td>
      <td class="value-small"><?= strtoupper(htmlspecialchars($term['name'] ?? '')) ?></td>
    </tr>
    <tr>
      <td class="label">Next Term Begins:</td>
      <td class="value" colspan="3"><?= !empty($term['next_term_begins']) ? strtoupper(date('jS F, Y', strtotime($term['next_term_begins']))) : '—' ?></td>
      <td class="label-small">Date:</td>
      <td class="value-small"><?= date('d/m/Y') ?></td>
    </tr>
  </table>

  <table class="scores-table">
    <thead>
      <tr>
        <th style="text-align:left; padding-left:12px;">Subject</th>
        <th style="width:70px;">Class<br>Score (50)</th>
        <th style="width:70px;">Exams<br>Score (50)</th>
        <th style="width:70px;">Total<br>Score (100)</th>
        <th style="width:60px;">Grade</th>
        <th style="width:60px;">Position</th>
        <th style="width:160px;">Level of Proficiency</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($scores as $s): ?>
      <tr>
        <td class="subject-name"><?= htmlspecialchars($s['subject_name']) ?></td>
        <td class="center"><?= $s['class_score'] !== null ? number_format((float)$s['class_score'], 1) : '–' ?></td>
        <td class="center"><?= $s['exam_score'] !== null ? number_format((float)$s['exam_score'], 1) : '–' ?></td>
        <td class="center" style="font-weight:800; background:#f9f9f9;"><?= $s['overall_total'] !== null ? number_format((float)$s['overall_total'], 1) : '0' ?></td>
        <td class="center"><?= $s['proficiency_level'] ?? '5' ?></td>
        <td class="center"><?= $s['subject_position'] ? ordinal((int)$s['subject_position']) : '—' ?></td>
        <td class="center remarks-cell"><?= htmlspecialchars(PROFICIENCY_SCALE[$s['proficiency_level'] ?? 5]['label'] ?? 'EMERGING') ?></td>
      </tr>
      <?php endforeach; ?>

      <?php 
      $rowsToFill = max(0, 8 - count($scores));
      for($i=0; $i<$rowsToFill; $i++): ?>
      <tr style="height:26px;">
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
      </tr>
      <?php endfor; ?>

      <tr class="total-row">
        <td colspan="3" style="text-align:right; padding-right:15px; text-transform:uppercase; letter-spacing:1px; font-size:11px;">Overall Total:</td>
        <td class="center"><?= $aggregate ? number_format((float)$aggregate['aggregate_score'], 1) : '0.0' ?></td>
        <td colspan="3"></td>
      </tr>

      <tr>
        <td style="font-weight:700;">Attendance:</td>
        <td class="center"><?= $attendance['days_present'] ?? '0' ?></td>
        <td style="text-align:right; font-weight:700;">Out of:</td>
        <td class="center"><?= $term['total_school_days'] ?? '60' ?></td>
        <td style="font-weight:700;">Promoted to:</td>
        <td colspan="2"></td>
      </tr>

      <tr>
        <td colspan="2" style="font-weight:700;">Conduct / Character:</td>
        <td colspan="5"><?= htmlspecialchars($remarks['conduct_character'] ?? '–') ?></td>
      </tr>
      <tr>
        <td colspan="2" style="font-weight:700;">Attitude:</td>
        <td colspan="5"><?= htmlspecialchars($remarks['attitude'] ?? '–') ?></td>
      </tr>
      <tr>
        <td colspan="2" style="font-weight:700;">Class Teacher's Remarks:</td>
        <td colspan="5" style="font-style:italic;"><?= htmlspecialchars($remarks['teacher_remark'] ?? '–') ?></td>
      </tr>
      <tr>
        <td colspan="2" style="font-weight:700; height:60px; vertical-align:top; border-bottom:2px solid #000;">Headteacher's Remarks:</td>
        <td colspan="5" style="border-bottom:2px solid #000; vertical-align:top; padding-top:5px;">
          <div style="min-height:30px;"><?= htmlspecialchars($remarks['headmaster_remark'] ?? '') ?></div>
          <div style="margin-top:10px; text-align:right; font-size:10px; font-weight:700; opacity:0.6;">SIGNATURE & STAMP</div>
        </td>
      </tr>
    </tbody>
  </table>

  <table class="prof-table">
    <thead>
      <tr>
        <th>LEVEL OF PROFICIENCY</th>
        <th>BENCHMARK</th>
      </tr>
    </thead>
    <tbody>
      <tr><td>1: HIGHLY PROFICIENT (HP)</td><td>80% +</td></tr>
      <tr><td>2: PROFICIENT (P)</td><td>68-79%</td></tr>
      <tr><td>3: APPROACHING PROFICIENCY</td><td>54-67%</td></tr>
      <tr><td>4: DEVELOPING</td><td>40-53%</td></tr>
      <tr><td>5: EMERGING</td><td>39% AND BELOW</td></tr>
    </tbody>
  </table>

  <div style="margin-top:30px; text-align:center; font-size:10px; color:#999; text-transform:uppercase; letter-spacing:1px; z-index:1; position:relative;">
    © <?= date('Y') ?> <?= SCHOOL_NAME ?> — Official Student Record
  </div>

</div>
</body>
</html>
