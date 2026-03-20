<?php
/**
 * Teacher Dashboard Template
 * Uaddara Basic School — SBA Management System
 */

$pageTitle = 'Teacher Dashboard';
include __DIR__ . '/../layout/header.php';

global $activeTerm, $assignedBundles;
$base = defined('APP_BASE') ? APP_BASE : '';
$userName = Session::get('user_name', 'Teacher');
?>

<!-- ── Teacher Header ────────────────────────────────────────── -->
<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div>
    <h1 class="m-0" style="font-size:var(--text-3xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Hello, <?= htmlspecialchars($userName) ?> 👋</h1>
    <p class="text-muted m-0 mt-1" style="font-size:var(--text-sm);">
      Welcome back. Here is your overview for <strong><?= htmlspecialchars($activeTerm['year_name'] ?? 'Active') ?> · <?= htmlspecialchars($activeTerm['name'] ?? 'Term') ?></strong>.
    </p>
  </div>
  <div class="flex gap-3">
    <a href="<?= $base ?>/teacher/import" class="btn btn-outline" style="border-radius:var(--radius-full);">
       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
       Bulk Import
    </a>
  </div>
</div>

<?php if (empty($assignedBundles)): ?>
<!-- ── Empty State ────────────────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center animate-fade-in" style="padding:6rem 2rem; text-align:center; border-style:dashed;">
  <div style="background:var(--clr-surface-2); padding:2rem; border-radius:50%; margin-bottom:2rem; color:var(--clr-primary-300);">
     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 012-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text);">No Assignments Found</h2>
  <p class="text-muted" style="max-width:400px; margin:0 auto 2rem;">You haven't been assigned any classes or subjects for the current active term. Please contact the Administrator if this is an error.</p>
</div>

<?php else: ?>
<!-- ── Assignment Grid ────────────────────────────────────────── -->
<div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(340px, 1fr)); gap:1.5rem;">
  <?php foreach ($assignedBundles as $b): 
      $totalSt    = (int)$b['student_count'];
      $sbaDone    = (int)$b['sba_completed_count'];
      $examDone   = (int)$b['exam_completed_count'];
      $isComplete = ($totalSt > 0 && $sbaDone >= $totalSt && $examDone >= $totalSt);
      
      // Progress calculation
      $sbaPercent = ($totalSt > 0) ? round(($sbaDone / $totalSt) * 100) : 0;
      $examPercent = ($totalSt > 0) ? round(($examDone / $totalSt) * 100) : 0;
  ?>
  <div class="card hover-lift animate-fade-in" style="padding:0; overflow:hidden; border:1px solid var(--clr-border); display:flex; flex-direction:column;">
    
    <!-- Card Header -->
    <div style="padding:1.5rem; background:var(--clr-surface-2); border-bottom:1px solid var(--clr-border);">
      <div style="font-size:10px; font-weight:800; color:var(--clr-primary); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.5rem;">
        <?= htmlspecialchars($b['level_name']) ?>
      </div>
      <h3 style="margin:0; font-weight:800; font-size:1.25rem; color:var(--clr-text);">
        <?= htmlspecialchars($b['class_name']) ?><?= $b['section'] ? " ({$b['section']})" : '' ?>
      </h3>
      <p class="text-muted" style="font-size:14px; margin:0.25rem 0 0;"><?= htmlspecialchars($b['subject_name']) ?></p>
    </div>

    <!-- Progress Stats -->
    <div style="padding:1.5rem; flex:1;">
      <div class="mb-5">
        <div class="flex justify-between items-center mb-2">
          <span style="font-size:12px; font-weight:700; color:var(--clr-text);">SBA Components</span>
          <span style="font-size:12px; font-weight:800; color:<?= $sbaPercent >= 100 ? 'var(--clr-success)' : 'var(--clr-text)' ?>;"><?= $sbaPercent ?>%</span>
        </div>
        <div style="height:6px; background:var(--clr-border); border-radius:10px; overflow:hidden;">
          <div style="height:100%; width:<?= $sbaPercent ?>%; background:var(--clr-primary); border-radius:10px; transition:width 0.5s ease;"></div>
        </div>
        <div class="text-muted" style="font-size:10px; margin-top:0.4rem; font-weight:600;">
          <?= $sbaDone ?> of <?= $totalSt ?> students recorded
        </div>
      </div>

      <div>
        <div class="flex justify-between items-center mb-2">
          <span style="font-size:12px; font-weight:700; color:var(--clr-text);">End of Term Exam</span>
          <span style="font-size:12px; font-weight:800; color:<?= $examPercent >= 100 ? 'var(--clr-success)' : 'var(--clr-text)' ?>;"><?= $examPercent ?>%</span>
        </div>
        <div style="height:6px; background:var(--clr-border); border-radius:10px; overflow:hidden;">
          <div style="height:100%; width:<?= $examPercent ?>%; background:var(--clr-primary-300); border-radius:10px; transition:width 0.5s ease;"></div>
        </div>
        <div class="text-muted" style="font-size:10px; margin-top:0.4rem; font-weight:600;">
          <?= $examDone ?> of <?= $totalSt ?> students recorded
        </div>
      </div>
    </div>

    <!-- Action -->
    <div style="padding:1rem 1.5rem; border-top:1px solid var(--clr-border); background:var(--clr-surface-2);">
      <?php if ($b['is_locked']): ?>
        <div class="flex items-center gap-2 text-muted" style="font-size:12px; font-weight:700;">
           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
           LOCKED BY ADMIN
        </div>
      <?php else: ?>
        <a href="<?= $base ?>/teacher/scores?id=<?= $b['class_subject_id'] ?>" 
           class="btn <?= $isComplete ? 'btn-outline' : 'btn-primary' ?> w-full" 
           style="justify-content:center; font-size:12px; padding:10px;">
           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
           <?= $isComplete ? 'View / Edit Scores' : 'Enter Scores' ?>
        </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>
