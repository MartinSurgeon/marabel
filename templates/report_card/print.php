<?php
/**
 * Report Card — Individual Term Report
 * Uaddara Basic School — SBA Management System
 *
 * Displays a beautifully formatted report card for one student / one term.
 * Accessible at /report?student={id}&term={id}
 */

// Use globals set by ReportCardController
global $rc_student, $rc_term, $rc_scores, $rc_aggregate,
       $rc_classSize, $rc_remarks, $rc_attendance, $rc_classTeacher;

$student      = $rc_student      ?? [];
$term         = $rc_term         ?? [];
$scores       = $rc_scores       ?? [];
$aggregate    = $rc_aggregate    ?? null;
$classSize    = $rc_classSize    ?? 0;
$remarks      = $rc_remarks      ?? null;
$attendance   = $rc_attendance   ?? null;
$classTeacher = $rc_classTeacher ?? null;

$base = defined('APP_BASE') ? APP_BASE : '';

// Proficiency helpers
$profColors = [
    1 => ['bg' => '#dcfce7', 'text' => '#166534', 'border' => '#bbf7d0'],
    2 => ['bg' => '#eff6ff', 'text' => '#1e40af', 'border' => '#bfdbfe'],
    3 => ['bg' => '#fffbeb', 'text' => '#92400e', 'border' => '#fde68a'],
    4 => ['bg' => '#fff7ed', 'text' => '#9a3412', 'border' => '#fed7aa'],
    5 => ['bg' => '#fef2f2', 'text' => '#991b1b', 'border' => '#fecaca'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report Card — <?= htmlspecialchars($student['full_name'] ?? 'Student') ?> · <?= htmlspecialchars($term['name'] ?? '') ?></title>
  <meta name="description" content="Term report card for <?= htmlspecialchars($student['full_name'] ?? '') ?>">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <link rel="icon" type="image/png" href="<?= $base ?>/assets/img/school-logo.png">
  <style>
    /* ── Report Card Theme ───────────────────────────────────────── */
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    :root {
      --rc-primary:   #4f1d96;
      --rc-accent:    #7c3aed;
      --rc-gold:      #d97706;
      --rc-gold-bg:   #fffbeb;
      --rc-green:     #065f46;
      --rc-border:    #e5e7eb;
      --rc-surface:   #f9fafb;
      --rc-text:      #111827;
      --rc-muted:     #6b7280;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', system-ui, sans-serif;
      background: var(--rc-surface);
      color: var(--rc-text);
      min-height: 100vh;
      padding: 1.5rem 1rem 4rem;
    }

    /* ── Toolbar (screen only) ───────────────────────────────────── */
    .rc-toolbar {
      max-width: 860px;
      margin: 0 auto 1.5rem;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      flex-wrap: wrap;
    }
    @media print { .rc-toolbar { display: none; } }

    /* ── Card Shell ──────────────────────────────────────────────── */
    .rc-card {
      max-width: 860px;
      margin: 0 auto;
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 4px 40px rgba(0,0,0,0.10);
      overflow: hidden;
    }
    @media print {
      body { background:#fff; padding:0; }
      .rc-card { box-shadow:none; border-radius:0; max-width:100%; }
    }

    /* ── Header / School Identity ────────────────────────────────── */
    .rc-header {
      background: linear-gradient(135deg, var(--rc-primary) 0%, var(--rc-accent) 100%);
      color: #fff;
      padding: 2rem 2.5rem 1.75rem;
      text-align: center;
      position: relative;
    }
    .rc-header-logo {
      width: 72px; height: 72px;
      border-radius: 50%;
      border: 3px solid rgba(255,255,255,0.35);
      object-fit: contain;
      background: rgba(255,255,255,0.12);
      padding: 6px;
      margin-bottom: 0.75rem;
    }
    .rc-school-name {
      font-size: 1.375rem;
      font-weight: 900;
      letter-spacing: -0.02em;
      line-height: 1.2;
    }
    .rc-school-sub {
      font-size: 0.8rem;
      opacity: 0.8;
      margin-top: 0.25rem;
      font-weight: 500;
    }
    .rc-report-badge {
      display: inline-block;
      margin-top: 1rem;
      background: rgba(255,255,255,0.18);
      color: #fff;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      padding: 5px 16px;
      border-radius: 99px;
      border: 1px solid rgba(255,255,255,0.3);
    }

    /* ── Student Bio Row ─────────────────────────────────────────── */
    .rc-bio {
      display: grid;
      grid-template-columns: auto 1fr auto;
      align-items: center;
      gap: 1.5rem;
      padding: 1.75rem 2.5rem;
      border-bottom: 1px solid var(--rc-border);
      background: #fff;
    }
    @media (max-width: 600px) {
      .rc-bio { grid-template-columns: 1fr; text-align: center; }
    }
    .rc-avatar {
      width: 80px; height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid var(--rc-border);
      background: #f3f4f6;
      flex-shrink: 0;
    }
    .rc-student-name {
      font-size: 1.25rem;
      font-weight: 800;
      color: var(--rc-text);
      line-height: 1.2;
    }
    .rc-student-meta {
      font-size: 0.8rem;
      color: var(--rc-muted);
      margin-top: 0.25rem;
    }
    .rc-bio-stats {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 0.5rem;
    }
    @media (max-width: 600px) { .rc-bio-stats { align-items: center; } }
    .rc-term-badge {
      background: var(--rc-primary);
      color: #fff;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.05em;
      padding: 4px 12px;
      border-radius: 99px;
      text-transform: uppercase;
    }
    .rc-year-label {
      font-size: 11px;
      color: var(--rc-muted);
      font-weight: 600;
    }

    /* ── Summary Metric Cards ────────────────────────────────────── */
    .rc-summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      border-bottom: 1px solid var(--rc-border);
    }
    @media (max-width: 580px) {
      .rc-summary-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .rc-metric {
      padding: 1.25rem 1rem;
      text-align: center;
      border-right: 1px solid var(--rc-border);
    }
    .rc-metric:last-child { border-right: none; }
    .rc-metric-value {
      font-size: 1.625rem;
      font-weight: 900;
      letter-spacing: -0.03em;
      color: var(--rc-primary);
      line-height: 1;
    }
    .rc-metric-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--rc-muted);
      margin-top: 0.4rem;
    }

    /* ── Scores Table ────────────────────────────────────────────── */
    .rc-section {
      padding: 1.75rem 2.5rem;
      border-bottom: 1px solid var(--rc-border);
    }
    .rc-section-title {
      font-size: 11px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: var(--rc-muted);
      margin-bottom: 1rem;
    }
    .rc-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .rc-table th {
      font-size: 10px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--rc-muted);
      padding: 7px 10px;
      border-bottom: 2px solid var(--rc-border);
      white-space: nowrap;
    }
    .rc-table td {
      padding: 10px 10px;
      border-bottom: 1px solid #f3f4f6;
      vertical-align: middle;
    }
    .rc-table tbody tr:hover { background: #fafafa; }
    .rc-table tbody tr:last-child td { border-bottom: none; }
    .rc-table .subject-name { font-weight: 600; color: var(--rc-text); }
    .rc-table .score-cell { text-align: center; font-variant-numeric: tabular-nums; }
    .rc-table .total-cell {
      text-align: center;
      font-weight: 800;
      font-size: 14px;
      color: var(--rc-primary);
    }
    .rc-table .pos-cell {
      text-align: center;
      font-size: 11px;
      font-weight: 700;
      color: var(--rc-muted);
    }

    /* ── Proficiency Badge ───────────────────────────────────────── */
    .prof-badge {
      display: inline-block;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.04em;
      padding: 3px 8px;
      border-radius: 4px;
      border: 1px solid transparent;
      white-space: nowrap;
    }

    /* ── Remarks + Attendance ────────────────────────────────────── */
    .rc-remarks-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
    }
    @media (max-width: 600px) { .rc-remarks-grid { grid-template-columns: 1fr; } }
    .rc-remark-box {
      background: var(--rc-surface);
      border: 1px solid var(--rc-border);
      border-radius: 10px;
      padding: 1rem 1.25rem;
    }
    .rc-remark-label {
      font-size: 10px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: var(--rc-muted);
      margin-bottom: 0.5rem;
    }
    .rc-remark-value {
      font-size: 13px;
      font-weight: 600;
      color: var(--rc-text);
      line-height: 1.5;
    }

    /* ── Attendance Bar ──────────────────────────────────────────── */
    .rc-att-bar {
      height: 8px;
      background: #e5e7eb;
      border-radius: 99px;
      overflow: hidden;
      margin-top: 0.6rem;
    }
    .rc-att-fill {
      height: 100%;
      border-radius: 99px;
      background: linear-gradient(90deg, #34d399 0%, #059669 100%);
    }

    /* ── Signature Row ───────────────────────────────────────────── */
    .rc-sig-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
      padding: 1.75rem 2.5rem 2rem;
    }
    @media (max-width: 500px) { .rc-sig-row { grid-template-columns: 1fr; } }
    .rc-sig-block { text-align: center; }
    .rc-sig-line {
      border-bottom: 1.5px solid var(--rc-border);
      margin-bottom: 0.4rem;
      min-height: 36px;
    }
    .rc-sig-label {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: var(--rc-muted);
    }

    /* ── Footer Ribbon ───────────────────────────────────────────── */
    .rc-footer {
      background: linear-gradient(135deg, var(--rc-primary), var(--rc-accent));
      color: rgba(255,255,255,0.75);
      text-align: center;
      font-size: 11px;
      font-weight: 600;
      padding: 0.875rem 1rem;
    }
  </style>
