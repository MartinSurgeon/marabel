<?php
/**
 * Bulk Print Report Cards
 * Uaddara Basic School — SBA Management System
 * 
 * PREMIUM VERSION: Optimized for official presentation and print.
 * Prints all students in a class.
 */

global $bp_classInfo, $bp_term, $bp_students, $bp_scores, $bp_aggregates,
       $bp_remarks, $bp_attendance, $bp_classSize, $bp_classTeacher, $bp_isPublished;

$classInfo    = $bp_classInfo ?? [];
$term         = $bp_term ?? [];
$students     = $bp_students ?? [];
$scoresData   = $bp_scores ?? [];
$aggregates   = $bp_aggregates ?? [];
$remarksData  = $bp_remarks ?? [];
$attendance   = $bp_attendance ?? [];
$classSize    = $bp_classSize ?? 0;
// $classTeacher = $bp_classTeacher ?? null;
$gradingSystem = $classInfo['grading_system'] ?? 'proficiency';

$scale = ($gradingSystem === 'waec') ? WAEC_SCALE : PROFICIENCY_SCALE;

$base = defined('APP_BASE') ? APP_BASE : '';

// Signature and Stamp Paths
$sigPath = null;
$stampPath = null;
$sigDir = ROOT_PATH . '/assets/uploads/signatures';
foreach (['png', 'jpg', 'jpeg'] as $ext) {
    if (!$sigPath && file_exists("$sigDir/headmaster_signature.$ext")) $sigPath = "$base/assets/uploads/signatures/headmaster_signature.$ext";
    if (!$stampPath && file_exists("$sigDir/school_stamp.$ext")) $stampPath = "$base/assets/uploads/signatures/school_stamp.$ext";
}

