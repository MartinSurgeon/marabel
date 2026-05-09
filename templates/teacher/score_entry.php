<?php
/**
 * Score Entry Grid Template
 */

$pageTitle = 'Score Entry';
include __DIR__ . '/../layout/header.php';

global $classSub, $studentList, $sbaData, $examData;
$base = defined('APP_BASE') ? APP_BASE : '';
?>

<?php if (isset($classSub)): ?>
<!-- ── Grid Header ────────────────────────────────────────── -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 animate-fade-in" style="position: relative; z-index: 20;">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="<?= $base ?>/teacher/scores" class="btn btn-ghost btn-xs" style="padding:4px; margin-left:-8px;" title="Back to Subjects">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">
        <?= htmlspecialchars($classSub['subject_name']) ?>
      </h1>
    </div>
    <p class="text-muted m-0" style="font-size:var(--text-sm);">
      Class: <strong><?= htmlspecialchars($classSub['class_name'] . ' ' . ($classSub['section'] ?? '')) ?></strong> 
      · Term: <strong><?= Session::get('active_term', 'Current') ?></strong>
    </p>
  </div>
  
  <div class="flex items-center gap-3">
    <div id="save-indicator" class="flex items-center gap-2 px-4 py-2 rounded-full border border-transparent transition-all" style="font-size:12px; font-weight:700;">
       <!-- Dynamically filled by JS -->
    </div>

    <!-- ── Export Dropdown ── -->
    <div class="dropdown relative" id="export-dropdown-wrap">
      <button id="export-btn" class="btn btn-primary" style="border-radius:var(--radius-full); gap:0.5rem;" title="Export score list">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" width="16" height="16">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Export
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="13" height="13">
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>
      <div id="export-dd" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-200 z-50 overflow-hidden animate-fade-in">
        <!-- PDF — primary -->
        <a href="<?= $base ?>/teacher/export-scores?id=<?= (int)$classSub['id'] ?>&format=pdf"
           target="_blank"
           id="export-pdf-link"
           style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:12px; font-weight:700; color:#6d28d9; background:#f5f3ff; border-bottom:1px solid #ede9fe; text-decoration:none;"
           onmouseover="this.style.background='#ede9fe'" onmouseout="this.style.background='#f5f3ff'">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          <span>
            Print / Save PDF
            <span style="display:block; font-size:10px; font-weight:500; color:#7c3aed; margin-top:1px;">Opens print dialog</span>
          </span>
        </a>
        <!-- CSV -->
        <a href="<?= $base ?>/teacher/export-scores?id=<?= (int)$classSub['id'] ?>&format=csv"
           id="export-csv-link"
           style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:12px; font-weight:700; color:var(--clr-text); border-bottom:1px solid #f3f4f6; text-decoration:none;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16" style="color:#16a34a;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Download CSV
        </a>
        <!-- Excel -->
        <a href="<?= $base ?>/teacher/export-scores?id=<?= (int)$classSub['id'] ?>&format=excel"
           id="export-excel-link"
           style="display:flex; align-items:center; gap:10px; padding:11px 16px; font-size:12px; font-weight:700; color:var(--clr-text); text-decoration:none;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16" style="color:#15803d;">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
          Download Excel (.xls)
        </a>
      </div>
    </div>

    <!-- ── Preview Reports Dropdown ── -->
    <div class="dropdown relative">
      <button id="preview-btn" class="btn btn-outline" style="border-radius:var(--radius-full); gap:0.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        Preview Reports
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
      </button>
      <div id="preview-dropdown" class="hidden absolute right-0 mt-2 w-64 max-h-96 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 z-50 animate-fade-in">
        <div class="p-3 border-b border-slate-100 sticky top-0 bg-white z-10">
          <input type="text" placeholder="Search student..." class="w-full p-2 text-xs border border-slate-200 rounded-lg" oninput="const q=this.value.toLowerCase(); this.parentElement.nextElementSibling.querySelectorAll('a').forEach(a=>a.style.display=a.textContent.toLowerCase().includes(q)?'':'none')">
        </div>
        <div class="py-1">
          <?php foreach ($studentList as $s): ?>
            <a href="<?= $base ?>/report?student=<?= $s['id'] ?>&term=<?= $classSub['term_id'] ?>" target="_blank" class="block px-4 py-2 text-xs hover:bg-slate-50 transition-colors border-b border-slate-100 last:border-0" style="color:var(--clr-text); font-weight:600;">
              <?= htmlspecialchars($s['full_name']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- ── Score Grid ─────────────────────────────────────────── -->
<div class="card" style="padding:0; overflow:hidden; border:1px solid var(--clr-border);">
  <div style="overflow-x:auto; max-height:calc(100vh - 280px); overflow-y:auto;">
    <table class="table-sba" style="width:100%; border-collapse:collapse; min-width:1000px;">
      <thead style="position:sticky; top:0; z-index:10; background:var(--clr-surface-2); box-shadow:0 1px 0 var(--clr-border);">
        <tr>
          <th style="width:50px; text-align:center; padding:1rem;">#</th>
          <th style="width:280px; text-align:left; padding:1rem; position:sticky; left:0; background:inherit; z-index:11; border-right:1px solid var(--clr-border);">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <span>Student Name</span>
              <button id="sort-names" class="text-primary hover:bg-slate-200/50 p-1 rounded transition-colors" title="Toggle Name Sort" style="border:none; background:transparent; cursor:pointer; color:var(--clr-primary);">
                <i class="fas fa-sort"></i>
              </button>
            </div>
          </th>
          <th style="width:100px; text-align:center; padding:1rem;">Class Test (15)</th>
          <th style="width:100px; text-align:center; padding:1rem;">Group Work (15)</th>
          <th style="width:100px; text-align:center; padding:1rem;">Project (15)</th>
          <th style="width:100px; text-align:center; padding:1rem;">Indiv. Test (15)</th>
          <th style="width:100px; text-align:center; padding:1rem; background:rgba(var(--clr-primary-rgb), 0.03);">SBA (60)</th>
          <th style="width:100px; text-align:center; padding:1rem; background:rgba(var(--clr-primary-rgb), 0.06);">SBA (50%)</th>
          <th style="width:120px; text-align:center; padding:1rem; border-left:2px solid var(--clr-border);">Exam (100)</th>
          <th style="width:100px; text-align:center; padding:1rem; background:rgba(var(--clr-primary-rgb), 0.1);">Exam (50%)</th>
          <th style="width:100px; text-align:center; padding:1rem; font-weight:900; background:var(--clr-primary); color:white;">Total (100)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($studentList as $index => $s): 
            $sba = $sbaData[$s['id']] ?? [];
            $exam = $examData[$s['id']] ?? [];
        ?>
        <tr data-student-id="<?= $s['id'] ?>" class="score-row hover:bg-slate-50 transition-colors">
          <td class="row-index" style="text-align:center; color:var(--clr-text-muted); font-size:12px;"><?= $index + 1 ?></td>
          <td style="padding:0.75rem 1rem; position:sticky; left:0; background:white; z-index:5; border-right:1px solid var(--clr-border);">
            <div class="student-name" style="font-weight:700; color:var(--clr-text); font-size:14px;"><?= htmlspecialchars($s['full_name']) ?></div>
            <div style="font-size:10px; color:var(--clr-text-muted); font-weight:600;"><?= $s['student_id_number'] ?></div>
          </td>
          
          <td style="padding:0;">
            <input type="number" step="1" min="0" max="15" 
                   class="score-input" data-field="class_test" 
                   value="<?= isset($sba['class_test']) ? (int)$sba['class_test'] : '' ?>" 
                   onfocus="this.select()">
          </td>
          <td style="padding:0;">
            <input type="number" step="1" min="0" max="15" 
                   class="score-input" data-field="group_work" 
                   value="<?= isset($sba['group_work']) ? (int)$sba['group_work'] : '' ?>" 
                   onfocus="this.select()">
          </td>
          <td style="padding:0;">
            <input type="number" step="1" min="0" max="15" 
                   class="score-input" data-field="project" 
                   value="<?= isset($sba['project']) ? (int)$sba['project'] : '' ?>" 
                   onfocus="this.select()">
          </td>
          <td style="padding:0;">
            <input type="number" step="1" min="0" max="15" 
                   class="score-input" data-field="individual_test" 
                   value="<?= isset($sba['individual_test']) ? (int)$sba['individual_test'] : '' ?>" 
                   onfocus="this.select()">
          </td>
          
          <!-- SBA Totals (Readonly) -->
          <td style="text-align:center; background:rgba(var(--clr-primary-rgb), 0.03); font-weight:700;">
            <span class="sub-total"><?= $sba['sub_total'] ?? '0' ?></span>
          </td>
          <td style="text-align:center; background:rgba(var(--clr-primary-rgb), 0.06); font-weight:800; color:var(--clr-primary);">
            <span class="scaled-sba"><?= $sba['class_score'] ?? '0' ?></span>
          </td>
          
          <!-- Exam -->
          <td style="padding:0; border-left:2px solid var(--clr-border);">
            <input type="number" step="1" min="0" max="100" 
                   class="score-input" data-field="raw_score" 
                   value="<?= isset($exam['raw_score']) ? (int)$exam['raw_score'] : '' ?>" 
                   onfocus="this.select()">
          </td>
          <td style="text-align:center; background:rgba(var(--clr-primary-rgb), 0.1); font-weight:800; color:var(--clr-primary-300);">
            <span class="scaled-exam"><?= $exam['exam_score'] ?? '0' ?></span>
          </td>
          
          <!-- Overall -->
          <td style="text-align:center; background:rgba(var(--clr-primary-rgb), 0.03); font-weight:900; font-size:1.1rem; border-left:1px solid var(--clr-border);">
            <span class="overall-total"><?= round(($sba['class_score'] ?? 0) + ($exam['exam_score'] ?? 0), 0) ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: /* SELECTION VIEW (Moved from Old Dashboard) */ ?>
  <?php global $activeTerm, $assignedBundles; ?>
  
  <div class="flex justify-between items-center mb-8 gap-4 animate-fade-in">
    <div>
      <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Score Entry Hub</h1>
      <p class="text-muted m-0" style="font-size:var(--text-sm);">Select a subject below to enter student scores and track completion.</p>
    </div>
    <div style="font-size: 13px; font-weight: 700; color: var(--clr-primary); background: var(--clr-primary-50); padding: 0.5rem 1rem; border-radius: var(--radius-full);">
       <?= htmlspecialchars($activeTerm['year_name'] ?? '') ?> · <?= htmlspecialchars($activeTerm['name'] ?? '') ?>
    </div>
  </div>

  <?php if (empty($assignedBundles)): ?>
    <div class="card flex flex-col items-center justify-center py-12 text-center" style="border-style: dashed;">
        <div style="color: var(--clr-border); margin-bottom: 1rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="48" height="48"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 012-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
        </div>
        <h3 style="font-weight: 700; color: var(--clr-text);">No assignments found</h3>
        <p class="text-muted text-sm">You haven't been assigned any classes for score entry this term.</p>
    </div>
  <?php else: ?>
    <div class="grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
      <?php foreach ($assignedBundles as $b): 
          $totalSt    = (int)$b['student_count'];
          $sbaDone    = (int)$b['sba_completed_count'];
          $examDone   = (int)$b['exam_completed_count'];
          $isComplete = ($totalSt > 0 && $sbaDone >= $totalSt && $examDone >= $totalSt);
          
          $sbaPercent  = ($totalSt > 0) ? min(100, round(($sbaDone  / $totalSt) * 100)) : 0;
          $examPercent = ($totalSt > 0) ? min(100, round(($examDone / $totalSt) * 100)) : 0;
      ?>
      <div class="card hover-lift animate-fade-in" style="padding:0; overflow:hidden; border:1px solid var(--clr-border); display:flex; flex-direction:column;">
        <div style="padding:1.5rem; background:var(--clr-surface-2); border-bottom:1px solid var(--clr-border);">
          <div style="font-size:10px; font-weight:800; color:var(--clr-primary); text-transform:uppercase; letter-spacing:0.06em; margin-bottom:0.5rem;">
            <?= htmlspecialchars($b['level_name']) ?>
          </div>
          <h3 style="margin:0; font-weight:800; font-size:1.125rem; color:var(--clr-text);">
            <?= htmlspecialchars($b['class_name']) ?><?= $b['section'] ? " ({$b['section']})" : '' ?>
          </h3>
          <p class="text-muted" style="font-size:13px; margin:0.25rem 0 0;"><?= htmlspecialchars($b['subject_name']) ?></p>
        </div>

        <div style="padding:1.25rem; flex:1;">
          <div class="mb-4">
            <div class="flex justify-between items-center mb-2 text-xs font-bold">
              <span>SBA Components</span>
              <span style="color:<?= $sbaPercent >= 100 ? 'var(--clr-success)' : 'var(--clr-text)' ?>;"><?= $sbaPercent ?>%</span>
            </div>
            <div style="height:6px; background:var(--clr-border); border-radius:10px; overflow:hidden;">
              <div style="height:100%; width:<?= $sbaPercent ?>%; background:var(--clr-primary); border-radius:10px; transition:width 0.5s ease;"></div>
            </div>
          </div>

          <div>
            <div class="flex justify-between items-center mb-2 text-xs font-bold">
              <span>End of Term Exam</span>
              <span style="color:<?= $examPercent >= 100 ? 'var(--clr-success)' : 'var(--clr-text)' ?>;"><?= $examPercent ?>%</span>
            </div>
            <div style="height:6px; background:var(--clr-border); border-radius:10px; overflow:hidden;">
              <div style="height:100%; width:<?= $examPercent ?>%; background:var(--clr-primary-300); border-radius:10px; transition:width 0.5s ease;"></div>
            </div>
          </div>
        </div>

        <div style="padding:1rem 1.25rem; border-top:1px solid var(--clr-border); background:var(--clr-surface-2);">
          <?php if ($b['is_locked']): ?>
            <div class="flex items-center gap-2 text-muted" style="font-size:11px; font-weight:800;">
               <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
               LOCKED BY ADMIN
            </div>
          <?php else: ?>
            <a href="<?= $base ?>/teacher/scores?id=<?= $b['class_subject_id'] ?>" 
               class="btn <?= $isComplete ? 'btn-outline' : 'btn-primary' ?> btn-sm w-full" 
               style="justify-content:center; font-size:12px;">
               <?= $isComplete ? 'View / Edit Scores' : 'Enter Scores' ?>
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<?php endif; ?>

<!-- ── Custom Styles for Grid ─────────────────────────────────── -->
<style>
.table-sba input.score-input {
  width: 100%;
  display: block;
  padding: 0.75rem 0.25rem;
  border: 1.5px solid #cbd5e1 !important; /* Explicit light blue-gray border */
  border-radius: 6px;
  background-color: #ffffff !important;   /* Force white background */
  text-align: center;
  font-weight: 700;
  font-size: 15px;
  font-family: inherit;
  color: #000000 !important;              /* Force black text */
  margin: 4px auto;
  max-width: 80px;
  transition: border-color 0.2s, box-shadow 0.2s;
}
.table-sba input.score-input:focus {
  outline: none !important;
  border-color: var(--clr-primary, #9633cc) !important;
  box-shadow: 0 0 0 3px rgba(150, 51, 204, 0.2) !important;
  background-color: #ffffff !important;
}
.table-sba input.score-input::-webkit-inner-spin-button,
.table-sba input.score-input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.score-row td {
  border-bottom: 1px solid var(--clr-border);
}
.score-row.saving {
  background: rgba(var(--clr-primary-rgb), 0.05);
}
.score-row.error {
  background: rgba(var(--clr-danger-rgb), 0.05);
}
/* Active Row Highlight (HCI) */
.score-row.active-row td {
  background-color: #f5f3ff !important; /* Subtle purple tint */
  transition: background-color 0.15s ease;
}
.alert-float {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 1000;
}
/* Fired for ~1 second when the user enters a value above the max */
@keyframes shake-red {
  0%,100% { outline-color: var(--clr-danger); transform: translateX(0); }
  25%      { transform: translateX(-3px); }
  75%      { transform: translateX(3px); }
}
.table-sba input.score-input.input-over-limit {
  outline: 2px solid var(--clr-danger) !important;
  background: rgba(var(--clr-danger-rgb), 0.08) !important;
  animation: shake-red 0.4s ease;
}

/* Contextual Tooltip Bubble (HCI) */
.input-error-bubble {
  position: absolute;
  background: var(--clr-danger);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 10px;
  font-weight: 800;
  white-space: nowrap;
  pointer-events: none;
  z-index: 1000;
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
  transform: translateY(-8px);
  animation: float-up 0.2s ease-out;
}
.input-error-bubble::after {
  content: '';
  position: absolute;
  bottom: -4px;
  left: 50%;
  transform: translateX(-50%);
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid var(--clr-danger);
}
@keyframes float-up {
  from { opacity: 0; transform: translateY(0); }
  to { opacity: 1; transform: translateY(-8px); }
}
</style>

<!-- ── Grid Logic (JS) ────────────────────────────────────────── -->
<script>
const CONFIG = {
  classSubjectId: <?= isset($classSub['id']) ? (int)$classSub['id'] : 0 ?>,
  termId: <?= isset($classSub['term_id']) ? (int)$classSub['term_id'] : 0 ?>,
  base: '<?= $base ?>',
  csrf: '<?= CSRF::token() ?>'
};

document.addEventListener('DOMContentLoaded', () => {
  const inputs = document.querySelectorAll('.score-input');
  const indicator = document.getElementById('save-indicator');
  
  let saveQueue = 0;

  function updateIndicator(status, message) {
    if (status === 'saving') {
      indicator.style.color = 'var(--clr-primary)';
      indicator.style.background = 'rgba(var(--clr-primary-rgb), 0.1)';
      indicator.innerHTML = `<svg class="animate-spin" viewBox="0 0 24 24" fill="none" width="14" height="14" style="border:2px solid currentColor; border-top-color:transparent; border-radius:50%;"></svg> Saving...`;
    } else if (status === 'saved') {
      indicator.style.color = 'var(--clr-success)';
      indicator.style.background = 'rgba(22, 163, 74, 0.1)';
      indicator.innerHTML = `✓ All changes saved`;
      setTimeout(() => { if(saveQueue === 0) indicator.style.opacity = '0.5'; }, 2000);
    } else if (status === 'error') {
      indicator.style.color = 'var(--clr-danger)';
      indicator.style.background = 'rgba(var(--clr-danger-rgb), 0.1)';
      indicator.innerHTML = `⚠ Error: ${message}`;
      indicator.style.opacity = '1';
    }
  }

  // Optimistic Calculation on Input
  // Sorting Logic
  const sortBtn = document.getElementById('sort-names');
  let sortOrder = 'asc';

  if (sortBtn) {
    sortBtn.addEventListener('click', () => {
      const tbody = document.querySelector('.table-sba tbody');
      const rows = Array.from(tbody.querySelectorAll('.score-row'));
      
      sortOrder = sortOrder === 'asc' ? 'desc' : 'asc';
      
      rows.sort((a, b) => {
        const nameA = a.querySelector('.student-name').textContent.toLowerCase().trim();
        const nameB = b.querySelector('.student-name').textContent.toLowerCase().trim();
        return sortOrder === 'asc' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
      });

      rows.forEach((row, idx) => {
        const indexEl = row.querySelector('.row-index');
        if (indexEl) indexEl.textContent = idx + 1;
        tbody.appendChild(row);
      });

      sortBtn.innerHTML = `<i class="fas fa-sort-alpha-${sortOrder === 'asc' ? 'down' : 'up'}"></i>`;
    });
  }

  // Optimistic Calculation on Input
  const calculateRow = (row) => {
    const sbaFields = ['class_test', 'group_work', 'project', 'individual_test'];
    let subTotal = 0;
    
    sbaFields.forEach(f => {
      const input = row.querySelector(`[data-field="${f}"]`);
      if (input) {
        const val = parseFloat(input.value) || 0;
        subTotal += val;
      }
    });

    const scaledSba = Math.round((subTotal / 60) * 50);
    const rawExamInput = row.querySelector('[data-field="raw_score"]');
    const rawExam = rawExamInput ? (parseFloat(rawExamInput.value) || 0) : 0;
    const scaledExam = Math.round((rawExam / 100) * 50);
    const finalTotal = Math.round(scaledSba + scaledExam);

    const subTotalEl = row.querySelector('.sub-total');
    if (subTotalEl) subTotalEl.textContent = subTotal % 1 === 0 ? subTotal : subTotal.toFixed(1);
    
    const scaledSbaEl = row.querySelector('.scaled-sba');
    if (scaledSbaEl) scaledSbaEl.textContent = scaledSba;
    
    const scaledExamEl = row.querySelector('.scaled-exam');
    if (scaledExamEl) scaledExamEl.textContent = scaledExam;
    
    const overallEl = row.querySelector('.overall-total');
    if (overallEl) overallEl.textContent = finalTotal;
  };

  // Dropdown Management (HCI: closes on click-outside)
  const previewBtn = document.getElementById('preview-btn');
  const previewDropdown = document.getElementById('preview-dropdown');

  if (previewBtn && previewDropdown) {
    previewBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      previewDropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', (e) => {
      if (!previewDropdown.classList.contains('hidden') && !previewDropdown.contains(e.target) && e.target !== previewBtn) {
        previewDropdown.classList.add('hidden');
      }
    });
  }

  // Export Dropdown (same click-outside pattern)
  const exportBtn = document.getElementById('export-btn');
  const exportDd  = document.getElementById('export-dd');

  if (exportBtn && exportDd) {
    exportBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      exportDd.classList.toggle('hidden');
      // Close the preview dropdown if open
      previewDropdown?.classList.add('hidden');
    });

    document.addEventListener('click', (e) => {
      if (!exportDd.classList.contains('hidden') && !exportDd.contains(e.target) && e.target !== exportBtn) {
        exportDd.classList.add('hidden');
      }
    });
  }

  // HCI Tooltip Helper
  function showInputError(el, message) {
    // Remove existing bubble if any
    const existing = el.parentElement.querySelector('.input-error-bubble');
    if (existing) existing.remove();

    const bubble = document.createElement('div');
    bubble.className = 'input-error-bubble';
    bubble.textContent = message;
    
    // Position it above the input
    el.parentElement.style.position = 'relative'; 
    el.parentElement.appendChild(bubble);
    
    bubble.style.left = '50%';
    bubble.style.marginLeft = `-${bubble.offsetWidth / 2}px`;
    bubble.style.top = '-20px';

    setTimeout(() => {
      bubble.style.transition = 'opacity 0.3s ease';
      bubble.style.opacity = '0';
      setTimeout(() => bubble.remove(), 300);
    }, 2200);
  }

  inputs.forEach(input => {
    // Immediate feedback + max-value enforcement
    input.addEventListener('input', (e) => {
      const el = e.target;
      const max = parseFloat(el.max);
      const min = parseFloat(el.min) || 0;
      
      // 1. Block decimals immediately
      if (el.value.includes('.')) {
        showInputError(el, "No decimal allowed");
        el.value = el.value.replace(/\./g, '');
      }

      // 2. Prevent leading zeros (e.g., "03" -> "3")
      if (el.value.length > 1 && el.value.startsWith('0')) {
        el.value = el.value.replace(/^0+/, '');
      }

      let val = parseFloat(el.value);

      if (!isNaN(val)) {
        if (val > max) {
          showInputError(el, `Max value is ${max}`);
          el.value = ''; // Reset for safety
          el.classList.add('input-over-limit');
          setTimeout(() => el.classList.remove('input-over-limit'), 1000);
        } else if (val < min) {
          showInputError(el, `Min value is ${min}`);
          el.value = '';
        } else {
          el.classList.remove('input-over-limit');
          el.title = '';
        }
      }
      calculateRow(e.target.closest('.score-row'));
    });

    input.addEventListener('change', async (e) => {
      const field = e.target.dataset.field;
      const value = e.target.value;
      const row = e.target.closest('.score-row');
      const studentId = row.dataset.studentId;

      saveQueue++;
      updateIndicator('saving');
      row.classList.add('saving');
      row.classList.remove('error');

      try {
        const formData = new FormData();
        formData.append('student_id', studentId);
        formData.append('class_subject_id', CONFIG.classSubjectId);
        formData.append('term_id', CONFIG.termId);
        formData.append('field', field);
        formData.append('value', value);
        formData.append('_csrf_token', CONFIG.csrf);

        const response = await fetch(`${CONFIG.base}/teacher/scores`, {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        if (!response.ok) {
           throw new Error(`Server ${response.status}`);
        }

        const result = await response.json();
        saveQueue--;

        if (result.success) {
          row.classList.remove('saving');
          if (saveQueue === 0) updateIndicator('saved');
        } else {
          row.classList.remove('saving');
          row.classList.add('error');
          updateIndicator('error', result.message || 'Validation failed');
        }
      } catch (err) {
        saveQueue--;
        console.error('Save error:', err);
        row.classList.remove('saving');
        row.classList.add('error');
        updateIndicator('error', err.message || 'Connection lost');
      }
    });

    // Highlight Active Row (HCI)
    input.addEventListener('focus', (e) => {
      const row = e.target.closest('.score-row');
      if (row) row.classList.add('active-row');
    });
    input.addEventListener('blur', (e) => {
      const row = e.target.closest('.score-row');
      if (row) row.classList.remove('active-row');
    });

    // Keyboard Navigation
    input.addEventListener('keydown', (e) => {
      const idx = Array.from(inputs).indexOf(input);
      const rowCount = <?= isset($studentList) ? count($studentList) : 0 ?>;
      const colCount = 5; // fields: class_test, group_work, project, indiv_test, raw_score

      if (e.key === 'Enter' || e.key === 'ArrowDown') {
        e.preventDefault();
        const next = inputs[idx + colCount];
        if (next) next.focus();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        const prev = inputs[idx - colCount];
        if (prev) prev.focus();
      } else if (e.key === 'ArrowRight' && (input.selectionEnd === input.value.length || input.value === '')) {
        const next = inputs[idx + 1];
        if (next) next.focus();
      } else if (e.key === 'ArrowLeft' && (input.selectionStart === 0 || input.value === '')) {
        const prev = inputs[idx - 1];
        if (prev) prev.focus();
      }
    });
  });

  function updateCalculations(row) {
    calculateRow(row);
  }
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
