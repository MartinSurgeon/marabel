<?php
/**
 * Teachers Management View
 * HCI/UX: Card-based grid for teachers, status toggles, quick assignment overview
 */
$pageTitle = 'Teachers Management';
include __DIR__ . '/../layout/header.php';

global $teachersList, $classesList, $subjectsList, $assignmentsList;
$base     = defined('APP_BASE') ? APP_BASE : '';
$teachers = $teachersList ?? [];
$classes  = $classesList ?? [];
$subjects = $subjectsList ?? [];
$assignments = $assignmentsList ?? [];
?>

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Teachers</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:600px;">
      Manage your teaching staff and their status. Assignments to specific classes and subjects can be configured here.
    </p>
  </div>
  <button class="btn btn-primary shadow-purple" onclick="openTeacherModal()" style="height:42px;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Add Teacher
  </button>
</div>

<?php if (empty($teachers)): ?>
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed;">
  <div style="background:var(--clr-primary-50); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text);">No teachers registered yet</h2>
  <p class="text-muted" style="max-width:320px; margin:0 auto 2.5rem;">Create profiles for your teaching staff to allow them to enter student scores.</p>
  <button class="btn btn-primary btn-lg" onclick="openTeacherModal()">Register First Teacher</button>
</div>

<?php else: ?>
<div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.5rem;">
  <?php foreach ($teachers as $t): ?>
  <div class="card hover-lift flex flex-col <?= !$t['is_active'] ? 'grayscale opacity-60' : '' ?>" style="padding:0; overflow:hidden;">
    <div style="padding:1.5rem 1.5rem 1rem;">
      <div class="flex justify-between items-start mb-4">
        <div class="flex items-center gap-3">
           <div style="width:48px; height:48px; background:var(--clr-primary-50); color:var(--clr-primary); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:center; font-size:1.25rem; font-weight:800;">
              <?= substr($t['full_name'], 0, 1) ?>
           </div>
           <div>
              <div style="font-weight:800; color:var(--clr-text); font-size:1.125rem; line-height:1.2;"><?= htmlspecialchars($t['full_name']) ?></div>
              <div style="font-size:11px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.02em;"><?= htmlspecialchars($t['email']) ?></div>
           </div>
        </div>
        <form method="POST" action="<?= $base ?>/admin/teachers" onsubmit="Loader.show()">
           <?= CSRF::field() ?>
           <input type="hidden" name="_action" value="teacher_toggle">
           <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
           <button type="submit" class="badge <?= $t['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer; border:none; padding:4px 8px;">
              <?= $t['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
           </button>
        </form>
      </div>

      <?php if ($t['phone']): ?>
      <div class="flex items-center gap-2 mb-4" style="font-size:13px; color:var(--clr-text-muted);">
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
         <?= htmlspecialchars($t['phone']) ?>
      </div>
      <?php endif; ?>

      <div class="grid" style="grid-template-columns:1fr 1fr; gap:0.75rem; border-top:1px solid var(--clr-border); padding-top:1rem; margin-top:1rem;">
         <div style="background:var(--clr-surface-2); padding:0.75rem; border-radius:var(--radius-md); text-align:center;">
            <div style="font-size:1.125rem; font-weight:800; color:var(--clr-primary);"><?= $t['class_count'] ?></div>
            <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase;">Classrooms</div>
         </div>
         <div style="background:var(--clr-surface-2); padding:0.75rem; border-radius:var(--radius-md); text-align:center;">
            <div style="font-size:1.125rem; font-weight:800; color:var(--clr-primary);"><?= $t['subject_count'] ?></div>
            <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase;">Subjects</div>
         </div>
      </div>
    </div>

    <div style="margin-top:auto; padding:0.75rem 1.5rem; background:linear-gradient(to right, var(--clr-surface-2), var(--clr-surface)); border-top:1px solid var(--clr-border); display:flex; justify-content:flex-end; gap:0.5rem; flex-wrap:wrap;">
       <button class="btn btn-secondary btn-xs" onclick="openAssignModal(<?= $t['id'] ?>, '<?= htmlspecialchars($t['full_name'], ENT_QUOTES) ?>')">
         <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg> ASSIGN
       </button>
       <button class="btn btn-ghost btn-xs" onclick='editTeacher(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)'>EDIT</button>
       <button class="btn btn-ghost btn-xs text-danger" onclick="confirmDeleteTeacher(<?= $t['id'] ?>, '<?= htmlspecialchars($t['full_name'], ENT_QUOTES) ?>')">DELETE</button>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>


<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- ══ Teacher Modal ════════════════════════════════════════ -->
<div id="modal-teacher" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-teacher-title" style="display:none;">
  <div class="modal w-full max-w-md mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-teacher-title">Teacher Profile</h3>
      <button class="modal-close" onclick="closeModal('modal-teacher')" aria-label="Close dialog">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/teachers" id="form-teacher" onsubmit="Loader.show()">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="teacher_store">
      <input type="hidden" name="teacher_id" id="teacher-id-field" value="">
      <div class="modal-body">
         <div class="form-group">
            <label class="form-label">Full Name <span class="required">*</span></label>
            <input type="text" name="full_name" id="teacher-name" class="form-control" placeholder="e.g. John Doe" required maxlength="200">
         </div>
         <div class="form-group">
            <label class="form-label">Email Address <span class="required">*</span></label>
            <input type="email" name="email" id="teacher-email" class="form-control" placeholder="johndoe@example.com" required maxlength="150" oninput="this.value = this.value.toLowerCase()">
            <p class="form-text">This will be used for login.</p>
         </div>
         <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" id="teacher-phone" class="form-control" placeholder="024XXXXXXX" maxlength="20">
         </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-teacher')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="teacher-submit-btn">Save teacher</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Form -->
<form method="POST" action="<?= $base ?>/admin/teachers" id="form-teacher-delete" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" value="teacher_delete">
  <input type="hidden" name="teacher_id" id="del-teacher-id">
</form>

<!-- ══ Assign Modal ════════════════════════════════════════ -->
<div id="modal-assign" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-assign-title" style="display:none;">
  <div class="modal w-full max-w-lg mx-4">
    <div class="modal-header">
      <div>
        <h3 class="modal-title" id="modal-assign-title">Assign Subjects</h3>
        <p class="text-muted text-xs mt-1 m-0" id="modal-assign-subtitle"></p>
      </div>
      <button class="modal-close" onclick="closeModal('modal-assign')" aria-label="Close dialog">&times;</button>
    </div>
    
    <div class="modal-body" style="padding-bottom:1rem;">
      <form method="POST" action="<?= $base ?>/admin/teachers" id="form-assign" onsubmit="Loader.show()">
        <?= CSRF::field() ?>
        <input type="hidden" name="_action" value="assign_subject">
        <input type="hidden" name="teacher_id" id="assign-teacher-id" value="">
        
        <div class="grid" style="grid-template-columns:1fr 1fr; gap:1rem; align-items:start;">
           <div class="form-group mb-0">
              <label class="form-label">Classes <span class="required">*</span></label>
              <div class="form-control" style="height:180px; overflow-y:auto; padding:0; background:var(--clr-surface-2);">
                 <?php foreach ($classes as $c): ?>
                    <label style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; border-bottom:1px solid var(--clr-border); cursor:pointer; font-size:13px; font-weight:500; margin:0; transition:background 0.2s;">
                       <input type="checkbox" name="class_ids[]" value="<?= $c['id'] ?>" style="accent-color:var(--clr-primary);">
                       <span><?= htmlspecialchars($c['class_name'] . ' ' . $c['section']) ?> <span class="text-muted" style="font-size:11px;">(<?= $c['level_name'] ?>)</span></span>
                    </label>
                 <?php endforeach; ?>
              </div>
           </div>
           <div class="form-group mb-0">
              <label class="form-label">Subjects <span class="required">*</span></label>
              <div class="form-control" style="height:180px; overflow-y:auto; padding:0; background:var(--clr-surface-2);">
                 <?php foreach ($subjects as $s): ?>
                    <label style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; border-bottom:1px solid var(--clr-border); cursor:pointer; font-size:13px; font-weight:500; margin:0; transition:background 0.2s;">
                       <input type="checkbox" name="subject_ids[]" value="<?= $s['id'] ?>" style="accent-color:var(--clr-primary);">
                       <span><?= htmlspecialchars($s['subject_name']) ?> <span class="text-muted" style="font-size:11px;">(<?= $s['level_name'] ?>)</span></span>
                    </label>
                 <?php endforeach; ?>
              </div>
           </div>
        </div>
        <button type="submit" class="btn btn-primary mt-4 w-full" style="width:100%; justify-content:center;">Assign Selected Combinations</button>
      </form>

      <div class="mt-6">
         <h4 style="font-size:var(--text-sm); font-weight:700; color:var(--clr-text); margin-bottom:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">Current Assignments</h4>
         <div id="assign-list-container" style="max-height:240px; overflow-y:auto; border:1px solid var(--clr-border); border-radius:var(--radius-md); background:var(--clr-bg);">
            <!-- Populated via JS -->
         </div>
      </div>
    </div>
  </div>
</div>

<!-- Remove Assignment Form -->
<form method="POST" action="<?= $base ?>/admin/teachers" id="form-assignment-remove" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" value="remove_subject">
  <input type="hidden" name="assignment_id" id="del-assignment-id">
</form>

<script>
const allAssignments = <?= json_encode($assignments) ?>;

function openTeacherModal() {
  document.getElementById('form-teacher').reset();
  document.getElementById('teacher-id-field').value = '';
  document.getElementById('modal-teacher-title').textContent = 'New Teacher Profile';
  document.getElementById('teacher-submit-btn').textContent = 'Create Profile';
  openModal('modal-teacher');
}

function editTeacher(t) {
  document.getElementById('form-teacher').reset();
  document.getElementById('teacher-id-field').value = t.id;
  document.getElementById('teacher-name').value = t.full_name;
  document.getElementById('teacher-email').value = t.email;
  document.getElementById('teacher-phone').value = t.phone || '';
  document.getElementById('modal-teacher-title').textContent = 'Edit Profile';
  document.getElementById('teacher-submit-btn').textContent = 'Update Profile';
  openModal('modal-teacher');
}

function confirmDeleteTeacher(id, name) {
  confirmAction(`Permanently remove teacher "${name}"?\n\nThis will also remove all their class and subject assignments. They will no longer be able to log in.`, () => {
    document.getElementById('del-teacher-id').value = id;
    document.getElementById('form-teacher-delete').submit();
  });
}

function openAssignModal(teacherId, teacherName) {
  document.getElementById('form-assign').reset();
  document.getElementById('assign-teacher-id').value = teacherId;
  document.getElementById('modal-assign-subtitle').textContent = `Teacher: ${teacherName}`;
  
  // Render assignments
  const container = document.getElementById('assign-list-container');
  const teacherAssignments = allAssignments.filter(a => Number(a.teacher_id) === Number(teacherId));
  
  if (teacherAssignments.length === 0) {
    container.innerHTML = `<div class="p-4 text-center text-muted text-sm">No assignments found for this academic year.</div>`;
  } else {
    let html = '';
    teacherAssignments.forEach(a => {
      html += `
        <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid var(--clr-border);">
          <div>
            <div style="font-weight:600; font-size:var(--text-sm); color:var(--clr-text);">${escapeHtml(a.subject_name)}</div>
            <div style="font-size:var(--text-xs); color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.02em;">${escapeHtml(a.class_name)} ${escapeHtml(a.section)}</div>
          </div>
          <button type="button" class="btn btn-ghost btn-xs text-danger" onclick="removeAssignment(${a.id})" aria-label="Remove Assignment">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      `;
    });
    // Remove last border
    html = html.replace(/border-bottom:1px solid var\(--clr-border\);"(?!.*border-bottom:1px solid var\(--clr-border\);")/s, 'border-bottom:none;"');
    container.innerHTML = html;
  }
  
  openModal('modal-assign');
}

function removeAssignment(id) {
  confirmAction({
    title: 'Remove Assignment?',
    message: 'Remove this subject assignment? This will remove the teacher from this specific class/subject for the current term.',
    confirmText: 'Yes, Remove',
    type: 'danger'
  }, () => {
    document.getElementById('del-assignment-id').value = id;
    document.getElementById('form-assignment-remove').submit();
  });
}

// Simple XSS escaper for JS templates
function escapeHtml(unsafe) {
    return (unsafe||'').toString()
         .replace(/&/g, "&amp;")
         .replace(/</g, "&lt;")
         .replace(/>/g, "&gt;")
         .replace(/"/g, "&quot;")
         .replace(/'/g, "&#039;");
}
</script>
