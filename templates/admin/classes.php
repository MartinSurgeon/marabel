<?php
/**
 * Classrooms Management View
 * HCI/UX: Year filter, card grid by level, modal CRUD, student count badges
 */
$pageTitle = 'Classrooms';
include __DIR__ . '/../layout/header.php';

global $classesList, $levelsList, $yearsList, $teachersList, $activeYearId;
$base       = defined('APP_BASE') ? APP_BASE : '';
$filterYear = $_GET['year_id'] ?? $activeYearId;
$classes    = $classesList ?? [];

// Group classes by level for display
$byLevel = [];
foreach ($classes as $c) {
    $byLevel[$c['level_name']][] = $c;
}

$levelColors = ['LP' => 'success', 'UP' => 'warning', 'JHS' => 'purple'];
?>

<!-- Toolbar -->
<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Classrooms</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:600px;">
      Manage classrooms, sections, and assign class teachers. Grouped by school level for easier administration.
    </p>
  </div>
  <div class="flex items-center gap-3">
    <!-- Year filter -->
    <form method="GET" action="<?= $base ?>/admin/classes" class="flex items-center gap-2" id="filter-form">
      <div style="font-size:11px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase;">Year</div>
      <select name="year_id" class="form-control" style="width:auto; min-width:140px; padding:0.5rem 1rem; height:42px;" onchange="Loader.show(); this.form.submit()">
        <?php foreach ($yearsList as $y): ?>
          <option value="<?= $y['id'] ?>" <?= $filterYear == $y['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($y['year_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
    <button class="btn btn-primary shadow-purple" onclick="openClassModal()" style="height:42px;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      New Classroom
    </button>
  </div>
</div>

<?php if (empty($yearsList)): ?>
<!-- ── Missing Year State ───────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center shadow-lg" style="padding:5rem 2rem; text-align:center; border-style:dashed;">
  <div style="background:var(--clr-surface-2); padding:1.5rem; border-radius:var(--radius-full); margin-bottom:1.5rem;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="56" height="56" style="color:var(--clr-warning)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text);">Setup Academic Year</h2>
  <p class="text-muted" style="max-width:320px; margin:0 auto 2rem;">Classes must belong to an academic year session.</p>
  <a href="<?= $base ?>/admin/years" class="btn btn-primary">Go to Academic Years</a>
</div>

<?php elseif (empty($classes)): ?>
<!-- ── Empty Classes State ──────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center;">
  <div style="background:var(--clr-primary-50); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text); margin-bottom:0.5rem;">Create Your Classes</h2>
  <p class="text-muted" style="max-width:320px; margin:0 auto 2.5rem;">No class sections have been registered for this academic session yet.</p>
  <button class="btn btn-primary btn-lg" onclick="openClassModal()">Register First Class</button>
</div>

<?php else: ?>

