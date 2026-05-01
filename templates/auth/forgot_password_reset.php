<?php
/**
 * Forgot Password — Step 3: Set New Password
 */
$base = defined('APP_BASE') ? APP_BASE : '';

$schoolName  = Config::get('school_name',    'Uaddara Basic School');
$schoolBody  = Config::get('school_body',    'Armed Forces Education Unit');
$schoolLogo  = Config::get('school_logo',    '/assets/img/school-logo.png');
$accentColor = Config::get('brand_accent_color', '#9633cc');

if (!str_starts_with($schoolLogo, 'http') && !str_starts_with($schoolLogo, $base)) {
    $schoolLogo = $base . '/' . ltrim($schoolLogo, '/');
}
$logoVersion = $schoolLogo . '?v=' . time();

// Must have a verified reset token in session
$fpVerified = Session::get('fp_verified');
$fpUserId   = Session::get('fp_user_id');
if (!$fpVerified || !$fpUserId) {
    header('Location: ' . $base . '/forgot-password');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password — <?= htmlspecialchars($schoolName) ?> SBA</title>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoVersion) ?>">
  <style>
    :root { --clr-primary: <?= $accentColor ?>; --clr-primary-600: <?= $accentColor ?>; }
    .auth-left { background-color: var(--clr-primary, #9633cc); }

    .fp-stepper { display:flex; align-items:center; justify-content:center; gap:0; margin-bottom:2rem; }
    .fp-step { display:flex; flex-direction:column; align-items:center; gap:.35rem; position:relative; }
    .fp-step-circle { width:2rem; height:2rem; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.75rem; font-weight:700; border:2px solid var(--clr-border,#e2e8f0); background:var(--clr-surface,#fff); color:var(--clr-text-muted,#94a3b8); transition:all .2s ease; z-index:1; }
    .fp-step.active .fp-step-circle { background:var(--clr-primary,#9633cc); border-color:var(--clr-primary,#9633cc); color:#fff; box-shadow:0 0 0 4px color-mix(in srgb, var(--clr-primary) 20%, transparent); }
    .fp-step.done .fp-step-circle { background:var(--clr-success,#22c55e); border-color:var(--clr-success,#22c55e); color:#fff; }
    .fp-step-label { font-size:.65rem; font-weight:600; color:var(--clr-text-muted,#94a3b8); text-transform:uppercase; letter-spacing:.05em; white-space:nowrap; }
    .fp-step.active .fp-step-label { color:var(--clr-primary,#9633cc); }
    .fp-step.done .fp-step-label { color:var(--clr-success,#22c55e); }
    .fp-connector { width:3.5rem; height:2px; background:var(--clr-border,#e2e8f0); margin:0 .25rem; margin-bottom:1.4rem; flex-shrink:0; }
    .fp-connector.done { background:var(--clr-success,#22c55e); }

    .fp-icon-wrap { width:4rem; height:4rem; border-radius:50%; background:color-mix(in srgb, var(--clr-primary,#9633cc) 12%, transparent); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; color:var(--clr-primary,#9633cc); }

    /* Strength meter */
    .strength-bar { height:4px; border-radius:2px; background:var(--clr-border,#e2e8f0); margin-top:.5rem; overflow:hidden; }
    .strength-fill { height:100%; border-radius:2px; transition:width .3s ease, background .3s ease; width:0; }
    .strength-label { font-size:var(--text-xs); margin-top:.35rem; font-weight:600; }

    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

<div class="auth-page">

  <div class="auth-left">
    <div class="auth-brand">
      <img src="<?= htmlspecialchars($logoVersion) ?>" alt="<?= htmlspecialchars($schoolName) ?>" class="auth-brand-logo">
      <h1><?= nl2br(htmlspecialchars($schoolName)) ?></h1>
      <p><?= htmlspecialchars($schoolBody) ?></p>
      <div class="auth-features">
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          </div>
          Choose a strong password with 8+ characters
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          All sessions will be kept active after reset
        </div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-box">

      <!-- Stepper -->
      <div class="fp-stepper" role="list" aria-label="Reset steps">
        <div class="fp-step done" role="listitem">
          <div class="fp-step-circle">✓</div>
          <div class="fp-step-label">Email</div>
        </div>
        <div class="fp-connector done"></div>
        <div class="fp-step done" role="listitem">
          <div class="fp-step-circle">✓</div>
          <div class="fp-step-label">Verify</div>
        </div>
        <div class="fp-connector done"></div>
        <div class="fp-step active" role="listitem">
          <div class="fp-step-circle">3</div>
          <div class="fp-step-label">Reset</div>
        </div>
      </div>

      <div class="fp-icon-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
      </div>

      <h2 style="text-align:center;">Set New Password</h2>
      <p class="auth-sub" style="text-align:center;">Identity verified. Choose a strong new password for your account.</p>

      <?php if ($error = Session::flash('fp_reset_error')): ?>
        <div class="alert alert-danger" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= $base ?>/forgot-password/reset" id="fp-reset-form" novalidate>
        <?= CSRF::field() ?>

        <div class="form-group">
          <label class="form-label" for="new-password">
            New Password <span class="required">*</span>
          </label>
          <div style="position:relative;">
            <input
              type="password"
              id="new-password"
              name="new_password"
              class="form-control"
              placeholder="Min. 8 characters"
              minlength="8"
              autocomplete="new-password"
              required
              style="padding-right:2.75rem;"
            >
            <button type="button" class="pwd-toggle" onclick="togglePassword('new-password',this)" aria-label="Toggle password visibility" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--clr-text-muted);padding:0;">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
          <!-- Password strength meter -->
          <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
          <div class="strength-label" id="strength-label" style="color:var(--clr-text-muted);"></div>
        </div>

        <div class="form-group">
          <label class="form-label" for="confirm-password">
            Confirm New Password <span class="required">*</span>
          </label>
          <div style="position:relative;">
            <input
              type="password"
              id="confirm-password"
              name="confirm_password"
              class="form-control"
              placeholder="Repeat your new password"
              autocomplete="new-password"
              required
              style="padding-right:2.75rem;"
            >
            <button type="button" class="pwd-toggle" onclick="togglePassword('confirm-password',this)" aria-label="Toggle confirm password visibility" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--clr-text-muted);padding:0;">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </button>
          </div>
          <p class="form-text" id="match-hint" style="display:none;"></p>
        </div>

        <button type="submit" class="btn btn-primary w-full btn-lg" id="fp-reset-btn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          Reset Password
        </button>
      </form>

      <p style="text-align:center;margin-top:1.5rem;">
        <a href="<?= $base ?>/login" style="font-size:var(--text-sm);color:var(--clr-text-muted);">
          ← Back to login
        </a>
      </p>

      <p style="text-align:center;font-size:var(--text-xs);color:var(--clr-text-muted);margin-top:2rem;">
        © <?= date('Y') ?> <?= htmlspecialchars($schoolName) ?> &mdash; <?= htmlspecialchars($schoolBody) ?>
      </p>
    </div>
  </div>
</div>

<script src="<?= $base ?>/assets/js/app.js"></script>
<script>
// ── Password toggle ────────────────────────────────────────────
function togglePassword(inputId, btn) {
  const inp = document.getElementById(inputId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

// ── Password strength ──────────────────────────────────────────
const pwdInput = document.getElementById('new-password');
const fill     = document.getElementById('strength-fill');
const lbl      = document.getElementById('strength-label');

pwdInput.addEventListener('input', () => {
  const val = pwdInput.value;
  let score = 0;
  if (val.length >= 8) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;

  const map = [
    { pct: 0,   color: 'var(--clr-border,#e2e8f0)',        text: '',          clr: '' },
    { pct: 25,  color: 'var(--clr-danger,#ef4444)',         text: 'Weak',       clr: 'var(--clr-danger,#ef4444)' },
    { pct: 50,  color: '#f97316',                           text: 'Fair',       clr: '#f97316' },
    { pct: 75,  color: '#eab308',                           text: 'Good',       clr: '#eab308' },
    { pct: 100, color: 'var(--clr-success,#22c55e)',        text: 'Strong 💪',  clr: 'var(--clr-success,#22c55e)' },
  ];
  const m = map[score] || map[0];
  fill.style.width = m.pct + '%';
  fill.style.background = m.color;
  lbl.textContent = m.text;
  lbl.style.color = m.clr;
});

// ── Password match check ───────────────────────────────────────
const confirmInput = document.getElementById('confirm-password');
const matchHint    = document.getElementById('match-hint');

confirmInput.addEventListener('input', checkMatch);
pwdInput.addEventListener('input', checkMatch);

function checkMatch() {
  if (!confirmInput.value) { matchHint.style.display = 'none'; return; }
  matchHint.style.display = 'block';
  if (pwdInput.value === confirmInput.value) {
    matchHint.textContent = '✓ Passwords match';
    matchHint.style.color = 'var(--clr-success,#22c55e)';
    confirmInput.style.borderColor = 'var(--clr-success,#22c55e)';
  } else {
    matchHint.textContent = '✗ Passwords do not match';
    matchHint.style.color = 'var(--clr-danger,#ef4444)';
    confirmInput.style.borderColor = 'var(--clr-danger,#ef4444)';
  }
}

// ── Submit loading ─────────────────────────────────────────────
document.getElementById('fp-reset-form').addEventListener('submit', e => {
  if (pwdInput.value !== confirmInput.value) {
    e.preventDefault();
    matchHint.textContent = '✗ Passwords do not match';
    matchHint.style.display = 'block';
    matchHint.style.color = 'var(--clr-danger,#ef4444)';
    confirmInput.focus();
    return;
  }
  const btn = document.getElementById('fp-reset-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg style="animation:spin .8s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Updating…';
});
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

</body>
</html>
