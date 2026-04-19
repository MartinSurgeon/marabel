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

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div>
    <h1 class="m-0" style="font-size:var(--text-3xl); font-weight:900; color:var(--clr-text); letter-spacing:-0.04em;">School Settings</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); opacity:0.8;">Configure your school's identity, branding, and official document signatures.</p>
  </div>
</div>

<!-- ── BRANDING & IDENTITY SECTION ────────────────────────────── -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
    
    <!-- Branding Card -->
    <div class="card" style="padding: 2rem; border-color: rgba(126, 43, 179, 0.15);">
        <div style="display:flex; align-items:center; gap:0.75rem; margin-bottom:2rem;">
            <div style="width:40px; height:40px; border-radius:12px; background:var(--clr-primary-50); color:var(--clr-primary); display:flex; align-items:center; justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <h3 style="margin:0; font-weight:900; font-size:1.1rem; letter-spacing:-0.01em;">School Identity</h3>
                <p style="font-size:11px; color:var(--clr-text-muted); margin:0;">Define your institution's name and brand guidelines.</p>
            </div>
        </div>

        <div class="grid" style="grid-template-columns: 1fr 150px; gap: 2rem;">
            <!-- Text Settings -->
            <form method="POST" action="<?= $base ?>/admin/settings">
                <?= CSRF::field() ?>
                <input type="hidden" name="type" value="branding">
                
                <div class="form-group">
                    <label class="form-label" style="text-transform:uppercase; font-size:11px; letter-spacing:0.05em; color:var(--clr-primary); font-weight:800;">Header Line 1 (Educational Body)</label>
                    <input type="text" id="input-school-body" name="school_body" class="form-control" value="<?= htmlspecialchars(Config::get('school_body', 'ARMED FORCES EDUCATION UNIT')) ?>" style="font-size:0.9rem;">
                </div>

                <div class="form-group">
                    <label class="form-label" style="text-transform:uppercase; font-size:11px; letter-spacing:0.05em; color:var(--clr-primary); font-weight:800;">Header Line 2 (School Name)</label>
                    <input type="text" id="input-school-name" name="school_name" class="form-control" value="<?= htmlspecialchars(Config::get('school_name', 'Marabel SBA')) ?>" required style="font-weight:600; font-size:1rem;">
                </div>

                <div class="form-group">
                    <label class="form-label" style="text-transform:uppercase; font-size:11px; letter-spacing:0.05em; color:var(--clr-primary); font-weight:800;">System Tagline</label>
                    <input type="text" id="input-school-tagline" name="school_tagline" class="form-control" value="<?= htmlspecialchars(Config::get('school_tagline', 'SBA System')) ?>" style="font-size:0.9rem;">
                </div>

                <div class="form-group" style="margin-bottom:2rem;">
                    <label class="form-label" style="text-transform:uppercase; font-size:11px; letter-spacing:0.05em; color:var(--clr-primary); font-weight:800;">Report Accent Color</label>
                    <div style="display:flex; gap:0.75rem; align-items:center;">
                        <input type="color" id="input-accent-color" name="brand_accent_color" value="<?= htmlspecialchars(Config::get('brand_accent_color', '#c00000')) ?>" style="width:42px; height:42px; padding:2px; border:1.5px solid var(--clr-border); border-radius:8px; cursor:pointer; background:#fff;">
                        <span id="color-hex-display" style="font-family:var(--font-mono); font-size:12px; font-weight:600; color:var(--clr-text-muted);"><?= htmlspecialchars(Config::get('brand_accent_color', '#c00000')) ?></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary shadow-purple px-8" onclick="Loader.show()">Update Identity</button>
            </form>

            <!-- Logo Section -->
            <div style="display:flex; flex-direction:column; gap:1rem; align-items:center; border-left:1px solid var(--clr-border); padding-left:2rem;">
                <label class="form-label" style="text-transform:uppercase; font-size:10px; letter-spacing:0.05em; color:var(--clr-text-muted); font-weight:800; text-align:center;">Institutional Logo</label>
                <?php $logoPath = $base . Config::get('school_logo', '/assets/img/school-logo.png'); ?>
                <div style="width:120px; height:120px; border-radius:18px; background:#fff; border:1.5px solid var(--clr-border); padding:12px; display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden; box-shadow:var(--shadow-sm);">
                    <img src="<?= htmlspecialchars($logoPath) ?>?v=<?= time() ?>" alt="School Logo" style="max-width:100%; max-height:100%; object-fit:contain;">
                    
                    <?php if (Config::get('school_logo')): ?>
                        <button type="button" 
                                onclick="confirmDelete('logo', 'revert to the default system logo')" 
                                style="position:absolute; top:4px; right:4px; width:22px; height:22px; border-radius:50%; background:rgba(239,68,68,0.1); border:none; color:#ef4444; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.2s;"
                                onmouseenter="this.style.background='rgba(239,68,68,0.2)'"
                                onmouseleave="this.style.background='rgba(239,68,68,0.1)'">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    <?php endif; ?>
                </div>
                
                <form method="POST" action="<?= $base ?>/admin/settings" enctype="multipart/form-data">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="type" value="logo">
                    <label for="logo-upload" class="btn btn-ghost btn-sm" style="font-size:11px; font-weight:700; cursor:pointer; border:1px solid var(--clr-border);">
                        Change Logo
                    </label>
                    <input type="file" id="logo-upload" name="image" accept=".png,.jpg,.jpeg" required style="display:none;" onchange="this.form.submit(); Loader.show();">
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Mockup Card -->
    <div class="card" style="padding: 2rem; background: var(--clr-surface-2); border-color:transparent;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; gap:0.6rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" style="color:var(--clr-primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h3 style="margin:0; font-weight:800; font-size:0.95rem; color:var(--clr-text);">Report Card Preview</h3>
            </div>
            <span class="badge badge-purple" style="font-size:10px;">Official Document Scale</span>
        </div>

        <!-- Mockup Container -->
        <div style="background:#fff; border-radius:var(--radius-md); padding:24px; box-shadow:var(--shadow-lg); font-size:10px; border:1px solid #eee; min-height:280px; position:relative;">
             <!-- Watermark -->
             <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%) rotate(-15deg); width:150px; opacity:0.04; pointer-events:none;">
                <img src="<?= htmlspecialchars($logoPath) ?>" style="width:100%;">
            </div>

            <!-- Header -->
            <div style="display:flex; justify-content:space-between; margin-bottom:20px; border-bottom:1.5px solid #000; padding-bottom:15px; position:relative; z-index:1;">
                <img src="<?= htmlspecialchars($logoPath) ?>" style="width:60px; height:60px; object-fit:contain;">
                <div style="text-align:center; flex:1;">
                    <div id="preview-school-body" style="font-family:'Old Standard TT', serif; font-size:16px; font-weight:700; color:#111; line-height:1.1; text-transform:uppercase;"><?= htmlspecialchars(Config::get('school_body', 'ARMED FORCES EDUCATION UNIT')) ?></div>
                    <div id="preview-school-name" style="font-size:15px; font-weight:900; color:<?= htmlspecialchars(Config::get('brand_accent_color', '#c00000')) ?>; margin-top:4px; text-transform:uppercase;"><?= htmlspecialchars(Config::get('school_name', 'Marabel SBA')) ?></div>
                    <div id="preview-badge" style="display:inline-block; margin-top:6px; background:<?= htmlspecialchars(Config::get('brand_accent_color', '#c00000')) ?>; color:#fff; padding:2px 14px; font-size:9px; font-weight:800; letter-spacing:0.5px;">PUPIL'S REPORT FORM</div>
                </div>
                <div style="width:60px; height:70px; border:1px solid #000; background:#f9f8f9; display:flex; align-items:center; justify-content:center; color:#ddd;">
                    <svg fill="currentColor" viewBox="0 0 24 24" width="24" height="24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </div>
            </div>

            <!-- Table Rows -->
            <div style="border:1.2px solid #000; overflow:hidden;">
                <div style="background:#f1f5f9; display:grid; grid-template-columns: 2fr 1fr 1fr 1fr; border-bottom:1.2px solid #000; font-weight:800; font-size:8px;">
                    <div style="padding:4px 8px; border-right:1px solid #000;">SUBJECT</div>
                    <div style="padding:4px; border-right:1px solid #000; text-align:center;">CLASS</div>
                    <div style="padding:4px; border-right:1px solid #000; text-align:center;">EXAM</div>
                    <div style="padding:4px; text-align:center; background:rgba(0,0,0,0.05);">TOTAL</div>
                </div>
                <div style="display:grid; grid-template-columns: 2fr 1fr 1fr 1fr; font-weight:600; font-size:8px;">
                    <div style="padding:4px 8px; border-right:1px solid #000;">Language & Literacy</div>
                    <div style="padding:4px; border-right:1px solid #000; text-align:center;">45</div>
                    <div style="padding:4px; border-right:1px solid #000; text-align:center;">48</div>
                    <div style="padding:4px; text-align:center; font-weight:900;">93</div>
                </div>
            </div>

            <div style="margin-top:15px; display:flex; justify-content:space-between; align-items:flex-end;">
                <div style="font-size:7px; font-weight:700; color:#999;">© 2026 Academic Records System</div>
                <div style="text-align:right;">
                    <div style="width:40px; height:40px; border-radius:50%; background:#fcfbff; border:1px dashed var(--clr-border); margin:0 0 0 auto;"></div>
                    <div style="font-size:7px; font-weight:800; color:#888; margin-top:2px;">OFFICIAL VALIDATION</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── SIGNATURES & STAMPS SECTION ────────────────────────────── -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
    
    <!-- Signature Card -->
    <div class="card" style="padding: 1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
            <h3 style="margin:0; font-weight:800; font-size:1.05rem;">Headmaster Signature</h3>
            <?php if ($sigPath): ?>
                <button type="button" class="btn btn-ghost btn-xs text-red-600" onclick="confirmDelete('signature', 'remove the headmaster signature')" style="padding:2px 8px;">Remove</button>
            <?php endif; ?>
        </div>
        
        <div style="background:var(--clr-surface-2); border:1.5px dashed var(--clr-border); border-radius:var(--radius-md); padding:1.5rem; text-align:center; min-height:120px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
            <?php if ($sigPath): ?>
                <img src="<?= htmlspecialchars($sigPath) ?>?v=<?= time() ?>" alt="Signature" style="max-height:60px; max-width:100%; mix-blend-mode:multiply;">
            <?php else: ?>
                <div style="color:var(--clr-text-muted); text-align:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="24" height="24" style="margin-bottom:0.5rem; opacity:0.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    <div style="font-size:12px; font-weight:600;">No signature uploaded</div>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" action="<?= $base ?>/admin/settings" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <input type="hidden" name="type" value="signature">
            <div style="display:flex; gap:0.5rem; background:var(--clr-surface); border:1.5px solid var(--clr-border); border-radius:10px; padding:0.4rem 0.6rem;">
                <input type="file" name="image" accept=".png,.jpg,.jpeg" required style="font-size:11px; flex:1;">
                <button type="submit" class="btn btn-primary btn-sm" onclick="Loader.show()">Upload</button>
            </div>
        </form>
    </div>

    <!-- Stamp Card -->
    <div class="card" style="padding: 1.5rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.25rem;">
            <h3 style="margin:0; font-weight:800; font-size:1.05rem;">Official School Stamp</h3>
            <?php if ($stampPath): ?>
                <button type="button" class="btn btn-ghost btn-xs text-red-600" onclick="confirmDelete('stamp', 'remove the official school stamp')" style="padding:2px 8px;">Remove</button>
            <?php endif; ?>
        </div>
        
        <div style="background:var(--clr-surface-2); border:1.5px dashed var(--clr-border); border-radius:var(--radius-md); padding:1rem; text-align:center; min-height:120px; display:flex; align-items:center; justify-content:center; margin-bottom:1.25rem;">
            <?php if ($stampPath): ?>
                <img src="<?= htmlspecialchars($stampPath) ?>?v=<?= time() ?>" alt="Stamp" style="max-height:100px; max-width:100%; mix-blend-mode:multiply;">
            <?php else: ?>
                <div style="color:var(--clr-text-muted); text-align:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="24" height="24" style="margin-bottom:0.5rem; opacity:0.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <div style="font-size:12px; font-weight:600;">No stamp uploaded</div>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST" action="<?= $base ?>/admin/settings" enctype="multipart/form-data">
            <?= CSRF::field() ?>
            <input type="hidden" name="type" value="stamp">
            <div style="display:flex; gap:0.5rem; background:var(--clr-surface); border:1.5px solid var(--clr-border); border-radius:10px; padding:0.4rem 0.6rem;">
                <input type="file" name="image" accept=".png,.jpg,.jpeg" required style="font-size:11px; flex:1;">
                <button type="submit" class="btn btn-primary btn-sm" onclick="Loader.show()">Upload</button>
            </div>
        </form>
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

