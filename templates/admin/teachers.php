<?php
/**
 * Teachers Management View
 * HCI/UX: Hybrid List & Grid view switcher to reduce cognitive overload
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

<!-- DataTables 2.1.8 + Responsive 3.0.3 -->
<link href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.dataTables.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.min.js"></script>

<style>
/* DataTable 2.x Styling Consistent with Students Page */
div.dt-container { font-size: 13px; color: var(--clr-text); font-family: var(--font-sans); margin-top: 1rem; }
div.dt-layout-row { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
@media (max-width: 768px) {
    div.dt-layout-row { flex-direction: column; align-items: stretch; text-align: center; }
}
div.dt-container .dt-search input { border: 1px solid var(--clr-border); border-radius: var(--radius-md); padding: 0.5rem 1.75rem 0.5rem 1rem; font-size: 13px; transition: all 0.2s ease; background: var(--clr-surface); width: 240px; }
div.dt-container .dt-search input:focus { outline: none; border-color: var(--clr-primary); box-shadow: 0 0 0 4px rgba(105, 43, 196, 0.1); }
div.dt-container .dt-length select { border: 1px solid var(--clr-border); border-radius: var(--radius-md); padding: 0.45rem 2rem 0.45rem 0.8rem; font-size: 13px; background: var(--clr-surface); }
div.dt-container .dt-paging { display: flex; align-items: center; justify-content: flex-end; gap: 0.25rem; margin-top: 1rem; }
div.dt-container .dt-paging .dt-paging-button { padding: 0.5rem 1rem; border-radius: var(--radius-md); border: 1px solid var(--clr-border) !important; cursor: pointer; background: var(--clr-surface) !important; transition: all 0.2s; }
div.dt-container .dt-paging .dt-paging-button.current { background: var(--clr-primary) !important; color: white !important; border-color: var(--clr-primary) !important; }
table.dataTable thead th { border-bottom: 2px solid var(--clr-border) !important; color: var(--clr-text-muted); text-transform: uppercase; font-size: 11px; font-weight: 800; letter-spacing: 0.05em; padding: 1.25rem 0.75rem !important; }
table.dataTable tbody td { vertical-align: middle !important; padding: 0.875rem 0.75rem !important; }

/* View Switcher Styles */
.view-toggle-btn { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: var(--radius-md); background: var(--clr-surface-2); color: var(--clr-text-muted); border: 1px solid var(--clr-border); cursor: pointer; transition: all 0.2s; }
.view-toggle-btn.active { background: var(--clr-primary); color: white; border-color: var(--clr-primary); box-shadow: 0 4px 6px -1px rgba(105, 43, 196, 0.2); }
.view-toggle-btn:hover:not(.active) { background: var(--clr-surface); color: var(--clr-primary); }
</style>

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Teachers</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm);">Manage your teaching staff, status, and class/subject assignments.</p>
  </div>
  
  <div class="flex items-center gap-3">
    <!-- View Switcher (HCI: User Control & Preference) -->
    <div class="flex bg-gray-100 p-1 rounded-lg border border-gray-200" style="background:rgba(0,0,0,0.03);">
       <button type="button" class="view-toggle-btn" id="btn-view-list" onclick="toggleView('list')" title="List View">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
       </button>
       <button type="button" class="view-toggle-btn" id="btn-view-grid" onclick="toggleView('grid')" title="Grid View" style="margin-left:2px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
       </button>
    </div>

    <button class="btn btn-primary shadow-purple" onclick="openTeacherModal()" style="height:42px;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Add Teacher
    </button>
  </div>
</div>

<?php if (empty($teachers)): ?>
<div class="card flex flex-col items-center justify-center shadow-sm" style="padding:6rem 2rem; text-align:center; border-style:dashed; background:rgba(255,255,255,0.5);">
  <div style="background:var(--clr-primary-50); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text);">No teachers registered yet</h2>
  <p class="text-muted" style="max-width:320px; margin:0 auto 2.5rem;">Create profiles for your teaching staff to allow them to enter student scores.</p>
  <button class="btn btn-primary btn-lg" onclick="openTeacherModal()">Register First Teacher</button>
