<?php
/**
 * Score Import Template
 */

$pageTitle = 'Bulk Import';
include __DIR__ . '/../layout/header.php';
$base = defined('APP_BASE') ? APP_BASE : '';
?>

<!-- ── Import Header ────────────────────────────────────────── -->
<div class="mb-8">
  <div class="flex items-center gap-2 mb-2">
    <a href="<?= $base ?>/teacher" class="btn btn-ghost btn-xs" style="padding:4px; margin-left:-8px;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; color:var(--clr-text);">Bulk Score Import</h1>
  </div>
  <p class="text-muted m-0" style="font-size:var(--text-sm);">Upload CSV or Excel files to record scores for your assigned classes in bulk.</p>
</div>

<div class="grid" style="grid-template-columns:1.5fr 1fr; gap:2rem; align-items:start;">
  <!-- Upload Zone -->
  <div class="card" style="padding:4rem 2rem; border-style:dashed; text-align:center;">
    <div style="background:var(--clr-surface-2); display:inline-flex; padding:1.5rem; border-radius:50%; margin-bottom:1.5rem; color:var(--clr-primary-300);">
       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
    </div>
    <h3 style="font-weight:800; color:var(--clr-text); margin-bottom:0.5rem;">Select spreadsheet file</h3>
    <p class="text-muted mb-6" style="font-size:var(--text-ms);">Drag and drop your file here, or click to browse.</p>
    
    <input type="file" id="file-import" style="display:none;" accept=".csv, .xlsx, .xls">
    <button class="btn btn-primary" onclick="document.getElementById('file-import').click()">Browse Files</button>
    
    <div class="mt-4 text-muted" style="font-size:11px; font-weight:600;">MAX FILE SIZE: 5MB · FORMATS: .CSV, .XLSX</div>
  </div>

  <!-- Instructions -->
  <div class="card" style="background:var(--clr-surface-2); border:none;">
    <h4 style="font-weight:800; margin-bottom:1.5rem; font-size:var(--text-md);">Import Instructions</h4>
    <ul style="list-style:none; padding:0; display:flex; flex-direction:column; gap:1.25rem;">
      <li style="display:flex; gap:1rem;">
        <span style="width:24px; height:24px; background:var(--clr-primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0;">1</span>
        <div style="font-size:13px; line-height:1.5;">Download the student roster for your class to ensure the <strong>Student ID</strong> column is accurate.</div>
      </li>
      <li style="display:flex; gap:1rem;">
        <span style="width:24px; height:24px; background:var(--clr-primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0;">2</span>
        <div style="font-size:13px; line-height:1.5;">Fill in the SBA components (max 15 each) and end-of-term exam (max 100).</div>
      </li>
      <li style="display:flex; gap:1rem;">
        <span style="width:24px; height:24px; background:var(--clr-primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; flex-shrink:0;">3</span>
        <div style="font-size:13px; line-height:1.5;">Uploaded files will be validated instantly. Any errors in Student IDs will halt the process.</div>
      </li>
    </ul>
    
    <div class="alert-info mt-8" style="background:white; border:1px solid var(--clr-border); border-radius:var(--radius-md);">
       <div style="font-size:12px; font-weight:800; margin-bottom:0.5rem; color:var(--clr-primary);">COMING SOON</div>
       <div style="font-size:12px; line-height:1.4;">Automated import logic is currently under development. Please use the <strong>Manual Grid</strong> for now.</div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
