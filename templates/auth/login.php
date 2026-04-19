<?php
/**
 * Login Page — 3-tab design (Admin/Teacher | Parent | Student)
 * Dynamic Branding — Pulls from System Settings
 */
$base = defined('APP_BASE') ? APP_BASE : '';

// ── Fetch Branding Settings ─────────────────────────────────────
$schoolName   = Config::get('school_name',    'Uaddara Basic School');
$schoolBody   = Config::get('school_body',    'Armed Forces Education Unit');
$schoolTag    = Config::get('school_tagline', 'SBA Management System');
$schoolLogo   = Config::get('school_logo',    '/assets/img/school-logo.png');
$accentColor  = Config::get('brand_accent_color', '#9633cc'); // default rebecca purple

// Ensure logo path is correct and add cache buster
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
  <title>Login — <?= htmlspecialchars($schoolName) ?> SBA</title>
  <meta name="description" content="<?= htmlspecialchars($schoolTag) ?>">
      <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <link rel="icon" type="image/png" href="<?= htmlspecialchars($logoVersion) ?>">
  
  <style>
    :root {
      --clr-primary: <?= $accentColor ?>;
      --clr-primary-600: <?= $accentColor ?>;
    }
    /* Simple overlay to help text readability if primary color is very light */
    .auth-left { background-color: var(--clr-primary, #9633cc); }
  </style>
</head>
<body>

<div class="auth-page">

  <!-- ── Left Panel ─────────────────────────────────────────────── -->
  <div class="auth-left">
    <div class="auth-brand">
    <img src="<?= htmlspecialchars($logoVersion) ?>" alt="<?= htmlspecialchars($schoolName) ?>" class="auth-brand-logo">
      <h1><?= nl2br(htmlspecialchars($schoolName)) ?></h1>
      <p><?= htmlspecialchars($schoolBody) ?><br><?= htmlspecialchars($schoolTag) ?></p>

      <div class="auth-features">
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          Instant digital report cards for all classes
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          </div>
          Level of Proficiency powered analytics
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
          </div>
          SMS notifications to parents on publish
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          Secure, role-based access for all staff
        </div>
      </div>
    </div>
  </div>

  <!-- ── Right Panel ────────────────────────────────────────────── -->
  <div class="auth-right">
    <div class="auth-box">
      <h2>Welcome back</h2>
      <p class="auth-sub">Sign in to your account to continue</p>

      <?php if ($error = Session::flash('login_error')): ?>
        <div class="alert alert-danger" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if (isset($_GET['timeout'])): ?>
        <div class="alert alert-warning" role="alert">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Your session expired due to inactivity. Please log in again.
        </div>
      <?php endif; ?>

      <!-- Role tabs -->
      <div class="role-tabs" role="tablist" aria-label="Select your role">
        <button class="role-tab active" id="tab-staff"   role="tab" aria-selected="true"  data-target="form-staff"   aria-controls="form-staff"   type="button">Admin / Teacher</button>
        <button class="role-tab"        id="tab-parent"  role="tab" aria-selected="false" data-target="form-parent"  aria-controls="form-parent"  type="button">Parent</button>
        <button class="role-tab"        id="tab-student" role="tab" aria-selected="false" data-target="form-student" aria-controls="form-student" type="button">Student</button>
      </div>

      <!-- ── Staff Login (Admin + Teacher) ──────────────────────── -->
      <div id="form-staff" class="login-form" role="tabpanel" aria-labelledby="tab-staff">
        <form method="POST" action="<?= $base ?>/login" id="staff-form" novalidate>
          <?= CSRF::field() ?>
          <input type="hidden" name="role_type" value="staff">

          <div class="form-group">
            <label class="form-label" for="staff-email">
              Email Address <span class="required">*</span>
            </label>
            <input
              type="email"
              id="staff-email"
              name="email"
              class="form-control"
              placeholder="yourname@school.edu.gh"
              autocomplete="email"
              required
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="staff-password">
              Password <span class="required">*</span>
            </label>
            <div style="position:relative;">
              <input
                type="password"
                id="staff-password"
                name="password"
                class="form-control"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
                style="padding-right: 2.75rem;"
              >
              <button type="button" class="pwd-toggle" onclick="togglePassword('staff-password',this)" aria-label="Toggle password visibility" style="position:absolute;right:.75rem;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--clr-text-muted);padding:0;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </button>
            </div>
          </div>

          <div class="flex" style="justify-content:flex-end;margin-bottom:1.25rem;">
            <a href="<?= $base ?>/forgot-password" style="font-size:var(--text-sm);">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn-primary w-full btn-lg" id="staff-submit">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
            Sign In
          </button>
        </form>
      </div>

      <!-- ── Parent Login (Phone + OTP) ────────────────────────── -->
      <div id="form-parent" class="login-form" role="tabpanel" aria-labelledby="tab-parent" style="display:none;">
        <form method="POST" action="<?= $base ?>/otp" id="parent-form" novalidate>
          <?= CSRF::field() ?>
          <input type="hidden" name="role_type" value="parent">

          <div class="alert alert-info" style="margin-bottom:1.25rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            We'll send a one-time code to your registered phone number.
          </div>

          <div class="form-group">
            <label class="form-label" for="parent-phone">
              Phone Number <span class="required">*</span>
            </label>
            <input
              type="tel"
              id="parent-phone"
              name="phone"
              class="form-control"
              placeholder="0241234567"
              autocomplete="tel"
              required
              inputmode="numeric"
              value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
            >
            <p class="form-text">Enter the phone number registered with the school.</p>
          </div>

          <button type="submit" class="btn btn-primary w-full btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Send OTP Code
          </button>
        </form>
      </div>

      <!-- ── Student Login (ID + PIN) ──────────────────────────── -->
      <div id="form-student" class="login-form" role="tabpanel" aria-labelledby="tab-student" style="display:none;">
        <form method="POST" action="<?= $base ?>/login" id="student-form" novalidate>
          <?= CSRF::field() ?>
          <input type="hidden" name="role_type" value="student">

          <div class="form-group">
            <label class="form-label" for="student-id">
              Student ID <span class="required">*</span>
            </label>
            <input
              type="text"
              id="student-id"
              name="student_id"
              class="form-control"
              placeholder="e.g. UBS2024001"
              autocomplete="username"
              required
              value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="student-pin">
              4-Digit PIN <span class="required">*</span>
            </label>
            <input
              type="password"
              id="student-pin"
              name="pin"
              class="form-control"
              placeholder="••••"
              inputmode="numeric"
              maxlength="4"
              pattern="\d{4}"
              autocomplete="current-password"
              required
              style="letter-spacing:.5em; font-size:var(--text-lg); text-align:center; font-family:var(--font-mono);"
            >
          </div>

          <button type="submit" class="btn btn-primary w-full btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            View My Results
          </button>
        </form>
      </div>

      <p style="text-align:center;font-size:var(--text-xs);color:var(--clr-text-muted);margin-top:2rem;">
        © <?= date('Y') ?> <?= htmlspecialchars($schoolName) ?> &mdash; <?= htmlspecialchars($schoolBody) ?>
      </p>
    </div>
  </div>
</div>

<!-- Toast container for notifications -->
<div id="toast-container" aria-live="polite" aria-atomic="true"></div>

<script src="<?= $base ?>/assets/js/app.js"></script>
<script>
// ── Role tab switching ─────────────────────────────────────────
document.querySelectorAll('.role-tab').forEach(tab => {
  tab.addEventListener('click', () => {
    // Update tabs
    document.querySelectorAll('.role-tab').forEach(t => {
      t.classList.remove('active');
      t.setAttribute('aria-selected', 'false');
    });
    tab.classList.add('active');
    tab.setAttribute('aria-selected', 'true');

    // Show/hide forms
    document.querySelectorAll('.login-form').forEach(f => { f.style.display = 'none'; });
    document.getElementById(tab.dataset.target).style.display = 'block';
  });
});

// ── Password visibility toggle ─────────────────────────────────
function togglePassword(inputId, btn) {
  const inp = document.getElementById(inputId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

// ── Loading state on form submit ───────────────────────────────
document.querySelectorAll('.login-form form').forEach(form => {
  form.addEventListener('submit', e => {
    const btn = form.querySelector('[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<svg style="animation:spin .8s linear infinite" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Signing in…';
  });
});

// ── PIN input: numbers only ────────────────────────────────────
const pinInput = document.getElementById('student-pin');
if (pinInput) {
  pinInput.addEventListener('input', e => {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
  });
}
</script>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

</body>
</html>