</div>

<?php else: ?>
<!-- ══ List View Container ══════════════════════════════════════ -->
<div id="view-list" style="display:none;">
    <div class="card shadow-sm" style="padding:0; overflow:hidden; border:1px solid var(--clr-border);">
        <table id="teacher-table" class="display responsive nowrap" style="width:100%">
            <thead>
                <tr>
                    <th class="all">Teacher Name</th>
                    <th class="min-desktop">Contact</th>
                    <th class="min-tablet">Role</th>
                    <th class="min-desktop">Assignments</th>
                    <th class="all text-center">Status</th>
                    <th class="all text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teachers as $t): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            <div style="width:34px; height:34px; background:var(--clr-primary-50); color:var(--clr-primary); border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">
                                <?= substr($t['full_name'], 0, 1) ?>
                            </div>
                            <div>
                                <div style="font-weight:700; color:var(--clr-text); font-size:14px;"><?= htmlspecialchars($t['full_name']) ?></div>
                                <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.02em;"><?= htmlspecialchars($t['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div style="font-size:12px; font-weight:600; color:var(--clr-text-muted);">
                           <?= htmlspecialchars($t['phone'] ?: 'N/A') ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($t['lead_classes']): ?>
                            <span class="badge badge-purple" style="font-size:9px; padding:2px 6px;">CT: <?= htmlspecialchars($t['lead_classes']) ?></span>
                        <?php else: ?>
                            <span class="badge badge-gray" style="font-size:9px; padding:2px 6px;">Teacher</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-size:12px; font-weight:700; color:var(--clr-text);">
                            <?= $t['class_count'] ?> Cls · <?= $t['subject_count'] ?> Subj
                        </div>
                        <div style="font-size:10px; color:var(--clr-text-muted); max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?= htmlspecialchars($t['assignment_summary'] ?: 'No assignments') ?>
                        </div>
                    </td>
                    <td class="text-center">
                        <form method="POST" action="<?= $base ?>/admin/teachers" onsubmit="Loader.show()">
                            <?= CSRF::field() ?>
                            <input type="hidden" name="_action" value="teacher_toggle">
                            <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
                            <button type="submit" class="badge <?= $t['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer; border:none; padding:4px 8px; font-size:9px; font-weight:800; letter-spacing:0.03em;">
                                <?= $t['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
                            </button>
                        </form>
                    </td>
                    <td class="text-right">
                        <div class="flex justify-end gap-1">
                            <button class="btn btn-ghost btn-xs act-assign" data-id="<?= $t['id'] ?>" data-name="<?= htmlspecialchars($t['full_name'], ENT_QUOTES) ?>" style="color:var(--clr-primary);" title="Assign Subjects">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            </button>
                            <button class="btn btn-ghost btn-xs act-edit" data-teacher='<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>' title="Edit Profile">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ══ Grid View Container ══════════════════════════════════════ -->
<div id="view-grid" class="grid" style="grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:1.5rem; display:none;">
  <?php foreach ($teachers as $t): ?>
  <div class="card hover-lift flex flex-col <?= !$t['is_active'] ? 'grayscale opacity-60' : '' ?>" style="padding:0; overflow:hidden; border:1px solid rgba(0,0,0,0.05); background:rgba(255,255,255,0.7); backdrop-filter:blur(10px); border-radius:var(--radius-xl); box-shadow:0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);">
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
           <button type="submit" class="badge <?= $t['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer; border:none; padding:5px 10px; font-size:9px; letter-spacing:0.05em; box-shadow:0 2px 4px rgba(0,0,0,0.05);">
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

      <div style="margin-top:0.5rem;">
        <?php if ($t['lead_classes']): ?>
          <div class="mb-2">
            <span style="font-size:10px; font-weight:700; color:var(--clr-primary-600); text-transform:uppercase; letter-spacing:0.05em; display:block; margin-bottom:4px;">Class Teacher</span>
            <div class="flex flex-wrap gap-1">
              <?php foreach (explode(', ', $t['lead_classes']) as $lc): ?>
                <span class="badge badge-purple" style="font-size:10px; padding:2px 6px;"><?= htmlspecialchars($lc) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($t['assignment_summary']): ?>
          <div class="mb-2">
            <span style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em; display:block; margin-bottom:4px;">Teaching</span>
            <div class="flex flex-wrap gap-1">
              <?php foreach (explode(', ', $t['assignment_summary']) as $as): ?>
                <span class="badge badge-gray" style="font-size:10px; padding:2px 6px;"><?= htmlspecialchars($as) ?></span>
              <?php endforeach; ?>
            </div>
          </div>
        <?php else: ?>
          <div style="font-size:11px; color:var(--clr-text-muted); font-style:italic; margin-top:0.5rem;">No subjects assigned yet</div>
        <?php endif; ?>
      </div>

      <div class="grid" style="grid-template-columns:1fr 1fr; gap:0.75rem; border-top:1px solid rgba(0,0,0,0.05); padding-top:1.25rem; margin-top:1.5rem;">
         <div style="background:var(--clr-primary-50); padding:0.875rem; border-radius:var(--radius-lg); text-align:center; border:1px solid rgba(79, 29, 150, 0.05);">
            <div style="font-size:1.375rem; font-weight:900; color:var(--clr-primary); letter-spacing:-0.03em;"><?= $t['class_count'] ?></div>
            <div style="font-size:10px; font-weight:800; color:var(--clr-primary-600); text-transform:uppercase; letter-spacing:0.04em;">Classes</div>
         </div>
         <div style="background:rgba(124, 58, 237, 0.05); padding:0.875rem; border-radius:var(--radius-lg); text-align:center; border:1px solid rgba(124, 58, 237, 0.05);">
            <div style="font-size:1.375rem; font-weight:900; color:var(--clr-accent); letter-spacing:-0.03em;"><?= $t['subject_count'] ?></div>
            <div style="font-size:10px; font-weight:800; color:var(--clr-accent); text-transform:uppercase; letter-spacing:0.04em;">Subjects</div>
         </div>
      </div>
    </div>

    <div style="margin-top:auto; padding:0.875rem 1.5rem; background:rgba(249,250,251,0.5); border-top:1px solid rgba(0,0,0,0.04); display:flex; justify-content:flex-end; gap:0.625rem; flex-wrap:wrap;">
       <button class="btn btn-secondary btn-xs font-bold" onclick="openAssignModal(<?= $t['id'] ?>, '<?= htmlspecialchars($t['full_name'], ENT_QUOTES) ?>')">ASSIGN</button>
       <button class="btn btn-ghost btn-xs font-bold" onclick='editTeacher(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)' style="color:var(--clr-primary);">EDIT</button>
       <button class="btn btn-ghost btn-xs font-bold text-danger" onclick="confirmDeleteTeacher(<?= $t['id'] ?>, '<?= htmlspecialchars($t['full_name'], ENT_QUOTES) ?>')">DELETE</button>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ══ Teacher Modal (Integrated Tabbed View) ══════════════════════════════════ -->
<div id="modal-teacher" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-teacher-title" style="display:none;">
  <div class="modal w-full max-w-2xl mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-teacher-title">Teacher Profile</h3>
      <button class="modal-close" onclick="closeModal('modal-teacher')" aria-label="Close dialog">&times;</button>
    </div>
    
    <!-- Tabs Header (HCI: Grouping related tasks) -->
    <div class="flex border-b border-gray-200 px-6">
      <button onclick="switchTeacherTab('profile')" id="tab-profile" class="py-3 px-4 text-sm font-bold border-b-2 border-primary text-primary transition-all">Profile Details</button>
      <button onclick="switchTeacherTab('assignments')" id="tab-assignments" class="py-3 px-4 text-sm font-bold border-b-2 border-transparent text-muted hover:text-primary transition-all">Subject Assignments</button>
    </div>

    <div class="modal-body" style="padding-top:1.5rem;">
      <!-- Profile Tab -->
      <div id="pane-profile">
        <form method="POST" action="<?= $base ?>/admin/teachers" id="form-teacher" onsubmit="Loader.show()">
          <?= CSRF::field() ?>
          <input type="hidden" name="_action" value="teacher_store">
          <input type="hidden" name="teacher_id" id="teacher-id-field" value="">
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="form-group">
              <label class="form-label">Full Name <span class="required">*</span></label>
              <input type="text" name="full_name" id="teacher-name" class="form-control" placeholder="e.g. John Doe" required maxlength="200">
            </div>
            <div class="form-group">
              <label class="form-label">Email Address <span class="required">*</span></label>
              <input type="email" name="email" id="teacher-email" class="form-control" placeholder="johndoe@example.com" required maxlength="150" oninput="this.value = this.value.toLowerCase()">
            </div>
            <div class="form-group">
              <label class="form-label">Phone Number</label>
              <input type="text" name="phone" id="teacher-phone" class="form-control" placeholder="024XXXXXXX" maxlength="20">
            </div>
            <div class="form-group">
              <label class="form-label">Classroom Assignments</label>
              <select name="class_ids[]" id="teacher-classrooms" class="form-control" multiple style="min-height:90px; padding:.5rem;">
                <?php foreach ($classes as $c): ?>
                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['level_name'] . ' - ' . $c['class_name'] . ' ' . $c['section']) ?></option>
                <?php endforeach; ?>
              </select>
              <p class="form-text">Hold Ctrl/Cmd to select multiple. These represent the teacher's role as a Class Teacher.</p>
            </div>
          </div>
          <div class="flex justify-end mt-6">
            <button type="submit" class="btn btn-primary" id="teacher-submit-btn">Save teacher</button>
          </div>
        </form>
      </div>

      <!-- Assignments Tab -->
      <div id="pane-assignments" style="display:none;">
        <div id="assign-only-when-exists">
          <form method="POST" action="<?= $base ?>/admin/teachers" id="form-assign-inner" onsubmit="Loader.show()">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="assign_subject">
            <input type="hidden" name="teacher_id" id="assign-teacher-id-inner" value="">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="form-group">
                <label class="form-label">Classes <span class="required">*</span></label>
                <div class="form-control p-0 overflow-y-auto" style="height:150px; background:var(--clr-surface-2);">
                  <?php foreach ($classes as $c): ?>
                    <label class="flex items-center gap-2 p-2 border-b border-gray-100 cursor-pointer hover:bg-white text-xs font-semibold m-0">
                      <input type="checkbox" name="class_ids[]" value="<?= $c['id'] ?>">
                      <span><?= htmlspecialchars($c['class_name'] . ' ' . $c['section']) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Subjects <span class="required">*</span></label>
                <div class="form-control p-0 overflow-y-auto" style="height:150px; background:var(--clr-surface-2);">
                  <?php foreach ($subjects as $s): ?>
                    <label class="flex items-center justify-between p-2 border-b border-gray-100 cursor-pointer hover:bg-white text-xs font-semibold m-0 transition-colors">
                      <div class="flex items-center gap-2">
                        <input type="checkbox" name="subject_ids[]" value="<?= $s['id'] ?>">
                        <span><?= htmlspecialchars($s['subject_name']) ?></span>
                      </div>
                      <?php 
                        $lvlBadgeColor = 'gray';
                        if ($s['level_name'] == 'LP') $lvlBadgeColor = 'success';
                        elseif ($s['level_name'] == 'UP') $lvlBadgeColor = 'warning';
                        elseif ($s['level_name'] == 'JHS') $lvlBadgeColor = 'purple';
                      ?>
                      <span class="badge badge-<?= $lvlBadgeColor ?>" style="font-size:9px; padding:2px 5px;"><?= htmlspecialchars($s['level_name']) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-full mt-4">Assign Selection</button>
          </form>

          <div class="mt-8">
            <h4 class="text-xs font-bold text-muted uppercase tracking-wider mb-2">Current Assignments</h4>
            <div id="inner-assign-list" class="border border-gray-200 rounded-lg overflow-y-auto max-h-48 bg-gray-50">
               <!-- Populated via JS -->
            </div>
          </div>
        </div>
        <div id="assign-new-teacher-message" class="text-center py-12" style="display:none;">
          <p class="text-muted">Please save the teacher profile before managing assignments.</p>
        </div>
      </div>
    </div>
    <div class="modal-footer bg-gray-50 border-t border-gray-100" style="justify-content:flex-end;">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modal-teacher')">Close</button>
    </div>
  </div>
