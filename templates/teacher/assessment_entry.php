<?php
/**
 * Speed Test / Mock Exam Score Entry
 * HCI/UX: pick assessment → pick class/subject → single-column grid with live WAEC grade
 */

$pageTitle = 'Speed Tests & Mocks';
include __DIR__ . '/../layout/header.php';

global $activeTerm, $assessmentList, $selectedAssessment, $assignedBundles, $classSub, $studentList, $scoreData;
$base  = defined('APP_BASE') ? APP_BASE : '';
$types = AssessmentController::TYPES;
$a     = $selectedAssessment;
$maxLabel = $a ? (float)$a['max_score'] : '';
?>

<?php if (!$a): ?>
<!-- ── Empty State ──────────────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center py-12 text-center" style="border-style:dashed;">
  <h3 style="font-weight:700; color:var(--clr-text);">No speed tests or mocks this term</h3>
  <p class="text-muted text-sm">The admin needs to create one before scores can be entered.</p>
</div>

<?php elseif (isset($classSub)): ?>
<!-- ── Grid Header ──────────────────────────────────────────── -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 animate-fade-in">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="<?= $base ?>/teacher/assessments?assessment=<?= $a['id'] ?>" class="btn btn-ghost btn-xs" style="padding:4px; margin-left:-8px;" title="Back">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">
        <?= htmlspecialchars($classSub['subject_name']) ?>
      </h1>
    </div>
    <p class="text-muted m-0" style="font-size:var(--text-sm);">
      <strong><?= htmlspecialchars($a['title']) ?></strong> (<?= $types[$a['assessment_type']] ?>, out of <?= $maxLabel ?>)
      · Class: <strong><?= htmlspecialchars($classSub['class_name'] . ' ' . ($classSub['section'] ?? '')) ?></strong>
    </p>
  </div>
  <div class="flex items-center gap-3">
    <div id="save-indicator" style="font-size:12px; font-weight:700;"></div>
    <a href="<?= $base ?>/assessments/results?assessment=<?= $a['id'] ?>&class=<?= $classSub['class_id'] ?>" class="btn btn-outline" style="border-radius:var(--radius-full);">View Class Results</a>
  </div>
</div>

<?php if ($a['is_locked']): ?>
<div class="alert alert-warning mb-4">This assessment is locked by the admin. Scores are read-only.</div>
<?php endif; ?>

<div class="card" style="padding:0; overflow:hidden; border:1px solid var(--clr-border);">
  <div style="overflow-x:auto; max-height:calc(100vh - 280px); overflow-y:auto;">
    <table class="table-sba" style="width:100%; border-collapse:collapse; min-width:620px;">
      <thead style="position:sticky; top:0; z-index:10; background:var(--clr-surface-2); box-shadow:0 1px 0 var(--clr-border);">
        <tr>
          <th style="width:50px; text-align:center; padding:1rem;">#</th>
          <th style="text-align:left; padding:1rem;">Student Name</th>
          <th style="width:130px; text-align:center; padding:1rem;">Score (<?= $maxLabel ?>)</th>
          <th style="width:90px; text-align:center; padding:1rem;">%</th>
          <th style="width:90px; text-align:center; padding:1rem; background:var(--clr-primary); color:white;">Grade</th>
          <th style="width:150px; text-align:left; padding:1rem;">Remark</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($studentList as $i => $s):
            $sc = $scoreData[$s['id']] ?? null;
        ?>
        <tr data-student-id="<?= $s['id'] ?>" class="hover:bg-slate-50 transition-colors">
          <td style="text-align:center; color:var(--clr-text-muted); font-size:12px;"><?= $i + 1 ?></td>
          <td style="padding:0.75rem 1rem;">
            <div style="font-weight:700; color:var(--clr-text); font-size:14px;"><?= htmlspecialchars($s['full_name']) ?></div>
            <div style="font-size:10px; color:var(--clr-text-muted); font-weight:600;"><?= htmlspecialchars($s['student_id_number']) ?></div>
          </td>
          <td style="padding:0;">
            <input type="number" step="0.5" min="0" max="<?= $maxLabel ?>" class="score-input assessment-input"
                   value="<?= $sc && $sc['raw_score'] !== null ? (float)$sc['raw_score'] : '' ?>"
                   data-saved="<?= $sc && $sc['raw_score'] !== null ? (float)$sc['raw_score'] : '' ?>"
                   aria-label="Score for <?= htmlspecialchars($s['full_name']) ?>"
                   <?= $a['is_locked'] ? 'disabled' : '' ?> onfocus="this.select()">
          </td>
          <td style="text-align:center; font-weight:700;"><span class="pct"><?= $sc ? (int)$sc['percentage'] : '—' ?></span></td>
          <td style="text-align:center;">
            <span class="grade badge <?= $sc ? GradingEngine::levelColourClass((int)$sc['grade'], 'waec') : 'badge-gray' ?>" style="font-size:13px; min-width:32px; justify-content:center;"><?= $sc ? (int)$sc['grade'] : '—' ?></span>
          </td>
          <td style="font-size:11px; font-weight:600; color:var(--clr-text-muted);"><span class="remark"><?= $sc ? GradingEngine::getProficiencyLabel((int)$sc['grade'], 'waec') : '' ?></span></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (empty($studentList)): ?>
