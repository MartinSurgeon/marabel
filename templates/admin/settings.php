<?php
/**
 * Admin Settings View
 * HCI: Clean, minimal cards to upload Headmaster Signature and School Stamp.
 */
$pageTitle = 'School Settings';
include __DIR__ . '/../layout/header.php';

$base = defined('APP_BASE') ? APP_BASE : '';

$getSettingImagePath = function(string $basename) use ($base) {
    $dir = ROOT_PATH . '/assets/uploads/signatures';
    foreach (['png', 'jpg', 'jpeg'] as $ext) {
        if (file_exists("$dir/$basename.$ext")) {
            return "$base/assets/uploads/signatures/$basename.$ext?" . time(); // cache bust
        }
    }
    return null;
};

$sigPath   = $getSettingImagePath('headmaster_signature');
$stampPath = $getSettingImagePath('school_stamp');
?>

<div class="flex justify-between items-center mb-6 gap-4 flex-wrap">
  <div>
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; color:var(--clr-text); letter-spacing:-0.03em;">School Settings</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm);">Manage the headmaster's signature and the official school stamp for report cards.</p>
  </div>
</div>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
    <!-- Signature Card -->
    <div class="card" style="padding: 1.5rem;">
        <h3 style="margin:0 0 1rem; font-weight:800;">Headmaster Signature</h3>
        
        <div style="background:var(--clr-surface-2); border:1.5px dashed var(--clr-border); border-radius:var(--radius-md); padding:2rem; text-align:center; min-height:150px; display:flex; align-items:center; justify-content:center; margin-bottom:1.5rem;">
            <?php if ($sigPath): ?>
                <img src="<?= htmlspecialchars($sigPath) ?>" alt="Signature" style="max-height:80px; max-width:100%; mix-blend-mode:multiply;">
            <?php else: ?>
                <span class="text-muted" style="font-weight:600;">No signature uploaded</span>
            <?php endif; ?>
        </div>

        <form method="POST" action="<?= $base ?>/admin/settings" enctype="multipart/form-data" class="flex gap-2">
            <?= CSRF::field() ?>
            <input type="hidden" name="type" value="signature">
            <input type="file" name="image" accept=".png,.jpg,.jpeg" required class="input" style="padding:0.4rem; flex:1; font-size:13px;">
            <button type="submit" class="btn btn-primary btn-sm flex-shrink-0" onclick="Loader.show()">Upload</button>
        </form>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">
            <p style="font-size:11px; margin:0; color:var(--clr-text-muted);">Recommended: Transparent PNG, max 400x150px.</p>
            <?php if ($sigPath): ?>
                <button type="button" class="btn btn-ghost btn-xs text-red-600 hover:bg-red-50" onclick="confirmDelete('signature', 'remove the headmaster signature')" style="padding:2px 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="12" height="12" style="display:inline-block; margin-right:2px; margin-top:-2px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Remove
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stamp Card -->
    <div class="card" style="padding: 1.5rem;">
        <h3 style="margin:0 0 1rem; font-weight:800;">Official School Stamp</h3>
        
        <div style="background:var(--clr-surface-2); border:1.5px dashed var(--clr-border); border-radius:var(--radius-md); padding:2rem; text-align:center; min-height:150px; display:flex; align-items:center; justify-content:center; margin-bottom:1.5rem;">
            <?php if ($stampPath): ?>
                <img src="<?= htmlspecialchars($stampPath) ?>" alt="Stamp" style="max-height:110px; max-width:100%; mix-blend-mode:multiply;">
            <?php else: ?>
                <span class="text-muted" style="font-weight:600;">No stamp uploaded</span>
            <?php endif; ?>
        </div>

        <form method="POST" action="<?= $base ?>/admin/settings" enctype="multipart/form-data" class="flex gap-2">
            <?= CSRF::field() ?>
            <input type="hidden" name="type" value="stamp">
            <input type="file" name="image" accept=".png,.jpg,.jpeg" required class="input" style="padding:0.4rem; flex:1; font-size:13px;">
            <button type="submit" class="btn btn-primary btn-sm flex-shrink-0" onclick="Loader.show()">Upload</button>
        </form>
        <div style="display:flex; justify-content:space-between; align-items:center; margin-top:0.5rem;">
            <p style="font-size:11px; margin:0; color:var(--clr-text-muted);">Recommended: Circular PNG with transparent background.</p>
            <?php if ($stampPath): ?>
                <button type="button" class="btn btn-ghost btn-xs text-red-600 hover:bg-red-50" onclick="confirmDelete('stamp', 'remove the official school stamp')" style="padding:2px 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="12" height="12" style="display:inline-block; margin-right:2px; margin-top:-2px;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Remove
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- ── Delete Confirmation Modal ─────────────────────────────── -->
<div id="modal-delete" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-delete-title" style="display:none;">
  <div class="modal w-full mx-4" style="max-width:400px; min-height: 0;">

    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:0.75rem;">
        <div style="width:36px;height:36px;border-radius:50%;background:rgba(239, 68, 68, 0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" style="color:#ef4444;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </div>
        <h3 class="modal-title" id="modal-delete-title">Confirm Removal</h3>
      </div>
    </div>
    
    <div class="modal-body py-4">
      <p style="margin:0; color:var(--clr-text-muted); font-size:14px; line-height:1.5;">Are you sure you want to <span id="modal-target-text" style="font-weight:700; color:var(--clr-text);"></span>? This action will remove it from all report cards instantly.</p>
    </div>
    
    <div class="modal-footer" style="display:flex; gap:0.75rem; border-top:1px solid var(--clr-border); padding-top:1rem;">
      <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('modal-delete')">Cancel</button>
      <form id="delete-form" method="POST" action="<?= $base ?>/admin/settings" style="flex:1; margin:0;" onsubmit="Loader.show()">
          <?= CSRF::field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="type" id="delete-type-input" value="">
          <button type="submit" class="btn" style="width:100%; background:#ef4444; color:#fff; border:none;">Remove Image</button>
      </form>
    </div>

  </div>
</div>

<script>
function confirmDelete(type, targetText) {
    document.getElementById('delete-type-input').value = type;
    document.getElementById('modal-target-text').textContent = targetText;
    openModal('modal-delete');
}
</script>
