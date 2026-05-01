<?php
/**
 * Forgot Password — Step 1: Enter Email
 * Staff (Admin / Teacher) only
 */
$base = defined('APP_BASE') ? APP_BASE : '';

$schoolName  = Config::get('school_name',    'Uaddara Basic School');
$schoolBody  = Config::get('school_body',    'Armed Forces Education Unit');
$schoolTag   = Config::get('school_tagline', 'SBA Management System');
$schoolLogo  = Config::get('school_logo',    '/assets/img/school-logo.png');
$accentColor = Config::get('brand_accent_color', '#9633cc');

if (!str_starts_with($schoolLogo, 'http') && !str_starts_with($schoolLogo, $base)) {
    $schoolLogo = $base . '/' . ltrim($schoolLogo, '/');
}
$logoVersion = $schoolLogo . '?v=' . time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password — <?= htmlspecialchars($schoolName) ?> SBA</title>
  <meta name="description" content="Reset your staff account password via SMS verification.">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoVersion) ?>">
  <style>
    :root { --clr-primary: <?= $accentColor ?>; --clr-primary-600: <?= $accentColor ?>; }
    .auth-left { background-color: var(--clr-primary, #9633cc); }

    /* ── Stepper ─────────────────────────────────────────── */
    .fp-stepper {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0;
      margin-bottom: 2rem;
    }
    .fp-step {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: .35rem;
      position: relative;
    }
    .fp-step-circle {
      width: 2rem;
      height: 2rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: .75rem;
      font-weight: 700;
      border: 2px solid var(--clr-border, #e2e8f0);
      background: var(--clr-surface, #fff);
      color: var(--clr-text-muted, #94a3b8);
      transition: all .2s ease;
      z-index: 1;
    }
    .fp-step.active .fp-step-circle {
      background: var(--clr-primary, #9633cc);
      border-color: var(--clr-primary, #9633cc);
      color: #fff;
      box-shadow: 0 0 0 4px color-mix(in srgb, var(--clr-primary) 20%, transparent);
    }
    .fp-step.done .fp-step-circle {
      background: var(--clr-success, #22c55e);
      border-color: var(--clr-success, #22c55e);
      color: #fff;
    }
    .fp-step-label {
      font-size: .65rem;
      font-weight: 600;
      color: var(--clr-text-muted, #94a3b8);
      text-transform: uppercase;
      letter-spacing: .05em;
      white-space: nowrap;
    }
    .fp-step.active .fp-step-label { color: var(--clr-primary, #9633cc); }
    .fp-step.done .fp-step-label { color: var(--clr-success, #22c55e); }
    .fp-connector {
      width: 3.5rem;
      height: 2px;
      background: var(--clr-border, #e2e8f0);
      margin: 0 .25rem;
      margin-bottom: 1.4rem;
      flex-shrink: 0;
    }
    .fp-connector.done { background: var(--clr-success, #22c55e); }

    .fp-icon-wrap {
      width: 4rem;
      height: 4rem;
      border-radius: 50%;
      background: color-mix(in srgb, var(--clr-primary, #9633cc) 12%, transparent);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.25rem;
      color: var(--clr-primary, #9633cc);
    }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>

<div class="auth-page">

  <!-- ── Left Panel ───────────────────────────────────────────── -->
  <div class="auth-left">
    <div class="auth-brand">
      <img src="<?= htmlspecialchars($logoVersion) ?>" alt="<?= htmlspecialchars($schoolName) ?>" class="auth-brand-logo">
      <h1><?= nl2br(htmlspecialchars($schoolName)) ?></h1>
      <p><?= htmlspecialchars($schoolBody) ?><br><?= htmlspecialchars($schoolTag) ?></p>

      <div class="auth-features">
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          </div>
          Secure SMS-based identity verification
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          </div>
          OTP sent to your registered phone number
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          Password changes are logged for security
        </div>
      </div>
    </div>
  </div>

  <!-- ── Right Panel ──────────────────────────────────────────── -->
  <div class="auth-right">
    <div class="auth-box">

      <!-- Stepper -->
      <div class="fp-stepper" role="list" aria-label="Reset steps">
        <div class="fp-step active" role="listitem">
          <div class="fp-step-circle">1</div>
          <div class="fp-step-label">Email</div>
        </div>
        <div class="fp-connector"></div>
        <div class="fp-step" role="listitem">
          <div class="fp-step-circle">2</div>
          <div class="fp-step-label">Verify</div>
        </div>
        <div class="fp-connector"></div>
        <div class="fp-step" role="listitem">
          <div class="fp-step-circle">3</div>
          <div class="fp-step-label">Reset</div>
        </div>
      </div>

      <!-- Icon -->
      <div class="fp-icon-wrap">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
      </div>

      <h2 style="text-align:center;">Forgot Password?</h2>
      <p class="auth-sub" style="text-align:center;">Enter your staff email address and we'll send a verification code to your registered phone.</p>

      <?php if ($error = Session::flash('fp_error')): ?>
        <div class="alert alert-danger" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($info = Session::flash('fp_info')): ?>
        <div class="alert alert-info" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <?= htmlspecialchars($info) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= $base ?>/forgot-password" id="fp-email-form" novalidate>
        <?= CSRF::field() ?>
        <input type="hidden" name="fp_step" value="request">

        <div class="form-group">
          <label class="form-label" for="fp-email">
            Staff Email Address <span class="required">*</span>
          </label>
          <input
            type="email"
            id="fp-email"
            name="email"
            class="form-control"
            placeholder="yourname@school.edu.gh"
            autocomplete="email"
            required
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          >
          <p class="form-text">Must match the email registered to your staff account.</p>
        </div>

        <button type="submit" class="btn btn-primary w-full btn-lg" id="fp-submit">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          Send Verification Code
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
document.getElementById('fp-email-form').addEventListener('submit', e => {
  const btn = document.getElementById('fp-submit');
  btn.disabled = true;
  btn.innerHTML = '<svg style="animation:spin .8s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Sending…';
});
</script>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

</body>
</html>
