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
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Promote Students to Next Year</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:640px;">
      Move students to their new class for the upcoming academic year. You can automatically promote passing students based on a pass mark or update any student individually.
    </p>
  </div>
  <?php if ($currentYear): ?>
  <div class="badge badge-primary" style="padding:8px 18px; border-radius:var(--radius-full); font-weight:800; font-size:12px;">
    Current Year: <?= htmlspecialchars($currentYear['year_name']) ?>
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
  <?php if (Session::get('role') === 'admin'): ?>
  <a href="<?= $base ?>/admin/years" class="btn btn-primary">Manage Academic Years</a>
  <?php endif; ?>
</div>

<?php elseif (empty($classes)): ?>
<!-- ── Empty: No Classes ─────────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed; background:var(--clr-surface-2);">
  <h2 style="font-weight:800; color:var(--clr-text);">No classes found for this year</h2>
  <p class="text-muted" style="margin:0 auto 2rem;">There are no class records associated with this academic session.</p>
  <?php if (Session::get('role') === 'admin'): ?>
  <a href="<?= $base ?>/admin/classes" class="btn btn-outline">Manage Classes</a>
  <?php endif; ?>
</div>

<?php else: ?>
<!-- ── How-it-Works Info Banner ──────────────────────────────── -->
<div class="alert-info" style="margin-bottom:2rem; border-radius:var(--radius-lg); padding:1rem 1.25rem; display:flex; gap:1rem; align-items:flex-start;">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20" style="flex-shrink:0; margin-top:2px;"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <div style="font-size:var(--text-sm);">
    <strong>💡 Two ways to move students:</strong><br>
    <strong>1. Auto-Promote:</strong> Automatically passes students who score at or above your chosen pass mark (e.g. 50%).<br>
    <strong>2. Manual Override:</strong> Lets you change any individual student's result to Pass or Repeat.
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
        <div style="font-size:10px; font-weight:700; color:var(--clr-danger); text-transform:uppercase; opacity:0.85;">Repeating</div>
      </div>
    </div>

    <!-- Actions -->
    <div style="padding:1rem 1.5rem; margin-top:auto; display:flex; gap:0.5rem; flex-wrap:wrap;">
      <?php if ($total > 0): ?>
        <?php if ($pending > 0): ?>
          <button class="btn btn-primary btn-sm" style="flex:1; justify-content:center; font-size:12px;"
                  onclick="openAutoModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>')">
            ⚡ Auto-Promote
          </button>
          <button class="btn btn-outline btn-sm" style="flex:1; justify-content:center; font-size:12px; color:var(--clr-text);"
                  onclick="openManualModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>')">
            ✏️ Manual Override
          </button>
        <?php else: ?>
          <form method="POST" action="<?= $base ?>/admin/promotions" onsubmit="return confirmUnpromote(event, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>')" style="flex:1; display:flex;">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="unpromote_class">
            <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
            <input type="hidden" name="year_id" value="<?= $currentYear['id'] ?>">
            <button type="submit" class="btn btn-ghost btn-sm text-danger" style="flex:1; justify-content:center; font-size:12px; font-weight:700; border:1px solid rgba(239,68,68,0.2);">
              🔄 Reset Class
            </button>
          </form>
          <button class="btn btn-outline btn-sm" style="flex:1; justify-content:center; font-size:12px; color:var(--clr-text);"
                  onclick="openManualModal(<?= $c['id'] ?>, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>')">
            ✏️ Edit Status
          </button>
        <?php endif; ?>
      <?php else: ?>
        <p class="text-muted m-0" style="font-size:var(--text-xs); padding:0.25rem 0;">No students enrolled.</p>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<?php