</head>
<body>

<!-- ── Screen-only Toolbar ──────────────────────────────────────── -->
<div class="rc-toolbar">
  <a href="javascript:history.back()" class="btn btn-ghost btn-xs" style="display:flex; align-items:center; gap:6px; text-decoration:none;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    Back
  </a>
  <button onclick="window.print()" class="btn btn-primary btn-xs" style="display:flex; align-items:center; gap:6px; margin-left:auto;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
    Print / Save PDF
  </button>
</div>

<!-- ── Report Card ───────────────────────────────────────────────── -->
<div class="rc-card">

  <!-- Header -->
  <div class="rc-header">
    <img
      src="<?= $base ?>/assets/img/school-logo.png"
      alt="School Logo"
      class="rc-header-logo"
      onerror="this.style.display='none'"
    >
    <div class="rc-school-name"><?= SCHOOL_NAME ?></div>
    <div class="rc-school-sub"><?= SCHOOL_BODY ?> · <?= SCHOOL_LOCATION ?></div>
    <div class="rc-report-badge">Student Report Card</div>
  </div>

  <!-- Student Bio -->
  <div class="rc-bio">
    <?php
      $photoSrc = (!empty($student['photo_path']) && file_exists(ROOT_PATH . '/' . ltrim($student['photo_path'], '/')))
        ? $base . '/' . ltrim($student['photo_path'], '/')
        : null;
    ?>
    <?php if ($photoSrc): ?>
      <img src="<?= htmlspecialchars($photoSrc) ?>" alt="Student Photo" class="rc-avatar">
    <?php else: ?>
      <div class="rc-avatar" style="display:flex; align-items:center; justify-content:center; font-size:2rem; color:#9ca3af;">
        <?= mb_strtoupper(mb_substr($student['full_name'] ?? 'S', 0, 1)) ?>
      </div>
    <?php endif; ?>

    <div>
      <div class="rc-student-name"><?= htmlspecialchars($student['full_name'] ?? '') ?></div>
      <div class="rc-student-meta">
        ID: <strong><?= htmlspecialchars($student['student_id_number'] ?? '—') ?></strong>
        · <?= htmlspecialchars($student['gender'] ?? '') ?>
        <?php if (!empty($student['date_of_birth'])): ?>
          · DOB: <?= date('d M Y', strtotime($student['date_of_birth'])) ?>
        <?php endif; ?>
      </div>
      <div class="rc-student-meta" style="margin-top:4px;">
        Class: <strong><?= htmlspecialchars(($student['class_name'] ?? '') . ($student['section'] ? " ({$student['section']})" : '')) ?></strong>
        · <?= htmlspecialchars($student['level_name'] ?? '') ?>
      </div>
    </div>

    <div class="rc-bio-stats">
      <div class="rc-term-badge"><?= htmlspecialchars($term['name'] ?? '') ?></div>
      <div class="rc-year-label"><?= htmlspecialchars($term['year_name'] ?? '') ?></div>
    </div>
  </div>

  <!-- Summary Metrics -->
  <?php
    $aggScore    = $aggregate ? (float)$aggregate['aggregate_score'] : 0;
    $classPos    = $aggregate ? (int)$aggregate['class_position'] : null;
    $numSubjects = $aggregate ? (int)$aggregate['number_of_subjects'] : count($scores);
    $avgScore    = $numSubjects > 0 ? round($aggScore / $numSubjects, 1) : 0;
    $daysPresent = $attendance ? (int)$attendance['days_present'] : null;
    $totalDays   = $term['total_school_days'] ? (int)$term['total_school_days'] : null;
    $attPct      = ($daysPresent !== null && $totalDays) ? round($daysPresent / $totalDays * 100) : null;
  ?>
  <div class="rc-summary-grid">
    <div class="rc-metric">
      <div class="rc-metric-value"><?= $aggScore > 0 ? number_format($aggScore, 1) : '—' ?></div>
      <div class="rc-metric-label">Total Score</div>
    </div>
    <div class="rc-metric">
      <div class="rc-metric-value"><?= $avgScore > 0 ? $avgScore : '—' ?></div>
      <div class="rc-metric-label">Average</div>
    </div>
    <div class="rc-metric">
      <div class="rc-metric-value" style="color:var(--rc-gold);">
        <?= $classPos ? ordinal($classPos) : '—' ?>
        <?php if ($classSize > 0): ?><span style="font-size:.9rem; font-weight:600; color:#9ca3af;"> / <?= $classSize ?></span><?php endif; ?>
      </div>
      <div class="rc-metric-label">Class Position</div>
    </div>
    <div class="rc-metric">
      <div class="rc-metric-value" style="color:<?= $attPct !== null ? ($attPct >= 75 ? '#059669' : '#dc2626') : 'var(--rc-muted)' ?>;">
        <?= $attPct !== null ? $attPct . '%' : '—' ?>
      </div>
      <div class="rc-metric-label">Attendance</div>
    </div>
  </div>

  <!-- Scores Table -->
  <div class="rc-section">
    <div class="rc-section-title">Subject Performance</div>

    <?php if (empty($scores)): ?>
      <div style="text-align:center; padding:2rem; color:var(--rc-muted); font-size:13px;">
        No scores have been recorded for this term.
      </div>
    <?php else: ?>
    <table class="rc-table">
      <thead>
        <tr>
          <th style="text-align:left;">Subject</th>
          <th class="score-cell">Class Score<br><span style="font-weight:500;">(/ 50)</span></th>
          <th class="score-cell">Exam Score<br><span style="font-weight:500;">(/ 50)</span></th>
          <th class="score-cell">Total<br><span style="font-weight:500;">(/ 100)</span></th>
          <th class="score-cell">Proficiency</th>
          <th class="pos-cell">Rank</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($scores as $s):
          $lvl   = (int)($s['proficiency_level'] ?? 5);
          $pc    = $profColors[$lvl] ?? $profColors[5];
          $pabbr = PROFICIENCY_SCALE[$lvl]['abbr']  ?? 'E';
          $plbl  = PROFICIENCY_SCALE[$lvl]['label'] ?? 'EMERGING';
        ?>
        <tr>
          <td class="subject-name" style="text-align:left;">
            <?= htmlspecialchars($s['subject_name']) ?>
            <?php if (!empty($s['subject_code'])): ?>
              <span style="font-size:10px; color:var(--rc-muted); margin-left:4px;"><?= htmlspecialchars($s['subject_code']) ?></span>
            <?php endif; ?>
          </td>
          <td class="score-cell"><?= $s['class_score'] !== null ? number_format($s['class_score'], 1) : '—' ?></td>
          <td class="score-cell"><?= $s['exam_score']  !== null ? number_format($s['exam_score'],  1) : '—' ?></td>
          <td class="total-cell"><?= $s['overall_total'] !== null ? number_format($s['overall_total'], 1) : '—' ?></td>
          <td class="score-cell">
            <span class="prof-badge" style="background:<?= $pc['bg'] ?>; color:<?= $pc['text'] ?>; border-color:<?= $pc['border'] ?>;" title="<?= $plbl ?>">
              <?= $pabbr ?>
            </span>
          </td>
          <td class="pos-cell">
            <?= $s['subject_position'] ? '#' . $s['subject_position'] : '—' ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr style="background:var(--rc-surface);">
          <td colspan="3" style="padding:10px; font-size:11px; font-weight:700; color:var(--rc-muted); text-align:right;">Totals</td>
          <td class="total-cell" style="border-top:2px solid var(--rc-border); font-size:1rem;"><?= $aggScore > 0 ? number_format($aggScore, 1) : '—' ?></td>
          <td></td><td></td>
        </tr>
      </tfoot>
    </table>
    <?php endif; ?>
  </div>

  <!-- Proficiency Key -->
  <div style="padding:0.75rem 2.5rem; border-bottom:1px solid var(--rc-border); display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center;">
    <span style="font-size:10px; font-weight:700; color:var(--rc-muted); text-transform:uppercase; letter-spacing:.05em; margin-right:0.5rem;">Proficiency Key:</span>
    <?php foreach (PROFICIENCY_SCALE as $lvl => $info):
      $pc = $profColors[$lvl] ?? $profColors[5];
    ?>
    <span class="prof-badge" style="background:<?= $pc['bg'] ?>; color:<?= $pc['text'] ?>; border-color:<?= $pc['border'] ?>;">
      <?= $info['abbr'] ?> – <?= $info['label'] ?>
    </span>
    <?php endforeach; ?>
  </div>

  <!-- Remarks & Attendance -->
  <div class="rc-section">
    <div class="rc-section-title">Remarks &amp; Attendance</div>
    <div class="rc-remarks-grid">

      <?php if ($remarks): ?>
        <div class="rc-remark-box">
          <div class="rc-remark-label">Conduct &amp; Character</div>
          <div class="rc-remark-value"><?= htmlspecialchars($remarks['conduct_character'] ?: '—') ?></div>
        </div>
        <div class="rc-remark-box">
          <div class="rc-remark-label">Attitude</div>
          <div class="rc-remark-value"><?= htmlspecialchars($remarks['attitude'] ?: '—') ?></div>
        </div>
        <?php if (!empty($remarks['teacher_remark'])): ?>
        <div class="rc-remark-box" style="grid-column: span 2;">
          <div class="rc-remark-label">Class Teacher's Remarks</div>
          <div class="rc-remark-value"><?= nl2br(htmlspecialchars($remarks['teacher_remark'])) ?></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($remarks['headmaster_remark'])): ?>
        <div class="rc-remark-box" style="grid-column: span 2; border-color:#a78bfa; background:#faf5ff;">
          <div class="rc-remark-label" style="color:var(--rc-primary);">Headmaster's Remarks</div>
          <div class="rc-remark-value"><?= nl2br(htmlspecialchars($remarks['headmaster_remark'])) ?></div>
        </div>
        <?php endif; ?>
      <?php else: ?>
        <div class="rc-remark-box" style="grid-column:span 2; color:var(--rc-muted); text-align:center;">
          No remarks recorded for this term.
        </div>
      <?php endif; ?>

      <!-- Attendance -->
      <?php if ($daysPresent !== null && $totalDays): ?>
      <div class="rc-remark-box" style="grid-column:span 2;">
        <div class="rc-remark-label">Attendance</div>
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
          <span class="rc-remark-value"><?= $daysPresent ?> of <?= $totalDays ?> days</span>
          <span style="font-weight:800; font-size:15px; color:<?= $attPct >= 75 ? '#059669' : '#dc2626' ?>;"><?= $attPct ?>%</span>
        </div>
        <div class="rc-att-bar">
          <div class="rc-att-fill" style="width:<?= min($attPct, 100) ?>%; background:<?= $attPct >= 75 ? 'linear-gradient(90deg, #34d399, #059669)' : 'linear-gradient(90deg, #fca5a5, #dc2626)' ?>;"></div>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>

  <!-- Next Term Info -->
  <?php if (!empty($term['next_term_begins'])): ?>
  <div style="padding:1rem 2.5rem; border-bottom:1px solid var(--rc-border); background:#fffbeb; display:flex; align-items:center; gap:0.75rem;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    <span style="font-size:12px; font-weight:700; color:#92400e;">
      Next term begins: <strong><?= date('l, d F Y', strtotime($term['next_term_begins'])) ?></strong>
    </span>
  </div>
  <?php endif; ?>

  <!-- Signature Section -->
  <div class="rc-sig-row">
    <div class="rc-sig-block">
      <div class="rc-sig-line"><?php if ($classTeacher): ?><div style="padding-top:14px; font-size:11px; font-weight:600; color:var(--rc-muted);"><?= htmlspecialchars($classTeacher['full_name']) ?></div><?php endif; ?></div>
      <div class="rc-sig-label">Class Teacher</div>
    </div>
    <div class="rc-sig-block">
      <div class="rc-sig-line"></div>
      <div class="rc-sig-label">Headmaster</div>
    </div>
    <div class="rc-sig-block">
      <div class="rc-sig-line"></div>
      <div class="rc-sig-label">Parent / Guardian</div>
    </div>
  </div>

  <!-- Footer -->
  <div class="rc-footer">
    <?= SCHOOL_NAME ?> &mdash; <?= htmlspecialchars($term['name'] . ' · ' . ($term['year_name'] ?? '')) ?> &mdash; Generated <?= date('d M Y') ?>
  </div>

</div><!-- .rc-card -->

<?php
// ── PHP helper: ordinal number ────────────────────────────────────
function ordinal(int $n): string {
    $s = ['th','st','nd','rd'];
    $v = $n % 100;
    return $n . ($s[($v - 20) % 10] ?? $s[$v] ?? $s[0]);
}
?>
</body>
</html>
