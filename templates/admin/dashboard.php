<?php
/**
 * Admin Dashboard View
 * Uaddara Basic School — SBA Management System
 */
$pageTitle = 'Dashboard Overview';
include __DIR__ . '/../layout/header.php';

global $stats, $activeYear, $activeTerm, $gradingProgress, $pendingPublish, $teacherCommitments;
$base = defined('APP_BASE') ? APP_BASE : '';
$s = $stats ?? ['students' => 0, 'teachers' => 0, 'classes' => 0];

// Safe calculations for progress bars
$expected = max(1, $gradingProgress['expected_scores']);
$sbaPct = min(100, round(($gradingProgress['entered_sba'] / $expected) * 100));
$examPct = min(100, round(($gradingProgress['entered_exam'] / $expected) * 100));
$totalPct = min(100, round((($gradingProgress['entered_sba'] + $gradingProgress['entered_exam']) / ($expected * 2)) * 100));
?>

<div class="stat-grid mb-8" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
  <!-- Stat Card: Term Info -->
  <div class="stat-card" style="border-left: 4px solid var(--clr-primary);">
    <div class="stat-icon" style="--stat-bg: var(--clr-primary-50); --stat-color: var(--clr-primary-600);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </div>
    <div>
      <div class="stat-label">Active Term</div>
      <div class="stat-value" style="font-size:1.25rem;">
        <?= $activeTerm ? "Term {$activeTerm['term_number']}" : 'No Active Term' ?>
      </div>
      <div class="stat-sub"><?= $activeYear ? htmlspecialchars($activeYear['year_name']) : 'Set up academic year' ?></div>
    </div>
  </div>

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
    <div class="stat-icon" style="--stat-bg: var(--clr-warning-bg); --stat-color: var(--clr-warning);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    </div>
    <div>
      <div class="stat-label">Classes</div>
      <div class="stat-value"><?= number_format($s['classes']) ?></div>
      <div class="stat-sub">This academic year</div>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns: var(--grid-main-sidebar, 2fr 1.2fr); gap:1.5rem;">
  
  <!-- Main Area -->
  <div class="flex flex-col gap-6">
    
    <!-- Grading Progress -->
    <div class="card p-6">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h3 class="m-0" style="font-size:1.125rem; font-weight:700;">School Grading Progress</h3>
          <p class="text-sm text-muted m-0">Overall completion for <?= $activeTerm ? "Term {$activeTerm['term_number']}" : 'Current Term' ?></p>
        </div>
        <div style="font-size:1.5rem; font-weight:800; color:var(--clr-primary);">
          <?= $totalPct ?>%
        </div>
      </div>

      <div style="background:var(--clr-surface-2); border-radius:var(--radius-full); height:12px; overflow:hidden; display:flex;">
        <div style="width:<?= $totalPct ?>%; background:var(--clr-primary); transition:width 1s ease-in-out;"></div>
      </div>

      <div class="grid mt-6" style="grid-template-columns: 1fr 1fr; gap:2rem;">
        <div>
          <div class="flex justify-between text-sm mb-2" style="font-weight:600;">
            <span>SBA Entries</span>
            <span style="color:var(--clr-success);"><?= $sbaPct ?>%</span>
          </div>
          <div style="background:var(--clr-surface-2); border-radius:var(--radius-full); height:6px; overflow:hidden;">
            <div style="width:<?= $sbaPct ?>%; background:var(--clr-success); height:100%;"></div>
          </div>
          <p class="text-xs text-muted mt-1 m-0"><?= number_format($gradingProgress['entered_sba']) ?> / <?= number_format($expected) ?> expected</p>
        </div>
        <div>
          <div class="flex justify-between text-sm mb-2" style="font-weight:600;">
            <span>Exam Entries</span>
            <span style="color:var(--clr-info);"><?= $examPct ?>%</span>
          </div>
          <div style="background:var(--clr-surface-2); border-radius:var(--radius-full); height:6px; overflow:hidden;">
            <div style="width:<?= $examPct ?>%; background:var(--clr-info); height:100%;"></div>
          </div>
          <p class="text-xs text-muted mt-1 m-0"><?= number_format($gradingProgress['entered_exam']) ?> / <?= number_format($expected) ?> expected</p>
        </div>
      </div>
    </div>

    <!-- Active Teachers SBA Commitment -->
    <div class="card p-0 overflow-hidden">
      <div class="card-header px-6 py-4">
        <h3 class="m-0" style="font-size:1rem; font-weight:700;">Teacher SBA Commitment</h3>
        <p class="text-xs text-muted m-0" style="font-weight:normal;">Top contributors this term based on subject assignments vs scores entered</p>
      </div>
      <table class="table mb-0">
        <thead>
          <tr>
            <th style="padding-left:1.5rem;">Teacher</th>
            <th class="text-center">Subjects</th>
            <th class="text-right pr-6">Progress</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($teacherCommitments)): ?>
            <tr><td colspan="3" class="text-center py-6 text-muted text-sm">No grading data available for teachers yet.</td></tr>
          <?php else: ?>
            <?php foreach($teacherCommitments as $tc): 
              $expectedT = max(1, $tc['expected_entries']);
              $pct = min(100, round(($tc['actual_entries'] / $expectedT) * 100));
            ?>
            <tr>
              <td style="padding-left:1.5rem;">
                <div style="font-weight:600; font-size:13px; color:var(--clr-text);"><?= htmlspecialchars($tc['full_name']) ?></div>
              </td>
              <td class="text-center">
                <span class="badge" style="background:var(--clr-surface-2); color:var(--clr-text-muted); font-size:10px;"><?= $tc['subjects_assigned'] ?> classes</span>
              </td>
              <td class="text-right pr-6">
                <div class="flex items-center justify-end gap-3">
                  <div style="width:60px; height:4px; background:var(--clr-surface-2); border-radius:2px; overflow:hidden;">
                    <div style="width:<?= $pct ?>%; height:100%; background:<?= $pct > 80 ? 'var(--clr-success)' : 'var(--clr-primary)' ?>;"></div>
                  </div>
                  <span style="font-size:11px; font-weight:800; color:var(--clr-text); width:32px; text-align:right;"><?= $pct ?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

  <!-- Right Sidebar Area -->
  <div class="flex flex-col gap-6">
    
    <!-- Pending Publish Alert Panel -->
    <div class="card <?= empty($pendingPublish) ? 'p-4' : 'p-0 overflow-hidden' ?>" style="<?= empty($pendingPublish) ? 'border:1px dashed var(--clr-border); background:transparent; box-shadow:none;' : 'border:1px solid var(--clr-warning-bg);' ?>">
      
      <?php if(empty($pendingPublish)): ?>
        <div class="flex items-center gap-3">
          <div style="width:36px; height:36px; border-radius:var(--radius-full); background:var(--clr-success-bg); color:var(--clr-success); display:flex; align-items:center; justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
          </div>
          <div>
            <h4 class="m-0" style="font-size:0.875rem; font-weight:700;">No Pending Publications</h4>
            <p class="m-0 text-xs text-muted">All locked grades have been published.</p>
          </div>
        </div>
      
      <?php else: ?>
        <div class="card-header flex justify-between items-center" style="background:var(--clr-warning-bg); border-bottom:1px solid rgba(0,0,0,0.05); padding:1rem 1.25rem;">
          <h3 class="m-0 flex items-center gap-2" style="font-size:1rem; font-weight:800; color:var(--clr-warning);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Pending Publish
          </h3>
          <span class="badge" style="background:#fff; color:var(--clr-warning); font-weight:800; border:none; box-shadow:0 2px 4px rgba(0,0,0,0.05);"><?= count($pendingPublish) ?></span>
        </div>
        <div class="p-3">
           <?php foreach(array_slice($pendingPublish, 0, 4) as $pp): ?>
             <div class="flex justify-between items-center p-3 mb-2 rounded shadow-sm" style="background:var(--clr-surface); border:1px solid var(--clr-border);">
                <div>
                  <div style="font-weight:700; font-size:13px; color:var(--clr-text);"><?= htmlspecialchars($pp['class_name']) ?> <?= htmlspecialchars($pp['section']) ?></div>
                  <div style="font-size:10px; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em;"><?= $pp['locked_subjects'] ?>/<?= $pp['total_subjects'] ?> Subjects Locked</div>
                </div>
                <a href="<?= $base ?>/admin/publish" class="btn btn-outline btn-xs" style="padding:4px 8px;">Review</a>
             </div>
           <?php endforeach; ?>
           <?php if(count($pendingPublish) > 4): ?>
             <a href="<?= $base ?>/admin/publish" class="block text-center text-xs font-semibold mt-2" style="color:var(--clr-primary);">View <?= count($pendingPublish) - 4 ?> more...</a>
           <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Quick Actions -->
    <div class="card p-0 overflow-hidden">
      <div class="card-header px-5 py-4">
        <h3 class="m-0" style="font-size:1rem; font-weight:700;">Quick Actions</h3>
      </div>
      <div class="flex flex-col">
        <a href="<?= $base ?>/admin/students" class="btn btn-ghost w-full" style="justify-content:flex-start; padding:1rem 1.25rem; border-radius:0; border-bottom:1px solid var(--clr-border);">
          <div style="width:32px; height:32px; border-radius:8px; background:var(--clr-primary-50); color:var(--clr-primary); display:flex; align-items:center; justify-content:center; margin-right:12px;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
          </div>
          <span style="font-weight:600; color:var(--clr-text);">Register Student</span>
        </a>
        <a href="<?= $base ?>/admin/classes" class="btn btn-ghost w-full" style="justify-content:flex-start; padding:1rem 1.25rem; border-radius:0; border-bottom:1px solid var(--clr-border);">
          <div style="width:32px; height:32px; border-radius:8px; background:var(--clr-info-bg); color:var(--clr-info); display:flex; align-items:center; justify-content:center; margin-right:12px;">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          </div>
          <span style="font-weight:600; color:var(--clr-text);">Manage Classes</span>
        </a>
        <a href="<?= $base ?>/admin/sms" class="btn btn-ghost w-full" style="justify-content:flex-start; padding:1rem 1.25rem; border-radius:0; border-bottom:1px solid var(--clr-border);">
          <div style="width:32px; height:32px; border-radius:8px; background:var(--clr-success-bg); color:var(--clr-success); display:flex; align-items:center; justify-content:center; margin-right:12px;">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
          </div>
          <span style="font-weight:600; color:var(--clr-text);">Send Broadcast SMS</span>
        </a>
        <a href="<?= $base ?>/admin/remarks" class="btn btn-ghost w-full" style="justify-content:flex-start; padding:1rem 1.25rem; border-radius:0;">
          <div style="width:32px; height:32px; border-radius:8px; background:var(--clr-primary-bg); color:var(--clr-primary); display:flex; align-items:center; justify-content:center; margin-right:12px; filter: hue-rotate(240deg);">
             <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
          </div>
          <span style="font-weight:600; color:var(--clr-text);">Terminal Remarks</span>
        </a>
      </div>
    </div>

  </div>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
