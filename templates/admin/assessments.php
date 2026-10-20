<?php
/**
 * Speed Tests & Mock Exams Management View
 * HCI/UX: One primary action (New Assessment), grouped by type, results one click away
 */
$pageTitle = 'Speed Tests & Mocks';
include __DIR__ . '/../layout/header.php';

global $termsList, $selectedTerm, $assessmentsList, $waecClasses;
$base        = defined('APP_BASE') ? APP_BASE : '';
$terms       = $termsList       ?? [];
$assessments = $assessmentsList ?? [];
$classes     = $waecClasses     ?? [];
$types       = AssessmentController::TYPES;

$byType = ['speed_test' => [], 'mock' => []];
foreach ($assessments as $a) {
    $byType[$a['assessment_type']][] = $a;
}
?>

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Speed Tests &amp; Mock Exams</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:640px;">
      Extra assessments for WAEC-graded classes (e.g. B9). Graded 1–9 with a Best-6 aggregate. They do not affect the term report card.
    </p>
  </div>
  <div class="flex items-center gap-3 flex-wrap">
    <form method="GET" action="<?= $base ?>/admin/assessments" class="m-0">
      <select name="term" class="form-control" onchange="this.form.submit()" style="height:42px; min-width:220px;" aria-label="Select term">
        <?php foreach ($terms as $t): ?>
        <option value="<?= $t['id'] ?>" <?= ($selectedTerm && $selectedTerm['id'] == $t['id']) ? 'selected' : '' ?>>
          <?= htmlspecialchars($t['year_name'] . ' · ' . $t['name']) ?><?= $t['is_active'] ? ' (Active)' : '' ?>
        </option>
        <?php endforeach; ?>
      </select>
    </form>
    <?php if ($selectedTerm): ?>
    <button class="btn btn-primary shadow-purple" onclick="openAssessmentModal()" style="height:42px;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      New Assessment
    </button>
    <?php endif; ?>
  </div>
</div>

<?php if (!$selectedTerm): ?>
<div class="card flex flex-col items-center justify-center" style="padding:4rem 2rem; text-align:center; border-style:dashed;">
  <h2 style="font-weight:800; color:var(--clr-text);">No active term</h2>
  <p class="text-muted">Set an active term under Academic Years &amp; Terms first.</p>
</div>

<?php else: ?>

<?php if (empty($classes)): ?>
<div class="alert alert-warning mb-6">
  No WAEC-graded classes have subjects assigned this term. Set a class's grading system to <strong>WAEC</strong> under Classrooms so teachers can enter scores.
</div>
<?php endif; ?>

<?php if (empty($assessments)): ?>
<div class="card flex flex-col items-center justify-center" style="padding:5rem 2rem; text-align:center; border-style:dashed; background:var(--clr-surface-2);">
  <h2 style="font-weight:800; color:var(--clr-text); margin-bottom:0.5rem;">No speed tests or mocks yet</h2>
  <p class="text-muted" style="max-width:360px; margin:0 auto 2rem;">Create one for <?= htmlspecialchars($selectedTerm['year_name'] . ' · ' . $selectedTerm['name']) ?>, then teachers can record subject scores for it.</p>
  <button class="btn btn-primary btn-lg" onclick="openAssessmentModal()">Create First Assessment</button>
</div>

<?php else: ?>
<?php foreach ($byType as $type => $items): if (empty($items)) continue; ?>
<div class="card" style="padding:0; overflow:hidden; margin-bottom:2rem;">
  <div class="card-header flex justify-between items-center" style="padding:1.25rem 2rem; background:var(--clr-surface-2); margin:0; border-bottom:1px solid var(--clr-border);">
    <h3 class="m-0" style="font-size:1.125rem; font-weight:800;"><?= $types[$type] ?>s</h3>
    <span class="text-muted" style="font-size:var(--text-xs); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;"><?= count($items) ?> TOTAL</span>
  </div>
  <div class="table-wrapper" style="border:none; border-radius:0;">
    <table class="table" style="margin-bottom:0;">
      <thead>
        <tr>
          <th style="padding-left:2rem;">Title</th>
          <th>Date</th>
          <th class="text-center">Out Of</th>
          <th class="text-center">Scores Entered</th>
          <th>Results</th>
          <th class="text-right pr-8" style="width:160px;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $a): ?>
        <tr>
          <td style="padding-left:2rem;">
            <div style="font-weight:700; color:var(--clr-text);"><?= htmlspecialchars($a['title']) ?></div>
            <?php if ($a['is_locked']): ?>
              <span class="badge badge-gray" style="font-size:10px; margin-top:4px;">LOCKED</span>
            <?php endif; ?>
          </td>
          <td><?= $a['assessment_date'] ? date('d M Y', strtotime($a['assessment_date'])) : '<span class="text-muted">—</span>' ?></td>
          <td class="text-center" style="font-weight:700;"><?= (float)$a['max_score'] ?></td>
          <td class="text-center"><?= (int)$a['scores_entered'] ?></td>
          <td>
            <div class="flex flex-wrap gap-1">
              <?php foreach ($classes as $c): ?>
              <a href="<?= $base ?>/assessments/results?assessment=<?= $a['id'] ?>&class=<?= $c['id'] ?>" class="badge badge-purple" style="text-decoration:none;">
                <?= htmlspecialchars($c['class_name'] . ($c['section'] ? ' ' . $c['section'] : '')) ?>
              </a>
              <?php endforeach; ?>
              <?php if (empty($classes)): ?><span class="text-muted" style="font-size:11px;">—</span><?php endif; ?>
            </div>
          </td>
          <td class="text-right pr-8">
            <div class="flex justify-end gap-2">
              <form method="POST" action="<?= $base ?>/admin/assessments" class="m-0" onsubmit="Loader.show()">
                <?= CSRF::field() ?>
                <input type="hidden" name="_action" value="assessment_lock">
                <input type="hidden" name="assessment_id" value="<?= $a['id'] ?>">
                <button type="submit" class="btn btn-ghost btn-xs" data-tooltip="<?= $a['is_locked'] ? 'Unlock score entry' : 'Lock score entry' ?>">
                  <?php if ($a['is_locked']): ?>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                  <?php else: ?>
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                  <?php endif; ?>
                </button>
              </form>
              <button class="btn btn-ghost btn-xs" onclick='editAssessment(<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>)' data-tooltip="Edit">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              </button>
              <button class="btn btn-ghost btn-xs text-danger" onclick="confirmDeleteAssessment(<?= $a['id'] ?>, <?= htmlspecialchars(json_encode($a['title']), ENT_QUOTES) ?>, <?= (int)$a['scores_entered'] ?>)" data-tooltip="Delete">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<?php if ($selectedTerm): ?>
