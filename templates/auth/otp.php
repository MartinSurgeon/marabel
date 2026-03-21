<?php
/**
 * OTP Entry Page — Parent Login (Phone + OTP)
 * Uaddara Basic School — SBA Management System
 */
$base = defined('APP_BASE') ? APP_BASE : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OTP Verification — Uaddara Basic School</title>
  <meta name="description" content="Verify your identity with a one-time code to access the Parent Portal">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <link rel="icon" type="image/png" href="<?= $base ?>/assets/img/school-logo.png">
  <style>
    .otp-inputs { display:flex; gap:0.75rem; justify-content:center; margin:1.5rem 0; }
    .otp-input {
      width:56px; height:64px; text-align:center; font-size:1.75rem; font-weight:800;
      border:2px solid var(--clr-border); border-radius:var(--radius-md);
      color:var(--clr-text); background:var(--clr-surface);
      transition:border-color 0.2s, box-shadow 0.2s;
      outline:none;
    }
    .otp-input:focus { border-color:var(--clr-primary); box-shadow:0 0 0 3px rgba(105,43,196,0.15); }
    .otp-input.filled { border-color:var(--clr-primary); background:var(--clr-primary-50); }
    .countdown { font-size:var(--text-sm); color:var(--clr-text-muted); text-align:center; margin-top:1rem; }
    .countdown span { font-weight:800; color:var(--clr-primary); font-variant-numeric:tabular-nums; }
    .resend-btn { background:none; border:none; cursor:pointer; color:var(--clr-primary); font-weight:700; font-size:var(--text-sm); padding:0; }
    .resend-btn:disabled { color:var(--clr-text-muted); cursor:default; }
  </style>
</head>
<body>

<div class="auth-page">
  <!-- Left Panel -->
  <div class="auth-left">
    <div class="auth-brand">
      <img src="<?= $base ?>/assets/img/school-logo.png" alt="Uaddara Basic School Logo" class="auth-brand-logo">
      <h1>Uaddara<br>Basic School</h1>
      <p>Armed Forces Education Unit, Kumasi<br>School-Based Assessment System</p>
      <div class="auth-features">
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          Secure OTP — no passwords to remember
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          </div>
          Code delivered via SMS to your phone
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          View your child's report card instantly
        </div>
      </div>
    </div>
  </div>

  <!-- Right Panel -->
  <div class="auth-right">
    <div class="auth-box">

      <?php if ($error = Session::flash('otp_error')): ?>
        <div class="alert alert-danger" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($info = Session::flash('otp_info')): ?>
        <div class="alert alert-info" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?= htmlspecialchars($info) ?>
        </div>
      <?php endif; ?>

      <div style="text-align:center; margin-bottom:2rem;">
        <div style="width:64px; height:64px; background:var(--clr-primary-50); border-radius:var(--radius-full); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="32" height="32" style="color:var(--clr-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
        </div>
        <h2 style="margin:0 0 0.5rem; font-size:1.5rem; font-weight:800;">Check Your Phone</h2>
        <p class="auth-sub" style="margin:0;">
          Enter the 6-digit code sent to<br>
          <strong style="color:var(--clr-text);"><?= htmlspecialchars(Session::get('otp_phone', 'your phone')) ?></strong>
        </p>
      </div>

      <form method="POST" action="<?= $base ?>/otp" id="otp-form" autocomplete="off">
        <?= CSRF::field() ?>
        <input type="hidden" name="role_type" value="parent_verify">
        <input type="hidden" name="otp" id="otp-hidden" value="">

        <!-- 6 individual OTP digit boxes -->
        <div class="otp-inputs" id="otp-boxes" role="group" aria-label="One-time code digits">
          <?php for ($i = 1; $i <= 6; $i++): ?>
          <input
            type="text"
            maxlength="1"
            class="otp-input"
            id="otp-<?= $i ?>"
            inputmode="numeric"
            pattern="[0-9]"
            autocomplete="one-time-code"
            aria-label="Digit <?= $i ?>"
          >
          <?php endfor; ?>
        </div>

        <button type="submit" class="btn btn-primary w-full btn-lg" id="otp-submit" disabled>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Verify & Sign In
        </button>
      </form>

      <div class="countdown" id="countdown-area">
        Code expires in <span id="countdown-timer">05:00</span>
      </div>

      <div style="text-align:center; margin-top:1.25rem;">
        <form method="POST" action="<?= $base ?>/otp" style="display:inline;" id="resend-form">
          <?= CSRF::field() ?>
          <input type="hidden" name="role_type" value="parent">
          <input type="hidden" name="phone" value="<?= htmlspecialchars(Session::get('otp_phone', '')) ?>">
          Didn't get it? &nbsp;
          <button type="submit" class="resend-btn" id="resend-btn" disabled>Resend Code</button>
        </form>
      </div>

      <div style="text-align:center; margin-top:1.5rem;">
        <a href="<?= $base ?>/login" style="font-size:var(--text-sm); color:var(--clr-text-muted);">← Back to login</a>
      </div>

    </div>
  </div>
</div>

<script>
// OTP digit navigation
const inputs = document.querySelectorAll('.otp-input');
const hiddenField = document.getElementById('otp-hidden');
const submitBtn   = document.getElementById('otp-submit');

function updateHidden() {
  let code = '';
  inputs.forEach(i => code += i.value);
  hiddenField.value = code;
  submitBtn.disabled = code.length < 6;
  inputs.forEach(i => i.classList.toggle('filled', i.value !== ''));
}

inputs.forEach((input, idx) => {
  input.addEventListener('input', (e) => {
    const val = e.target.value.replace(/[^0-9]/g, '');
    e.target.value = val.slice(-1);
    if (val && idx < inputs.length - 1) inputs[idx + 1].focus();
    updateHidden();
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && !e.target.value && idx > 0) {
      inputs[idx - 1].focus();
      inputs[idx - 1].value = '';
      updateHidden();
    }
  });

  input.addEventListener('paste', (e) => {
    e.preventDefault();
    const paste = (e.clipboardData.getData('text') || '').replace(/[^0-9]/g, '').slice(0, 6);
    paste.split('').forEach((ch, i) => { if (inputs[i]) inputs[i].value = ch; });
    if (inputs[Math.min(paste.length, 5)]) inputs[Math.min(paste.length, 5)].focus();
    updateHidden();
  });
});

// Auto-focus first input
inputs[0].focus();

// Countdown timer (5 min)
let seconds = 300;
const timerEl = document.getElementById('countdown-timer');
const resendBtn = document.getElementById('resend-btn');
const tick = setInterval(() => {
  seconds--;
  const m = Math.floor(seconds / 60).toString().padStart(2, '0');
  const s = (seconds % 60).toString().padStart(2, '0');
  timerEl.textContent = `${m}:${s}`;
  if (seconds <= 0) {
    clearInterval(tick);
    timerEl.textContent = 'Expired';
    timerEl.style.color = '#ef4444';
    resendBtn.disabled = false;
    submitBtn.disabled = true;
  }
}, 1000);
</script>
</body>
</html>
