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
        <p style="font-size:11px; margin-top:0.5rem; color:var(--clr-text-muted);">Recommended: Transparent PNG, max 400x150px.</p>
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
        <p style="font-size:11px; margin-top:0.5rem; color:var(--clr-text-muted);">Recommended: Circular PNG with transparent background.</p>
    </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