</div>

<!-- Remove Assignment Form -->
<form method="POST" action="<?= $base ?>/admin/teachers" id="form-assignment-remove" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" value="remove_subject">
  <input type="hidden" name="assignment_id" id="del-assignment-id">
</form>

<!-- Delete Form -->
<form method="POST" action="<?= $base ?>/admin/teachers" id="form-teacher-delete" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" value="teacher_delete">
  <input type="hidden" name="teacher_id" id="del-teacher-id">
</form>

<script>
const allAssignments = <?= json_encode($assignments) ?>;
const allTeachers    = <?= json_encode($teachers) ?>;

$(document).ready(function() {
    // 1. Initialize DataTable
    const table = $('#teacher-table').DataTable({
        responsive: true,
        pageLength: 25,
        dom: '<"dt-layout-row"lf>rt<"dt-layout-row"ip>',
        language: {
            search: "", searchPlaceholder: "Search teachers...",
            lengthMenu: "Show _MENU_",
        },
        columnDefs: [{ orderable: false, targets: -1 }]
    });

    // 2. Initial View state
    const savedView = localStorage.getItem('teacher_view_pref') || 'list';
    toggleView(savedView);

    // 3. Action Button Delegation (Works for both Table and Grid)
    $(document).on('click', '.act-edit', function() {
        const teacher = $(this).data('teacher');
        editTeacher(teacher);
    });

    $(document).on('click', '.act-assign', function() {
        const id = $(this).data('id');
        openAssignModal(id);
    });
});

