<?php
/**
 * Student Bulk Import Page
 * Admin interface mimicking the 2-step teacher score import UX
 */

$pageTitle = 'Import Students';
include __DIR__ . '/../layout/header.php';
$base = defined('APP_BASE') ? APP_BASE : '';

global $yearsList, $activeYearId;
$years = $yearsList ?? [];

// Default academic year is active one, or from URL
$filterYear = $_GET['year_id'] ?? $activeYearId;

// Flash messages from Session
$bulkResult = Session::get('bulk_import_result');
if ($bulkResult) Session::delete('bulk_import_result');

$errorMsg = $bulkResult['error'] ?? null;
$inserted = $bulkResult['inserted'] ?? null;
$rowErrors= $bulkResult['rowErrors'] ?? [];
?>

<!-- ── Page header ─────────────────────────────────────────────────── -->
<div class="mb-8">
  <div class="flex items-center gap-2 mb-2">
    <a href="<?= $base ?>/admin/students" class="btn btn-ghost btn-xs" style="padding:4px; margin-left:-8px;" aria-label="Back to students">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; color:var(--clr-text);">Bulk Student Import</h1>
  </div>
  <p class="text-muted m-0" style="font-size:var(--text-sm);">Easily add multiple students at once right from your Excel spreadsheet.</p>
</div>

<!-- ── Results Banner ──────────────────────────────────────────────── -->
<?php if ($errorMsg): ?>
<div class="alert alert-danger mb-6" role="alert">
  <div class="flex items-center gap-2 mb-2">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <strong><?= htmlspecialchars($errorMsg) ?></strong>
  </div>
</div>
<?php endif; ?>

<?php if ($inserted !== null && !$errorMsg): ?>
<div class="alert alert-success mb-6" role="alert" style="flex-wrap:wrap; gap:.5rem;">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
  <strong>Import complete:</strong>&nbsp;
  <span style="color:#166534;"><?= $bulkResult['inserted'] ?> student(s) added</span>,
  <?= $bulkResult['skipped'] ?> skipped (duplicates / blank rows)
  
  <?php if (!empty($rowErrors)): ?>
  <ul style="width:100%; margin:.5rem 0 0 1.5rem; font-size:12px; color:#991b1b;">
    <?php foreach ($rowErrors as $e): ?>
    <li><?= htmlspecialchars($e) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Two-Column Layout ─────────────────────────────────────────── -->
<div class="grid" style="grid-template-columns:1fr 1fr; gap:2rem; align-items:start;">

  <!-- ── STEP 1: Download Template ─────────────────────────────── -->
  <div class="card">
    <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem;">
      <div style="width:32px; height:32px; background:var(--clr-primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; flex-shrink:0;">1</div>
      <div>
        <div style="font-weight:800; font-size:15px;">Get the Format</div>
        <div style="font-size:12px; color:var(--clr-text-muted);">Download a blank CSV with the required columns</div>
      </div>
    </div>

    <form method="POST" action="<?= $base ?>/admin/import?action=template">
      <?= CSRF::field() ?>
      <div class="form-group mb-4">
        <p class="form-text" style="line-height:1.6; color:var(--clr-text);">
          Your Excel file MUST have exactly 4 columns at the top: <br>
          <code>Full_Name</code>, <code>Class</code>, <code>Section</code>, <code>Gender</code><br><br>
          Click below to download a perfect, ready-to-use template.
        </p>
      </div>
      <button type="submit" class="btn btn-ghost w-full" style="border:1px solid var(--clr-primary); color:var(--clr-primary); display:flex; align-items:center; justify-content:center; gap:.5rem;">
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
        <div style="font-size:12px; color:var(--clr-text-muted);">Upload your filled CSV to register the students</div>
      </div>
    </div>

    <form method="POST" action="<?= $base ?>/admin/import?action=upload" enctype="multipart/form-data" id="upload-form" onsubmit="Loader.show()">
      <?= CSRF::field() ?>

      <!-- Academic Year -->
      <div class="form-group mb-4">
        <label class="form-label" style="font-size:11px; text-transform:uppercase;">Academic Year <span class="required">*</span></label>
        <select name="academic_year_id" class="form-control" required style="height:38px;">
          <option value="">— Select Year —</option>
          <?php foreach ($years as $y): ?>
          <option value="<?= $y['id'] ?>" <?= $filterYear == $y['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($y['year_name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Drop zone -->
      <div
        id="drop-zone"
        onclick="document.getElementById('import_csv').click()"
        style="border:2px dashed var(--clr-border); border-radius:var(--radius-md); padding:2rem 1rem; text-align:center; cursor:pointer; transition:border-color .2s, background .2s; margin-bottom:1.25rem;"
        onmouseenter="this.style.borderColor='var(--clr-primary)'; this.style.background='var(--clr-primary-50)'"
        onmouseleave="this.style.borderColor='var(--clr-border)'; this.style.background=''"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="40" height="40" style="color:var(--clr-primary-300); margin:0 auto .75rem; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
        <p id="drop-label" style="font-weight:700; font-size:13px; color:var(--clr-text);">Click to choose a .csv file</p>
        <p style="font-size:11px; color:var(--clr-text-muted); margin-top:4px;">Drag &amp; drop also works · Max 5 MB</p>
        <input type="file" name="import_csv" id="import_csv" accept=".csv" style="display:none;">
      </div>

      <button type="submit" class="btn btn-primary w-full" id="upload-btn" style="display:flex; align-items:center; justify-content:center; gap:.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Validate &amp; Import
      </button>
    </form>
  </div>

</div>

<!-- ── Instructions ─────────────────────────────────────────────── -->
<div class="card mt-6" style="background:var(--clr-surface-2); border:none;">
  <h4 style="font-weight:800; margin-bottom:1.25rem; font-size:var(--text-md);">How it works</h4>
  <div class="grid" style="grid-template-columns:repeat(3,1fr); gap:1.5rem;">
    <?php foreach ([
      ['1', '#4f1d96', 'Match Classes', 'Make sure your "Class" column matches the classes registered in your database exactly (e.g. BASIC 1).'],
      ['2', 'var(--clr-text)', 'Skip Duplicates', 'The system is smart. If a student is already registered with the exact same name in that class, it will seamlessly skip them.'],
      ['3', '#047857', 'ID Auto-Generation', 'Don\'t worry about Student IDs. The system finds the last used numeric ID and automatically counts up (0001, 0002...).'],
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
</div>

<script>
// ── Drag & Drop ─────────────────────────────────────────────────────
const dropZone = document.getElementById('drop-zone');
const fileInput = document.getElementById('import_csv');
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
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