// ── Build class hierarchy data for JS ─────────────────────────────────
global $targetYearClasses;
$classHierarchy = [
    'BASIC 1' => 'BASIC 2', 'BASIC 2' => 'BASIC 3', 'BASIC 3' => 'BASIC 4',
    'BASIC 4' => 'BASIC 5', 'BASIC 5' => 'BASIC 6', 'BASIC 6' => 'BASIC 7',
    'BASIC 7' => 'BASIC 8', 'BASIC 8' => 'BASIC 9', 'BASIC 9' => null,
];
?>

<!-- ════════════════════════════════════════════════════════
     AUTO-PROMOTE MODAL (Redesigned & Simplified)
══════════════════════════════════════════════════════════ -->
<div id="modal-auto-promote" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
  <div class="modal w-full max-w-md mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="auto-modal-title">Promote Class</h3>
      <button class="modal-close" onclick="closeModal('modal-auto-promote')" aria-label="Close">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/promotions" onsubmit="Loader.show()" style="display:flex; flex-direction:column; flex:1; min-height:0;">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="auto_promote">
      <input type="hidden" name="class_id" id="auto-class-id">
      <input type="hidden" name="year_id" value="<?= $currentYear['id'] ?? '' ?>">
      <div class="modal-body" style="display:flex; flex-direction:column; gap:1.25rem;">

        <!-- ── Visual Progression Preview ── -->
        <div id="auto-progression-preview" style="display:flex; align-items:center; gap:0.75rem; padding:1rem 1.25rem; background:linear-gradient(135deg, var(--clr-surface-2), rgba(99,102,241,0.06)); border-radius:var(--radius-lg); border:1px solid var(--clr-border);">
          <div style="text-align:center; flex:1;">
            <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.06em;">Current Class</div>
            <div id="auto-preview-from" style="font-weight:800; font-size:1rem; color:var(--clr-text); margin-top:2px;">BASIC 1</div>
            <div id="auto-preview-year-from" style="font-size:11px; color:var(--clr-text-muted);"><?= htmlspecialchars($currentYear['year_name'] ?? '') ?></div>
          </div>
          <div id="auto-preview-arrow" style="font-size:1.5rem; color:var(--clr-primary); flex-shrink:0;">→</div>
          <div style="text-align:center; flex:1;">
            <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.06em;">New Class</div>
            <div id="auto-preview-to" style="font-weight:800; font-size:1rem; color:var(--clr-success); margin-top:2px;">BASIC 2</div>
            <div id="auto-preview-year-to" style="font-size:11px; color:var(--clr-text-muted);">—</div>
          </div>
        </div>

        <!-- ── Graduation Banner (hidden by default) ── -->
        <div id="auto-graduation-banner" style="display:none; padding:1rem 1.25rem; background:linear-gradient(135deg, #fef3c7, #fde68a); border-radius:var(--radius-lg); border:1px solid #f59e0b; text-align:center;">
          <div style="font-size:1.5rem; margin-bottom:0.25rem;">🎓</div>
          <div style="font-weight:800; font-size:14px; color:#92400e;">Graduation / School Exit</div>
          <div style="font-size:12px; color:#92400e; opacity:0.8; margin-top:0.25rem;">BASIC 9 students complete junior high school. Passing students graduate; others repeat.</div>
        </div>

        <!-- ── Target Year ── -->
        <div class="form-group">
          <label class="form-label">New Academic Year <span class="required">*</span></label>
          <select name="next_year_id" id="auto-target-year" class="form-control" required onchange="onTargetYearChange()">
            <option value="">— Select New Academic Year —</option>
            <?php foreach ($nextYears as $ny): ?>
            <option value="<?= $ny['id'] ?>"><?= htmlspecialchars($ny['year_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (empty($nextYears)): ?>
          <p class="form-text text-warning" style="margin-top:0.4rem;">⚠ No other academic years exist. <a href="<?= $base ?>/admin/years">Create one first.</a></p>
          <?php endif; ?>
        </div>

        <!-- ── Target Class (Smart Dropdown) ── -->
        <div class="form-group" id="auto-next-class-group">
          <label class="form-label">New Class for Promoted Students <span class="required">*</span></label>
          <select name="target_class_id" id="auto-next-class" class="form-control" required>
            <option value="">— Select new academic year first —</option>
          </select>
          <p class="form-text" id="auto-next-class-hint" style="margin-top:0.4rem;">
            Select the new academic year above to see available classes.
          </p>
        </div>

        <!-- ── Pass Mark / Threshold ── -->
        <div class="form-group">
          <label class="form-label">Required Pass Mark (%)</label>
          <!-- Quick Presets -->
          <div style="display:flex; gap:0.5rem; margin-bottom:0.75rem; flex-wrap:wrap;">
            <button type="button" class="btn btn-xs" id="preset-50"
                    style="font-size:11px; font-weight:700; padding:4px 14px; border-radius:var(--radius-full); background:var(--clr-primary); color:white; border:none;"
                    onclick="setThreshold(50)">50% Pass Mark</button>
            <button type="button" class="btn btn-xs" id="preset-60"
                    style="font-size:11px; font-weight:700; padding:4px 14px; border-radius:var(--radius-full); background:var(--clr-surface-2); color:var(--clr-text); border:1px solid var(--clr-border);"
                    onclick="setThreshold(60)">60% High Pass</button>
            <button type="button" class="btn btn-xs" id="preset-0"
                    style="font-size:11px; font-weight:700; padding:4px 14px; border-radius:var(--radius-full); background:var(--clr-surface-2); color:var(--clr-text); border:1px solid var(--clr-border);"
                    onclick="setThreshold(0)">0% Pass Everyone</button>
          </div>
          <div style="display:flex; align-items:center; gap:1rem;">
            <input type="range" name="threshold" id="threshold-slider" min="0" max="100" value="50" class="form-control"
                   style="flex:1; height:6px; accent-color:var(--clr-primary);"
                   oninput="updateThresholdUI(this.value)">
            <span id="threshold-display" style="font-weight:800; font-size:1.125rem; color:var(--clr-primary); min-width:48px;">50%</span>
          </div>
          <p class="form-text">Students scoring this mark or higher will pass to the new class.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-auto-promote')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="auto-submit-btn">Promote Passing Students</button>
      </div>
    </form>
  </div>
</div>

<!-- ════════════════════════════════════════════════════════
     MANUAL OVERRIDE MODAL (Redesigned & Simplified)
══════════════════════════════════════════════════════════ -->
<div id="modal-manual" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
  <div class="modal w-full max-w-xl mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="manual-modal-title">Change Student Results</h3>
      <button class="modal-close" onclick="closeModal('modal-manual')" aria-label="Close">&times;</button>
    </div>
    <div class="modal-body" style="padding:0;">
      <!-- Manual modal header with target year selector -->
      <div id="manual-header-bar" style="padding:0.75rem 1.5rem; background:var(--clr-surface-2); border-bottom:1px solid var(--clr-border); display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <label style="font-size:12px; font-weight:700; color:var(--clr-text-muted); white-space:nowrap;">New Academic Year:</label>
        <select id="manual-target-year" class="form-control" style="padding:0.3rem 0.5rem; font-size:12px; height:32px; width:140px;" onchange="onManualTargetYearChange()">
          <option value="">— Select —</option>
          <?php foreach ($nextYears as $ny): ?>
          <option value="<?= $ny['id'] ?>"><?= htmlspecialchars($ny['year_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <span id="manual-student-count" style="font-size:11px; color:var(--clr-text-muted); margin-left:auto;"></span>
      </div>
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
const TARGET_YEAR_CLASSES = <?= json_encode($targetYearClasses ?? []) ?>;
const CLASS_HIERARCHY = <?= json_encode($classHierarchy) ?>;

// ── State ──────────────────────────────────────────────────
let currentAutoClassName = '';
let currentAutoSection = '';
let currentManualClassId = 0;
let currentManualClassName = '';

// ── Helpers ────────────────────────────────────────────────
function esc(s) {
  const d = document.createElement('div');
  d.textContent = String(s || '');
  return d.innerHTML;
}

function getExpectedNextClass(className) {
  return CLASS_HIERARCHY[className] || null;
}

function isTerminalClass(className) {
  return className === 'BASIC 9';
}

// ── Threshold presets ──────────────────────────────────────
function setThreshold(val) {
  const slider = document.getElementById('threshold-slider');
  slider.value = val;
  updateThresholdUI(val);
}

function updateThresholdUI(val) {
  document.getElementById('threshold-display').textContent = val + '%';
  // Highlight active preset button
  [0, 50, 60].forEach(p => {
    const btn = document.getElementById('preset-' + p);
    if (!btn) return;
    if (parseInt(val) === p) {
      btn.style.background = 'var(--clr-primary)';
      btn.style.color = 'white';
      btn.style.border = 'none';
    } else {
      btn.style.background = 'var(--clr-surface-2)';
      btn.style.color = 'var(--clr-text)';
      btn.style.border = '1px solid var(--clr-border)';
    }
  });
}

// ── Auto-Promote Modal ────────────────────────────────────
function openAutoModal(classId, className) {
  currentAutoClassName = className;
  // Parse section from class data
  const classData = <?= json_encode(array_map(function($c) {
    return ['id' => $c['id'], 'class_name' => $c['class_name'], 'section' => $c['section'] ?? ''];
  }, $classes ?? [])) ?>;
  const matched = classData.find(c => c.id === classId);
  currentAutoSection = matched ? matched.section : '';

  document.getElementById('auto-class-id').value = classId;
  document.getElementById('auto-modal-title').textContent = 'Promote Class · ' + className + (currentAutoSection ? ' (' + currentAutoSection + ')' : '');

  // Update progression preview
  const expectedNext = getExpectedNextClass(className);
  const isGraduation = isTerminalClass(className);

  document.getElementById('auto-preview-from').textContent = className + (currentAutoSection ? ' (' + currentAutoSection + ')' : '');
  document.getElementById('auto-progression-preview').style.display = isGraduation ? 'none' : 'flex';
  document.getElementById('auto-graduation-banner').style.display = isGraduation ? 'block' : 'none';

  if (expectedNext) {
    document.getElementById('auto-preview-to').textContent = expectedNext;
  }

  // Auto-select the next academic year (pick the first one chronologically after current)
  const targetYearSelect = document.getElementById('auto-target-year');
  if (NEXT_YEARS.length === 1) {
    targetYearSelect.value = NEXT_YEARS[0].id;
  } else if (NEXT_YEARS.length > 0) {
    // Pick the closest next year (first in the sorted list)
    targetYearSelect.value = NEXT_YEARS[NEXT_YEARS.length - 1].id;
  }
  onTargetYearChange();

  // Reset threshold
  setThreshold(50);

  // Graduation mode: hide next class group, change submit text
  const nextClassGroup = document.getElementById('auto-next-class-group');
  const submitBtn = document.getElementById('auto-submit-btn');
  if (isGraduation) {
    nextClassGroup.style.display = 'none';
    document.getElementById('auto-next-class').removeAttribute('required');
    submitBtn.textContent = '🎓 Run Graduation Review';
  } else {
    nextClassGroup.style.display = 'block';
    document.getElementById('auto-next-class').setAttribute('required', 'required');
    submitBtn.textContent = 'Promote Passing Students';
  }

  openModal('modal-auto-promote');
}

function onTargetYearChange() {
  const targetYearId = document.getElementById('auto-target-year').value;
  const nextClassSelect = document.getElementById('auto-next-class');
  const hint = document.getElementById('auto-next-class-hint');
  const previewTo = document.getElementById('auto-preview-to');
  const previewYearTo = document.getElementById('auto-preview-year-to');

  // Update target year label in preview
  const selectedYear = NEXT_YEARS.find(y => y.id == targetYearId);
  previewYearTo.textContent = selectedYear ? selectedYear.year_name : '—';

  if (!targetYearId) {
    nextClassSelect.innerHTML = '<option value="">— Select new academic year first —</option>';
    hint.textContent = 'Select the new academic year above to see available classes.';
    return;
  }

  const classes = TARGET_YEAR_CLASSES[targetYearId] || [];
  const expectedNext = getExpectedNextClass(currentAutoClassName);

  if (classes.length === 0) {
    nextClassSelect.innerHTML = '<option value="">No classes in this year</option>';
    hint.innerHTML = '⚠ No classes exist in this year yet. <a href="' + BASE + '/admin/classes">Create classes first</a>, or the system will auto-create them.';
    // Fall back to showing the expected name
    if (expectedNext) {
      nextClassSelect.innerHTML += '<option value="' + esc(expectedNext) + '" selected>✨ ' + esc(expectedNext) + ' (auto-create)</option>';
      previewTo.textContent = expectedNext;
    }
    return;
  }

  // Build dropdown options, marking the recommended one
  let options = '';
  let autoSelected = false;

  // School policy: Special streaming rules for Basic 4 and Basic 8
  if (currentAutoClassName === 'BASIC 4') {
    options += '<option value="split_ab" selected>⚖️ Balanced 50/50 Split (BASIC 5A & 5B) ⭐ (Recommended)</option>';
    autoSelected = true;
  } else if (currentAutoClassName === 'BASIC 8') {
    options += '<option value="stream_merit" selected>🏆 Academic Stream (Top Grades → 9B, Others → 9A) ⭐ (Recommended)</option>';
    autoSelected = true;
  }

  classes.forEach(c => {
    const label = c.class_name + (c.section ? ' (' + c.section + ')' : '');
    const value = c.id;
    // Determine if this is the recommended target
    let isRecommended = false;
    if (!autoSelected && expectedNext && c.class_name === expectedNext) {
      if (currentAutoSection && c.section === currentAutoSection) {
        isRecommended = true;
      } else if (!currentAutoSection && (c.section === 'A' || !c.section)) {
        isRecommended = !autoSelected;
      }
    }

    const recLabel = isRecommended ? '⭐ ' + label + ' (Recommended)' : label;
    const selected = isRecommended && !autoSelected ? ' selected' : '';
    if (isRecommended && !autoSelected) autoSelected = true;

    options += '<option value="' + esc(value) + '"' + selected + '>' + esc(recLabel) + '</option>';
  });

  if (!autoSelected && expectedNext) {
    // Expected class doesn't exist in target year — offer auto-create
    const targetLabel = expectedNext + (currentAutoSection ? ' (' + currentAutoSection + ')' : '');
    options = '<option value="auto:' + esc(expectedNext) + '" selected>✨ ' + esc(targetLabel) + ' (auto-create)</option>' + options;
    autoSelected = true;
  }

  nextClassSelect.innerHTML = '<option value="">— Choose new class —</option>' + options;

  // Update preview & hint
  if (currentAutoClassName === 'BASIC 4') {
    previewTo.textContent = 'BASIC 5A & 5B';
    hint.innerHTML = '<span style="color:var(--clr-success); font-weight:700;">✓ Even Split:</span> Passing students will be divided 50/50 alternately between <strong>BASIC 5A</strong> and <strong>BASIC 5B</strong>.';
  } else if (currentAutoClassName === 'BASIC 8') {
    previewTo.textContent = 'BASIC 9B & 9A';
    hint.innerHTML = '<span style="color:var(--clr-success); font-weight:700;">✓ Merit Streaming:</span> Top-performing students go to <strong>BASIC 9B</strong>; remaining passing students go to <strong>BASIC 9A</strong>.';
  } else if (autoSelected && expectedNext) {
    const targetStreamLabel = expectedNext + (currentAutoSection ? ' (' + currentAutoSection + ')' : '');
    previewTo.textContent = targetStreamLabel;
    hint.innerHTML = '<span style="color:var(--clr-success); font-weight:700;">✓ Direct Stream:</span> Students in <strong>' + esc(currentAutoClassName) + (currentAutoSection ? ' (' + esc(currentAutoSection) + ')' : '') + '</strong> continue into <strong>' + esc(targetStreamLabel) + '</strong>.';
  } else {
    hint.textContent = 'Choose the class where promoted students will be placed.';
  }
}

// ── Unpromote confirmation ─────────────────────────────────
function confirmUnpromote(e, className) {
  e.preventDefault();
  confirmAction({
    title:       'Reset Promotion?',
    message:     `This will revert all students in ${className} to their original state in this academic year. Any target class assignments in the next year will be reset.`,
    confirmText: 'Yes, Reset',
    type:        'danger'
  }, () => { e.target.submit(); Loader.show(); });
  return false;
}

// ── Manual Override Modal ──────────────────────────────────
function openManualModal(classId, className) {
  currentManualClassId = classId;
  currentManualClassName = className;
  document.getElementById('manual-modal-title').textContent = 'Change Student Results · ' + className;

  // Auto-select the target year
  const manualYearSel = document.getElementById('manual-target-year');
  if (NEXT_YEARS.length === 1) {
    manualYearSel.value = NEXT_YEARS[0].id;
  } else if (NEXT_YEARS.length > 0) {
    manualYearSel.value = NEXT_YEARS[NEXT_YEARS.length - 1].id;
  }

  openModal('modal-manual');
  loadManualStudents(classId, className);
}

function onManualTargetYearChange() {
  // Re-render the student list with updated year options
  if (currentManualClassId) {
    loadManualStudents(currentManualClassId, currentManualClassName);
  }
}

function loadManualStudents(classId, className) {
  const targetYearId = document.getElementById('manual-target-year').value;
  const isGraduation = isTerminalClass(className);

  fetch(`${BASE}/admin/promotions?ajax_students=1&class_id=${classId}&year_id=${YEAR_ID}`, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.ok ? r.json() : Promise.reject(r.status))
  .then(data => {
    const el = document.getElementById('manual-student-list');
    const countEl = document.getElementById('manual-student-count');
    const expectedNext = data.expected_next || getExpectedNextClass(className);
    const isTerminal = data.is_terminal || isGraduation;

    if (!data.students || data.students.length === 0) {
      el.innerHTML = '<div style="padding:3rem; text-align:center; color:var(--clr-text-muted);">No students found in this class.</div>';
      countEl.textContent = '';
      return;
    }

    countEl.textContent = data.students.length + ' student' + (data.students.length !== 1 ? 's' : '');

    const classes = targetYearId ? (TARGET_YEAR_CLASSES[targetYearId] || []) : [];

    el.innerHTML = data.students.map(s => {
      const statusColor = s.promotion_status === 'promoted' ? 'var(--clr-success)'
                        : s.promotion_status === 'repeated' ? 'var(--clr-danger)' : 'var(--clr-text-muted)';
      const statusLabel = s.promotion_status === 'promoted' ? (isTerminal ? 'Graduated' : 'Passed')
                        : s.promotion_status === 'repeated' ? 'Repeating' : 'Pending';
      const statusIcon = s.promotion_status === 'promoted' ? '✓' : s.promotion_status === 'repeated' ? '✗' : '○';

      // Score badge colour
      const scoreColor = s.avg_score >= 60 ? 'var(--clr-success)' : s.avg_score >= 50 ? '#d97706' : 'var(--clr-danger)';

      let targetClassWidget = '';
      if (isTerminal) {
        targetClassWidget = `<input type="hidden" name="target_class_id" value=""><span style="font-size:11px; font-weight:700; color:var(--clr-primary); min-width:80px;">🎓 Graduating</span>`;
      } else {
        let opts = '<option value="">— Class —</option>';
        const srcSec = data.source_section || '';
        classes.forEach(c => {
          const label = c.class_name + (c.section ? ' (' + c.section + ')' : '');
          const isAssigned = s.target_class_id && parseInt(s.target_class_id) === parseInt(c.id);
          const isRec = !s.target_class_id && expectedNext && c.class_name === expectedNext && (!srcSec || c.section === srcSec);
          const selected = (isAssigned || isRec) ? ' selected' : '';
          opts += `<option value="${esc(c.id)}"${selected}>${esc(label)}${isRec ? ' ⭐' : ''}</option>`;
        });
        if (expectedNext && !classes.some(c => c.class_name === expectedNext && (!srcSec || c.section === srcSec))) {
          const targetAutoLabel = expectedNext + (srcSec ? ' (' + srcSec + ')' : '');
          opts += `<option value="auto:${esc(expectedNext)}" selected>✨ ${esc(targetAutoLabel)} (auto-create)</option>`;
        }
        targetClassWidget = `<select name="target_class_id" class="form-control" style="padding:0.25rem 0.4rem; font-size:11px; height:30px; width:130px; border-radius:var(--radius-sm);">${opts}</select>`;
      }

      return `
      <div style="padding:0.75rem 1.5rem; border-bottom:1px solid var(--clr-border); display:flex; gap:0.75rem; align-items:center; flex-wrap:wrap;">
        <!-- Student Info -->
        <div style="flex:1; min-width:160px;">
          <div style="font-weight:700; font-size:13px; color:var(--clr-text);">${esc(s.full_name)}</div>
          <div style="font-size:11px; color:var(--clr-text-muted); display:flex; gap:0.5rem; align-items:center; margin-top:2px;">
            <span>ID: ${esc(s.student_id_number)}</span>
            <span style="width:4px; height:4px; border-radius:50%; background:var(--clr-border); display:inline-block;"></span>
            <span style="font-weight:700; color:${scoreColor};">${s.avg_score}% Avg</span>
            ${s.manual_override == 1 ? '<span class="badge" style="font-size:9px; padding:1px 5px; background:rgba(99,102,241,0.1); color:var(--clr-primary);">Override</span>' : ''}
          </div>
        </div>
        <!-- Current Status Badge -->
        <span style="font-size:10px; font-weight:800; color:${statusColor}; text-transform:uppercase; padding:3px 10px; border-radius:var(--radius-full); background:${statusColor}15; white-space:nowrap;">
          ${statusIcon} ${statusLabel}
        </span>
        <!-- Action Form -->
        <form method="POST" action="${BASE}/admin/promotions" onsubmit="Loader.show()" style="display:flex; gap:0.4rem; align-items:center; flex-wrap:wrap;">
          <input type="hidden" name="_csrf_token" value="${document.querySelector('input[name=_csrf_token]').value}">
          <input type="hidden" name="_action" value="manual_promote">
          <input type="hidden" name="student_id" value="${s.id}">
          <input type="hidden" name="year_id" value="${YEAR_ID}">
          <input type="hidden" name="next_year_id" value="${targetYearId || ''}">
          ${targetClassWidget}
          <select name="promo_status" class="form-control" style="padding:0.25rem 0.4rem; font-size:11px; height:30px; width:105px; border-radius:var(--radius-sm);">
            <option value="promoted" ${s.promotion_status==='promoted'?'selected':''}>${isTerminal ? '🎓 Graduate' : '✓ Pass'}</option>
            <option value="repeated" ${s.promotion_status==='repeated'?'selected':''}>✗ Repeat</option>
          </select>
          <button type="submit" class="btn btn-xs btn-primary" style="height:30px; font-size:11px; padding:0 12px; border-radius:var(--radius-sm);">Save</button>
        </form>
      </div>`;
    }).join('');
  })
  .catch(() => {
    document.getElementById('manual-student-list').innerHTML =
      '<div style="padding:3rem; text-align:center; color:var(--clr-danger);">Failed to load students. Please reload.</div>';
  });
}
</script>

