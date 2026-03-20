<?php
/**
 * Student Promotion View
 * HCI/UX: Two-mode promotion — automated (score threshold) + manual override per student.
 */
$pageTitle = 'Student Promotions';
include __DIR__ . '/../layout/header.php';

global $pageYear, $yearsList, $classesSummary, $nextYearsList;
$base        = defined('APP_BASE') ? APP_BASE : '';
$years       = $yearsList       ?? [];
$classes     = $classesSummary  ?? [];
$nextYears   = $nextYearsList   ?? [];
$currentYear = $pageYear        ?? null;
?>

<!-- ── Page Header ──────────────────────────────────────────────── -->
<div class="flex justify-between items-center mb-6 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Student Promotions</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:640px;">
      Advance students to the next academic year. Run automated promotions based on aggregate score thresholds or manually override any student's status.
    </p>
  </div>
  <?php if ($currentYear): ?>
  <div class="badge badge-primary" style="padding:8px 18px; border-radius:var(--radius-full); font-weight:800; font-size:12px;">
    Reviewing: <?= htmlspecialchars($currentYear['year_name']) ?>
  </div>
  <?php endif; ?>
</div>

<!-- ── Year Filter Tabs ─────────────────────────────────────────── -->
<?php if (!empty($years)): ?>
<div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:2rem;">
  <?php foreach ($years as $y): ?>
  <a href="<?= $base ?>/admin/promotions?year_id=<?= $y['id'] ?>"
     class="btn btn-sm <?= ($currentYear && $currentYear['id'] == $y['id']) ? 'btn-primary' : 'btn-ghost' ?>"
     style="font-weight:700; font-size:12px;">
    <?= htmlspecialchars($y['year_name']) ?>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!$currentYear): ?>
<!-- ── Empty: No Academic Year ─────────────────────────────── -->
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed;">
  <div style="background:var(--clr-surface-2); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary-300)"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text); margin-bottom:.5rem;">No Academic Year Selected</h2>
  <p class="text-muted" style="max-width:320px; margin:0 auto 2rem;">Set up at least one academic year to manage student promotions.</p>
  <a href="<?= $base ?>/admin/years" class="btn btn-primary">Manage Academic Years</a>
</div>

<?php elseif (empty($classes)): ?>
<!-- ── Empty: No Classes ─────────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed; background:var(--clr-surface-2);">
  <h2 style="font-weight:800; color:var(--clr-text);">No classes found for this year</h2>
  <p class="text-muted" style="margin:0 auto 2rem;">There are no class records associated with this academic session.</p>
  <a href="<?= $base ?>/admin/classes" class="btn btn-outline">Manage Classes</a>
</div>

<?php else: ?>
<!-- ── How-it-Works Info Banner ──────────────────────────────── -->
<div class="alert-info" style="margin-bottom:2rem; border-radius:var(--radius-lg); padding:1rem 1.25rem; display:flex; gap:1rem; align-items:flex-start;">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20" style="flex-shrink:0; margin-top:2px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <div style="font-size:var(--text-sm);">
    <strong>Two modes available:</strong>
    Auto-Promote calculates each student's score average and promotes based on your threshold.
    Manual Override lets you individually set any student as Promoted or Held Back, regardless of scores.
    Manual overrides always take priority.
  </div>
</div>