function togglePass(btn) {
    const input = btn.previousElementSibling;
    if (input.type === 'password') {
        input.type = 'text';
        btn.style.color = 'var(--clr-primary)';
    } else {
        input.type = 'password';
        btn.style.color = 'var(--clr-text-muted)';
    }
}

/**
 * Dynamic Branding Preview Controller
 */
document.addEventListener('DOMContentLoaded', () => {
    const inputs = {
        body: document.getElementById('input-school-body'),
        name: document.getElementById('input-school-name'),
        color: document.getElementById('input-accent-color')
    };

    const previews = {
        body: document.getElementById('preview-school-body'),
        name: document.getElementById('preview-school-name'),
        badge: document.getElementById('preview-badge'),
        hex: document.getElementById('color-hex-display')
    };

    if (inputs.body) {
        inputs.body.addEventListener('input', (e) => {
            previews.body.textContent = e.target.value.toUpperCase() || 'OFFICIAL STUDENT RECORD';
        });
    }

    if (inputs.name) {
        inputs.name.addEventListener('input', (e) => {
            previews.name.textContent = e.target.value.toUpperCase() || 'INSTITUTION NAME';
        });
    }

    if (inputs.color) {
        inputs.color.addEventListener('input', (e) => {
            const val = e.target.value;
            previews.name.style.color = val;
            previews.badge.style.backgroundColor = val;
            previews.hex.textContent = val.toUpperCase();
        });
    }
});
</script>
