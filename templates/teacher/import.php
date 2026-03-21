<?php
/**
 * Score Import Page — Teacher
 * Uaddara Basic School — SBA Management System
 */

$pageTitle = 'Bulk Score Import';
include __DIR__ . '/../layout/header.php';
$base = defined('APP_BASE') ? APP_BASE : '';

// ── Load teacher's class-subject assignments ──────────────────────
$userId = Session::userId();
$role   = Session::role();

$whereClause = ($role === 'admin') ? '' : 'AND cs.teacher_id = ?';
$params      = ($role === 'admin') ? [] : [$userId];

$assignments = DB::query(
    "SELECT cs.id as cs_id, c.class_name, c.section, s.subject_name, t.name as term_name, t.id as term_id
     FROM class_subjects cs
     JOIN classes  c ON c.id  = cs.class_id
     JOIN subjects s ON s.id  = cs.subject_id
     JOIN terms    t ON t.id  = cs.term_id
     WHERE t.is_active = 1 {$whereClause}
     ORDER BY c.class_name, c.section, s.subject_name",
    $params
);

// Flash messages
$successMsg  = Session::flash('import_success');
$warnMsg     = Session::flash('import_warn');
$errorMsg    = Session::flash('import_error');
$rowErrors   = Session::get('import_errors', []);
if (!empty($rowErrors)) Session::delete('import_errors');
?>

<!-- Page header -->
<div class="mb-8">
  <div class="flex items-center gap-2 mb-2">
    <a href="<?= $base ?>/teacher" class="btn btn-ghost btn-xs" style="padding:4px; margin-left:-8px;" aria-label="Back to dashboard">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; color:var(--clr-text);">Bulk Score Import</h1>
  </div>
  <p class="text-muted m-0" style="font-size:var(--text-sm);">Download a pre-filled template, add scores in your spreadsheet, then re-upload to save all marks at once.</p>
</div>

<!-- ── Flash Messages ──────────────────────────────────────────────── -->
<?php if ($successMsg): ?>
<div class="alert alert-success flex items-center gap-2 mb-6" role="alert">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<?php if ($warnMsg): ?>
<div class="alert alert-warning flex items-center gap-2 mb-4" role="alert">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  <?= htmlspecialchars($warnMsg) ?>
</div>
<?php endif; ?>

<?php if ($errorMsg): ?>
<div class="alert alert-danger mb-4" role="alert">
  <div class="flex items-center gap-2 mb-2">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <strong><?= htmlspecialchars($errorMsg) ?></strong>
  </div>
  <?php if (!empty($rowErrors)): ?>
  <ul style="margin:.5rem 0 0 1.5rem; font-size:12px; line-height:1.8;">
    <?php foreach ($rowErrors as $e): ?>
    <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if (empty($assignments)): ?>
<!-- ── No assignments ────────────────────────────────────────────── -->
<div class="card" style="padding:3rem 2rem; text-align:center; color:var(--clr-text-muted);">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="48" height="48" style="margin:0 auto 1rem; opacity:.4; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6M9 8h1M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
  <p style="font-weight:700; font-size:15px;">No active class assignments found.</p>
  <p style="font-size:13px; margin-top:4px;">Ask an administrator to assign you to a class and subject for the active term.</p>
</div>
<?php else: ?>
<!-- ── Two-Column Layout ─────────────────────────────────────────── -->
<div class="grid" style="grid-template-columns:1fr 1fr; gap:2rem; align-items:start;">

  <!-- ── STEP 1: Download Template ─────────────────────────────── -->
  <div class="card">
    <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem;">
      <div style="width:32px; height:32px; background:var(--clr-primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; flex-shrink:0;">1</div>
      <div>
        <div style="font-weight:800; font-size:15px;">Download Template</div>
        <div style="font-size:12px; color:var(--clr-text-muted);">Get a CSV pre-filled with your class roster</div>
      </div>
    </div>

    <form method="POST" action="<?= $base ?>/teacher/import?action=template">
      <?= CSRF::field() ?>
      <div class="form-group">
        <label class="form-label" for="dl-cs-select">Class &amp; Subject <span class="required">*</span></label>
        <select name="class_subject_id" id="dl-cs-select" class="form-control" required>
          <option value="">— Select class / subject —</option>
          <?php foreach ($assignments as $a): ?>
          <option value="<?= $a['cs_id'] ?>">
            <?= htmlspecialchars($a['class_name'] . ($a['section'] ? " ({$a['section']})" : '') . ' · ' . $a['subject_name']) ?>
            [<?= htmlspecialchars($a['term_name']) ?>]
          </option>
          <?php endforeach; ?>
        </select>
        <p class="form-text">Existing scores are pre-filled so re-downloading won't lose data.</p>
      </div>
      <button type="submit" class="btn btn-primary w-full" style="display:flex; align-items:center; justify-content:center; gap:.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V19a2 2 0 002 2h14a2 2 0 002-2v-2"/></svg>
        Download CSV Template
      </button>
    </form>
  </div>

  <!-- ── STEP 2: Upload Filled CSV ─────────────────────────────── -->
  <div class="card">
    <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem;">
      <div style="width:32px; height:32px; background:var(--clr-success, #059669); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; flex-shrink:0;">2</div>
      <div>
        <div style="font-weight:800; font-size:15px;">Upload &amp; Import</div>
        <div style="font-size:12px; color:var(--clr-text-muted);">Upload your filled CSV to save all scores</div>
      </div>
    </div>

    <form method="POST" action="<?= $base ?>/teacher/import?action=upload" enctype="multipart/form-data" id="upload-form">
      <?= CSRF::field() ?>

      <!-- Drop zone -->
      <div
        id="drop-zone"
        onclick="document.getElementById('score_csv').click()"
        style="border:2px dashed var(--clr-border); border-radius:var(--radius-md); padding:2.5rem 1rem; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; margin-bottom:1.25rem;"
        onmouseenter="this.style.borderColor='var(--clr-primary)'; this.style.background='var(--clr-primary-50)'"
        onmouseleave="this.style.borderColor='var(--clr-border)'; this.style.background=''"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="40" height="40" style="color:var(--clr-primary-300); margin:0 auto .75rem; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        <p id="drop-label" style="font-weight:700; font-size:13px; color:var(--clr-text);">Click to choose a .csv file</p>
        <p style="font-size:11px; color:var(--clr-text-muted); margin-top:4px;">Drag &amp; drop also works · Max 5 MB</p>
        <input type="file" name="score_csv" id="score_csv" accept=".csv" style="display:none;" required>
      </div>

      <button type="submit" class="btn btn-primary w-full" id="upload-btn" style="display:flex; align-items:center; justify-content:center; gap:.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Validate &amp; Import Scores
      </button>
    </form>
  </div>

