<?php
/**
 * Admin Dashboard View
 * Uaddara Basic School — SBA Management System
 */
$pageTitle = 'Dashboard Overview';
include __DIR__ . '/../layout/header.php';

global $stats;
$base = defined('APP_BASE') ? APP_BASE : '';
$s = $stats ?? ['students' => 0, 'teachers' => 0, 'classes' => 0];
?>

<div class="stat-grid">
  <!-- Stat Card: Students -->
  <div class="stat-card">
    <div class="stat-icon" style="--stat-bg: var(--clr-success-bg); --stat-color: var(--clr-success);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
    </div>
    <div>
      <div class="stat-label">Total Students</div>
      <div class="stat-value"><?= number_format($s['students']) ?></div>
      <div class="stat-sub up">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="14" height="14"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd"/></svg>
        Active enrollment
      </div>
    </div>
  </div>

  <!-- Stat Card: Teachers -->
  <div class="stat-card">
    <div class="stat-icon" style="--stat-bg: var(--clr-info-bg); --stat-color: var(--clr-info);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </div>
    <div>
      <div class="stat-label">Teachers</div>
      <div class="stat-value"><?= number_format($s['teachers']) ?></div>
      <div class="stat-sub">Active teaching staff</div>
    </div>
  </div>

  <!-- Stat Card: Classes -->
  <div class="stat-card">
    <div class="stat-icon" style="--stat-bg: var(--clr-primary-50); --stat-color: var(--clr-primary-600);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    </div>
    <div>
      <div class="stat-label">Classes</div>
      <div class="stat-value"><?= number_format($s['classes']) ?></div>
      <div class="stat-sub">This academic year</div>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns: 2fr 1fr; gap:1.5rem;">
  <!-- Recent Activity -->
  <div class="card flex flex-col" style="padding:0; overflow:hidden;">
    <div class="card-header flex justify-between" style="padding:1.25rem 1.5rem; margin-bottom:0;">
      <h3 class="m-0" style="font-size:1.125rem; font-weight:700;">Pending Report Cards</h3>
      <a href="<?= $base ?>/admin/publish" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="card-body" style="padding:0;">
      <div class="flex flex-col items-center justify-center" style="padding:4rem 2rem; text-align:center; color:var(--clr-text-muted);">
        <div style="background:var(--clr-surface-2); padding:1.5rem; border-radius:var(--radius-full); margin-bottom:1.5rem;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="48" height="48" style="opacity:0.4;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h4 style="color:var(--clr-text); margin-bottom:0.5rem;">All Clear!</h4>
        <p style="max-width:300px; margin:0 auto 1.5rem;">No active terms or pending publish requests found at the moment.</p>
        <a href="<?= $base ?>/admin/terms" class="btn btn-primary btn-sm">Manage Terms</a>
      </div>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="card flex flex-col" style="padding:0; overflow:hidden;">
    <div class="card-header" style="padding:1.25rem 1.5rem; margin-bottom:0;">
      <h3 class="m-0" style="font-size:1.125rem; font-weight:700;">Quick Actions</h3>
    </div>
    <div class="card-body flex flex-col" style="gap:0.75rem; padding:1.5rem;">
      <a href="<?= $base ?>/admin/students" class="btn btn-outline w-full" style="justify-content:flex-start; padding:0.875rem 1rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" class="mr-2" style="opacity:0.7;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        Register New Student
      </a>
      <a href="<?= $base ?>/admin/classes" class="btn btn-outline w-full" style="justify-content:flex-start; padding:0.875rem 1rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" class="mr-2" style="opacity:0.7;"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        Manage Classes
      </a>
      <a href="<?= $base ?>/admin/sms" class="btn btn-outline w-full" style="justify-content:flex-start; padding:0.875rem 1rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" class="mr-2" style="opacity:0.7;"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        Send Broadcast SMS
      </a>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
