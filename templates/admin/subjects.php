<?php
/**
 * Subjects Management View
 * HCI/UX: Grouped by level, premium table, sort order control, active toggle
 */
$pageTitle = 'Subjects & Curriculum';
include __DIR__ . '/../layout/header.php';

global $subjectsList, $levelsList;
$base     = defined('APP_BASE') ? APP_BASE : '';
$subjects = $subjectsList ?? [];
$levels   = $levelsList   ?? [];

// Group subjects by level
$byLevel = [];
foreach ($subjects as $s) {
    $byLevel[$s['level_name']][] = $s;
}

$levelColors = ['LP' => 'success', 'UP' => 'warning', 'JHS' => 'purple'];
?>

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Subjects & Curriculum</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:600px;">
      Define the academic subjects for each school level. These will be available for class assignments and score recording.
    </p>
  </div>
  <button class="btn btn-primary shadow-purple" onclick="openSubjectModal()" style="height:42px;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    New Subject
  </button>
</div>

<?php if (empty($subjects)): ?>
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed; background:var(--clr-surface-2);">
  <div style="background:var(--clr-surface); padding:2rem; border-radius:var(--radius-full); box-shadow:var(--shadow-lg); margin-bottom:2rem;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary-300)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text); margin-bottom:0.5rem;">The curriculum is empty</h2>
  <p class="text-muted" style="max-width:320px; margin:0 auto 2rem;">Start adding subjects like Mathematics, English, etc. for your school levels.</p>
  <button class="btn btn-primary btn-lg" onclick="openSubjectModal()">Add Your First Subject</button>
</div>

<?php else: ?>
<!-- ── Subjects by Level ────────────────────────────────────── -->
<?php foreach ($byLevel as $levelName => $levelSubjects): ?>
<?php $levelCode = $levelSubjects[0]['level_code']; ?>
<div class="card" style="padding:0; overflow:hidden; margin-bottom:2.5rem;">
  <div class="card-header flex justify-between items-center" style="padding:1.25rem 2rem; background:var(--clr-surface-2); margin:0; border-bottom:1px solid var(--clr-border);">
    <div class="flex items-center gap-3">
       <span class="badge badge-<?= $levelColors[$levelCode] ?? 'purple' ?>" style="font-size:10px; padding:0.25rem 0.6rem;"><?= $levelCode ?></span>
       <h3 class="m-0" style="font-size:1.125rem; font-weight:800;"><?= htmlspecialchars($levelName) ?></h3>
    </div>
    <span class="text-muted" style="font-size:var(--text-xs); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;"><?= count($levelSubjects) ?> SUBJECTS</span>
  </div>

  <div class="table-wrapper">
    <table class="table" style="margin-bottom:0;">
      <thead>
        <tr>
          <th style="padding-left:2rem; width:80px;">Order</th>
          <th>Subject Name</th>
          <th>Teaching Staff</th>
          <th>Code</th>
          <th style="width:120px;" class="text-center">Status</th>
          <th style="width:120px;" class="text-right pr-8">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($levelSubjects as $s): ?>
        <tr class="<?= !$s['is_active'] ? 'opacity-50 grayscale' : '' ?>">
          <td style="padding-left:2rem;">
            <span style="font-weight:800; color:var(--clr-text-muted); font-family:var(--font-mono); font-size:11px;">#<?= str_pad($s['sort_order'], 2, '0', STR_PAD_LEFT) ?></span>
          </td>
          <td>
            <div style="font-weight:700; color:var(--clr-text);"><?= htmlspecialchars($s['subject_name']) ?></div>
          </td>
          <td>
            <div class="flex flex-wrap gap-1">
              <?php if ($s['assigned_teachers']): ?>
                <?php foreach (explode(', ', $s['assigned_teachers']) as $teacher): ?>
                  <span class="badge badge-gray" style="font-size:10px; padding:2px 6px;"><?= htmlspecialchars($teacher) ?></span>
                <?php endforeach; ?>
              <?php else: ?>
                <span style="font-size:10px; color:var(--clr-text-muted); font-style:italic;">No teachers</span>
              <?php endif; ?>
            </div>
          </td>
          <td><code style="font-size:11px;"><?= htmlspecialchars($s['subject_code'] ?: '—') ?></code></td>
          <td class="text-center">
             <form method="POST" action="<?= $base ?>/admin/subjects" onsubmit="Loader.show()">
               <?= CSRF::field() ?>
               <input type="hidden" name="_action" value="subject_toggle">
               <input type="hidden" name="subject_id" value="<?= $s['id'] ?>">
               <button type="submit" class="btn btn-ghost btn-xs <?= $s['is_active'] ? 'text-success' : 'text-danger' ?>" style="font-weight:800; font-size:10px;">
                  <?= $s['is_active'] ? 'ACTIVE' : 'DISABLED' ?>
               </button>
             </form>
          </td>
          <td class="text-right pr-8">
            <div class="flex justify-end gap-2">
              <button class="btn btn-ghost btn-xs" onclick='editSubject(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)' data-tooltip="Edit Subject">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              </button>
              <button class="btn btn-ghost btn-xs text-danger" onclick="confirmDeleteSubject(<?= $s['id'] ?>, '<?= htmlspecialchars($s['subject_name'], ENT_QUOTES) ?>')" data-tooltip="Delete Subject">
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