function toggleView(type) {
  const list = document.getElementById('view-list');
  const grid = document.getElementById('view-grid');
  const btnList = document.getElementById('btn-view-list');
  const btnGrid = document.getElementById('btn-view-grid');
  
  if (!list || !grid) return;

  if (type === 'list') {
    list.style.display = 'block';
    grid.style.display = 'none';
    btnList.classList.add('active');
    btnGrid.classList.remove('active');
  } else {
    list.style.display = 'none';
    grid.style.display = 'grid';
    btnGrid.classList.add('active');
    btnList.classList.remove('active');
  }
  localStorage.setItem('teacher_view_pref', type);
}

function switchTeacherTab(tab) {
  const pProfile = document.getElementById('pane-profile');
  const pAssign  = document.getElementById('pane-assignments');
  const tProfile = document.getElementById('tab-profile');
  const tAssign  = document.getElementById('tab-assignments');
  
  if (tab === 'profile') {
    pProfile.style.display = 'block';
    pAssign.style.display  = 'none';
    tProfile.classList.add('border-primary', 'text-primary');
    tProfile.classList.remove('border-transparent', 'text-muted');
    tAssign.classList.add('border-transparent', 'text-muted');
    tAssign.classList.remove('border-primary', 'text-primary');
  } else {
    pProfile.style.display = 'none';
    pAssign.style.display  = 'block';
    tAssign.classList.add('border-primary', 'text-primary');
    tAssign.classList.remove('border-transparent', 'text-muted');
    tProfile.classList.add('border-transparent', 'text-muted');
    tProfile.classList.remove('border-primary', 'text-primary');
  }
}

