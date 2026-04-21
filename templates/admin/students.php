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

<!-- DataTables 2.1.8 + Responsive 3.0.3 + AutoFill 2.7.0 -->
<link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/autofill/2.7.0/css/autoFill.dataTables.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/autofill/2.7.0/js/dataTables.autoFill.min.js"></script>

<style>
/* Robust DataTables 2.x Styles */
div.dt-container { font-size: 13px; color: var(--clr-text); font-family: var(--font-sans); margin-top: 1rem; padding: 0 1rem 1rem 1rem; }
div.dt-layout-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
@media (max-width: 768px) {
    div.dt-layout-row { flex-direction: column; align-items: stretch; text-align: center; }
    div.dt-container .dt-search input { width: 100% !important; margin: 0 !important; }
}
div.dt-container .dt-search input { border: 1px solid var(--clr-border); border-radius: var(--radius-md); padding: 0.5rem 1rem; font-size: 13px; transition: all 0.2s ease; background: var(--clr-surface); }
div.dt-container .dt-search input:focus { outline: none; border-color: var(--clr-primary-400); box-shadow: 0 0 0 4px rgba(105, 43, 196, 0.1); }
div.dt-container .dt-length select { border: 1px solid var(--clr-border); border-radius: var(--radius-md); padding: 0.45rem 2rem 0.45rem 0.8rem; font-size: 13px; background: var(--clr-surface); }
div.dt-container .dt-paging { display: flex; align-items: center; justify-content: flex-end; gap: 0.25rem; margin-top: 1rem; }
div.dt-container .dt-paging .dt-paging-button { padding: 0.5rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--clr-border) !important; cursor: pointer; background: var(--clr-surface) !important; transition: all 0.2s; }
div.dt-container .dt-paging .dt-paging-button:hover { background: var(--clr-surface-2) !important; }
div.dt-container .dt-paging .dt-paging-button.current { background: var(--clr-primary) !important; color: white !important; border-color: var(--clr-primary) !important; font-weight: 700; }
table.dataTable thead th { border-bottom: 2px solid var(--clr-border) !important; color: var(--clr-text-muted); text-transform: uppercase; font-size: 11px; font-weight: 800; letter-spacing: 0.05em; padding: 1rem 0.75rem !important; }
/* Responsive '+' control styling */
table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control:before { 
    background-color: var(--clr-primary) !important;
    border: none !important;
    box-shadow: 0 2px 4px rgba(105, 43, 196, 0.3) !important;
}
</style>

<div class="flex justify-between items-center mb-6 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Students & Enrolment</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:600px;">Manage student profiles, registrations, and class placements.</p>
  </div>
  <button class="btn btn-primary shadow-purple" id="btn-register-top" style="height:42px;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Register Student
  </button>
</div>

<!-- Filters Toolbar -->
<div class="card p-4 mb-6 shadow-sm" style="border:1px solid var(--clr-border);">
  <form method="GET" action="<?= $base ?>/admin/students" class="flex items-center gap-4 flex-wrap" id="filter-form">
    <div class="flex items-center gap-2">
       <span style="font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Session</span>
       <select name="year_id" class="form-control filter-select" style="width:auto; min-width:140px; height:38px;">
          <?php foreach ($years as $y): ?>
            <option value="<?= $y['id'] ?>" <?= $filterYear == $y['id'] ? 'selected' : '' ?>><?= htmlspecialchars($y['year_name']) ?></option>
          <?php endforeach; ?>
       </select>
    </div>
    <div class="flex items-center gap-2">
       <span style="font-size:11px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase;">Class</span>
       <select name="class_id" class="form-control filter-select" style="width:auto; min-width:160px; height:38px;">
          <option value="">— All Classes —</option>
          <?php
          $byLevel = [];
          foreach ($classes as $c) {
              $byLevel[$c['level_name']][] = $c;
          }
          foreach ($byLevel as $levelName => $levelClasses):
          ?>
          <optgroup label="<?= htmlspecialchars($levelName) ?>">
            <?php foreach ($levelClasses as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $filterClass == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['class_name']) ?><?= $c['section'] ? " ({$c['section']})" : '' ?>
            </option>
            <?php endforeach; ?>
          </optgroup>
          <?php endforeach; ?>
       </select>
    </div>
  </form>
