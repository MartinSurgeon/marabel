<?php
/**
 * Forgot Password — Step 2: Verify OTP
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

// Must have a pending reset in session
$fpEmail = Session::get('fp_email');
$fpPhone = Session::get('fp_masked_phone');
if (!$fpEmail) {
    header('Location: ' . $base . '/forgot-password');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Code — <?= htmlspecialchars($schoolName) ?> SBA</title>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoVersion) ?>">
  <style>
    :root { --clr-primary: <?= $accentColor ?>; --clr-primary-600: <?= $accentColor ?>; }
    .auth-left { background-color: var(--clr-primary, #9633cc); }

    .fp-stepper { display:flex; align-items:center; justify-content:center; gap:0; margin-bottom:2rem; }
    .fp-step { display:flex; flex-direction:column; align-items:center; gap:.35rem; position:relative; }
    .fp-step-circle {
      width:2rem; height:2rem; border-radius:50%; display:flex; align-items:center;
      justify-content:center; font-size:.75rem; font-weight:700;
      border:2px solid var(--clr-border,#e2e8f0); background:var(--clr-surface,#fff);
      color:var(--clr-text-muted,#94a3b8); transition:all .2s ease; z-index:1;
    }
    .fp-step.active .fp-step-circle { background:var(--clr-primary,#9633cc); border-color:var(--clr-primary,#9633cc); color:#fff; box-shadow:0 0 0 4px color-mix(in srgb, var(--clr-primary) 20%, transparent); }
    .fp-step.done .fp-step-circle { background:var(--clr-success,#22c55e); border-color:var(--clr-success,#22c55e); color:#fff; }
    .fp-step-label { font-size:.65rem; font-weight:600; color:var(--clr-text-muted,#94a3b8); text-transform:uppercase; letter-spacing:.05em; white-space:nowrap; }
    .fp-step.active .fp-step-label { color:var(--clr-primary,#9633cc); }
    .fp-step.done .fp-step-label { color:var(--clr-success,#22c55e); }
    .fp-connector { width:3.5rem; height:2px; background:var(--clr-border,#e2e8f0); margin:0 .25rem; margin-bottom:1.4rem; flex-shrink:0; }
    .fp-connector.done { background:var(--clr-success,#22c55e); }

    .fp-icon-wrap { width:4rem; height:4rem; border-radius:50%; background:color-mix(in srgb, var(--clr-primary,#9633cc) 12%, transparent); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; color:var(--clr-primary,#9633cc); }

    /* OTP input boxes */
    .otp-row { display:flex; gap:.6rem; justify-content:center; margin:1.5rem 0; }
    .otp-box {
      width:3rem; height:3.5rem;
      border:2px solid var(--clr-border,#e2e8f0);
      border-radius:.75rem;
      text-align:center;
      font-size:1.5rem;
      font-weight:800;
      font-family:var(--font-mono,'monospace');
      background:var(--clr-surface,#fff);
      color:var(--clr-text,#1e293b);
      transition:border-color .2s, box-shadow .2s;
      outline:none;
    }
    .otp-box:focus { border-color:var(--clr-primary,#9633cc); box-shadow:0 0 0 4px color-mix(in srgb, var(--clr-primary) 20%, transparent); }
    .otp-box.filled { border-color:var(--clr-primary,#9633cc); }

    /* Countdown timer */
    .countdown-wrap { text-align:center; font-size:var(--text-sm); color:var(--clr-text-muted,#94a3b8); margin-bottom:1.5rem; }
    .countdown-num { font-weight:700; color:var(--clr-primary,#9633cc); }
    .countdown-expired { color:var(--clr-danger,#ef4444); font-weight:600; }

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
          OTP expires in 10 minutes for your security
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          Never share your OTP with anyone
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
        <div class="fp-step active" role="listitem">
          <div class="fp-step-circle">2</div>
          <div class="fp-step-label">Verify</div>
        </div>
        <div class="fp-connector"></div>
        <div class="fp-step" role="listitem">
          <div class="fp-step-circle">3</div>
          <div class="fp-step-label">Reset</div>
        </div>
      </div>

      <div class="fp-icon-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
        </svg>
      </div>

      <h2 style="text-align:center;">Check Your Phone</h2>
      <p class="auth-sub" style="text-align:center;">
        We sent a 6-digit code to <strong><?= htmlspecialchars($fpPhone ?? '•••') ?></strong>.<br>
        Enter it below to continue.
      </p>

      <?php if ($error = Session::flash('fp_otp_error')): ?>
        <div class="alert alert-danger" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= $base ?>/forgot-password/verify" id="fp-verify-form" novalidate>
        <?= CSRF::field() ?>
        <!-- Hidden concatenated OTP value submitted on form submit -->
        <input type="hidden" name="otp" id="otp-hidden">

        <div class="otp-row" role="group" aria-label="One-time code digits">
          <?php for ($i = 1; $i <= 6; $i++): ?>
            <input
              type="text"
              class="otp-box"
              id="otp-<?= $i ?>"
              maxlength="1"
              inputmode="numeric"
              pattern="\d"
              autocomplete="<?= $i === 1 ? 'one-time-code' : 'off' ?>"
              aria-label="Digit <?= $i ?>"
            >
          <?php endfor; ?>
        </div>

        <!-- Countdown timer -->
        <div class="countdown-wrap" id="countdown-wrap">
          Code expires in <span class="countdown-num" id="countdown-display">10:00</span>
        </div>

        <button type="submit" class="btn btn-primary w-full btn-lg" id="fp-verify-btn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Verify Code
        </button>
      </form>

      <!-- Resend -->
      <p style="text-align:center;margin-top:1.25rem;font-size:var(--text-sm);">
        Didn't receive a code?
        <a href="<?= $base ?>/forgot-password" style="font-weight:600;">Try again</a>
      </p>

      <p style="text-align:center;margin-top:.75rem;">
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
// ── OTP box navigation ─────────────────────────────────────────
const boxes = document.querySelectorAll('.otp-box');
const hiddenOtp = document.getElementById('otp-hidden');

boxes.forEach((box, i) => {
  box.addEventListener('input', e => {
    const val = e.target.value.replace(/\D/g, '').slice(-1);
    e.target.value = val;
    e.target.classList.toggle('filled', val !== '');
    if (val && i < boxes.length - 1) boxes[i + 1].focus();
    syncHidden();
  });

  box.addEventListener('keydown', e => {
    if (e.key === 'Backspace' && !e.target.value && i > 0) {
      boxes[i - 1].focus();
      boxes[i - 1].value = '';
      boxes[i - 1].classList.remove('filled');
      syncHidden();
    }
  });

  // Handle paste on any box
  box.addEventListener('paste', e => {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
    pasted.split('').forEach((ch, idx) => {
      if (boxes[idx]) {
        boxes[idx].value = ch;
        boxes[idx].classList.add('filled');
      }
    });
    const nextEmpty = pasted.length < boxes.length ? pasted.length : boxes.length - 1;
    boxes[nextEmpty].focus();
    syncHidden();
  });
});

function syncHidden() {
  hiddenOtp.value = Array.from(boxes).map(b => b.value).join('');
}

// Auto-submit when 6 digits entered
function checkAutoSubmit() {
  const code = Array.from(boxes).map(b => b.value).join('');
  if (code.length === 6) {
    syncHidden();
    setTimeout(() => document.getElementById('fp-verify-form').requestSubmit(), 200);
  }
}
boxes.forEach(b => b.addEventListener('input', checkAutoSubmit));

// Focus first box on load
boxes[0].focus();

// ── Countdown timer (10 min = 600 s) ──────────────────────────
let remaining = 600;
const display = document.getElementById('countdown-display');
const wrap = document.getElementById('countdown-wrap');

const tick = setInterval(() => {
  remaining--;
  if (remaining <= 0) {
    clearInterval(tick);
    wrap.innerHTML = '<span class="countdown-expired">⚠ Code has expired. Please <a href="<?= $base ?>/forgot-password">request a new one</a>.</span>';
    document.getElementById('fp-verify-btn').disabled = true;
    return;
  }
  const m = String(Math.floor(remaining / 60)).padStart(2, '0');
  const s = String(remaining % 60).padStart(2, '0');
  display.textContent = `${m}:${s}`;
  if (remaining <= 60) display.style.color = 'var(--clr-danger, #ef4444)';
}, 1000);

// ── Form submit loading state ──────────────────────────────────
document.getElementById('fp-verify-form').addEventListener('submit', e => {
  const btn = document.getElementById('fp-verify-btn');
  btn.disabled = true;
  btn.innerHTML = '<svg style="animation:spin .8s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Verifying…';
});
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

</body>
</html>
