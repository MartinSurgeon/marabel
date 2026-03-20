<?php
/**
 * Students Management View
 * HCI/UX: Class-based filtering, quick registration, status management, student ID search
 */
$pageTitle = 'Student Management';
include __DIR__ . '/../layout/header.php';

global $studentsList, $classesList, $yearsList, $activeYearId;
$base       = defined('APP_BASE') ? APP_BASE : '';
$students   = $studentsList ?? [];
$classes    = $classesList ?? [];
$years      = $yearsList ?? [];

$filterYear  = $_GET['year_id'] ?? $activeYearId;
$filterClass = $_GET['class_id'] ?? null;
?>

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Students & Enrolment</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:600px;">
      Manage student profiles, registrations, and class placements for the academic session.
    </p>
  </div>
  <button class="btn btn-primary shadow-purple" onclick="openStudentModal()" style="height:42px;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Register Student
  </button>
</div>

<!-- Filters Toolbar -->
<div class="card p-4 mb-6 shadow-sm" style="border:1px solid var(--clr-border);">
  <form method="GET" action="<?= $base ?>/admin/students" class="flex items-center gap-4 flex-wrap" id="filter-form">
    <div class="flex items-center gap-2">
       <span style="font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Session</span>
       <select name="year_id" class="form-control" style="width:auto; min-width:140px; height:38px;" onchange="Loader.show(); this.form.submit()">
          <?php foreach ($years as $y): ?>
            <option value="<?= $y['id'] ?>" <?= $filterYear == $y['id'] ? 'selected' : '' ?>>
               <?= htmlspecialchars($y['year_name']) ?>
            </option>
          <?php endforeach; ?>
       </select>
    </div>
    
    <div class="flex items-center gap-2">
       <span style="font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Class</span>
       <select name="class_id" class="form-control" style="width:auto; min-width:160px; height:38px;" onchange="Loader.show(); this.form.submit()">
          <option value="">— All Classes —</option>
          <?php foreach ($classes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $filterClass == $c['id'] ? 'selected' : '' ?>>
               <?= htmlspecialchars($c['class_name']) ?><?= $c['section'] ? " ({$c['section']})" : '' ?>
            </option>
          <?php endforeach; ?>
       </select>
    </div>

    <div style="flex:1; min-width:200px; position:relative;">
       <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--clr-text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
       <input type="text" id="student-search" placeholder="Quick search by name or ID..." class="form-control" style="padding-left:36px; height:38px;">
    </div>
  </form>
</div>

<?php if (empty($students)): ?>
<div class="card flex flex-col items-center justify-center shadow-lg" style="padding:6rem 2rem; text-align:center; border-style:dashed;">
  <div style="background:var(--clr-surface-2); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary-300)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text);">No Students Found</h2>
  <p class="text-muted" style="max-width:320px; margin:0 auto 2.5rem;">Register your first student or adjust your filters to see the roster.</p>
  <button class="btn btn-primary" onclick="openStudentModal()">Register Student</button>
</div>

<?php else: ?>
<div class="card" style="padding:0; overflow:hidden;">
  <div class="table-wrapper">
    <table class="table" style="margin-bottom:0;" id="student-table">
      <thead>
        <tr>
          <th style="padding-left:2rem;">Student ID</th>
          <th>Full Name</th>
          <th>Gender</th>
          <th>Class Placement</th>
          <th style="width:120px;" class="text-center">Status</th>
          <th style="width:120px;" class="text-right pr-8">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
          <td style="padding-left:2rem;">
            <span style="font-family:var(--font-mono); font-weight:800; color:var(--clr-primary); font-size:12px;"><?= htmlspecialchars($s['student_id_number']) ?></span>
          </td>
          <td>
            <div style="display:flex; align-items:center; gap:0.75rem;">
               <div style="width:32px; height:32px; background:var(--clr-surface-2); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; color:var(--clr-primary-700); font-weight:800; font-size:11px;">
                  <?= substr($s['full_name'], 0, 1) ?>
               </div>
               <div>
                  <div style="font-weight:700; color:var(--clr-text); line-height:1.2;"><?= htmlspecialchars($s['full_name']) ?></div>
                  <?php if ($s['surname']): ?>
                    <div style="font-size:10px; color:var(--clr-text-muted); text-transform:uppercase; font-weight:600;"><?= htmlspecialchars($s['surname']) ?></div>
                  <?php endif; ?>
               </div>
            </div>
          </td>
          <td>
             <span class="badge" style="background:<?= $s['gender'] == 'Male' ? '#e1f5fe' : '#fce4ec' ?>; color:<?= $s['gender'] == 'Male' ? '#0288d1' : '#c2185b' ?>; font-size:10px; font-weight:800; border:none;">
               <?= strtoupper($s['gender'] ?: 'N/A') ?>
             </span>
          </td>
          <td>
             <div style="font-weight:600; color:var(--clr-text);"><?= htmlspecialchars($s['class_name'] ?: 'Unassigned') ?></div>
             <div style="font-size:11px; color:var(--clr-text-muted);"><?= htmlspecialchars($s['section'] ?: 'No section') ?></div>
          </td>
          <td class="text-center">
             <span class="badge badge-<?= $s['status'] == 'active' ? 'success' : 'warning' ?>" style="font-size:10px;"><?= strtoupper($s['status']) ?></span>
          </td>
          <td class="text-right pr-8">
            <div class="flex justify-end gap-2">
              <button class="btn btn-ghost btn-xs" onclick='editStudent(<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>)' data-tooltip="Edit profile">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              </button>
              <button class="btn btn-ghost btn-xs text-danger" onclick="confirmDeleteStudent(<?= $s['id'] ?>, '<?= htmlspecialchars($s['full_name'], ENT_QUOTES) ?>')" data-tooltip="Remove record">
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
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- ══ Student Registration Modal ══════════════════════════════ -->
<div id="modal-student" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-student-title" style="display:none;">
  <div class="modal w-full max-w-xl mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-student-title">Student Registration</h3>
      <button class="modal-close" onclick="closeModal('modal-student')" aria-label="Close dialog">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/students" id="form-student" onsubmit="Loader.show()">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="student_store">
      <input type="hidden" name="student_id" id="student-id-field" value="">
      <input type="hidden" name="academic_year_id" value="<?= $filterYear ?>">
      <div class="modal-body">
         
         <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div class="form-group">
               <label class="form-label">Student ID / Index No. <span class="required">*</span></label>
               <input type="text" name="student_id_number" id="stu-id-num" class="form-control" required maxlength="50" placeholder="e.g. 20240001">
            </div>
            <div class="form-group">
               <label class="form-label">Full Name <span class="required">*</span></label>
               <input type="text" name="full_name" id="stu-name" class="form-control" required maxlength="200" placeholder="e.g. Ama Serwaa">
            </div>
         </div>

         <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div class="form-group">
               <label class="form-label">Surname</label>
               <input type="text" name="surname" id="stu-surname" class="form-control" maxlength="100" placeholder="e.g. Appiah">
            </div>
            <div class="form-group">
               <label class="form-label">Class Placement <span class="required">*</span></label>
               <select name="current_class_id" id="stu-class" class="form-control" required>
                  <option value="">— Select Class —</option>
                  <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name']) ?><?php if($c['section']) echo " ({$c['section']})"; ?> — <?= htmlspecialchars($c['level_name']) ?></option>
                  <?php endforeach; ?>
               </select>
            </div>
         </div>

         <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
            <div class="form-group">
               <label class="form-label">Gender</label>
               <select name="gender" id="stu-gender" class="form-control">
                  <option value="">— Select —</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                  <option value="Other">Other</option>
               </select>
            </div>
            <div class="form-group">
               <label class="form-label">Date of Birth</label>
               <input type="date" name="date_of_birth" id="stu-dob" class="form-control">
            </div>
         </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-student')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="student-submit-btn">Complete Registration</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Form -->
<form method="POST" action="<?= $base ?>/admin/students" id="form-student-delete" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" value="student_delete">
  <input type="hidden" name="student_id" id="del-student-id">
</form>

<script>
function openStudentModal() {
  document.getElementById('form-student').reset();
  document.getElementById('student-id-field').value = '';
  document.getElementById('modal-student-title').textContent = 'New Student Registration';
  document.getElementById('student-submit-btn').textContent = 'Complete Registration';
  openModal('modal-student');
}

function editStudent(s) {
  document.getElementById('form-student').reset();
  document.getElementById('student-id-field').value = s.id;
  document.getElementById('stu-id-num').value = s.student_id_number;
  document.getElementById('stu-name').value = s.full_name;
  document.getElementById('stu-surname').value = s.surname || '';
  document.getElementById('stu-class').value = s.current_class_id;
  document.getElementById('stu-gender').value = s.gender || '';
  document.getElementById('stu-dob').value = s.date_of_birth || '';
  document.getElementById('modal-student-title').textContent = 'Edit Profile: ' + s.full_name;
  document.getElementById('student-submit-btn').textContent = 'Update Record';
  openModal('modal-student');
}

function confirmDeleteStudent(id, name) {
  confirmAction(`Permanently remove record for "${name}"?\n\nThis will delete all their academic history if no scores are locked. This action cannot be undone.`, () => {
    document.getElementById('del-student-id').value = id;
    document.getElementById('form-student-delete').submit();
  });
}

// Client-side search
document.getElementById('student-search').addEventListener('input', function(e) {
  const q = e.target.value.toLowerCase();
  const rows = document.querySelectorAll('#student-table tbody tr');
  rows.forEach(row => {
    const text = row.innerText.toLowerCase();
    row.style.display = text.includes(q) ? '' : 'none';
  });
});
</script>
