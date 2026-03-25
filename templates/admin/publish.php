<?php
/**
 * Result Publishing View — v2 (Robust)
 * HCI/UX: Rich class cards with student counts, score coverage, warnings, and step-by-step flow.
 */
$pageTitle = 'Result Publishing';
include __DIR__ . '/../layout/header.php';

global $activeTerm, $classesProgress, $activeYear;
$base    = defined('APP_BASE') ? APP_BASE : '';
$classes = $classesProgress ?? [];
$term    = $activeTerm;

// Summary stats
$totalClasses    = count($classes);
$publishedCount  = count(array_filter($classes, fn($c) => $c['is_published']));
$lockedCount     = count(array_filter($classes, fn($c) => !$c['is_published'] && ($c['locked_subjects'] >= $c['total_subjects'] && $c['total_subjects'] > 0)));
?>

<!-- ── Page Header ──────────────────────────────────────────────── -->
<div class="flex justify-between items-center mb-6 gap-4 flex-wrap">
  <div style="flex:1; min-width:260px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Result Publishing</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:600px;">
      Finalise termly results: lock score entry, compute positions &amp; proficiency levels, then make report cards visible to parents and students.
    </p>
  </div>
  <?php if ($term): ?>
  <div style="display:flex; flex-direction:column; align-items:flex-end; gap:0.6rem;">
    <div class="flex items-center gap-3">
        <?php if ($publishedCount > 0): ?>
        <form method="POST" action="<?= $base ?>/admin/publish" id="bulkUnpublishForm" onsubmit="return confirmBulkUnpublish(event, '<?= htmlspecialchars($term['name'], ENT_QUOTES) ?>')">
          <?= CSRF::field() ?>
          <input type="hidden" name="_action" value="bulk_unpublish">
          <input type="hidden" name="term_id" value="<?= $term['id'] ?>">
          <button type="submit" class="btn btn-ghost btn-xs text-danger" style="font-weight:700; border:1px solid rgba(239,68,68,0.2); display:flex; align-items:center; gap:4px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
            Unpublish All
          </button>
        </form>
        <?php endif; ?>
        <div class="badge badge-primary" style="padding:7px 16px; border-radius:var(--radius-full); font-weight:800; font-size:12px;">
          <?= htmlspecialchars($term['name']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($activeYear['year_name'] ?? '') ?>
        </div>
    </div>
    <?php if ($totalClasses > 0): ?>
    <span class="text-muted" style="font-size:var(--text-xs); font-weight:600;">
      <?= $publishedCount ?>/<?= $totalClasses ?> classes published
      <?php if ($lockedCount > 0): ?> · <?= $lockedCount ?> ready to publish<?php endif; ?>
    </span>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php if (!$term || !$activeYear): ?>
<!-- ── No Active Term ────────────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed;">
  <div style="background:var(--clr-surface-2); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary-300)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text);">No Active Term</h2>
  <p class="text-muted" style="max-width:340px; margin:0 auto 2.5rem;">
    Please activate a term in the Academic Years section before managing results.
  </p>
  <a href="<?= $base ?>/admin/years" class="btn btn-primary">Manage Academic Years</a>
</div>

<?php elseif (empty($classes)): ?>
<!-- ── No Classes ────────────────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed; background:var(--clr-surface-2);">
  <h2 style="font-weight:800; color:var(--clr-text);">No Classes Found</h2>
  <p class="text-muted" style="max-width:300px; margin:0 auto 2rem;">No classes are assigned to the current academic year.</p>
  <a href="<?= $base ?>/admin/classes" class="btn btn-outline">Manage Classes</a>
</div>

<?php else: ?>
<!-- ── Workflow Steps Banner ──────────────────────────────── -->
<div style="display:flex; gap:0; margin-bottom:2rem; border-radius:var(--radius-lg); overflow:hidden; border:1px solid var(--clr-border); background:var(--clr-surface);">
  <?php
  $steps = [
    ['num' => '1', 'label' => 'Teachers Enter Scores', 'sub' => 'SBA & Exam'],
    ['num' => '2', 'label' => 'Lock Score Entry',      'sub' => 'Prevents edits'],
    ['num' => '3', 'label' => 'Publish Results',       'sub' => 'Computes ranks'],
    ['num' => '4', 'label' => 'Parents View Reports',  'sub' => 'Via portal/SMS'],
  ];
  foreach ($steps as $i => $step):
  ?>
  <div style="flex:1; padding:0.875rem 1rem; border-right:<?= $i < 3 ? '1px solid var(--clr-border)' : 'none' ?>; display:flex; align-items:center; gap:0.625rem; min-width:0;">
    <div style="width:28px; height:28px; flex-shrink:0; border-radius:50%; background:var(--clr-primary-50); color:var(--clr-primary-600); font-weight:900; font-size:12px; display:flex; align-items:center; justify-content:center; border:2px solid var(--clr-primary-200);"><?= $step['num'] ?></div>
    <div style="min-width:0;">
      <div style="font-weight:700; font-size:12px; color:var(--clr-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= $step['label'] ?></div>
      <div style="font-size:10px; color:var(--clr-text-muted); font-weight:500;"><?= $step['sub'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Class Cards ────────────────────────────────────────── -->
<div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(380px, 1fr)); gap:1.5rem;">
  <?php foreach ($classes as $c):
    $students         = (int)$c['student_count'];
    $totalSubj        = (int)$c['total_subjects'];
    $lockedSubj       = (int)$c['locked_subjects'];
    $studentsScored   = (int)($c['students_with_scores'] ?? 0);
    $readiness        = ($totalSubj > 0) ? round(($lockedSubj / $totalSubj) * 100) : 0;
    $scoreCoverage    = ($students > 0) ? round(($studentsScored / $students) * 100) : 0;
    $isPublished      = (bool)($c['is_published'] ?? false);
    $isFullyLocked    = ($lockedSubj >= $totalSubj && $totalSubj > 0);
    $noStudents       = ($students === 0);
    $noSubjects       = ($totalSubj === 0);
    $canPublish       = $isFullyLocked && !$isPublished && !$noStudents && !$noSubjects;
  ?>
  <div class="card hover-lift" style="padding:0; overflow:hidden; border:1px solid var(--clr-border); display:flex; flex-direction:column;
      <?= $isPublished ? 'border-color:rgba(22,163,74,0.3);' : ($isFullyLocked ? 'border-color:rgba(234,179,8,0.35);' : '') ?>">

    <!-- Card Header -->
    <div style="padding:1.25rem 1.5rem; background:<?= $isPublished ? 'rgba(22,163,74,0.05)' : 'var(--clr-surface-2)' ?>; border-bottom:1px solid var(--clr-border); display:flex; justify-content:space-between; align-items:flex-start;">
      <div>
        <div style="font-size:10px; font-weight:800; color:var(--clr-primary); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.2rem;"><?= htmlspecialchars($c['level_name']) ?></div>
        <h3 style="margin:0; font-weight:800; font-size:1.1rem; color:var(--clr-text);">
          <?= htmlspecialchars($c['class_name']) ?><?= $c['section'] ? " ({$c['section']})" : '' ?>
        </h3>
      </div>
      <?php if ($isPublished): ?>
        <span class="badge badge-success" style="font-size:10px; padding:4px 10px; display:flex; align-items:center; gap:4px; font-weight:800;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          PUBLISHED
        </span>
      <?php elseif ($isFullyLocked): ?>
        <span style="font-size:10px; font-weight:800; padding:4px 10px; background:#fff8e7; color:#b45309; border:1.5px solid #fde68a; border-radius:var(--radius-full); display:flex; align-items:center; gap:4px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
          LOCKED
        </span>
      <?php elseif ($noStudents || $noSubjects): ?>
        <span style="font-size:10px; font-weight:800; padding:4px 10px; background:rgba(239,68,68,0.08); color:var(--clr-danger); border:1.5px solid rgba(239,68,68,0.2); border-radius:var(--radius-full); display:flex; align-items:center; gap:4px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
          ACTION REQUIRED
        </span>
      <?php else: ?>
        <span style="font-size:10px; font-weight:800; padding:4px 10px; background:var(--clr-surface-2); color:var(--clr-text-muted); border-radius:var(--radius-full); border:1.5px solid var(--clr-border); display:flex; align-items:center; gap:4px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="11" height="11"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          IN PROGRESS
        </span>
      <?php endif; ?>
    </div>

    <!-- Stats Grid -->
    <div style="padding:1.25rem 1.5rem; display:flex; flex-direction:column; gap:1rem; flex:1;">

      <!-- Warnings -->
      <?php if ($noStudents): ?>
      <div style="display:flex; gap:0.5rem; align-items:center; background:rgba(239,68,68,0.06); color:var(--clr-danger); border-left:3px solid var(--clr-danger); border-radius:0 var(--radius-sm) var(--radius-sm) 0; padding:0.6rem 0.75rem; font-size:var(--text-xs); font-weight:600;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        No students enrolled in this class.
      </div>
      <?php elseif ($noSubjects): ?>
      <div style="display:flex; gap:0.5rem; align-items:center; background:rgba(234,179,8,0.08); color:#92400e; border-left:3px solid #d97706; border-radius:0 var(--radius-sm) var(--radius-sm) 0; padding:0.6rem 0.75rem; font-size:var(--text-xs); font-weight:600;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <span>No subjects assigned. <a href="<?= $base ?>/admin/teachers" style="color:inherit; text-decoration:underline; border-bottom:1px dashed currentColor;">Assign now →</a></span>
      </div>
      <?php endif; ?>

      <!-- Score lock progress -->
      <div>
        <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:700; color:var(--clr-text); margin-bottom:0.4rem;">
          <span>Subjects Locked</span>
          <span style="color:<?= $isFullyLocked ? 'var(--clr-success)' : 'var(--clr-text)' ?>;"><?= $lockedSubj ?>/<?= $totalSubj ?></span>
        </div>
        <div style="width:100%; height:7px; background:var(--clr-surface-2); border-radius:10px; overflow:hidden;">
          <div style="width:<?= $readiness ?>%; height:100%; background:<?= $isFullyLocked ? 'var(--clr-success)' : 'linear-gradient(to right, var(--clr-primary-300), var(--clr-primary))' ?>; border-radius:10px; transition:width 0.6s ease;"></div>
        </div>
      </div>

      <!-- 3-stat row -->
      <div class="grid" style="grid-template-columns:repeat(3,1fr); gap:0.6rem;">
        <div style="background:var(--clr-surface-2); padding:0.625rem; border-radius:var(--radius-md); text-align:center;">
          <div style="font-size:1rem; font-weight:800; color:var(--clr-text);"><?= $students ?></div>
          <div style="font-size:9px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.04em;">Students</div>
        </div>
        <div style="background:var(--clr-surface-2); padding:0.625rem; border-radius:var(--radius-md); text-align:center;">
          <div style="font-size:1rem; font-weight:800; color:<?= $scoreCoverage >= 80 ? 'var(--clr-success)' : ($scoreCoverage > 0 ? 'var(--clr-warning)' : 'var(--clr-danger)') ?>;"><?= $scoreCoverage ?>%</div>
          <div style="font-size:9px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.04em;">Score Coverage</div>
        </div>
        <div style="background:var(--clr-surface-2); padding:0.625rem; border-radius:var(--radius-md); text-align:center;">
          <div style="font-size:1rem; font-weight:800; color:var(--clr-text);"><?= $totalSubj - $lockedSubj ?></div>
          <div style="font-size:9px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.04em;">Pending</div>
        </div>
      </div>

      <?php if ($isPublished && $c['published_at']): ?>
      <div style="font-size:var(--text-xs); color:var(--clr-text-muted);">
        Published: <?= date('d M Y, g:i A', strtotime($c['published_at'])) ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Actions Footer -->
    <div style="padding:0.875rem 1.5rem; background:rgba(0,0,0,0.02); border-top:1px solid var(--clr-border); display:flex; justify-content:flex-end; align-items:center; gap:0.5rem; flex-wrap:wrap;">

      <?php if ($isPublished): ?>
        <!-- Unpublish -->
        <form method="POST" action="<?= $base ?>/admin/publish" onsubmit="return confirmUnpublish(event, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>')">
          <?= CSRF::field() ?>
          <input type="hidden" name="_action" value="unpublish">
          <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
          <input type="hidden" name="term_id"  value="<?= $term['id'] ?>">
          <button type="submit" class="btn btn-ghost btn-xs text-danger">Unpublish</button>
        </form>
        <!-- Unlock -->
        <form method="POST" action="<?= $base ?>/admin/publish" onsubmit="Loader.show()">
          <?= CSRF::field() ?>
          <input type="hidden" name="_action" value="unlock_class">
          <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
          <input type="hidden" name="term_id"  value="<?= $term['id'] ?>">
          <button type="submit" class="btn btn-ghost btn-xs text-warning" style="display:flex; align-items:center; gap:4px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
            Unlock Scores
          </button>
        </form>

      <?php else: ?>
        
        <?php if ($isFullyLocked): ?>
          <!-- Unlock -->
          <form method="POST" action="<?= $base ?>/admin/publish" onsubmit="Loader.show()">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="unlock_class">
            <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
            <input type="hidden" name="term_id"  value="<?= $term['id'] ?>">
            <button type="submit" class="btn btn-ghost btn-xs text-warning" style="display:flex; align-items:center; gap:4px;">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
              Unlock
            </button>
          </form>
        <?php elseif (!$noStudents && !$noSubjects): ?>
          <!-- Lock All Scores -->
          <form method="POST" action="<?= $base ?>/admin/publish" onsubmit="return confirmLock(event, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>')">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="lock_class">
            <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
            <input type="hidden" name="term_id"  value="<?= $term['id'] ?>">
            <button type="submit" class="btn btn-outline btn-xs text-primary" style="display:flex; align-items:center; gap:4px;">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
              Lock Scores
            </button>
          </form>
        <?php endif; ?>

        <!-- Publish (Only if fully locked) -->
        <?php if ($canPublish): ?>
          <form method="POST" action="<?= $base ?>/admin/publish" onsubmit="return confirmPublish(event, '<?= htmlspecialchars($c['class_name'], ENT_QUOTES) ?>', <?= $students ?>, <?= $totalSubj ?>)">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="publish_class">
            <input type="hidden" name="class_id" value="<?= $c['id'] ?>">
            <input type="hidden" name="term_id"  value="<?= $term['id'] ?>">
            <button type="submit" class="btn btn-primary btn-xs" style="display:flex; align-items:center; gap:4px;">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              Publish Results
            </button>
          </form>
        <?php endif; ?>

      <?php endif; ?>

      <!-- Stub: Print button -->
      <?php if ($isPublished): ?>
      <button class="btn btn-ghost btn-xs" data-tooltip="Print all report cards for this class" style="cursor:not-allowed;" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>


<?php include __DIR__ . '/../layout/footer.php'; ?>

<script>
function confirmLock(e, className) {
  e.preventDefault();
  confirmAction({
    title:       'Lock Score Entry?',
    message:     `Lock all subjects for ${className}? Teachers will not be able to modify scores until you unlock.`,
    confirmText: 'Yes, Lock',
    type:        'warning'
  }, () => { e.target.submit(); Loader.show(); });
  return false;
}

function confirmPublish(e, className, students, subjects) {
  e.preventDefault();
  confirmAction({
    title:       `Publish ${className}?`,
    message:     `This will compute positions and proficiency levels for ${students} student(s) across ${subjects} subject(s). Results will become visible to parents and students immediately.`,
    confirmText: 'Compute & Publish',
    type:        'danger'
  }, () => { e.target.submit(); Loader.show(); });
  return false;
}

function confirmUnpublish(e, className) {
  e.preventDefault();
  confirmAction({
    title:       'Hide Results?',
    message:     `Reports for ${className} will no longer be visible to parents or students. Scores and computed data are kept.`,
    confirmText: 'Yes, Unpublish',
    type:        'warning'
  }, () => { e.target.submit(); Loader.show(); });
  return false;
}

function confirmBulkUnpublish(e, termName) {
  e.preventDefault();
  confirmAction({
    title:       'Unpublish ALL Results?',
    message:     `This will hide ALL report cards for EVERY class in ${termName}. Parents and students will not be able to view their results until you publish them again individually.`,
    confirmText: 'Yes, Hide Everything',
    type:        'danger'
  }, () => { e.target.submit(); Loader.show(); });
  return false;
}

</script>