<!-- ── Class Cards Grid ──────────────────────────────────────── -->
<div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(360px, 1fr)); gap:1.5rem;">
  <?php foreach ($classes as $c):
    $total    = (int)$c['total_students'];
    $promoted = (int)$c['promoted_count'];
    $repeated = (int)$c['repeated_count'];
    $pending  = (int)$c['pending_count'];
    $doneAll  = ($total > 0 && $pending === 0);
    $levelColors = ['LP' => '#16a34a', 'UP' => '#d97706', 'JHS' => '#7c3aed'];
    $levelColor  = $levelColors[$c['level_code']] ?? '#6366f1';
  ?>
  <div class="card hover-lift" style="padding:0; overflow:hidden; border:1px solid var(--clr-border); display:flex; flex-direction:column;">

    <!-- Card Header -->
    <div style="padding:1.25rem 1.5rem; background:var(--clr-surface-2); border-bottom:1px solid var(--clr-border); display:flex; justify-content:space-between; align-items:center;">
      <div>
        <div style="font-size:10px; font-weight:800; color:<?= $levelColor ?>; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.25rem;">
          <?= htmlspecialchars($c['level_name']) ?>
        </div>
        <h3 style="margin:0; font-weight:800; font-size:1.1rem; color:var(--clr-text);">
          <?= htmlspecialchars($c['class_name']) ?><?= $c['section'] ? " ({$c['section']})" : '' ?>
        </h3>
      </div>
      <?php if ($doneAll): ?>
        <span class="badge badge-success" style="font-size:10px; padding:4px 10px;">DONE</span>
      <?php elseif ($total === 0): ?>
        <span class="badge" style="font-size:10px; padding:4px 10px; background:var(--clr-surface-2); color:var(--clr-text-muted);">EMPTY</span>
      <?php else: ?>
        <span class="badge badge-warning" style="font-size:10px; padding:4px 10px; background:#fff2e0; color:#c05621;"><?= $pending ?> PENDING</span>
      <?php endif; ?>
    </div>

    <!-- Stats -->
    <div class="grid" style="grid-template-columns:repeat(3, 1fr); border-bottom:1px solid var(--clr-border);">
      <div style="padding:1rem 0; text-align:center; border-right:1px solid var(--clr-border);">
        <div style="font-size:1.375rem; font-weight:800; color:var(--clr-text);"><?= $total ?></div>
        <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase;">Total</div>
      </div>
      <div style="padding:1rem 0; text-align:center; border-right:1px solid var(--clr-border); background:rgba(22,163,74,0.04);">
        <div style="font-size:1.375rem; font-weight:800; color:var(--clr-success);"><?= $promoted ?></div>
        <div style="font-size:10px; font-weight:700; color:var(--clr-success); text-transform:uppercase; opacity:0.85;">Promoted</div>
      </div>
      <div style="padding:1rem 0; text-align:center; background:rgba(239,68,68,0.04);">
        <div style="font-size:1.375rem; font-weight:800; color:var(--clr-danger);"><?= $repeated ?></div>
        <div style="font-size:10px; font-weight:700; color:var(--clr-danger); text-transform:uppercase; opacity:0.85;">Held Back</div>
      </div>
    </div>

    <!-- Actions -->
    <div style="padding:1rem 1.5rem; margin-top:auto; display:flex; gap:0.5rem; flex-wrap:wrap;">
      <?php if ($total > 0): ?>
        <button class="btn btn-primary btn-sm" style="flex:1; justify-content:center; font-size:12px;"
                onclick="openAutoModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>')">
          ⚡ Auto-Promote
        </button>
        <button class="btn btn-outline btn-sm" style="flex:1; justify-content:center; font-size:12px; color:var(--clr-text);"
                onclick="openManualModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>')">
          ✏️ Manual Override
        </button>
      <?php else: ?>
        <p class="text-muted m-0" style="font-size:var(--text-xs); padding:0.25rem 0;">No students enrolled.</p>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- ════════════════════════════════════════════════════════
     AUTO-PROMOTE MODAL
