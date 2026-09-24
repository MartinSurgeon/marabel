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

// Avatar Color Helper (HCI Scanability - High Contrast Deep Palette)
$avatarPalettes = [
    '#6b21a8', // Deep Purple
    '#1e40af', // Royal Blue
    '#166534', // Forest Green
    '#9f1239', // Crimson
    '#9a3412', // Terracotta
    '#134e4a', // Teal
    '#334155', // Charcoal
    '#3730a3', // Deep Indigo
    '#155e75', // Dark Cyan
    '#7f1d1d', // Maroon
    '#78350f', // Bronze
    '#1e3a8a', // Navy
    '#581c87', // Violet
    '#065f46', // Emerald
    '#831843', // Pink
];

if (!function_exists('getAvatarStyle')) {
    function getAvatarStyle(string $id, string $name) {
        global $avatarPalettes;
        // Combine ID and Name for a more unique seed
        $hash = crc32($id . $name);
        $color = $avatarPalettes[abs($hash) % count($avatarPalettes)];
        return "background: $color; color: #ffffff; text-shadow: 0 1px 2px rgba(0,0,0,0.2);";
    }
}
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

/* Teacher modal: class-teacher pickers (design tokens, touch-friendly) */
.teacher-classpick:hover { background: var(--clr-surface); }
.assign-pick-row:hover { background: var(--clr-surface); }
.assign-pick-row:last-child { border-bottom: none !important; }
</style>

<?php
// Compute summary counts & maps for high-performance rendering
$teacherAssignmentsMap = [];
foreach ($assignments as $a) {
    if (!empty($a['teacher_id'])) {
        $tid = (int)$a['teacher_id'];
        $className = trim($a['class_name'] . ($a['section'] ? ' ' . $a['section'] : ''));
        $teacherAssignmentsMap[$tid][$className][] = $a['subject_name'];
    }
}

$totalTeachers     = count($teachers);
$activeTeachers    = count(array_filter($teachers, fn($t) => !empty($t['is_active'])));
$maleTeachers      = count(array_filter($teachers, fn($t) => ($t['gender'] ?? '') === 'Male'));
$femaleTeachers    = count(array_filter($teachers, fn($t) => ($t['gender'] ?? '') === 'Female'));
$formMastersCount  = count(array_filter($teachers, fn($t) => !empty($t['lead_classes'])));
$totalPairings     = count($assignments);
?>

<div class="flex justify-between items-center mb-6 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Teachers Management</h1>
    <div class="flex items-center gap-2 mt-1">
      <p class="text-muted m-0" style="font-size:var(--text-sm);">Manage your teaching staff, form master roles, and subject allocations.</p>
    </div>
  </div>
  
  <div class="flex items-center gap-3">
    <!-- View Switcher (HCI: User Control & Preference) -->
    <div class="flex bg-gray-100 p-1 rounded-lg border border-gray-200" style="background:rgba(0,0,0,0.03);">
       <button type="button" class="view-toggle-btn active" id="btn-view-grid" onclick="toggleView('grid')" title="Grid View">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
       </button>
       <button type="button" class="view-toggle-btn" id="btn-view-list" onclick="toggleView('list')" title="List View" style="margin-left:2px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
       </button>
    </div>

    <button class="btn btn-primary shadow-purple font-bold flex items-center gap-1.5" onclick="openTeacherModal()" style="height:40px; border-radius:10px;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      Add Teacher
    </button>
  </div>
</div>

<!-- ══ Top KPI Metric Cards (Dashboard Synergy) ══════════════════ -->
<div class="stat-grid mb-6" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
  <!-- Total Staff -->
  <div class="stat-card" style="border-left: 4px solid var(--clr-primary);">
    <div class="stat-icon" style="--stat-bg: var(--clr-primary-50); --stat-color: var(--clr-primary-600);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    </div>
    <div>
      <div class="stat-label">Total Staff</div>
      <div class="stat-value"><?= number_format($totalTeachers) ?></div>
      <div class="stat-sub flex items-center gap-3">
        <span style="font-weight:800; color:var(--clr-info); display:flex; align-items:center; gap:3px;">
          <svg fill="currentColor" viewBox="0 0 24 24" width="10" height="10"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
          <?= number_format($maleTeachers) ?>M
        </span>
        <span style="font-weight:800; color:#ec4899; display:flex; align-items:center; gap:3px;">
          <svg fill="currentColor" viewBox="0 0 24 24" width="10" height="10"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
          <?= number_format($femaleTeachers) ?>F
        </span>
      </div>
    </div>
  </div>

  <!-- Active Status -->
  <div class="stat-card">
    <div class="stat-icon" style="--stat-bg: var(--clr-success-bg); --stat-color: var(--clr-success);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div>
      <div class="stat-label">Active Staff</div>
      <div class="stat-value" style="color:var(--clr-success);"><?= number_format($activeTeachers) ?></div>
      <div class="stat-sub"><?= $totalTeachers > 0 ? round(($activeTeachers / $totalTeachers) * 100) : 0 ?>% Active Rate</div>
    </div>
  </div>

  <!-- Form Masters -->
  <div class="stat-card">
    <div class="stat-icon" style="--stat-bg: rgba(168,85,247,0.1); --stat-color: #a855f7;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
    </div>
    <div>
      <div class="stat-label">Form Masters</div>
      <div class="stat-value"><?= number_format($formMastersCount) ?></div>
      <div class="stat-sub">Class Leadership</div>
    </div>
  </div>

  <!-- Subject Allocations -->
  <div class="stat-card">
    <div class="stat-icon" style="--stat-bg: var(--clr-info-bg); --stat-color: var(--clr-info);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
    </div>
    <div>
      <div class="stat-label">Allocations</div>
      <div class="stat-value"><?= number_format($totalPairings) ?></div>
      <div class="stat-sub">Active Pairings</div>
    </div>
  </div>