<!-- ── Classes by Level ─────────────────────────────────────── -->
<?php foreach ($byLevel as $levelName => $levelClasses): ?>
<?php $levelCode = $levelClasses[0]['level_code']; ?>
<div style="margin-bottom:3.5rem;">
  <div class="flex items-center gap-4 mb-6">
    <div style="height:2px; flex:1; background:linear-gradient(to right, var(--clr-primary-200), transparent);"></div>
    <h3 class="m-0" style="font-size:0.875rem; font-weight:800; text-transform:uppercase; letter-spacing:0.1em; color:var(--clr-primary-700);">
      <?= htmlspecialchars($levelName) ?>
    </h3>
    <span class="badge badge-<?= $levelColors[$levelCode] ?? 'purple' ?>" style="font-size:11px; padding:0.25rem 0.6rem;"><?= count($levelClasses) ?> SECTION<?= count($levelClasses) != 1 ? 'S' : '' ?></span>
    <div style="height:2px; flex:1; background:linear-gradient(to left, var(--clr-primary-200), transparent);"></div>
  </div>

  <div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:1.5rem;">
    <?php foreach ($levelClasses as $cls): ?>
    <div class="card hover-lift flex flex-col" style="padding:0; overflow:hidden; border:1px solid rgba(0,0,0,0.06); box-shadow:0 10px 15px -3px rgba(0,0,0,0.04), 0 4px 6px -2px rgba(0,0,0,0.02); background:rgba(255,255,255,0.8); backdrop-filter:blur(10px);">
      <div style="padding:1.5rem 1.5rem 1rem;">
        <div class="flex justify-between items-start mb-3">
          <div style="font-size:2rem; font-weight:900; letter-spacing:-0.04em; color:var(--clr-primary); line-height:1;">
            <?= htmlspecialchars($cls['class_name']) ?><?= $cls['section'] ? '<span style="opacity:0.4; font-weight:400; font-size:1.5rem; margin-left:2px;">' . htmlspecialchars($cls['section']) . '</span>' : '' ?>
          </div>
          <div class="flex gap-1">
            <button class="btn btn-ghost btn-xs" onclick='editClass(<?= htmlspecialchars(json_encode($cls), ENT_QUOTES) ?>)' data-tooltip="Edit Details">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </button>
            <button class="btn btn-ghost btn-xs text-danger" onclick="confirmDeleteClass(<?= $cls['id'] ?>, '<?= htmlspecialchars($cls['class_name'], ENT_QUOTES) ?>')" data-tooltip="Delete Class">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>

        <div class="flex items-center gap-2 mb-4">
           <div style="background:var(--clr-primary-50); color:var(--clr-primary); padding:3px 10px; border-radius:var(--radius-full); font-size:11px; font-weight:800; display:flex; align-items:center; border:1px solid rgba(79, 29, 150, 0.1);">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12" class="mr-1.5 opacity-80"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 015.25-2.906z"/></svg>
              <?= $cls['student_count'] ?> PUPILS
           </div>
        </div>

        <div style="border-top:1px solid var(--clr-border); padding-top:1rem; margin-top:auto;">
           <div style="font-size:11px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; margin-bottom:0.25rem;">Class Teacher(s)</div>
           <?php if ($cls['teacher_name']): ?>
             <?php foreach (explode(', ', $cls['teacher_name']) as $tName): ?>
             <div class="flex items-center gap-2 mb-1">
                <div style="width:24px; height:24px; background:var(--clr-surface-2); border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center; color:var(--clr-primary-700); font-weight:800; font-size:10px;">
                   <?= substr($tName, 0, 1) ?>
                </div>
                <div style="font-size:13px; font-weight:600; color:var(--clr-text);"><?= htmlspecialchars($tName) ?></div>
             </div>
             <?php endforeach; ?>
           <?php else: ?>
             <div class="flex items-center gap-2 text-warning" style="font-size:13px; font-weight:500;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Not assigned
             </div>
           <?php endif; ?>
        </div>
      </div>
      <div style="margin-top:auto; padding:0.875rem 1.5rem; background:rgba(249,250,251,0.5); border-top:1px solid rgba(0,0,0,0.04); display:flex; justify-content:flex-end;">
         <a href="<?= $base ?>/admin/students?class_id=<?= $cls['id'] ?>" class="btn btn-ghost btn-xs font-bold" style="font-size:10px; letter-spacing:0.04em; color:var(--clr-primary);">ROSTER & SCORES →</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>