function ordinal(int $n): string {
    $s = ['th','st','nd','rd'];
    $v = $n % 100;
    return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bulk Print: <?= htmlspecialchars($classInfo['class_name'] ?? 'Class') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Old+Standard+TT:wght@400;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary: #7e2bb3;
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
      padding: 20px;
      background: #f0f0f2;
    }
    @page {
      size: A4 portrait;
      margin: 12mm;
    }
    @media print {
      body { padding: 0 !important; background: #fff !important; margin: 0 !important; }
      .no-print, .toolbar { display: none !important; }
      .container { 
        border: none !important; 
        box-shadow: none !important; 
        margin: 0 !important; 
        padding: 0 !important; 
        width: 100% !important; 
        max-width: none !important; 
        overflow: visible !important;
      }
      .container::before { opacity: 0.03 !important; }
      .page-break { page-break-after: always; margin: 0; padding: 0; border: none; }
      /* Ensure the last page doesn't have an empty blank page after it */
      .page-break:last-of-type { page-break-after: auto; }
    }
    
    .toolbar {
      max-width: 820px;
      margin: 0 auto 30px auto;
      background: #fff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 20px;
      z-index: 100;
      border: 1px solid #e2e8f0;
    }
    .toolbar-info {
        display: flex;
        flex-direction: column;
    }
    .toolbar-title { font-weight: 800; font-size: 16px; color: #111; margin:0;}
    .toolbar-meta { font-size: 12px; color: #64748b; font-weight: 500; margin-top:2px; }
    .toolbar-actions { display: flex; gap: 10px; }
    .btn {
      text-decoration: none;
      padding: 10px 20px;
      font-weight: 700;
      border: none;
      cursor: pointer;
      border-radius: 8px;
      font-size: 13.5px;
      transition: all 0.2s;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .btn-outline { background: transparent; border: 1.5px solid #cbd5e1; color: #475569; }
    .btn-outline:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }
    .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 4px 12px rgba(79, 29, 150, 0.25); }
    .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

    .container {
      background: #fff;
      max-width: 820px;
      margin: 0 auto 40px auto;
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
      background: url('<?= $base . Config::get('school_logo', '/assets/img/school-logo.png') ?>') center/contain no-repeat;
      transform: translate(-50%, -50%) rotate(-15deg);
      opacity: 0.04;
      pointer-events: none;
      z-index: 0;
    }

    /* Header Components */
    .school-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px; position: relative; z-index: 1; }
    .school-logo { width: 110px; }
    .school-logo img { width: 100%; height: auto; display: block; }
    
    .school-text { text-align: center; flex: 1; padding: 0 15px; }
    .school-text h1 { margin: 0; font-family: 'Old Standard TT', serif; font-size: 26px; font-weight: 700; color: #111; letter-spacing: 0.5px; }
    .school-text h2 { margin: 8px 0 0 0; font-size: 20px; font-weight: 800; color: #000; letter-spacing: -0.2px; }
    .pupil-badge { display: inline-block; margin-top: 15px; background: <?= htmlspecialchars(Config::get('brand_accent_color', '#c00000')) ?>; color: #fff; padding: 5px 25px; font-size: 14px; font-weight: 800; letter-spacing: 1px; border: 1px solid #000; box-shadow: 2px 2px 0 rgba(0,0,0,0.1); }
    
    .student-photo { width: 110px; height: 120px; background: #f8f8f8; border: 1px solid #000; display: flex; align-items: center; justify-content: center; }
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
    .prof-table { width: 70%; margin: 20px auto 0; border: 1.5px solid #000; font-size: 11px; }
    .prof-table th, .prof-table td { border: 1px solid #000; padding: 5px 12px; }
    .prof-table th { background: #eee; font-weight: 800; text-align: left; }
    .prof-table td { font-weight: 700; }
  </style>
</head>
<body>

<div class="toolbar no-print">
  <div class="toolbar-info">
      <h2 class="toolbar-title">Bulk Print: <?= htmlspecialchars($classInfo['class_name'] ?? '') ?><?= $classInfo['section'] ? " ({$classInfo['section']})" : '' ?></h2>
      <div class="toolbar-meta"><?= htmlspecialchars($term['name'] ?? '') ?> &middot; <?= $classSize ?> Students Formatted for Printing</div>
  </div>
  <div class="toolbar-actions">
      <a href="<?= $base ?>/admin/publish" class="btn btn-outline">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back
      </a>
      <button onclick="window.print()" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          Print All Reports
      </button>
  </div>
</div>

<?php 
$count = 0;
foreach ($students as $student): 
    $s_id = $student['id'];
    $agg  = $aggregates[$s_id] ?? null;
    $rem  = $remarksData[$s_id] ?? null;
    $att  = $attendance[$s_id] ?? null;
    $scs  = $scoresData[$s_id] ?? [];

    $photoSrc = (!empty($student['photo_path']) && file_exists(ROOT_PATH . '/' . ltrim($student['photo_path'], '/')))
      ? $base . '/' . ltrim($student['photo_path'], '/')
      : null;
?>

<div class="container page-break">
  <?php if (!$bp_isPublished): ?>
    <!-- <div class="draft-watermark">DRAFT PREVIEW</div> -->
  <?php endif; ?>
  
  <div class="school-header">
    <div class="school-logo">
      <img src="<?= $base . Config::get('school_logo', '/assets/img/school-logo.png') ?>" alt="School Badge" onerror="this.style.visibility='hidden'">
    </div>
    
    <div class="school-text">
      <h1 style="text-transform:uppercase;"><?= htmlspecialchars(Config::get('school_body', 'ARMED FORCES EDUCATION UNIT')) ?></h1>
      <h2 style="text-transform:uppercase;"><?= htmlspecialchars(Config::get('school_name', 'MARABEL SBA')) ?></h2>
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
      <td class="label-small"><?= ($gradingSystem === 'waec') ? 'Aggregate:' : 'Position:' ?></td>
      <td class="value-small">
        <?php if ($gradingSystem === 'waec'): ?>
           <?= $agg['aggregate_grade'] ?? '—' ?>
        <?php else: ?>
           <?= $agg ? ordinal((int)$agg['class_position']) : '—' ?>
        <?php endif; ?>
      </td>
    </tr>
    <tr>
      <td class="label">Class:</td>
      <td class="value" colspan="3"><?= mb_strtoupper(htmlspecialchars(($classInfo['class_name'] ?? '') . ' ' . ($classInfo['section'] ?? ''))) ?></td>
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
        <th style="width:60px;"><?= $gradingSystem === 'waec' ? 'Grade' : 'Grade (1-5)' ?></th>
        <th style="width:60px;">Position</th>
        <th style="width:180px;"><?= $gradingSystem === 'waec' ? 'Remarks' : 'Level of Proficiency' ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($scs as $s): ?>
      <tr>
        <td class="subject-name"><?= htmlspecialchars($s['subject_name']) ?></td>
        <td class="center"><?= $s['class_score'] !== null ? number_format((float)$s['class_score'], 0) : '–' ?></td>
        <td class="center"><?= $s['exam_score'] !== null ? number_format((float)$s['exam_score'], 0) : '–' ?></td>
        <td class="center" style="font-weight:800; background:#f9f9f9;"><?= $s['overall_total'] !== null ? number_format((float)$s['overall_total'], 0) : '0' ?></td>
        <td class="center"><?= $s['proficiency_level'] ?? ($gradingSystem === 'waec' ? '9' : '5') ?></td>
        <td class="center"><?= $s['subject_position'] ? ordinal((int)$s['subject_position']) : '—' ?></td>
        <td class="center remarks-cell"><?= htmlspecialchars($scale[$s['proficiency_level'] ?? ($gradingSystem === 'waec' ? '9' : '5')]['label'] ?? '—') ?></td>
      </tr>
      <?php endforeach; ?>

      <?php 
      $rowsToFill = max(0, 5 - count($scs));
      for($i=0; $i<$rowsToFill; $i++): ?>
      <tr style="height:26px;">
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td>
      </tr>
      <?php endfor; ?>

      <tr class="total-row">
        <td colspan="3" style="text-align:right; padding-right:15px; text-transform:uppercase; letter-spacing:1px; font-size:11px;">Overall Total:</td>
        <td class="center"><?= $agg ? number_format((float)$agg['aggregate_score'], 0) : '0' ?></td>
        <td colspan="3"></td>
      </tr>

      <tr>
        <td style="font-weight:700;">Attendance:</td>
        <td class="center"><?= $att['days_present'] ?? '0' ?></td>
        <td style="text-align:right; font-weight:700;">Out of:</td>
        <td class="center"><?= $term['total_school_days'] ?? '60' ?></td>
        <td style="font-weight:700;">Promoted to:</td>
        <td colspan="2"></td>
      </tr>

      <tr>
        <td colspan="2" style="font-weight:700;">Class Teacher's Remarks:</td>
        <td colspan="5" style="font-style:italic;"><?= htmlspecialchars($rem['teacher_remark'] ?? '–') ?></td>
      </tr>
      <tr>
        <td colspan="2" style="font-weight:700; height:60px; vertical-align:top; border-bottom:2px solid #000;">Headteacher's Remarks:</td>
        <td colspan="5" style="border-bottom:2px solid #000; vertical-align:top; padding-top:5px;">
          <div style="min-height:30px;"><?= htmlspecialchars($rem['headmaster_remark'] ?? '') ?></div>
          <div style="margin-top:20px; text-align:right; font-size:10px; font-weight:700; opacity:0.6; position:relative; min-height:40px;">
              <?php if ($stampPath): ?>
                <img src="<?= $stampPath ?>" alt="Stamp" style="position:absolute; right:150px; bottom:-10px; max-height:85px; opacity:0.85; mix-blend-mode:multiply; pointer-events:none;">
              <?php endif; ?>
              <?php if ($sigPath): ?>
                <img src="<?= $sigPath ?>" alt="Signature" style="position:absolute; right:30px; bottom:20px; max-height:55px; mix-blend-mode:multiply; pointer-events:none;">
              <?php endif; ?>
              <span style="position:absolute; right:0; bottom:0;">SIGNATURE & STAMP</span>
          </div>
        </td>
      </tr>
    </tbody>
  </table>

  <table class="prof-table">
    <thead>
      <tr>
        <th><?= $gradingSystem === 'waec' ? 'WAEC GRADE DEFINITION' : 'LEVEL OF PROFICIENCY' ?></th>
        <th>BENCHMARK</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($scale as $grade => $meta): ?>
      <tr>
        <td><?= $grade ?>: <?= $meta['label'] ?> (<?= $meta['abbr'] ?>)</td>
        <td><?= $meta['range'] ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div style="margin-top:30px; text-align:center; font-size:10px; color:#999; text-transform:uppercase; letter-spacing:1px; z-index:1; position:relative;">
    © <?= date('Y') ?> <?= htmlspecialchars(Config::get('school_name', 'Marabel SBA')) ?> — Official Student Record
  </div>

</div>

<?php 
$count++;
endforeach; 
?>

</body>
</html>