</div>

<!-- ══ Search & Filter Toolbar ═══════════════════════════════════ -->
<div class="card p-3 mb-6 flex items-center justify-between gap-4 flex-wrap" style="border:1px solid var(--clr-border); background:var(--clr-surface); border-radius:14px;">
  <!-- Live search input -->
  <div class="flex items-center gap-2 flex-1" style="min-width:240px;">
    <div style="position:relative; width:100%; max-width:380px;">
      <svg style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--clr-text-muted);" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
      <input type="text" id="teacher-search-input" placeholder="Search teacher by name, email, or phone…" class="form-control" style="padding-left:36px; height:38px; font-size:13px; border-radius:8px;" oninput="filterTeacherCards()">
    </div>
  </div>

  <!-- Filter Tabs -->
  <div class="flex items-center gap-1 flex-wrap" id="status-filter-group">
    <button type="button" class="btn btn-sm btn-primary filter-tab active" data-filter="all" onclick="setStatusFilter('all', this)" style="border-radius:9999px; padding:5px 14px; font-size:11px; font-weight:700;">All (<?= $totalTeachers ?>)</button>
    <button type="button" class="btn btn-sm btn-ghost filter-tab" data-filter="active" onclick="setStatusFilter('active', this)" style="border-radius:9999px; padding:5px 14px; font-size:11px; font-weight:700; color:var(--clr-text-muted);">Active (<?= $activeTeachers ?>)</button>
    <button type="button" class="btn btn-sm btn-ghost filter-tab" data-filter="form-master" onclick="setStatusFilter('form-master', this)" style="border-radius:9999px; padding:5px 14px; font-size:11px; font-weight:700; color:var(--clr-text-muted);">Form Masters (<?= $formMastersCount ?>)</button>
    <button type="button" class="btn btn-sm btn-ghost filter-tab" data-filter="unassigned" onclick="setStatusFilter('unassigned', this)" style="border-radius:9999px; padding:5px 14px; font-size:11px; font-weight:700; color:var(--clr-text-muted);">Unassigned</button>
  </div>
</div>