function openTeacherModal() {
  document.getElementById('form-teacher').reset();
  document.getElementById('teacher-id-field').value = '';
  document.getElementById('modal-teacher-title').textContent = 'New Teacher Profile';
  document.getElementById('teacher-submit-btn').textContent = 'Create Profile';
  
  document.getElementById('tab-assignments').style.display = 'none';
  document.getElementById('assign-only-when-exists').style.display = 'none';
  document.getElementById('assign-new-teacher-message').style.display = 'block';
  
  switchTeacherTab('profile');
  openModal('modal-teacher');
}

function editTeacher(t) {
  populateTeacherData(t);
  switchTeacherTab('profile');
  openModal('modal-teacher');
}

function openAssignModal(teacherId) {
  const teacher = allTeachers.find(x => Number(x.id) === Number(teacherId));
  if (!teacher) return;
  populateTeacherData(teacher);
  switchTeacherTab('assignments');
  openModal('modal-teacher');
}

function populateTeacherData(t) {
  document.getElementById('form-teacher').reset();
  document.getElementById('form-assign-inner').reset();
  
  document.getElementById('teacher-id-field').value = t.id;
  document.getElementById('assign-teacher-id-inner').value = t.id;
  document.getElementById('teacher-name').value = t.full_name;
  document.getElementById('teacher-email').value = t.email;
  document.getElementById('teacher-phone').value = t.phone || '';
  
  const cSelect = document.getElementById('teacher-classrooms');
  if (cSelect) {
      Array.from(cSelect.options).forEach(opt => opt.selected = false);
      if (t.assigned_class_ids) {
        const ids = t.assigned_class_ids.split(',');
        Array.from(cSelect.options).forEach(opt => {
          if (ids.includes(opt.value)) opt.selected = true;
        });
      }
  }
  
  document.getElementById('modal-teacher-title').textContent = 'Manage: ' + t.full_name;
  document.getElementById('teacher-submit-btn').textContent = 'Update Profile';
  
  document.getElementById('tab-assignments').style.display = 'block';
  document.getElementById('assign-only-when-exists').style.display = 'block';
  document.getElementById('assign-new-teacher-message').style.display = 'none';
  
  renderAssignmentsList(t.id);
}

