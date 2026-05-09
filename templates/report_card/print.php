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
$gradingSystem = $rc_gradingSystem ?? 'proficiency';

$scale = ($gradingSystem === 'waec') ? WAEC_SCALE : PROFICIENCY_SCALE;
$gradeLabel = ($gradingSystem === 'waec') ? 'Grade' : 'Proficiency';

$base = defined('APP_BASE') ? APP_BASE : '';

function ordinal(int $n): string
{
  $s = ['th', 'st', 'nd', 'rd'];
  $v = $n % 100;
  return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
}

$photoSrc = (!empty($student['photo_path']) && file_exists(ROOT_PATH . '/' . ltrim($student['photo_path'], '/')))
  ? $base . '/' . ltrim($student['photo_path'], '/')
  : null;

// Signature and Stamp Paths
$sigPath = null;
$stampPath = null;
$sigDir = ROOT_PATH . '/assets/uploads/signatures';
foreach (['png', 'jpg', 'jpeg'] as $ext) {
  if (!$sigPath && file_exists("$sigDir/headmaster_signature.$ext")) $sigPath = "$base/assets/uploads/signatures/headmaster_signature.$ext";
  if (!$stampPath && file_exists("$sigDir/school_stamp.$ext")) $stampPath = "$base/assets/uploads/signatures/school_stamp.$ext";
}
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
      --primary: #000;
      --charcoal: #111;
      --border: #000;
      --bg-soft: #fff;
    }

    * {
      box-sizing: border-box;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    body {
      font-family: 'Inter', -apple-system, sans-serif;
      font-size: 12px;
      line-height: 1.3;
      color: var(--charcoal);
      margin: 0;
      padding: 0;
      background: #525659;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    @page {
      size: A4 portrait;
      margin: 0;
    }

    @media print {
      body {
        background: none !important;
        padding: 0 !important;
      }

      .no-print,
      .toolbar {
        display: none !important;
      }

      .container {
        margin: 0 !important;
        border: none !important;
        box-shadow: none !important;
        width: 210mm !important;
        height: 297mm !important;
      }
    }

    .container {
      background: #fff;
      width: 210mm;
      height: 297mm;
      margin: 20px auto;
      padding: 12mm 15mm;
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      border: 1px solid #d1d1d1;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
    }

    /* Watermark Effect */
    .container::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 450px;
      height: 450px;
      background: url('<?= $base . Config::get('school_logo', '/assets/img/school-logo.png') ?>') center/contain no-repeat;
      transform: translate(-50%, -50%) rotate(-15deg);
      opacity: 0.03;
      pointer-events: none;
      z-index: 0;
    }

    /* Toolbar */
    .toolbar {
      width: 210mm;
      margin: 15px auto 0 auto;
      display: flex;
      justify-content: space-between;
      padding: 0 5px;
    }

    .toolbar .btn {
      text-decoration: none;
      padding: 10px 20px;
      background: #1a73e8;
      color: #fff;
      font-weight: 600;
      border: none;
      cursor: pointer;
      border-radius: 4px;
      font-size: 13px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    /* Header Section */
    .header-section {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      gap: 20px;
      position: relative;
      z-index: 1;
    }

    .school-logo {
      width: 90px;
      flex-shrink: 0;
    }

    .school-logo img {
      width: 100%;
      height: auto;
    }

    .school-info {
      flex-grow: 1;
      text-align: center;
    }

    .school-info h1 {
      margin: 0;
      font-family: 'Old Standard TT', serif;
      font-size: 24px;
      font-weight: 700;
      color: #000;
      line-height: 1.2;
    }

    .school-info h2 {
      margin: 5px 0;
      font-size: 20px;
      font-weight: 800;
      text-transform: uppercase;
    }

    .report-title-badge {
      display: inline-block;
      border: 1.5px solid #000;
      padding: 4px 20px;
      font-weight: 800;
      font-size: 13px;
      margin-top: 10px;
      letter-spacing: 1px;
    }

    .student-photo {
      width: 100px;
      height: 110px;
      border: 1px solid #000;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #fdfdfd;
    }

    .student-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    /* Meta Info Grid */
    .meta-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 15px;
      position: relative;
      z-index: 1;
    }

    .meta-table {
      width: 100%;
      border-collapse: collapse;
    }

    .meta-table td {
      padding: 5px 0;
      vertical-align: middle;
    }

    .meta-table .label {
      width: 120px;
      font-weight: 700;
      font-size: 11px;
      text-transform: uppercase;
      color: #444;
    }

    .meta-table .value {
      border-bottom: 1px solid #000;
      font-weight: 800;
      font-size: 13px;
      padding-left: 5px;
    }

    /* Scores Table */
    .scores-section {
      flex-grow: 1;
      position: relative;
      z-index: 1;
    }

    .scores-table {
      width: 100%;
      border-collapse: collapse;
      border: 2px solid #000;
    }

    .scores-table th {
      border: 1px solid #000;
      background: #f2f2f2;
      padding: 8px 5px;
      font-size: 10px;
      font-weight: 800;
      text-transform: uppercase;
      text-align: center;
    }

    .scores-table td {
      border: 1px solid #000;
      padding: 6px 8px;
    }

    .scores-table .subject-name {
      font-weight: 700;
      font-size: 12px;
      background: #fff;
    }

    .center {
      text-align: center;
    }

    .bold {
      font-weight: 800;
    }

    /* Footer Details */
    .footer-section {
      margin-top: 15px;
      position: relative;
      z-index: 1;
    }

    .summary-grid {
      display: grid;
      grid-template-columns: 1.5fr 1fr;
      gap: 20px;
    }

    .remarks-box {
      border: 1.5px solid #000;
      padding: 8px;
      margin-bottom: 10px;
    }

    .remarks-title {
      font-weight: 800;
      font-size: 11px;
      text-transform: uppercase;
      margin-bottom: 4px;
      display: block;
    }

    .remarks-content {
      font-style: italic;
      min-height: 20px;
      display: block;
    }

    .grading-scale {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000;
      font-size: 10px;
    }

    .grading-scale th {
      background: #f2f2f2;
      border: 1px solid #000;
      padding: 4px;
      font-weight: 800;
      text-align: left;
    }

    .grading-scale td {
      border: 1px solid #000;
      padding: 3px 6px;
      font-weight: 600;
    }

    .signature-area {
      display: flex;
      justify-content: flex-end;
      align-items: flex-end;
      gap: 40px;
      margin-top: 10px;
    }

    .sig-block {
      text-align: center;
      width: 200px;
      position: relative;
    }

    .sig-line {
      border-top: 1.5px solid #000;
      margin-top: 40px;
      padding-top: 5px;
      font-weight: 800;
      font-size: 11px;
      text-transform: uppercase;
    }

    .stamp-img {
      position: absolute;
      bottom: 25px;
      right: 50px;
      max-height: 80px;
      opacity: 0.8;
      pointer-events: none;
      mix-blend-mode: multiply;
    }

    .sig-img {
      position: absolute;
      bottom: 30px;
      left: 50%;
      transform: translateX(-50%);
      max-height: 50px;
      pointer-events: none;
      mix-blend-mode: multiply;
    }

    .copyright {
      text-align: center;
      font-size: 9px;
      color: #777;
      margin-top: 15px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
  </style>
</head>

<body>

  <div class="toolbar no-print">
    <a href="javascript:history.back()" class="btn">← Back to Dashboard</a>
    <button onclick="window.print()" class="btn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
      </svg>
      Print Official Report
    </button>
  </div>

  <div class="container">
    <div class="header-section">
      <div class="school-logo">
        <img src="<?= $base . Config::get('school_logo', '/assets/img/school-logo.png') ?>" alt="Badge" decoding="async" fetchpriority="high" onerror="this.style.visibility='hidden'">
      </div>
      <div class="school-info">
        <h1><?= htmlspecialchars(Config::get('school_body', 'ARMED FORCES EDUCATION UNIT')) ?></h1>
        <h2><?= htmlspecialchars(Config::get('school_name', 'UADDARA BASIC SCHOOL')) ?></h2>
        <div class="report-title-badge">PUPIL'S REPORT FORM</div>
      </div>
      <div class="student-photo">
        <?php if ($photoSrc): ?>
          <img src="<?= htmlspecialchars($photoSrc) ?>" alt="Student" decoding="async" fetchpriority="high">
        <?php else: ?>
          <svg fill="#eee" viewBox="0 0 24 24" width="60" height="60">
            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
        <?php endif; ?>
      </div>
    </div>

    <div class="meta-grid">
      <table class="meta-table">
        <tr>
          <td class="label">Name of Pupil:</td>
          <td class="value"><?= mb_strtoupper(htmlspecialchars($student['full_name'] ?? '')) ?></td>
        </tr>
        <tr>
          <td class="label">Class:</td>
          <td class="value"><?= mb_strtoupper(htmlspecialchars(($student['class_name'] ?? '') . ' ' . ($student['section'] ?? ''))) ?></td>
        </tr>
        <tr>
          <td class="label">Academic Year:</td>
          <td class="value"><?= htmlspecialchars($term['year_name'] ?? '') ?></td>
        </tr>
        <tr>
          <td class="label">Term:</td>
          <td class="value"><?= strtoupper(htmlspecialchars($term['name'] ?? '')) ?></td>
        </tr>
      </table>
      <table class="meta-table">
        <tr>
          <td class="label"><?= ($gradingSystem === 'waec') ? 'Aggregate:' : 'Pos. in Class:' ?></td>
          <td class="value">
            <?php if ($gradingSystem === 'waec'): ?>
              <?= $aggregate['aggregate_grade'] ?? '—' ?>
            <?php else: ?>
              <?= $aggregate ? ordinal((int)$aggregate['class_position']) : '—' ?>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td class="label">No. on Roll:</td>
          <td class="value"><?= $classSize ?></td>
        </tr>
        <tr>
          <td class="label">Next Term Starts:</td>
          <td class="value"><?= !empty($term['next_term_begins']) ? strtoupper(date('jS M, Y', strtotime($term['next_term_begins']))) : '—' ?></td>
        </tr>
        <tr>
          <td class="label">Date:</td>
          <td class="value"><?= date('d/m/Y') ?></td>
        </tr>
      </table>
    </div>

    <div class="scores-section">
      <table class="scores-table">
        <thead>
          <tr>
            <th style="text-align:left; padding-left:12px;">Subject</th>
            <th style="width:75px;">Class Score<br>(50%)</th>
            <th style="width:75px;">Exam Score<br>(50%)</th>
            <th style="width:75px;">Total Score<br>(100%)</th>
            <th style="width:65px;"><?= $gradingSystem === 'waec' ? 'Grade' : 'Grade<br>(1-5)' ?></th>
            <th style="width:65px;">Pos.</th>
            <th style="width:180px;"><?= $gradingSystem === 'waec' ? 'Remarks' : 'Proficiency Level' ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($scores as $s): ?>
            <tr>
              <td class="subject-name"><?= htmlspecialchars($s['subject_name']) ?></td>
              <td class="center"><?= $s['class_score'] !== null ? number_format((float)$s['class_score'], 0) : '–' ?></td>
              <td class="center"><?= $s['exam_score'] !== null ? number_format((float)$s['exam_score'], 0) : '–' ?></td>
              <td class="center bold" style="background:#f9f9f9;"><?= $s['overall_total'] !== null ? number_format((float)$s['overall_total'], 0) : '0' ?></td>
              <td class="center"><?= $s['proficiency_level'] ?? ($gradingSystem === 'waec' ? '9' : '5') ?></td>
              <td class="center"><?= $s['subject_position'] ? ordinal((int)$s['subject_position']) : '—' ?></td>
              <td class="center" style="font-size:11px; font-weight:600;"><?= htmlspecialchars($scale[$s['proficiency_level'] ?? ($gradingSystem === 'waec' ? '9' : '5')]['label'] ?? '—') ?></td>
            </tr>
          <?php endforeach; ?>

          <?php
          $rowsToFill = max(0, 10 - count($scores));
          for ($i = 0; $i < $rowsToFill; $i++): ?>
            <tr style="height:28px;">
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          <?php endfor; ?>
        </tbody>
        <tfoot>
          <tr style="background:#f2f2f2; font-weight:800;">
            <td colspan="3" style="text-align:right; padding-right:15px; text-transform:uppercase; font-size:11px;">Overall Performance Total:</td>
            <td class="center"><?= $aggregate ? number_format((float)$aggregate['aggregate_score'], 0) : '0' ?></td>
            <td colspan="3"></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="footer-section">
      <div class="summary-grid">
        <div class="remarks-area">
          <div class="remarks-box">
            <span class="remarks-title">Attendance & Conduct:</span>
            <div style="display:flex; gap:20px; font-weight:700; margin-bottom:5px;">
              <span>Present: <?= $attendance['days_present'] ?? '0' ?> Days</span>
              <span>Out of: <?= $term['total_school_days'] ?? '60' ?> Days</span>
            </div>
            <span class="remarks-title" style="margin-top:10px;">Class Teacher's Remarks:</span>
            <span class="remarks-content"><?= htmlspecialchars($remarks['teacher_remark'] ?? '–') ?></span>
          </div>

          <div class="remarks-box" style="margin-bottom:0;">
            <span class="remarks-title">Headteacher's Comments / Recommendations:</span>
            <span class="remarks-content" style="min-height:40px;"><?= htmlspecialchars($remarks['headmaster_remark'] ?? 'Recommended for promotion.') ?></span>
          </div>
        </div>

        <div class="grading-area">
          <table class="grading-scale">
            <thead>
              <tr>
                <th colspan="2"><?= $gradingSystem === 'waec' ? 'WAEC GRADE SYSTEM' : 'PROFICIENCY SCALE' ?></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($scale as $grade => $meta): ?>
                <tr>
                  <td><?= $grade ?>: <?= $meta['label'] ?></td>
                  <td style="text-align:center;"><?= $meta['range'] ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="signature-area">
        <div class="sig-block">
          <?php if ($stampPath): ?>
            <img src="<?= $stampPath ?>" alt="Stamp" class="stamp-img">
          <?php endif; ?>
          <?php if ($sigPath): ?>
            <img src="<?= $sigPath ?>" alt="Signature" class="sig-img">
          <?php endif; ?>
          <div class="sig-line">Headteacher's Signature & Stamp</div>
        </div>
      </div>

      <div class="copyright">
        © <?= date('Y') ?> <?= htmlspecialchars(Config::get('school_name', 'Marabel SBA')) ?> — Official Student Record & Academic Performance Report
      </div>
    </div>
  </div>
</body>
</body>

</html>