<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- ══ Subject Modal ═════════════════════════════════════════ -->
<div id="modal-subject" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-subject-title" style="display:none;">
  <div class="modal w-full max-w-md mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-subject-title">Subject Details</h3>
      <button class="modal-close" onclick="closeModal('modal-subject')" aria-label="Close dialog">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/subjects" id="form-subject" onsubmit="Loader.show()">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="subject_store">
      <input type="hidden" name="subject_id" id="subject-id-field" value="">
      <div class="modal-body">
        
        <div class="form-group">
          <label class="form-label">School Level <span class="required">*</span></label>
          <select name="level_id" id="subject-level" class="form-control" required>
            <option value="">— Select Level —</option>
            <?php foreach ($levels as $l): ?>
            <option value="<?= $l['id'] ?>"><?= htmlspecialchars($l['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Subject Name <span class="required">*</span></label>
          <input type="text" name="subject_name" id="subject-name" class="form-control" placeholder="e.g. Mathematics" required maxlength="100">
        </div>

        <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
          <div class="form-group">
            <label class="form-label">Subject Code</label>
            <input type="text" name="subject_code" id="subject-code" class="form-control" placeholder="e.g. MATH" maxlength="20" oninput="this.value = this.value.toUpperCase()">
          </div>
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" id="subject-order" class="form-control" value="0" min="0" max="255">
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-subject')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="subject-submit-btn">Save Subject</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Form -->
<form method="POST" action="<?= $base ?>/admin/subjects" id="form-subject-delete" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" value="subject_delete">
  <input type="hidden" name="subject_id" id="del-subject-id">
</form>

<script>
function openSubjectModal() {
  document.getElementById('form-subject').reset();
  document.getElementById('subject-id-field').value = '';
  document.getElementById('modal-subject-title').textContent = 'New Subject';
  document.getElementById('subject-submit-btn').textContent = 'Create Subject';
  openModal('modal-subject');
}

function editSubject(s) {
  document.getElementById('form-subject').reset();
  document.getElementById('subject-id-field').value = s.id;
  document.getElementById('subject-level').value = s.level_id;
  document.getElementById('subject-name').value = s.subject_name;
  document.getElementById('subject-code').value = s.subject_code || '';
  document.getElementById('subject-order').value = s.sort_order;
  document.getElementById('modal-subject-title').textContent = 'Edit ' + s.subject_name;
  document.getElementById('subject-submit-btn').textContent = 'Update Subject';
  openModal('modal-subject');
}

function confirmDeleteSubject(id, name) {
  confirmAction(`Delete the subject "${name}"?\n\nThis will permanently remove it from the curriculum if no scores are linked to it.`, () => {
    document.getElementById('del-subject-id').value = id;
    document.getElementById('form-subject-delete').submit();
  });
}
</script>