</div>

<?php if (empty($students)): ?>
<div class="card flex flex-col items-center justify-center shadow-lg" style="padding:6rem 2rem; text-align:center; border-style:dashed;">
  <div style="background:var(--clr-surface-2); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary-300)"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text);">No Students Found</h2>
  <button class="btn btn-primary" id="btn-register-empty">Register Student</button>
</div>
<?php else: ?>
<div class="card" style="padding:0; overflow:hidden;">
  <div class="table-wrapper">
    <table class="table display responsive nowrap" style="width:100%; margin-bottom:0;" id="student-table">
      <thead>
        <tr>
          <th class="all" style="padding-left:2rem;">ID</th>
          <th class="all">Full Name</th>
          <th class="min-desktop">Parent/Guardian</th>
          <th class="min-tablet">Class Placement</th>
          <th class="min-tablet text-center">Status</th>
          <th class="all text-right pr-8">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($students as $s): ?>
        <tr>
          <td style="padding-left:2rem;"><span style="font-family:var(--font-mono); font-weight:800; color:var(--clr-primary); font-size:12px;"><?= htmlspecialchars($s['student_id_number']) ?></span></td>
          <td>
            <div style="display:flex; align-items:center; gap:0.75rem;">
               <div style="width:32px; height:32px; background:var(--clr-surface-2); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; color:var(--clr-primary-700); font-weight:800; font-size:11px;"><?= substr($s['full_name'], 0, 1) ?></div>
               <div style="font-weight:700; color:var(--clr-text); line-height:1.2; display:flex; align-items:center; gap:0.5rem;">
                  <?= htmlspecialchars($s['full_name']) ?>
                  <?php if (($s['gender'] ?? '') === 'Male'): ?>
                    <span class="badge" style="background:rgba(59,130,246,0.1); color:#3b82f6; border:1px solid rgba(59,130,246,0.2); font-size:9px; padding:1px 4px;">M</span>
                  <?php elseif (($s['gender'] ?? '') === 'Female'): ?>
                    <span class="badge" style="background:rgba(236,72,153,0.1); color:#ec4899; border:1px solid rgba(236,72,153,0.2); font-size:9px; padding:1px 4px;">F</span>
                  <?php endif; ?>
               </div>
            </div>
          </td>
          <td><?= $s['linked_parents'] ? '<div style="font-size:11px; font-weight:600;">'.htmlspecialchars($s['linked_parents']).'</div>' : '<span class="text-muted italic block" style="font-size:11px;">None</span>' ?></td>
          <td><div style="font-weight:600;"><?= htmlspecialchars($s['class_name'] ?: 'Unassigned') ?></div><div style="font-size:11px; color:var(--clr-text-muted);"><?= htmlspecialchars($s['section'] ?: '') ?></div></td>
          <td class="text-center"><span class="badge badge-<?= $s['status'] == 'active' ? 'success' : 'warning' ?>" style="font-size:10px;"><?= strtoupper($s['status']) ?></span></td>
          <td class="text-right pr-8">
            <div class="flex justify-end gap-2">
              <button class="btn btn-ghost btn-xs act-parent" data-id="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['full_name'], ENT_QUOTES) ?>" style="color:var(--clr-primary);" title="Manage Parents">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              </button>
              <button class="btn btn-ghost btn-xs act-edit" data-student='<?= htmlspecialchars(json_encode($s), ENT_QUOTES) ?>' title="Edit Profile">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
              </button>
              <button class="btn btn-ghost btn-xs act-delete text-danger" data-id="<?= $s['id'] ?>" data-name="<?= htmlspecialchars($s['full_name'], ENT_QUOTES) ?>" data-has-records="<?= $s['has_records'] ? 1 : 0 ?>" title="Remove Record">
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
<div id="modal-student" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
    <div class="modal w-full max-w-xl mx-4">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-student-title">Student Registration</h3>
            <button class="modal-close" onclick="closeModal('modal-student')">&times;</button>
        </div>
        <form method="POST" action="<?= $base ?>/admin/students?year_id=<?= urlencode((string)$filterYear) ?>&class_id=<?= urlencode((string)$filterClass) ?>" id="form-student" onsubmit="Loader.show()">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="student_store">
            <input type="hidden" name="student_id" id="student-id-field" value="">
            <input type="hidden" name="academic_year_id" value="<?= $filterYear ?>">
            <div class="modal-body">
                <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
                    <div class="form-group"><label class="form-label">Student ID <span class="required">*</span></label><input type="text" name="student_id_number" id="stu-id-num" class="form-control" required placeholder="e.g. 20240001"></div>
                    <div class="form-group"><label class="form-label">Full Name <span class="required">*</span></label><input type="text" name="full_name" id="stu-name" class="form-control" required placeholder="e.g. Ama Serwaa"></div>
                </div>
                <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
                    <div class="form-group"><label class="form-label">Surname</label><input type="text" name="surname" id="stu-surname" class="form-control" placeholder="Optional"></div>
                    <div class="form-group">
                        <label class="form-label">Class <span class="required">*</span></label>
                        <select name="current_class_id" id="stu-class" class="form-control" required>
                          <option value="">— Select —</option>
                          <?php
                          if (!isset($byLevel)) {
                              $byLevel = [];
                              foreach ($classes as $c) { $byLevel[$c['level_name']][] = $c; }
                          }
                          foreach ($byLevel as $lvlName => $lvlClasses):
                          ?>
                          <optgroup label="<?= htmlspecialchars($lvlName) ?>">
                            <?php foreach ($lvlClasses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name'] . ($c['section'] ? ' ' . $c['section'] : '')) ?></option>
                            <?php endforeach; ?>
                          </optgroup>
                          <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
                    <div class="form-group"><label class="form-label">Gender</label><select name="gender" id="stu-gender" class="form-control"><option value="">— Select —</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
                    <div class="form-group"><label class="form-label">DOB</label><input type="date" name="date_of_birth" id="stu-dob" class="form-control"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Account Status</label>
                    <select name="status" id="stu-status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="transferred">Transferred</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('modal-student')">Cancel</button><button type="submit" class="btn btn-primary" id="student-submit-btn">Complete</button></div>
        </form>
    </div>
</div>

<form method="POST" action="<?= $base ?>/admin/students?year_id=<?= urlencode((string)$filterYear) ?>&class_id=<?= urlencode((string)$filterClass) ?>" id="form-student-delete" style="display:none">
    <?= CSRF::field() ?>
    <input type="hidden" name="_action" value="student_delete">
    <input type="hidden" name="student_id" id="del-student-id">
    <input type="hidden" name="force_delete" id="del-force-delete" value="0">
</form>

<!-- Parent Modal -->
<div id="modal-parent" class="modal-backdrop" role="dialog" aria-modal="true" style="display:none;">
    <div class="modal w-full max-w-lg mx-4">
        <div class="modal-header"><div><h3 class="modal-title" id="modal-parent-title">Guardian Management</h3><div id="modal-parent-subtitle" style="font-size:12px; opacity:0.7;"></div></div><button class="modal-close" onclick="closeModal('modal-parent')">&times;</button></div>
        <div class="modal-body">
            <div id="modal-parent-list" class="mb-5"></div>
            <div style="background:var(--clr-surface-2); border-radius:var(--radius-md); padding:1.25rem; border:1px solid var(--clr-border);">
                <h4 style="font-size:12px; font-weight:800; margin-bottom:1rem;" id="parent-form-title">LINK CONTACT</h4>
                <form method="POST" action="<?= $base ?>/admin/students?year_id=<?= urlencode((string)$filterYear) ?>&class_id=<?= urlencode((string)$filterClass) ?>" id="form-parent-link" onsubmit="Loader.show()">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="_action" value="parent_link">
                    <input type="hidden" name="student_id" id="parent-link-student-id">
                    <div class="form-group"><label class="form-label">Full Name <span class="required">*</span></label><input type="text" name="parent_name" id="parent-name-field" class="form-control" required placeholder="Parent or Guardian Name"></div>
                    <div class="grid" style="grid-template-columns:1fr 1fr; gap:1rem;">
                        <div class="form-group"><label class="form-label">Phone # <span class="required">*</span></label><input type="tel" name="parent_phone" id="parent-phone-field" class="form-control" required inputmode="numeric" placeholder="e.g. 0244000000"></div>
                        <div class="form-group"><label class="form-label">Relationship</label><select name="relationship" id="parent-rel-field" class="form-control"><option value="Parent">Parent</option><option value="Father">Father</option><option value="Mother">Mother</option><option value="Guardian">Guardian</option><option value="Other">Other</option></select></div>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" id="parent-submit-btn">Update Active Contact</button>
                    <p style="font-size:10px; color:var(--clr-text-muted); margin-top:0.75rem; text-align:center;">Note: Setting a new contact will replace the existing one.</p>
                </form>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-ghost" onclick="closeModal('modal-parent')">Close</button></div>
    </div>
</div>

<script>
const allStudents = <?= json_encode($students) ?>;
$(document).ready(function() {
    // 1. Initialize DataTable with Responsive support
    const table = $('#student-table').DataTable({
        autoFill: true,
        responsive: true,
        pageLength: 25,
        dom: '<"dt-layout-row"lf>rt<"dt-layout-row"ip>',
        language: {
            search: "", searchPlaceholder: "Search records...",
            lengthMenu: "Show _MENU_",
            paginate: { previous: 'Prev', next: 'Next' }
        },
        columnDefs: [{ orderable: false, targets: -1 }]
    });

    // 2. Global Function Definitions (Outside ready scopes but within script)
    window.openStudentModal = function() {
        $('#form-student')[0].reset();
        $('#student-id-field').val('');
        $('#stu-status').val('active');
        $('#modal-student-title').text('New Student Registration');
        $('#student-submit-btn').text('Complete Registration');
        openModal('modal-student');
    };

    window.editStudent = function(s) {
        $('#form-student')[0].reset();
        $('#student-id-field').val(s.id);
        $('#stu-id-num').val(s.student_id_number);
        $('#stu-name').val(s.full_name);
        $('#stu-surname').val(s.surname || '');
        $('#stu-class').val(s.current_class_id);
        $('#stu-gender').val(s.gender || '');
        $('#stu-dob').val(s.date_of_birth || '');
        $('#stu-status').val(s.status || 'active');
        $('#modal-student-title').text('Edit Profile: ' + s.full_name);
        $('#student-submit-btn').text('Update Record');
        openModal('modal-student');
    };

    window.openParentModal = function(id, name) {
        $('#parent-link-student-id').val(id);
        $('#modal-parent-subtitle').text('Student: ' + name);
        resetParentForm();
        fetchParents(id);
        openModal('modal-parent');
    };

    function resetParentForm() {
        $('#form-parent-link')[0].reset();
        $('#parent-form-title').text('LINK CONTACT');
        $('#parent-submit-btn').text('Update Active Contact');
    }

    function fetchParents(studentId) {
        $('#modal-parent-list').html('<div class="p-4 text-center text-muted">Loading contact info...</div>');
        $.get('<?= $base ?>/admin/students', { _action: 'parent_get', student_id: studentId }, function(data) {
            if (!data || data.length === 0) {
                $('#modal-parent-list').html('<div class="text-sm text-muted italic p-3 surface-2 rounded border">No active contact linked.</div>');
                return;
            }

            let html = `
                <table class="table table-sm" style="font-size:12px;">
                    <thead>
                        <tr style="background:var(--clr-surface-2)">
                            <th style="padding:0.5rem;">Contact Name</th>
                            <th>Phone</th>
                            <th>Rel.</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>`;
            data.forEach(p => {
                html += `
                    <tr>
                        <td style="padding:0.5rem; font-weight:700;">${p.parent_name}</td>
                        <td style="font-family:var(--font-mono);">${p.parent_phone}</td>
                        <td><span class="badge" style="background:var(--clr-surface-2); font-size:9px;">${p.relationship}</span></td>
                        <td class="text-right">
                           <div class="flex justify-end gap-1">
                               <button onclick="editParentLink('${p.parent_name}', '${p.parent_phone}', '${p.relationship}')" class="btn btn-ghost btn-xs" title="Edit"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg></button>
                               <button onclick="confirmUnlinkParent(${p.link_id})" class="btn btn-ghost btn-xs text-danger" title="Unlink"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                           </div>
                        </td>
                    </tr>`;
            });
            html += '</tbody></table>';
            $('#modal-parent-list').html(html);
        });
    }

    window.editParentLink = function(name, phone, rel) {
        $('#parent-name-field').val(name);
        $('#parent-phone-field').val(phone);
        $('#parent-rel-field').val(rel);
        $('#parent-form-title').text('EDIT CONTACT');
        $('#parent-submit-btn').text('Save Changes');
    };

    window.confirmUnlinkParent = function(linkId) {
        confirmAction({
            title: 'Unlink Contact?',
            message: 'This will remove the parent/guardian phone link from this student. They will no longer be able to access the student portal for this record.',
            confirmText: 'Unlink Now',
            type: 'danger'
        }, () => {
            const form = $('<form method="POST" action="">')
                .append($('<input type="hidden" name="_action" value="parent_unlink">'))
                .append($('<input type="hidden" name="link_id" value="' + linkId + '">'))
                .append($('<?= CSRF::field() ?>'));
            $('body').append(form);
            Loader.show();
            form.submit();
        });
    };

    window.confirmDeleteStudent = function(id, name, isDeep = false) {
        confirmAction({
            title: isDeep ? 'Wipe All Data & Delete?' : 'Permanently Delete Profile?',
            message: isDeep 
                ? `BE CAREFUL: "${name}" has academic records (marks/attendance). If you proceed, ALL their scores and history will be deleted forever and cannot be recovered. Are you absolutely sure?`
                : `Are you sure you want to completely remove the profile for "${name}"? This action is permanent and cannot be undone.`,
            confirmText: isDeep ? 'Yes, Wipe & Delete Everything' : 'Yes, Delete Profile',
            type: 'danger'
        }, () => {
            $('#del-student-id').val(id);
            $('#del-force-delete').val(isDeep ? '1' : '0');
            $('#form-student-delete').submit();
        });
    };

    // 3. Event Delegation for Action Buttons (Robust for Redraws/Responsive)
    $('#student-table').on('click', '.act-edit', function() {
        const data = $(this).data('student');
        window.editStudent(data);
    });
    $('#student-table').on('click', '.act-parent', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        window.openParentModal(id, name);
    });
    $('#student-table').on('click', '.act-delete', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const hasRecords = $(this).data('has-records');

        if (hasRecords) {
            confirmAction({
                title: 'Student Has Academic Records',
                message: `The student "${name}" has recorded scores and attendance in the system. To keep these records safe, we recommend changing their status to "Inactive" instead of deleting.`,
                confirmText: 'Make Inactive (Recommended)',
                cancelText: 'Delete Anyway',
                type: 'warning'
            }, () => {
                // Primary Action: Change to Inactive
                const actionUrl = '<?= $base ?>/admin/students?year_id=<?= urlencode((string)$filterYear) ?>&class_id=<?= urlencode((string)$filterClass) ?>';
                const form = $('<form method="POST" action="' + actionUrl + '">')
                    .append($('<input type="hidden" name="_action" value="student_status">'))
                    .append($('<input type="hidden" name="student_id" value="' + id + '">'))
                    .append($('<input type="hidden" name="status" value="inactive">'))
                    .append($('<?= CSRF::field() ?>'));
                $('body').append(form);
                Loader.show();
                form.submit();
            }, () => {
                // Secondary Action (Cancel Clicked -> Proceed to Deep Delete)
                window.confirmDeleteStudent(id, name, true);
            });
        } else {
            window.confirmDeleteStudent(id, name, false);
        }
    });

    // 4. Register Buttons (Static)
    $('#btn-register-top, #btn-register-empty').on('click', window.openStudentModal);

    // 5. Filters (Submit on Change)
    $('.filter-select').on('change', function() {
        if (typeof Loader !== 'undefined') Loader.show();
        $(this).closest('form').submit();
    });
});

// Auto-dismiss bulk result banner
const banner = document.getElementById('bulk-result-banner');
if (banner) setTimeout(() => banner.style.display = 'none', 8000);
</script>