</div>

<!-- ── Instructions ─────────────────────────────────────────────── -->
<div class="card mt-6" style="background:var(--clr-surface-2); border:none;">
  <h4 style="font-weight:800; margin-bottom:1.25rem; font-size:var(--text-md);">How it works</h4>
  <div class="grid" style="grid-template-columns:repeat(3,1fr); gap:1.5rem;">
    <?php foreach ([
      ['1', '#4f1d96', 'Download', 'Choose your class and subject, then click "Download CSV Template". The file comes pre-filled with your student roster and existing scores.'],
      ['2', '#0369a1', 'Fill Scores', 'Open in Excel or Google Sheets. Fill in the SBA components (max 15 each) and the exam raw score (max 100). Leave cells blank to skip.'],
      ['3', '#047857', 'Upload', 'Save as .csv and upload here. All rows are validated first — any error shows a clear message before any data is saved.'],
    ] as [$num, $color, $title, $desc]): ?>
    <div style="display:flex; gap:.75rem;">
      <div style="width:26px; height:26px; background:<?= $color ?>; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0; margin-top:2px;"><?= $num ?></div>
      <div>
        <div style="font-weight:800; font-size:13px; margin-bottom:.25rem;"><?= $title ?></div>
        <div style="font-size:12px; line-height:1.55; color:var(--clr-text-muted);"><?= $desc ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="alert" style="margin-top:1.5rem; background:rgba(239,68,68,.08); border-color:rgba(239,68,68,.2); color:#991b1b; font-size:12px;">
    <strong>Strict validation:</strong> If any Student ID doesn't match the class roster, or any score is out of range, <em>no scores are saved at all</em>. Fix the errors and re-upload.
  </div>
</div>
<?php endif; ?>

<script>
// ── Drag & Drop ─────────────────────────────────────────────────────
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('score_csv');
const dropLabel = document.getElementById('drop-label');

if (dropZone && fileInput) {
  fileInput.addEventListener('change', () => {
    const name = fileInput.files[0]?.name;
    if (name) {
      dropLabel.textContent = '✓ ' + name;
      dropLabel.style.color = 'var(--clr-success, #059669)';
    }
  });

  ['dragover','dragenter'].forEach(evt => dropZone.addEventListener(evt, e => {
    e.preventDefault();
    dropZone.style.borderColor = 'var(--clr-primary)';
    dropZone.style.background  = 'var(--clr-primary-50)';
  }));

  ['dragleave','dragend'].forEach(evt => dropZone.addEventListener(evt, () => {
    dropZone.style.borderColor = 'var(--clr-border)';
    dropZone.style.background  = '';
  }));

  dropZone.addEventListener('drop', e => {
    e.preventDefault();
    const files = e.dataTransfer.files;
    if (files.length) {
      fileInput.files = files;
      dropLabel.textContent = '✓ ' + files[0].name;
      dropLabel.style.color = 'var(--clr-success, #059669)';
    }
    dropZone.style.borderColor = 'var(--clr-border)';
    dropZone.style.background  = '';
  });
}

// ── Loading state on upload ─────────────────────────────────────────
const uploadForm = document.getElementById('upload-form');
const uploadBtn  = document.getElementById('upload-btn');
if (uploadForm && uploadBtn) {
  uploadForm.addEventListener('submit', () => {
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<svg style="animation:spin .8s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Validating &amp; Importing…';
  });
}
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