<p class="text-muted text-sm mt-4">No active students in this class.</p>
<?php endif; ?>

<script>
(function () {
  const CONFIG = {
    base: '<?= $base ?>',
    csrf: '<?= CSRF::token() ?>',
    assessmentId: <?= (int)$a['id'] ?>,
    csId: <?= (int)$classSub['id'] ?>,
    max: <?= (float)$a['max_score'] ?>,
    scale: <?= json_encode(WAEC_SCALE) ?>,
  };
  const indicator = document.getElementById('save-indicator');
  const colourClass = g => g <= 3 ? 'level-hp' : g <= 6 ? 'level-p' : g <= 8 ? 'level-ap' : 'level-e';

  function gradeFor(pct) {
    for (const [lvl, r] of Object.entries(CONFIG.scale)) {
      if (pct >= r.min && pct <= r.max) return { grade: +lvl, label: r.label };
    }
    return { grade: 9, label: CONFIG.scale[9].label };
  }

  function paint(row, pct, grade, label) {
    const g = row.querySelector('.grade');
    row.querySelector('.pct').textContent = pct === null ? '—' : pct;
    row.querySelector('.remark').textContent = label || '';
    g.textContent = grade === null ? '—' : grade;
    g.className = 'grade badge ' + (grade === null ? 'badge-gray' : colourClass(grade));
  }

  function setIndicator(text, colour) {
    indicator.textContent = text;
    indicator.style.color = colour;
  }

  async function save(input) {
    const row = input.closest('tr');
    const raw = input.value.trim();
    if (raw === input.dataset.saved) return;

    const val = raw === '' ? null : parseFloat(raw);
    if (val !== null && (isNaN(val) || val < 0 || val > CONFIG.max)) {
      input.classList.add('is-invalid');
      Toast.show(`Score must be between 0 and ${CONFIG.max}`, 'error');
      return;
    }
    input.classList.remove('is-invalid');

    // Optimistic live grade
    if (val === null) paint(row, null, null, '');
    else { const pct = Math.round(val / CONFIG.max * 100); const g = gradeFor(pct); paint(row, pct, g.grade, g.label); }

    setIndicator('Saving…', 'var(--clr-text-muted)');
    const fd = new FormData();
    fd.append('_csrf_token', CONFIG.csrf);
    fd.append('assessment_id', CONFIG.assessmentId);
    fd.append('class_subject_id', CONFIG.csId);
    fd.append('student_id', row.dataset.studentId);
    fd.append('value', raw);

    try {
      const res  = await fetch(`${CONFIG.base}/teacher/assessments`, { method: 'POST', body: fd });
      const data = await res.json();
      if (!data.success) throw new Error(data.message || 'Save failed');
      input.dataset.saved = raw;
      paint(row, data.percentage, data.grade, data.label);
      setIndicator('✓ All changes saved', 'var(--clr-success)');
    } catch (e) {
      input.classList.add('is-invalid');
      setIndicator('Not saved', 'var(--clr-danger)');
      Toast.show(e.message || 'Could not save score', 'error');
    }
  }

  const inputs = Array.from(document.querySelectorAll('.assessment-input'));
  inputs.forEach((input, idx) => {
    input.addEventListener('change', () => save(input));
    input.addEventListener('keydown', e => {
      // Enter / arrows move down the column for fast entry
      if (e.key === 'Enter' || e.key === 'ArrowDown') { e.preventDefault(); inputs[idx + 1]?.focus(); }
      if (e.key === 'ArrowUp') { e.preventDefault(); inputs[idx - 1]?.focus(); }
    });
  });
})();
</script>