<?php if (empty($teachers)): ?>
<div class="card flex flex-col items-center justify-center shadow-sm" style="padding:6rem 2rem; text-align:center; border-style:dashed; background:rgba(255,255,255,0.5);">
  <div style="background:var(--clr-primary-50); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
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
                            <div style="width:34px; height:34px; <?= getAvatarStyle($t['id'], $t['full_name']) ?> border-radius:var(--radius-md); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800;">
                                <?= strtoupper(substr(trim($t['full_name']), 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:700; color:var(--clr-text); font-size:14px; display:flex; align-items:center; gap:6px;">
                                    <?= htmlspecialchars($t['full_name']) ?>
                                    <?php if ($t['gender'] === 'Male'): ?>
                                        <svg fill="var(--clr-info)" viewBox="0 0 24 24" width="10" height="10" title="Male"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    <?php elseif ($t['gender'] === 'Female'): ?>
                                        <svg fill="#ec4899" viewBox="0 0 24 24" width="10" height="10" title="Female"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                    <?php endif; ?>
                                </div>
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
                            <?= $t['current_year_subjects'] ?> Subj · <?= $t['class_count'] ?> Cls
                            <?php if ($t['subject_count'] > $t['current_year_subjects']): ?>
                               <span class="text-warning" title="Teacher has <?= ($t['subject_count'] - $t['current_year_subjects']) ?> assignments in other sessions" style="cursor:help;">
                                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="14" height="14" style="display:inline; vertical-align:text-bottom; margin-left:2px;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                               </span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:10px; color:var(--clr-text-muted); max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?= htmlspecialchars($t['assignment_summary'] ?: 'No assignments this session') ?>
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

<!-- ══ Grid View Container (Redesigned Compact Modern Cards) ══ -->
<div id="view-grid" class="grid" style="grid-template-columns:repeat(auto-fill, minmax(330px, 1fr)); gap:1.5rem;">
  <?php foreach ($teachers as $t): 
    $tid = (int)$t['id'];
    $classesAssigned = $teacherAssignmentsMap[$tid] ?? [];
    $totalSubjectsInSession = (int)($t['current_year_subjects'] ?? 0);
    $isFormMaster = !empty($t['lead_classes']);
    $genderColor = ($t['gender'] === 'Male') ? 'var(--clr-info)' : (($t['gender'] === 'Female') ? '#ec4899' : 'var(--clr-text-muted)');
  ?>
  <div class="teacher-grid-card mgmt-card hover-lift flex flex-col justify-between <?= !$t['is_active'] ? 'opacity-75' : '' ?>"
       data-name="<?= strtolower(htmlspecialchars($t['full_name'])) ?>" 
       data-email="<?= strtolower(htmlspecialchars($t['email'])) ?>" 
       data-phone="<?= strtolower(htmlspecialchars($t['phone'] ?? '')) ?>" 
       data-active="<?= $t['is_active'] ? '1' : '0' ?>"
       data-form-master="<?= $isFormMaster ? '1' : '0' ?>"
       data-assigned="<?= $totalSubjectsInSession > 0 ? '1' : '0' ?>"
       style="border-radius:1.25rem; border:1px solid var(--clr-border); background:var(--clr-surface); transition:all 0.25s ease; min-height:280px;"
  >
    <!-- Upper Body Section -->
    <div style="padding:1.5rem 1.5rem 1rem;">
      <!-- Header: Avatar, Name/Email, Status Toggle -->
      <div class="flex justify-between items-start gap-3 mb-3">
        <div class="flex items-center gap-3 min-w-0">
          <div style="width:46px; height:46px; <?= getAvatarStyle($t['id'], $t['full_name']) ?> border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.15rem; font-weight:900; flex-shrink:0; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
            <?= strtoupper(substr(trim($t['full_name']), 0, 1)) ?>
          </div>
          <div class="min-w-0">
            <div class="flex items-center gap-1.5">
              <h3 class="teacher-card-name m-0 truncate" style="font-weight:800; font-size:1rem; color:var(--clr-text); line-height:1.2;">
                <?= htmlspecialchars($t['full_name']) ?>
              </h3>
              <?php if ($t['gender']): ?>
                <span title="<?= htmlspecialchars($t['gender']) ?>" style="color:<?= $genderColor ?>; flex-shrink:0;">
                  <svg fill="currentColor" viewBox="0 0 24 24" width="11" height="11"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                </span>
              <?php endif; ?>
            </div>
            <div class="truncate text-muted" style="font-size:11px; font-weight:600; text-transform:lowercase; margin-top:2px;">
              <?= htmlspecialchars($t['email']) ?>
            </div>
          </div>
        </div>

        <!-- 1-Click Active / Inactive Status Badge -->
        <form method="POST" action="<?= $base ?>/admin/teachers" class="m-0" onsubmit="Loader.show()">
          <?= CSRF::field() ?>
          <input type="hidden" name="_action" value="teacher_toggle">
          <input type="hidden" name="teacher_id" value="<?= $t['id'] ?>">
          <button type="submit" class="badge <?= $t['is_active'] ? 'badge-success' : 'badge-danger' ?>" style="cursor:pointer; border:none; padding:4px 9px; font-size:9px; font-weight:800; letter-spacing:0.04em; border-radius:9999px;" title="Click to toggle active status">
            <?= $t['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
          </button>
        </form>
      </div>

      <!-- Contact & Role Chips -->
      <div class="flex items-center gap-2 flex-wrap mb-4">
        <?php if ($t['phone']): ?>
          <a href="tel:<?= htmlspecialchars($t['phone']) ?>" class="inline-flex items-center gap-1.5 text-muted hover:text-purple-600 transition" style="font-size:12px; font-weight:600; text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <?= htmlspecialchars($t['phone']) ?>
          </a>
        <?php endif; ?>

        <?php if ($isFormMaster): ?>
          <span class="badge badge-purple" style="font-size:10px; padding:3px 8px; border-radius:9999px; font-weight:700;">
            🎯 Form Master: <?= htmlspecialchars($t['lead_classes']) ?>
          </span>
        <?php endif; ?>

        <span class="badge" style="background:rgba(99, 102, 241, 0.08); color:#6366f1; font-size:10px; padding:3px 8px; border-radius:9999px; font-weight:700;">
          📚 <?= $totalSubjectsInSession ?> Subject<?= $totalSubjectsInSession !== 1 ? 's' : '' ?> · <?= count($classesAssigned) ?> Class<?= count($classesAssigned) !== 1 ? 'es' : '' ?>
        </span>
      </div>

      <!-- Smart Grouped Teaching Allocations -->
      <div style="border-top:1px solid rgba(0,0,0,0.05); padding-top:0.75rem;">
        <div style="font-size:10px; font-weight:800; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">
          Teaching Allocations
        </div>

        <?php if (!empty($classesAssigned)): ?>
          <div class="flex flex-wrap gap-1.5" style="max-height:85px; overflow:hidden;">
            <?php 
              $shownCount = 0;
              $maxChips = 3;
              $classKeys = array_keys($classesAssigned);
              $totalClasses = count($classKeys);
              
              foreach ($classesAssigned as $clsName => $subjs):
                if ($shownCount >= $maxChips) break;
                $subCount = count($subjs);
                $subPreview = implode(', ', $subjs);
                $shownCount++;
            ?>
              <span class="badge badge-gray" 
                    title="<?= htmlspecialchars($clsName . ': ' . $subPreview) ?>" 
                    style="font-size:11px; padding:4px 8px; border-radius:8px; border:1px solid rgba(0,0,0,0.06); background:#f8fafc; font-weight:600; color:#334155;">
                <strong style="color:var(--clr-text);"><?= htmlspecialchars($clsName) ?></strong> 
                <span style="color:var(--clr-text-muted); font-size:10px;">(<?= $subCount == 1 ? htmlspecialchars($subjs[0]) : "{$subCount} subjects" ?>)</span>
              </span>
            <?php endforeach; ?>

            <?php if ($totalClasses > $maxChips): 
              $remaining = $totalClasses - $maxChips;
              $popoverId = "pop-teacher-{$t['id']}";
            ?>
              <div class="relative inline-block" style="z-index:10;">
                <button type="button" class="badge badge-purple cursor-pointer" 
                        onclick="toggleTeacherPopover('<?= $popoverId ?>', event)" 
                        style="font-size:10px; padding:4px 8px; border-radius:8px; border:none; font-weight:800;">
                  +<?= $remaining ?> more &darr;
                </button>
                
                <!-- Popover -->
                <div id="<?= $popoverId ?>" class="teacher-popover hidden absolute left-0 bottom-full mb-2 w-64 p-3 bg-white rounded-xl shadow-xl border border-gray-200 z-50 text-left" style="font-size:11px;">
                  <div class="font-bold text-gray-800 border-b pb-1 mb-2">All Teaching Classes</div>
                  <div class="space-y-1.5 max-h-48 overflow-y-auto">
                    <?php foreach ($classesAssigned as $clsName => $subjs): ?>
                      <div>
                        <span class="font-bold text-purple-700"><?= htmlspecialchars($clsName) ?>:</span> 
                        <span class="text-gray-600 text-[10px]"><?= htmlspecialchars(implode(', ', $subjs)) ?></span>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div style="font-size:11px; color:var(--clr-text-muted); font-style:italic; padding:4px 0;">
            No subjects assigned for this session
            <?php if ($t['subject_count'] > 0): ?>
              <div style="font-size:10px; color:var(--clr-warning); font-weight:700; margin-top:2px;">
                ⚠️ <?= $t['subject_count'] ?> assignments in other sessions
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Action Toolbar Footer -->
    <div style="padding:0.75rem 1.25rem; background:#f8fafc; border-top:1px solid #f1f5f9; border-radius:0 0 1.25rem 1.25rem; display:flex; align-items:center; justify-content:space-between; gap:0.5rem;">
      <button class="btn btn-primary btn-sm font-bold flex items-center gap-1" 
              onclick="openAssignModal(<?= $t['id'] ?>, '<?= htmlspecialchars($t['full_name'], ENT_QUOTES) ?>')"
              style="border-radius:8px; padding:6px 14px; font-size:12px; box-shadow:0 2px 4px rgba(105, 43, 196, 0.15);">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="13" height="13"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Assign
      </button>

      <div class="flex items-center gap-1">
        <button class="btn btn-ghost btn-sm font-bold flex items-center gap-1" 
                onclick='editTeacher(<?= htmlspecialchars(json_encode($t), ENT_QUOTES) ?>)' 
                style="color:var(--clr-primary); padding:6px 10px; font-size:12px;"
                title="Edit Profile">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
          Edit
        </button>

        <button class="btn btn-ghost btn-sm font-bold text-danger flex items-center" 
                onclick="confirmDeleteTeacher(<?= $t['id'] ?>, '<?= htmlspecialchars($t['full_name'], ENT_QUOTES) ?>')"
                style="padding:6px 8px;"
                title="Delete Teacher">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- No Results Match Search State -->
<div id="no-filter-results" class="card text-center py-12 px-4 shadow-sm" style="display:none; border-style:dashed; background:rgba(255,255,255,0.7);">
  <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
  <h3 class="font-bold text-gray-700 text-base m-0">No matching teachers found</h3>
  <p class="text-xs text-gray-400 mt-1">Try adjusting your search terms or filter selection.</p>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- ══ Teacher Modal (after layout close: fixed overlay covers full viewport + sidebar; avoid transformed <main> ancestor) ══ -->
<div id="modal-teacher" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-teacher-title" style="display:none;">
  <div id="modal-teacher-inner" class="modal w-full max-w-md mx-4 min-h-0">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-teacher-title">Teacher Details</h3>
      <button class="modal-close" onclick="closeModal('modal-teacher')" aria-label="Close dialog">&times;</button>
    </div>

    <div id="teacher-modal-tabs" class="flex shrink-0 overflow-x-auto" role="tablist" aria-label="Teacher sections" style="display:none; border-bottom:1px solid var(--clr-border); padding:0 1rem;">
      <button type="button" role="tab" id="tab-profile" aria-selected="true" aria-controls="pane-profile" onclick="switchTeacherTab('profile')" class="py-3 px-3 sm:px-4 text-sm font-bold whitespace-nowrap border-b-2 transition-colors" style="border-color:var(--clr-primary-500); color:var(--clr-primary-600);">Profile</button>
      <button type="button" role="tab" id="tab-assignments" aria-selected="false" aria-controls="pane-assignments" onclick="switchTeacherTab('assignments')" class="py-3 px-3 sm:px-4 text-sm font-bold whitespace-nowrap border-b-2 border-transparent text-muted transition-colors" style="color:var(--clr-text-muted);">Subject assignments</button>
    </div>

    <div class="modal-flex-stack">
    <div class="modal-body">
      <!-- Profile Tab -->
      <div id="pane-profile" role="tabpanel" aria-labelledby="tab-profile">
        <form method="POST" action="<?= $base ?>/admin/teachers" id="form-teacher" onsubmit="Loader.show()">
          <?= CSRF::field() ?>
          <input type="hidden" name="_action" value="teacher_store">
          <input type="hidden" name="teacher_id" id="teacher-id-field" value="">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
            <div class="form-group md:col-span-2">
              <label class="form-label">Full name <span class="required">*</span></label>
              <input type="text" name="full_name" id="teacher-name" class="form-control" placeholder="e.g. John Doe" required maxlength="200" autocomplete="name">
            </div>
            <div class="form-group">
              <label class="form-label">Email <span class="required">*</span></label>
              <input type="email" name="email" id="teacher-email" class="form-control" placeholder="name@school.edu" required maxlength="150" autocomplete="email" oninput="this.value = this.value.toLowerCase()">
            </div>
            <div class="form-group">
              <label class="form-label">Phone</label>
              <input type="tel" name="phone" id="teacher-phone" class="form-control" placeholder="024XXXXXXX" maxlength="20" autocomplete="tel">
              <p class="form-text mt-1" style="font-size:11px; color:var(--clr-text-muted);">A welcome SMS with login credentials will be sent to this number.</p>
            </div>
            <div class="form-group">
              <label class="form-label">Gender <span class="required">*</span></label>
              <select name="gender" id="teacher-gender" class="form-control" required>
                <option value="">— Select —</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="form-group md:col-span-2">
              <label class="form-label">Class teacher for</label>
              <div id="teacher-classrooms-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 sm:p-4 rounded-lg overflow-y-auto" style="max-height:min(200px, 40vh); background:var(--clr-surface-2); border:1px solid var(--clr-border);">
                <?php foreach ($classes as $c): ?>
                  <label class="teacher-classpick flex items-center gap-2 p-2 rounded cursor-pointer m-0 transition-colors" style="border:1px solid transparent;">
                    <input type="checkbox" name="class_ids[]" value="<?= $c['id'] ?>" class="w-4 h-4 accent-purple-600 shrink-0">
                    <span class="text-xs font-bold" style="color:var(--clr-text);"><?= htmlspecialchars($c['class_name'] . ' ' . $c['section']) ?></span>
                  </label>
                <?php endforeach; ?>
              </div>
              <p class="form-text mt-2">Select classes where this teacher is the <strong>class teacher</strong>.</p>
            </div>
          </div>
        </form>
      </div>

      <!-- Assignments Tab -->
      <div id="pane-assignments" role="tabpanel" aria-labelledby="tab-assignments" style="display:none;">
        <div id="assign-only-when-exists">
          <form method="POST" action="<?= $base ?>/admin/teachers" id="form-assign-inner" onsubmit="Loader.show()">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="assign_subject">
            <input type="hidden" name="teacher_id" id="assign-teacher-id-inner" value="">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- ── Classes: grouped by level with per-level Select All ── -->
              <div class="form-group">
                <label class="form-label">Classes <span class="required">*</span></label>
                <div class="form-control p-0 overflow-y-auto" style="height:min(200px,46vh); min-height:140px; background:var(--clr-surface-2);">
                  <?php
                  $classByLevel = [];
                  foreach ($classes as $c) { $classByLevel[$c['level_name']][] = $c; }
                  $levelMeta = ['Lower Primary' => ['code'=>'LP','color'=>'success'], 'Upper Primary' => ['code'=>'UP','color'=>'warning'], 'JHS' => ['code'=>'JHS','color'=>'purple']];
                  foreach ($classByLevel as $lvlName => $lvlClasses):
                    $meta  = $levelMeta[$lvlName] ?? ['code'=>'', 'color'=>'gray'];
                    $lvlId = 'lvl-' . preg_replace('/\W+/', '_', strtolower($lvlName));
                  ?>
                  <!-- Level header row with Select-All toggle -->
                  <div class="flex items-center justify-between px-3 py-1 sticky top-0"
                       style="background:var(--clr-surface); border-bottom:1px solid var(--clr-border); z-index:1;">
                    <label class="flex items-center gap-2 cursor-pointer m-0">
                      <input type="checkbox" class="level-all-chk w-4 h-4 accent-purple-600"
                             data-level="<?= htmlspecialchars($lvlName) ?>"
                             onchange="toggleLevelClasses(this)"
                             title="Select all <?= htmlspecialchars($lvlName) ?> classes">
                      <span class="text-[10px] font-black uppercase tracking-wider" style="color:var(--clr-text-muted);">
                        <?= htmlspecialchars($lvlName) ?>
                      </span>
                    </label>
                    <span class="badge badge-<?= $meta['color'] ?>" style="font-size:9px; padding:2px 6px;">
                      <?= count($lvlClasses) ?> class<?= count($lvlClasses) != 1 ? 'es' : '' ?>
                    </span>
                  </div>
                  <!-- Individual class checkboxes -->
                  <?php foreach ($lvlClasses as $c): ?>
                  <label class="assign-pick-row flex items-center gap-2 pl-6 pr-3 py-2 cursor-pointer text-xs font-semibold m-0 transition-colors"
                         style="border-bottom:1px solid var(--clr-border);">
                    <input type="checkbox" name="class_ids[]" value="<?= $c['id'] ?>"
                           class="class-level-chk w-4 h-4 accent-purple-600"
                           data-level="<?= htmlspecialchars($lvlName, ENT_QUOTES) ?>"
                           onchange="syncLevelHeader('<?= htmlspecialchars($lvlName, ENT_QUOTES) ?>')">
                    <span><?= htmlspecialchars($c['class_name'] . ($c['section'] ? ' ' . $c['section'] : '')) ?></span>
                  </label>
                  <?php endforeach; ?>
                  <?php endforeach; ?>
                </div>
              </div>

              <!-- ── Subjects: unchanged layout ── -->
              <div class="form-group">
                <label class="form-label">Subjects <span class="required">*</span></label>
                <div class="form-control p-0 overflow-y-auto" style="height:min(200px,46vh); min-height:140px; background:var(--clr-surface-2);">
                  <?php foreach ($subjects as $s): ?>
                    <label class="assign-pick-row flex items-center justify-between p-2 cursor-pointer text-xs font-semibold m-0 transition-colors"
                           style="border-bottom:1px solid var(--clr-border);">
                      <div class="flex items-center gap-2">
                        <input type="checkbox" name="subject_ids[]" value="<?= $s['id'] ?>">
                        <span><?= htmlspecialchars($s['subject_name']) ?></span>
                      </div>
                      <?php
                        $lvlBadgeColor = 'gray';
                        if ($s['level_name'] == 'Lower Primary') $lvlBadgeColor = 'success';
                        elseif ($s['level_name'] == 'Upper Primary') $lvlBadgeColor = 'warning';
                        elseif ($s['level_name'] == 'JHS') $lvlBadgeColor = 'purple';
                      ?>
                      <span class="badge badge-<?= $lvlBadgeColor ?>" style="font-size:9px; padding:2px 5px;">
                        <?= htmlspecialchars($s['level_name']) ?>
                      </span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <div style="background:var(--clr-info-bg); border:1px solid rgba(0,0,0,0.05); border-radius:var(--radius-md); padding:0.625rem 1rem; margin-top:0.75rem; font-size:11px; color:var(--clr-text-muted);">
              <strong style="color:var(--clr-text);">Tip:</strong>
              Use the level header checkboxes (e.g. <em>Lower Primary</em>) to instantly select <strong>all classes in that section</strong>.
              A teacher teaching the same subject across LP, UP, and JHS can be set up in a single submission.
            </div>
            <button type="submit" class="btn btn-primary w-full mt-4">Assign Selected Subjects</button>
          </form>

          <div class="mt-8 pt-6 border-t border-gray-100">
            <div class="flex justify-between items-center mb-3">
               <div>
                  <h4 class="text-xs font-bold text-gray-800 uppercase tracking-widest m-0">Active Subject Assignments</h4>
                  <p class="text-[10px] text-muted m-0">Manage which subjects this teacher handles in different classrooms.</p>
               </div>
               <button type="button" id="btn-bulk-remove" class="btn btn-ghost btn-xs text-danger font-bold" style="display:none;" onclick="handleBulkRemove()">
                  REMOVE SELECTED
               </button>
            </div>
            
            <div id="inner-assign-list-container" class="border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white">
               <div class="flex items-center gap-3 bg-gray-50 p-3 border-b border-gray-200">
                  <input type="checkbox" id="check-all-assignments" onchange="toggleSelectAllAssignments(this)" class="w-4 h-4 accent-purple-600">
                  <span class="text-[10px] font-black text-gray-500 uppercase tracking-tighter">Select All</span>
               </div>
               <div id="inner-assign-list" class="overflow-y-auto max-h-64">
                  <!-- Populated via JS -->
               </div>
            </div>
          </div>
        </div>
        <form method="POST" action="<?= $base ?>/admin/teachers" id="form-bulk-remove" style="display:none;">
           <?= CSRF::field() ?>
           <input type="hidden" name="_action" value="bulk_remove_subjects">
           <div id="bulk-remove-ids-container"></div>
        </form>
        <div id="assign-new-teacher-message" class="text-center py-12" style="display:none;">
          <p class="text-muted">Save the teacher profile first, then you can assign subjects to classes.</p>
        </div>
      </div>
    </div>
    <div class="modal-footer" id="teacher-modal-footer">
      <button type="button" class="btn btn-ghost" onclick="closeModal('modal-teacher')">Cancel</button>
      <button type="submit" form="form-teacher" class="btn btn-primary shadow-purple" id="teacher-submit-btn">Save</button>
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

    // 2. Initial View state (Default to Grid for modern experience)
    const savedView = localStorage.getItem('teacher_view_pref') || 'grid';
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

let currentStatusFilter = 'all';

function setStatusFilter(filter, btn) {
  currentStatusFilter = filter;
  document.querySelectorAll('#status-filter-group .filter-tab').forEach(b => {
    b.classList.remove('btn-primary', 'active');
    b.classList.add('btn-ghost');
    b.style.color = 'var(--clr-text-muted)';
  });
  btn.classList.remove('btn-ghost');
  btn.classList.add('btn-primary', 'active');
  btn.style.color = '#ffffff';
  filterTeacherCards();
}

function filterTeacherCards() {
  const query = (document.getElementById('teacher-search-input')?.value || '').toLowerCase().trim();
  const cards = document.querySelectorAll('.teacher-grid-card');
  let visibleCount = 0;

  cards.forEach(card => {
    const name = (card.dataset.name || '').toLowerCase();
    const email = (card.dataset.email || '').toLowerCase();
    const phone = (card.dataset.phone || '').toLowerCase();
    const isActive = card.dataset.active === '1';
    const isFormMaster = card.dataset.formMaster === '1';
    const isAssigned = card.dataset.assigned === '1';

    // Status filter match
    let statusMatch = true;
    if (currentStatusFilter === 'active') statusMatch = isActive;
    else if (currentStatusFilter === 'form-master') statusMatch = isFormMaster;
    else if (currentStatusFilter === 'unassigned') statusMatch = !isAssigned;

    // Search query match
    let searchMatch = true;
    if (query.length > 0) {
      searchMatch = name.includes(query) || email.includes(query) || phone.includes(query);
    }

    if (statusMatch && searchMatch) {
      card.style.display = 'flex';
      visibleCount++;
    } else {
      card.style.display = 'none';
    }
  });

  const noResults = document.getElementById('no-filter-results');
  if (noResults) {
    noResults.style.display = visibleCount === 0 ? 'block' : 'none';
  }

  // Also sync with DataTable if in List View
  if ($.fn.dataTable && $.fn.dataTable.isDataTable('#teacher-table')) {
    const table = $('#teacher-table').DataTable();
    table.search(query).draw();
  }
}

function toggleTeacherPopover(popId, evt) {
  if (evt) evt.stopPropagation();
  const target = document.getElementById(popId);
  if (!target) return;
  const isHidden = target.classList.contains('hidden');
  
  // Close all open popovers
  document.querySelectorAll('.teacher-popover').forEach(p => p.classList.add('hidden'));

  if (isHidden) {
    target.classList.remove('hidden');
  }
}

// Dismiss popovers on outside click
document.addEventListener('click', function(e) {
  if (!e.target.closest('.teacher-popover')) {
    document.querySelectorAll('.teacher-popover').forEach(p => p.classList.add('hidden'));
  }
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

function setTeacherModalWidth(compact) {
  const inner = document.getElementById('modal-teacher-inner');
  if (!inner) return;
  inner.classList.remove('max-w-md', 'max-w-2xl');
  inner.classList.add(compact ? 'max-w-md' : 'max-w-2xl');
}

function styleTeacherTabButton(btn, active) {
  if (!btn) return;
  btn.setAttribute('aria-selected', active ? 'true' : 'false');
  btn.style.borderBottomWidth = '2px';
  btn.style.borderBottomStyle = 'solid';
  if (active) {
    btn.style.borderBottomColor = 'var(--clr-primary-500)';
    btn.style.color = 'var(--clr-primary-600)';
  } else {
    btn.style.borderBottomColor = 'transparent';
    btn.style.color = 'var(--clr-text-muted)';
  }
}

function switchTeacherTab(tab) {
  const pProfile = document.getElementById('pane-profile');
  const pAssign  = document.getElementById('pane-assignments');
  const tProfile = document.getElementById('tab-profile');
  const tAssign  = document.getElementById('tab-assignments');
  const submitBtn = document.getElementById('teacher-submit-btn');

  if (tab === 'profile') {
    pProfile.style.display = 'block';
    pAssign.style.display  = 'none';
    pProfile.setAttribute('aria-hidden', 'false');
    pAssign.setAttribute('aria-hidden', 'true');
    styleTeacherTabButton(tProfile, true);
    styleTeacherTabButton(tAssign, false);
    if (submitBtn) submitBtn.style.display = '';
  } else {
    pProfile.style.display = 'none';
    pAssign.style.display  = 'block';
    pProfile.setAttribute('aria-hidden', 'true');
    pAssign.setAttribute('aria-hidden', 'false');
    styleTeacherTabButton(tProfile, false);
    styleTeacherTabButton(tAssign, true);
    if (submitBtn) submitBtn.style.display = 'none';
  }
}

function openTeacherModal() {
  document.getElementById('form-teacher').reset();
  document.getElementById('teacher-id-field').value = '';
  const formAssign = document.getElementById('form-assign-inner');
  if (formAssign) formAssign.reset();
  const assignTid = document.getElementById('assign-teacher-id-inner');
  if (assignTid) assignTid.value = '';
  document.getElementById('modal-teacher-title').textContent = 'New Teacher';
  const submitBtn = document.getElementById('teacher-submit-btn');
  if (submitBtn) {
    submitBtn.textContent = 'Create Teacher';
    submitBtn.style.display = '';
  }

  const tabBar = document.getElementById('teacher-modal-tabs');
  if (tabBar) tabBar.style.display = 'none';
  setTeacherModalWidth(true);

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
  // openModal() focuses the first input in the dialog; profile fields are hidden on this tab — focus assignments instead
  setTimeout(() => {
    const pane = document.getElementById('pane-assignments');
    const first = pane && pane.querySelector('#form-assign-inner input[type="checkbox"]');
    if (first) first.focus();
  }, 200);
}

function populateTeacherData(t) {
  document.getElementById('form-teacher').reset();
  document.getElementById('form-assign-inner').reset();
  
  document.getElementById('teacher-id-field').value = t.id;
  document.getElementById('assign-teacher-id-inner').value = t.id;
  document.getElementById('teacher-name').value = t.full_name;
  document.getElementById('teacher-email').value = t.email;
  document.getElementById('teacher-phone').value = t.phone || '';
  document.getElementById('teacher-gender').value = t.gender || '';
  
  const grid = document.getElementById('teacher-classrooms-grid');
  if (grid) {
      const checkboxes = grid.querySelectorAll('input[type="checkbox"]');
      checkboxes.forEach(cb => cb.checked = false);
      if (t.assigned_class_ids) {
        const ids = t.assigned_class_ids.split(',');
        checkboxes.forEach(cb => {
          if (ids.includes(cb.value)) cb.checked = true;
        });
      }
  }
  
  document.getElementById('modal-teacher-title').textContent = 'Edit ' + t.full_name;
  const submitBtn = document.getElementById('teacher-submit-btn');
  if (submitBtn) submitBtn.textContent = 'Update Teacher';

  const tabBar = document.getElementById('teacher-modal-tabs');
  if (tabBar) tabBar.style.display = 'flex';
  setTeacherModalWidth(false);

  document.getElementById('assign-only-when-exists').style.display = 'block';
  document.getElementById('assign-new-teacher-message').style.display = 'none';
  
  renderAssignmentsList(t.id);
}

function renderAssignmentsList(teacherId) {
  const container = document.getElementById('inner-assign-list');
  const bulkBtn   = document.getElementById('btn-bulk-remove');
  const checkAll  = document.getElementById('check-all-assignments');
  const teacherAssignments = allAssignments.filter(a => Number(a.teacher_id) === Number(teacherId));
  
  checkAll.checked = false;
  bulkBtn.style.display = 'none';

  if (teacherAssignments.length === 0) {
    container.innerHTML = `<div class="p-10 text-center text-muted text-xs font-bold italic opacity-60">No active subject assignments.</div>`;
    return;
  }

  // Group by level for cleaner display
  const levelOrder = ['Lower Primary', 'Upper Primary', 'JHS'];
  const levelColors = {'Lower Primary': '#16a34a', 'Upper Primary': '#d97706', 'JHS': '#7c3aed'};
  const grouped = {};
  teacherAssignments.forEach(a => {
    const lvl = a.level_name || 'Other';
    if (!grouped[lvl]) grouped[lvl] = [];
    grouped[lvl].push(a);
  });

  let html = '';
  // Render in LP → UP → JHS order, then any others
  const orderedKeys = [...levelOrder.filter(l => grouped[l]), ...Object.keys(grouped).filter(l => !levelOrder.includes(l))];

  orderedKeys.forEach(lvl => {
    const color = levelColors[lvl] || '#64748b';
    html += `<div style="padding:4px 12px; background:#f8fafc; border-bottom:1px solid #e2e8f0;">
      <span style="font-size:9px; font-weight:900; text-transform:uppercase; letter-spacing:0.08em; color:${color};">${escapeHtml(lvl)}</span>
    </div>`;
    grouped[lvl].forEach(a => {
      html += `
        <label class="flex items-center gap-3 p-3 border-b border-gray-100 last:border-0 hover:bg-purple-50 transition-colors cursor-pointer m-0">
          <input type="checkbox" class="assignment-checkbox w-4 h-4 accent-purple-600" value="${a.id}" onchange="updateBulkRemoveBtn()">
          <div class="flex-1">
            <div class="text-xs font-bold text-gray-800">${escapeHtml(a.subject_name)}</div>
            <div class="text-[10px] text-muted uppercase font-bold tracking-tight">${escapeHtml(a.class_name)}${a.section ? ' ' + escapeHtml(a.section) : ''}</div>
          </div>
          <button type="button" class="p-2 text-danger hover:bg-red-100 rounded-full transition-colors" onclick="event.preventDefault(); removeAssignment(${a.id})" title="Remove Assignment">
            <svg style="width:14px; height:14px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
          </button>
        </label>
      `;
    });
  });
  container.innerHTML = html;
}

function toggleLevelClasses(masterChk) {
  const lvl = masterChk.dataset.level;
  document.querySelectorAll(`#form-assign-inner .class-level-chk[data-level="${lvl}"]`)
    .forEach(cb => { cb.checked = masterChk.checked; });
}

function syncLevelHeader(levelName) {
  const all = document.querySelectorAll(`#form-assign-inner .class-level-chk[data-level="${levelName}"]`);
  const allChecked = Array.from(all).every(cb => cb.checked);
  const anyChecked = Array.from(all).some(cb => cb.checked);
  const hdr = document.querySelector(`#form-assign-inner .level-all-chk[data-level="${levelName}"]`);
  if (hdr) {
    hdr.checked       = allChecked;
    hdr.indeterminate = !allChecked && anyChecked;
  }
}

function toggleSelectAllAssignments(master) {
   const checkboxes = document.querySelectorAll('.assignment-checkbox');
   checkboxes.forEach(cb => cb.checked = master.checked);
   updateBulkRemoveBtn();
}

function updateBulkRemoveBtn() {
   const checked = document.querySelectorAll('.assignment-checkbox:checked');
   const bulkBtn = document.getElementById('btn-bulk-remove');
   bulkBtn.style.display = checked.length > 0 ? 'block' : 'none';
}

function handleBulkRemove() {
   const checked = document.querySelectorAll('.assignment-checkbox:checked');
   const ids = Array.from(checked).map(cb => cb.value);
   
   confirmAction({
     title: 'Remove Multiple Assignments?',
     message: `You are about to remove ${ids.length} subject assignments. Continue?`,
     confirmText: 'Yes, Remove Selected',
     type: 'danger'
   }, () => {
      const container = document.getElementById('bulk-remove-ids-container');
      container.innerHTML = '';
      ids.forEach(id => {
         const input = document.createElement('input');
         input.type = 'hidden';
         input.name = 'assignment_ids[]';
         input.value = id;
         container.appendChild(input);
      });
      document.getElementById('form-bulk-remove').submit();
   });
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
