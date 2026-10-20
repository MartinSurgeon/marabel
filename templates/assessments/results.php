<?php
/**
 * Speed Test / Mock Exam Results Broadsheet
 * HCI/UX: rank first, grade badges colour-coded, one primary action (Print)
 */

global $rsAssessment, $rsClass, $rsSubjects, $rsResults, $rsSubjectStats, $rsRankByAggregate, $rsSiblings;
$a        = $rsAssessment;
$class    = $rsClass;
$types    = AssessmentController::TYPES;
$base     = defined('APP_BASE') ? APP_BASE : '';
$className = $class['class_name'] . ($class['section'] ? ' ' . $class['section'] : '');

$pageTitle = $a['title'] . ' Results';
include __DIR__ . '/../layout/header.php';

$abbr = fn($s) => $s['subject_code'] ?: $s['subject_name'];
?>

<style>
  .rs-table th, .rs-table td { padding:0.6rem 0.75rem; border-bottom:1px solid var(--clr-border); font-size:13px; }
  .rs-table th { font-size:11px; text-transform:uppercase; letter-spacing:0.04em; background:var(--clr-surface-2); white-space:nowrap; }
  .rs-grade { display:inline-flex; min-width:28px; justify-content:center; font-weight:800; }
  .print-only { display:none; }
  @media print {
    .sidebar, .app-header, .mobile-bottom-nav, .bottom-sheet, .no-print, .alert { display:none !important; }
    .app-layout, .app-main { display:block !important; margin:0 !important; padding:0 !important; }
    .print-only { display:block; }
    .card { box-shadow:none !important; border:none !important; }
    .rs-wrap { overflow:visible !important; max-height:none !important; }
    .rs-table th, .rs-table td { padding:4px 6px; font-size:11px; }
    @page { size: landscape; margin: 10mm; }
  }
</style>

<!-- Print header -->
<div class="print-only" style="text-align:center; margin-bottom:12px;">
  <div style="font-size:18px; font-weight:800;"><?= htmlspecialchars(Config::get('school_name', SCHOOL_NAME)) ?></div>
  <div style="font-size:14px; font-weight:700;"><?= htmlspecialchars($a['title']) ?> — <?= htmlspecialchars($className) ?></div>
  <div style="font-size:12px;"><?= htmlspecialchars($a['year_name'] . ' · ' . $a['term_name']) ?><?= $a['assessment_date'] ? ' · ' . date('d M Y', strtotime($a['assessment_date'])) : '' ?></div>
</div>

<div class="flex justify-between items-center mb-6 gap-4 flex-wrap no-print">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="<?= $base ?><?= Session::role() === 'admin' ? '/admin/assessments?term=' . $a['term_id'] : '/teacher/assessments?assessment=' . $a['id'] ?>" class="btn btn-ghost btn-xs" style="padding:4px; margin-left:-8px;" title="Back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">
        <?= htmlspecialchars($a['title']) ?> · <?= htmlspecialchars($className) ?>
      </h1>
    </div>
    <p class="text-muted m-0" style="font-size:var(--text-sm);">
      <?= $types[$a['assessment_type']] ?> · <?= htmlspecialchars($a['year_name'] . ' · ' . $a['term_name']) ?>
      · Ranked by <?= $rsRankByAggregate ? 'Best-6 aggregate (lower is better)' : 'average %' ?>
    </p>
  </div>
  <div class="flex items-center gap-3">
    <?php if (count($rsSiblings) > 1): ?>
    <select class="form-control" style="height:40px; min-width:180px;" aria-label="Switch assessment"
            onchange="location.href='<?= $base ?>/assessments/results?class=<?= (int)$class['id'] ?>&assessment=' + this.value">
      <?php foreach ($rsSiblings as $sib): ?>
      <option value="<?= $sib['id'] ?>" <?= $sib['id'] == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($sib['title']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>
    <button class="btn btn-primary shadow-purple" onclick="window.print()" style="height:40px;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      Print Results
    </button>
  </div>
</div>

<?php if (empty($rsSubjects)): ?>
<div class="card text-center py-12" style="border-style:dashed;">
  <p class="text-muted">No subjects are assigned to this class for the term.</p>
</div>
<?php else: ?>
<div class="card" style="padding:0; overflow:hidden; border:1px solid var(--clr-border);">
  <div class="rs-wrap" style="overflow-x:auto;">
    <table class="rs-table" style="width:100%; border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:center; width:50px;">Pos.</th>
          <th style="text-align:left;">Student</th>
          <?php foreach ($rsSubjects as $sub): ?>
          <th style="text-align:center;" title="<?= htmlspecialchars($sub['subject_name']) ?>"><?= htmlspecialchars($abbr($sub)) ?></th>
          <?php endforeach; ?>
          <th style="text-align:center;">Avg %</th>
          <th style="text-align:center; background:var(--clr-primary); color:#fff;">Aggregate</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rsResults as $r): ?>
        <tr>
          <td style="text-align:center; font-weight:800;"><?= $r['position'] ?? '—' ?></td>
          <td>
            <div style="font-weight:700; color:var(--clr-text);"><?= htmlspecialchars($r['student']['full_name']) ?></div>
            <div style="font-size:10px; color:var(--clr-text-muted);"><?= htmlspecialchars($r['student']['student_id_number']) ?></div>
          </td>
          <?php foreach ($rsSubjects as $sub): $sc = $r['scores'][$sub['id']] ?? null; ?>
          <td style="text-align:center;">
            <?php if ($sc): ?>
              <span class="badge rs-grade <?= GradingEngine::levelColourClass((int)$sc['grade'], 'waec') ?>" title="<?= (int)$sc['percentage'] ?>%"><?= (int)$sc['grade'] ?></span>
              <div style="font-size:10px; color:var(--clr-text-muted);"><?= (int)$sc['percentage'] ?>%</div>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <?php endforeach; ?>
          <td style="text-align:center; font-weight:700;"><?= $r['average'] ?? '—' ?></td>
          <td style="text-align:center; font-weight:900; font-size:15px;">
            <?php if ($r['aggregate'] !== null): ?>
              <?= $r['aggregate'] ?>
            <?php elseif ($r['count'] > 0): ?>
              <span class="text-muted" style="font-size:11px; font-weight:600;" title="All 4 core subjects (English, Maths, Integrated Science, Social Studies) are needed">Incomplete</span>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td></td>
          <td style="font-weight:800; font-size:11px; text-transform:uppercase; color:var(--clr-text-muted);">Subject Average</td>
          <?php foreach ($rsSubjects as $sub): $st = $rsSubjectStats[$sub['id']]; ?>
          <td style="text-align:center; font-weight:700;"><?= $st['average'] !== null ? $st['average'] . '%' : '—' ?></td>
          <?php endforeach; ?>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<!-- WAEC key -->
<div class="flex flex-wrap gap-2 mt-4" style="font-size:11px;">
  <?php foreach (WAEC_SCALE as $lvl => $g): ?>
  <span class="badge <?= GradingEngine::levelColourClass($lvl, 'waec') ?>"><?= $lvl ?> · <?= htmlspecialchars($g['label']) ?> (<?= $g['range'] ?>)</span>
  <?php endforeach; ?>
</div>
<p class="text-muted mt-2" style="font-size:11px;">Aggregate = grades of the 4 core subjects (English Language, Mathematics, Integrated Science, Social Studies) + best 2 other subjects.</p>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