<!-- ══ Assessment Modal ══════════════════════════════════════ -->
<div id="modal-assessment" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-assessment-title" style="display:none;">
  <div class="modal w-full max-w-md mx-4 min-h-0">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-assessment-title">New Assessment</h3>
      <button class="modal-close" onclick="closeModal('modal-assessment')" aria-label="Close dialog">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/assessments" id="form-assessment" class="modal-flex-stack m-0" onsubmit="Loader.show()">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="assessment_store">
      <input type="hidden" name="assessment_id" id="assessment-id-field" value="">
      <input type="hidden" name="term_id" value="<?= (int)$selectedTerm['id'] ?>">
      <div class="modal-body">

        <div class="form-group">
          <label class="form-label">Type <span class="required">*</span></label>
          <select name="assessment_type" id="assessment-type" class="form-control" required>
            <?php foreach ($types as $key => $label): ?>
            <option value="<?= $key ?>"><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Title <span class="required">*</span></label>
          <input type="text" name="title" id="assessment-title" class="form-control" placeholder="e.g. Mock 1" required maxlength="100">
        </div>

        <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
          <div class="form-group">
            <label class="form-label">Date</label>
            <input type="date" name="assessment_date" id="assessment-date" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Scored Out Of <span class="required">*</span></label>
            <input type="number" name="max_score" id="assessment-max" class="form-control" value="100" min="1" max="1000" step="0.5" required>
          </div>
        </div>
        <p class="form-text m-0">Scores are converted to a percentage, then graded on the WAEC 1–9 scale. Changing "Out Of" re-grades any scores already entered.</p>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-assessment')">Cancel</button>
        <button type="submit" class="btn btn-primary shadow-purple" id="assessment-submit-btn">Create Assessment</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Form -->
<form method="POST" action="<?= $base ?>/admin/assessments" id="form-assessment-delete" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" value="assessment_delete">
  <input type="hidden" name="assessment_id" id="del-assessment-id">
</form>

<script>
function openAssessmentModal() {
  document.getElementById('form-assessment').reset();
  document.getElementById('assessment-id-field').value = '';
  document.getElementById('modal-assessment-title').textContent = 'New Assessment';
  document.getElementById('assessment-submit-btn').textContent = 'Create Assessment';
  openModal('modal-assessment');
}

function editAssessment(a) {
  document.getElementById('form-assessment').reset();
  document.getElementById('assessment-id-field').value = a.id;
  document.getElementById('assessment-type').value = a.assessment_type;
  document.getElementById('assessment-title').value = a.title;
  document.getElementById('assessment-date').value = a.assessment_date || '';
  document.getElementById('assessment-max').value = parseFloat(a.max_score);
  document.getElementById('modal-assessment-title').textContent = 'Edit ' + a.title;
  document.getElementById('assessment-submit-btn').textContent = 'Save Changes';
  openModal('modal-assessment');
}

function confirmDeleteAssessment(id, title, scoreCount) {
  const safe = title.replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  confirmAction({
    title: 'Delete assessment?',
    message: `"${safe}" will be deleted` + (scoreCount > 0 ? ` along with <strong>${scoreCount} recorded score(s)</strong>` : '') + '. This cannot be undone.',
    confirmText: 'Delete',
  }, () => {
    document.getElementById('del-assessment-id').value = id;
    document.getElementById('form-assessment-delete').submit();
  });
}
</script>
<?php endif; ?>
