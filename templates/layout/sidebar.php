<?php
/**
 * Sidebar Layout Partial
 * Uaddara Basic School — SBA Management System
 */
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$role = Session::role();
$base = defined('APP_BASE') ? APP_BASE : '';

function navActive(string $path): string {
    global $currentPath;
    return str_starts_with($currentPath, $path) ? ' active' : '';
}
?>
<nav class="sidebar" id="sidebar" aria-label="Main navigation">
  <!-- Mobile close button -->
  <button
    class="sidebar-close"
    id="sidebar-close-btn"
    aria-label="Close navigation"
    onclick="Sidebar.close()"
  >
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
  </button>

  <!-- Logo / School brand -->
  <div class="sidebar-logo">
    <img src="<?= $base ?>/assets/img/school-logo.png" alt="Uaddara Basic School">
    <div>
      <div class="school-name">Uaddara Basic School</div>
      <div class="school-sub">SBA System</div>
    </div>
  </div>

  <div class="sidebar-nav">

    <?php if ($role === 'admin'): ?>
    <!-- ── Admin Navigation ─────────────────────────────── -->
    <div class="nav-section-label">Overview</div>
    <a href="<?= $base ?>/admin" class="nav-item<?= navActive($base . '/admin') ?>" aria-label="Dashboard">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
      Dashboard
    </a>

    <div class="nav-section-label">Academic</div>
    <a href="<?= $base ?>/admin/years"    class="nav-item<?= navActive($base . '/admin/years') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      Academic Years & Terms
    </a>
    <a href="<?= $base ?>/admin/classes"  class="nav-item<?= navActive($base . '/admin/classes') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      Classes & Sections
    </a>
    <a href="<?= $base ?>/admin/subjects" class="nav-item<?= navActive($base . '/admin/subjects') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
      Subjects
    </a>

    <div class="nav-section-label">People</div>
    <a href="<?= $base ?>/admin/teachers" class="nav-item<?= navActive($base . '/admin/teachers') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
      Teachers
    </a>
    <a href="<?= $base ?>/admin/students" class="nav-item<?= navActive($base . '/admin/students') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
      Students
    </a>

    <div class="nav-section-label">Reports</div>
    <a href="<?= $base ?>/admin/publish" class="nav-item<?= navActive($base . '/admin/publish') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      Publish Reports
    </a>
    <a href="<?= $base ?>/admin/promotions" class="nav-item<?= navActive($base . '/admin/promotions') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
      Promotions
    </a>

    <div class="nav-section-label">Communication</div>
    <a href="<?= $base ?>/admin/sms" class="nav-item<?= navActive($base . '/admin/sms') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
      SMS Centre
    </a>

    <?php elseif ($role === 'teacher'): ?>
    <!-- ── Teacher Navigation ───────────────────────────── -->
    <div class="nav-section-label">Overview</div>
    <a href="<?= $base ?>/teacher" class="nav-item<?= navActive($base . '/teacher') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
      My Dashboard
    </a>

    <div class="nav-section-label">Score Entry</div>
    <a href="<?= $base ?>/teacher/scores" class="nav-item<?= navActive($base . '/teacher/scores') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
      Enter Scores
    </a>
    <a href="<?= $base ?>/teacher/import" class="nav-item<?= navActive($base . '/teacher/import') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
      Import from Excel
    </a>

    <?php elseif ($role === 'parent'): ?>
    <!-- ── Parent Navigation ────────────────────────────── -->
    <div class="nav-section-label">My Children</div>
    <a href="<?= $base ?>/parent" class="nav-item<?= navActive($base . '/parent') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
      Dashboard
    </a>

    <?php elseif ($role === 'student'): ?>
    <!-- ── Student Navigation ───────────────────────────── -->
    <div class="nav-section-label">My Results</div>
    <a href="<?= $base ?>/student" class="nav-item<?= navActive($base . '/student') ?>">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
      My Report Card
    </a>
    <?php endif; ?>

  </div><!-- /sidebar-nav -->

  <!-- User footer -->
  <div class="sidebar-footer">
    <div class="user-avatar" aria-hidden="true">
      <?= strtoupper(substr(Session::get('user_name', '?'), 0, 1)) ?>
    </div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars(Session::get('user_name', 'User')) ?></div>
      <div class="user-role"><?= ucfirst(Session::role() ?? '') ?></div>
    </div>
    <a href="<?= $base ?>/logout" title="Sign out" style="color:rgba(255,255,255,.5);transition:color .15s" aria-label="Sign out">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
    </a>
  </div>
</nav>

<!-- Sidebar backdrop (created by JS but we pre-declare for clarity) -->
<div id="sidebar-backdrop" class="sidebar-backdrop" aria-hidden="true"></div>