<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- ══ Class Modal ════════════════════════════════════════════ -->
<div id="modal-class" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-class-title" style="display:none;">
  <div class="modal w-full max-w-lg mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-class-title">Classroom Information</h3>
      <button class="modal-close" onclick="closeModal('modal-class')" aria-label="Close dialog">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/classes" id="form-class" onsubmit="Loader.show()">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="class_store">
      <input type="hidden" name="class_id" id="class-id-field" value="">
      <div class="modal-body">
        <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
          <div class="form-group">
            <label class="form-label">Academic Session <span class="required">*</span></label>
            <select name="academic_year_id" id="class-year" class="form-control" required>
              <?php foreach ($yearsList as $y): ?>
              <option value="<?= $y['id'] ?>" <?= ($filterYear == $y['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($y['year_name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">School Level <span class="required">*</span></label>
            <select id="class-level" name="level_id" class="form-control" required onchange="suggestClassName()">
              <option value="">— Select —</option>
              <?php foreach ($levelsList as $l): ?>
              <option value="<?= $l['id'] ?>" data-code="<?= $l['code'] ?>">
                <?= htmlspecialchars($l['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid" style="grid-template-columns:2fr 1fr; gap:1.5rem;">
          <div class="form-group">
            <label class="form-label">Classroom Name <span class="required">*</span></label>
            <input type="text" id="class-name-input" name="class_name" class="form-control"
              placeholder="e.g. B1, B8" required maxlength="20"
              oninput="this.value = this.value.toUpperCase()">
            <p class="form-text">B1-B3 (LP), B4-B6 (UP), B7-B9 (JHS)</p>
          </div>
          <div class="form-group">
            <label class="form-label">Section</label>
            <input type="text" id="class-section" name="section" class="form-control"
              placeholder="e.g. A" maxlength="5"
              oninput="this.value = this.value.toUpperCase()">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Assign Class Teacher(s)</label>
          <select name="teacher_ids[]" id="class-teacher" class="form-control" multiple style="min-height:90px; padding:.5rem;">
            <?php foreach ($teachersList as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <p class="form-text">Hold Ctrl/Cmd to select multiple teachers. Class teachers manage score entry for their assigned students.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-class')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="class-submit-btn">Save Classroom</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Form -->
<form method="POST" action="<?= $base ?>/admin/classes" id="form-class-delete" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" value="class_delete">
  <input type="hidden" name="class_id" id="del-class-id">
</form>

<script>
function openClassModal() {
  document.getElementById('form-class').reset();
  document.getElementById('class-id-field').value = '';
  document.getElementById('modal-class-title').textContent = 'New Classroom';
  document.getElementById('class-submit-btn').textContent = 'Create Classroom';
  openModal('modal-class');
}

function editClass(cls) {
  document.getElementById('form-class').reset();
  document.getElementById('class-id-field').value = cls.id;
  document.getElementById('class-year').value = cls.academic_year_id;
  document.getElementById('class-level').value = cls.level_id;
  document.getElementById('class-name-input').value = cls.class_name;
  document.getElementById('class-section').value = cls.section || '';
  
  const tSelect = document.getElementById('class-teacher');
  Array.from(tSelect.options).forEach(opt => opt.selected = false);
  if (cls.class_teacher_ids) {
    const ids = cls.class_teacher_ids.split(',');
    Array.from(tSelect.options).forEach(opt => {
      if (ids.includes(opt.value)) opt.selected = true;
    });
  }
  
  document.getElementById('modal-class-title').textContent = 'Edit ' + cls.class_name;
  document.getElementById('class-submit-btn').textContent = 'Update Classroom';
  openModal('modal-class');
}

function confirmDeleteClass(id, name) {
  confirmAction(`Delete the class "${name}"?\n\nThis will remove all associated student enrollments and scores recorded for this section.`, () => {
    document.getElementById('del-class-id').value = id;
    document.getElementById('form-class-delete').submit();
  });
}

function suggestClassName() {
  const sel = document.getElementById('class-level');
  const opt = sel.options[sel.selectedIndex];
  const code = opt?.dataset?.code ?? '';
  const inp = document.getElementById('class-name-input');
  if (inp.value === '') {
    const map = { LP: 'B1', UP: 'B4', JHS: 'B7' };
    inp.value = map[code] || '';
    inp.focus();
  }
}
</script>