<?php else: ?>
<!-- ── Selection View ───────────────────────────────────────── -->
<div class="flex justify-between items-center mb-6 gap-4 flex-wrap animate-fade-in">
  <div>
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Speed Tests &amp; Mocks</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm);">Choose an assessment, then a subject to record scores. Grades use the WAEC 1–9 scale.</p>
  </div>
  <div style="font-size:13px; font-weight:700; color:var(--clr-primary); background:var(--clr-primary-50); padding:0.5rem 1rem; border-radius:var(--radius-full);">
    <?= htmlspecialchars(($activeTerm['year_name'] ?? '') . ' · ' . ($activeTerm['name'] ?? '')) ?>
  </div>
</div>

<!-- Assessment picker -->
<div class="flex flex-wrap gap-2 mb-6" role="tablist" aria-label="Assessments">
  <?php foreach ($assessmentList as $item): $isSel = $item['id'] == $a['id']; ?>
  <a href="<?= $base ?>/teacher/assessments?assessment=<?= $item['id'] ?>" role="tab" aria-selected="<?= $isSel ? 'true' : 'false' ?>"
     class="btn btn-sm <?= $isSel ? 'btn-primary' : 'btn-outline' ?>" style="border-radius:var(--radius-full);">
    <?= htmlspecialchars($item['title']) ?>
    <span style="opacity:.7; font-weight:600; margin-left:4px;"><?= $item['assessment_type'] === 'mock' ? 'Mock' : 'Speed' ?></span>
    <?php if ($item['is_locked']): ?>🔒<?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>

<?php if (empty($assignedBundles)): ?>
  <div class="card flex flex-col items-center justify-center py-12 text-center" style="border-style:dashed;">
    <h3 style="font-weight:700; color:var(--clr-text);">No WAEC-graded subjects assigned</h3>
    <p class="text-muted text-sm">Speed tests and mocks are recorded for WAEC-graded classes (e.g. B9) only.</p>
  </div>
<?php else: ?>
  <div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:1.5rem;">
    <?php foreach ($assignedBundles as $b):
        $total = (int)$b['student_count'];
        $done  = (int)$b['entered_count'];
        $pct   = $total > 0 ? min(100, round($done / $total * 100)) : 0;
    ?>
    <div class="card hover-lift" style="padding:0; overflow:hidden; border:1px solid var(--clr-border); display:flex; flex-direction:column;">
      <div style="padding:1.25rem 1.5rem; background:var(--clr-surface-2); border-bottom:1px solid var(--clr-border);">
        <h3 style="margin:0; font-weight:800; font-size:1.125rem; color:var(--clr-text);">
          <?= htmlspecialchars($b['class_name']) ?><?= $b['section'] ? ' (' . htmlspecialchars($b['section']) . ')' : '' ?>
        </h3>
        <p class="text-muted" style="font-size:13px; margin:0.25rem 0 0;"><?= htmlspecialchars($b['subject_name']) ?>
          <?php if (Session::role() === 'admin' && $b['teacher_name']): ?> · <?= htmlspecialchars($b['teacher_name']) ?><?php endif; ?>
        </p>
      </div>
      <div style="padding:1.25rem 1.5rem; flex:1;">
        <div class="flex justify-between items-center mb-2 text-xs font-bold">
          <span><?= $done ?> / <?= $total ?> scores</span>
          <span style="color:<?= $pct >= 100 ? 'var(--clr-success)' : 'var(--clr-text)' ?>;"><?= $pct ?>%</span>
        </div>
        <div style="height:6px; background:var(--clr-border); border-radius:10px; overflow:hidden;">
          <div style="height:100%; width:<?= $pct ?>%; background:var(--clr-primary); border-radius:10px;"></div>
        </div>
      </div>
      <div style="padding:1rem 1.5rem; border-top:1px solid var(--clr-border); background:var(--clr-surface-2); display:flex; gap:8px;">
        <a href="<?= $base ?>/teacher/assessments?assessment=<?= $a['id'] ?>&cs=<?= $b['class_subject_id'] ?>"
           class="btn <?= $pct >= 100 || $a['is_locked'] ? 'btn-outline' : 'btn-primary' ?> btn-sm" style="flex:1; justify-content:center;">
          <?= $a['is_locked'] ? 'View Scores' : ($pct >= 100 ? 'View / Edit Scores' : 'Enter Scores') ?>
        </a>
        <a href="<?= $base ?>/assessments/results?assessment=<?= $a['id'] ?>&class=<?= $b['class_id'] ?>" class="btn btn-ghost btn-sm" title="Class results">Results</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