══════════════════════════════════════════════════════════ -->
<div id="modal-auto-promote" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
  <div class="modal w-full max-w-md mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="auto-modal-title">Auto-Promote Class</h3>
      <button class="modal-close" onclick="closeModal('modal-auto-promote')" aria-label="Close">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/promotions" onsubmit="Loader.show()">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="auto_promote">
      <input type="hidden" name="class_id" id="auto-class-id">
      <input type="hidden" name="year_id" value="<?= $currentYear['id'] ?? '' ?>">
      <div class="modal-body" style="display:flex; flex-direction:column; gap:1.25rem;">

        <div class="alert-info" style="border-radius:var(--radius-md); font-size:var(--text-sm);">
          The system will calculate each student's average score. Students meeting or exceeding the threshold are promoted; others are held back.
        </div>

        <div class="form-group">
          <label class="form-label">Promote to Academic Year <span class="required">*</span></label>
          <select name="next_year_id" class="form-control" required>
            <option value="">— Select Target Year —</option>
            <?php foreach ($nextYears as $ny): ?>
            <option value="<?= $ny['id'] ?>"><?= htmlspecialchars($ny['year_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($nextYears)): ?>
          <p class="form-text text-warning" style="margin-top:0.4rem;">⚠ No other academic years exist. <a href="<?= $base ?>/admin/years">Create one first.</a></p>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label class="form-label">Next Class Name (Optional)</label>
          <input type="text" name="next_class_name" class="form-control" placeholder="e.g. B2, B5A — leave blank to assign later" maxlength="20">
          <p class="form-text">Students will keep this as their 'next class' until reassigned.</p>
        </div>

        <div class="form-group">
          <label class="form-label">Promotion Threshold (%)</label>
          <div style="display:flex; align-items:center; gap:1rem;">
            <input type="range" name="threshold" id="threshold-slider" min="0" max="100" value="50" class="form-control" style="flex:1; height:6px; accent-color:var(--clr-primary);" oninput="document.getElementById('threshold-display').textContent = this.value + '%'">
            <span id="threshold-display" style="font-weight:800; font-size:1.125rem; color:var(--clr-primary); min-width:48px;">50%</span>
          </div>
          <p class="form-text">Students averaging <strong>at or above</strong> this score are promoted. Default: 50%.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-auto-promote')">Cancel</button>
        <button type="submit" class="btn btn-primary">Run Auto-Promotion</button>
      </div>
    </form>
  </div>
</div>

<!-- ════════════════════════════════════════════════════════
     MANUAL OVERRIDE MODAL
══════════════════════════════════════════════════════════ -->
<div id="modal-manual" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
  <div class="modal w-full max-w-xl mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="manual-modal-title">Manual Override</h3>
      <button class="modal-close" onclick="closeModal('modal-manual')" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body" style="padding:0;">
      <div id="manual-student-list" style="max-height:480px; overflow-y:auto;">
        <div class="flex items-center justify-center" style="padding:3rem; color:var(--clr-text-muted);">
          Loading students…
        </div>
      </div>
    </div>
    <div class="modal-footer" style="border-top:1px solid var(--clr-border);">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modal-manual')">Close</button>
    </div>
  </div>
</div>


<script>
const BASE   = '<?= $base ?>';
const YEAR_ID = <?= (int)($currentYear['id'] ?? 0) ?>;
const NEXT_YEARS = <?= json_encode($nextYears) ?>;

function openAutoModal(classId, className) {
  document.getElementById('auto-class-id').value = classId;
  document.getElementById('auto-modal-title').textContent = 'Auto-Promote · ' + className;
  // Reset slider
  const slider = document.getElementById('threshold-slider');
  slider.value = 50;
  document.getElementById('threshold-display').textContent = '50%';
  openModal('modal-auto-promote');
}

function openManualModal(classId, className) {
  document.getElementById('manual-modal-title').textContent = 'Manual Override · ' + className;
  openModal('modal-manual');

  // Build next-year options HTML
  const nextYearOpts = NEXT_YEARS.map(y => `<option value="${y.id}">${esc(y.year_name)}</option>`).join('');

  // Fetch students for this class via inline query results
  fetch(`${BASE}/admin/promotions?ajax_students=1&class_id=${classId}&year_id=${YEAR_ID}`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.ok ? r.json() : Promise.reject(r.status))
  .then(data => {
    const el = document.getElementById('manual-student-list');
    if (!data.students || data.students.length === 0) {
      el.innerHTML = '<div style="padding:3rem; text-align:center; color:var(--clr-text-muted);">No students found in this class.</div>';
      return;
    }
    el.innerHTML = data.students.map(s => {
      const statusColor = s.promotion_status === 'promoted' ? 'var(--clr-success)'
                        : s.promotion_status === 'repeated' ? 'var(--clr-danger)' : 'var(--clr-text-muted)';
      const statusLabel = s.promotion_status ? s.promotion_status.charAt(0).toUpperCase() + s.promotion_status.slice(1) : 'Pending';
      return `
      <div style="padding:1rem 1.5rem; border-bottom:1px solid var(--clr-border); display:flex; flex-wrap:wrap; gap:0.75rem; align-items:center;">
        <div style="flex:1; min-width:160px;">
          <div style="font-weight:700; color:var(--clr-text);">${esc(s.full_name)}</div>
          <div style="font-size:var(--text-xs); color:var(--clr-text-muted);">ID: ${esc(s.student_id_number)} · Avg: ${s.avg_score}%</div>
        </div>
        <span style="font-size:11px; font-weight:700; color:${statusColor}; text-transform:uppercase;">${statusLabel}</span>
        <form method="POST" action="${BASE}/admin/promotions" onsubmit="Loader.show()" style="display:flex; gap:0.5rem; align-items:center; flex-wrap:wrap;">
          <input type="hidden" name="_csrf_token" value="${document.querySelector('input[name=_csrf_token]').value}">
          <input type="hidden" name="_action" value="manual_promote">
          <input type="hidden" name="student_id" value="${s.id}">
          <input type="hidden" name="year_id" value="${YEAR_ID}">
          <select name="next_year_id" class="form-control" style="padding:0.3rem 0.5rem; font-size:12px; height:32px; width:130px;">
            <option value="">Target Year</option>
            ${nextYearOpts}
          </select>
          <select name="promo_status" class="form-control" style="padding:0.3rem 0.5rem; font-size:12px; height:32px; width:120px;">
            <option value="promoted" ${s.promotion_status==='promoted'?'selected':''}>✓ Promote</option>
            <option value="repeated" ${s.promotion_status==='repeated'?'selected':''}>✗ Hold Back</option>
          </select>
          <button type="submit" class="btn btn-xs btn-primary" style="height:32px; font-size:11px;">Save</button>
        </form>
      </div>`;
    }).join('');
  })
  .catch(() => {
    document.getElementById('manual-student-list').innerHTML =
      '<div style="padding:3rem; text-align:center; color:var(--clr-danger);">Failed to load students. Please reload.</div>';
  });
}

function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s || '');
  return d.innerHTML;
}
</script>