function renderAssignmentsList(teacherId) {
  const container = document.getElementById('inner-assign-list');
  const teacherAssignments = allAssignments.filter(a => Number(a.teacher_id) === Number(teacherId));
  
  if (teacherAssignments.length === 0) {
    container.innerHTML = `<div class="p-6 text-center text-muted text-xs font-bold">No active subject assignments.</div>`;
    return;
  }
  
  let html = '';
  teacherAssignments.forEach(a => {
    html += `
      <div class="flex justify-between items-center p-3 border-b border-gray-100 last:border-0 hover:bg-white transition-colors">
        <div>
          <div class="text-xs font-bold text-gray-800">${escapeHtml(a.subject_name)}</div>
          <div class="text-[10px] text-muted uppercase font-bold tracking-tight">${escapeHtml(a.class_name)} ${escapeHtml(a.section)}</div>
        </div>
        <button type="button" class="p-1 text-danger hover:bg-red-50 rounded" onclick="removeAssignment(${a.id})" title="Remove Assignment">
          <svg style="width:14px; height:14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    `;
  });
  container.innerHTML = html;
}

function confirmDeleteTeacher(id, name) {
  confirmAction(`Permanently remove teacher "${name}"?\n\nThis will remove all their records.`, () => {
    document.getElementById('del-teacher-id').value = id;
    document.getElementById('form-teacher-delete').submit();
  });
}

function removeAssignment(id) {
  confirmAction({
    title: 'Remove Assignment?',
    message: 'Remove this subject from the teacher?',
    confirmText: 'Yes, Remove',
    type: 'danger'
  }, () => {
    document.getElementById('del-assignment-id').value = id;
    document.getElementById('form-assignment-remove').submit();
  });
}

function escapeHtml(unsafe) {
    return (unsafe||'').toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
