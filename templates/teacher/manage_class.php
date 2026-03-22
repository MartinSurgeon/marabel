<?php
/**
 * Class Management Template (Attendance & Remarks)
 */

$pageTitle = 'Class Management';
include __DIR__ . '/../layout/header.php';

global $activeTerm, $targetClass, $studentList, $attData, $remData;
$base = defined('APP_BASE') ? APP_BASE : '';
?>

<!-- ── Header ────────────────────────────────────────────── -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
  <div>
    <div class="flex items-center gap-2 mb-1">
      <a href="<?= $base ?>/teacher" class="btn btn-ghost btn-xs" style="padding:4px; margin-left:-8px;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </a>
      <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">
        Attendance & Remarks
      </h1>
    </div>
    <p class="text-muted m-0" style="font-size:var(--text-sm);">
      Class: <strong><?= htmlspecialchars($targetClass['class_name'] . ' ' . ($targetClass['section'] ?? '')) ?></strong> 
      · Term: <strong><?= htmlspecialchars($activeTerm['year_name'] . ' ' . $activeTerm['name']) ?></strong>
    </p>
  </div>
  
  <div id="save-indicator" class="flex items-center gap-2 px-4 py-2 rounded-full border border-transparent transition-all" style="font-size:12px; font-weight:700;">
     <!-- Dynamically filled by JS -->
  </div>
</div>

<!-- ── Management Grid ────────────────────────────────────── -->
<div class="card" style="padding:0; overflow:hidden; border:1px solid var(--clr-border);">
  <div style="overflow-x:auto;">
    <table class="table-management" style="width:100%; border-collapse:collapse; min-width:1100px;">
      <thead style="background:var(--clr-surface-2); box-shadow:0 1px 0 var(--clr-border);">
        <tr>
          <th style="width:50px; text-align:center; padding:1rem;">#</th>
          <th style="width:250px; text-align:left; padding:1rem; position:sticky; left:0; background:inherit; z-index:11; border-right:1px solid var(--clr-border);">Student Name</th>
          <th style="width:120px; text-align:center; padding:1rem;">Attendance (<?= $activeTerm['total_school_days'] ?>)</th>
          <th style="width:120px; text-align:center; padding:1rem;">Conduct (1-5)</th>
          <th style="width:120px; text-align:center; padding:1rem;">Attitude (1-5)</th>
          <th style="text-align:left; padding:1rem;">Teacher's Remark</th>
          <th style="width:100px; text-align:center; padding:1rem;">Preview</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($studentList as $index => $s): 
            $rem = $remData[$s['id']] ?? [];
            $daysPresent = $attData[$s['id']] ?? '';
        ?>
        <tr data-student-id="<?= $s['id'] ?>" class="manage-row hover:bg-slate-50 transition-colors">
          <td style="text-align:center; color:var(--clr-text-muted); font-size:12px;"><?= $index + 1 ?></td>
          <td style="padding:0.75rem 1rem; position:sticky; left:0; background:white; z-index:5; border-right:1px solid var(--clr-border);">
            <div style="font-weight:700; color:var(--clr-text); font-size:14px;"><?= htmlspecialchars($s['full_name']) ?></div>
            <div style="font-size:10px; color:var(--clr-text-muted); font-weight:600;"><?= $s['student_id_number'] ?></div>
          </td>
          
          <td style="padding:0.5rem 0.75rem;">
            <input type="number" min="0" max="<?= $activeTerm['total_school_days'] ?>" 
                   class="manage-input" data-field="days_present" 
                   value="<?= $daysPresent ?>" 
                   style="width:80px; text-align:center; margin:0 auto; display:block;">
          </td>
          
          <td style="padding:0.5rem 0.75rem;">
            <select class="manage-input" data-field="conduct_character" style="width:100%;">
                <option value="">Select...</option>
                <?php for($i=1; $i<=5; $i++): ?>
                    <option value="<?= $i ?>" <?= ($rem['conduct_character'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
          </td>
          
          <td style="padding:0.5rem 0.75rem;">
            <select class="manage-input" data-field="attitude" style="width:100%;">
                <option value="">Select...</option>
                <?php for($i=1; $i<=5; $i++): ?>
                    <option value="<?= $i ?>" <?= ($rem['attitude'] ?? '') == $i ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
          </td>
          
          <td style="padding:0.5rem 1rem;">
            <textarea class="manage-input" data-field="teacher_remark" 
                      rows="1" placeholder="Enter remark..."
                      style="width:100%; border:1px solid var(--clr-border); border-radius:6px; padding:8px; font-size:13px; resize:vertical; display:block;"><?= htmlspecialchars($rem['teacher_remark'] ?? '') ?></textarea>
          </td>

          <td style="text-align:center; padding:1rem;">
            <a href="<?= $base ?>/report?student=<?= $s['id'] ?>&term=<?= $activeTerm['id'] ?>" 
               target="_blank" class="btn btn-ghost btn-sm" title="Preview Report Card"
               style="padding:8px; color:var(--clr-primary);">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
.manage-input {
    border: 1px solid var(--clr-border);
    border-radius: 6px;
    padding: 8px;
    font-size: 14px;
    background: white;
    transition: all 0.2s;
}
.manage-input:focus {
    border-color: var(--clr-primary);
    box-shadow: 0 0 0 3px rgba(var(--clr-primary-rgb), 0.1);
    outline: none;
}
.manage-row td { border-bottom: 1px solid var(--clr-border); }
.manage-row.saving { background: rgba(var(--clr-primary-rgb), 0.05); }
.manage-row.error { background: rgba(var(--clr-danger-rgb), 0.05); }
</style>

<script>
const CONFIG = {
    classId: <?= (int)$targetClass['id'] ?>,
    termId: <?= (int)$activeTerm['id'] ?>,
    base: '<?= $base ?>',
    csrf: '<?= CSRF::token() ?>'
};

document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.manage-input');
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
            indicator.innerHTML = `✓ Saved`;
            setTimeout(() => { if(saveQueue === 0) indicator.style.opacity = '0.5'; }, 2000);
        } else if (status === 'error') {
            indicator.style.color = 'var(--clr-danger)';
            indicator.style.background = 'rgba(var(--clr-danger-rgb), 0.1)';
            indicator.innerHTML = `⚠ Error: ${message}`;
            indicator.style.opacity = '1';
        }
    }

    inputs.forEach(input => {
        input.addEventListener('change', async (e) => {
            const field = e.target.dataset.field;
            const value = e.target.value;
            const row = e.target.closest('.manage-row');
            const studentId = row.dataset.studentId;

            saveQueue++;
            updateIndicator('saving');
            row.classList.add('saving');
            row.classList.remove('error');

            try {
                const formData = new FormData();
                formData.append('student_id', studentId);
                formData.append('field', field);
                formData.append('value', value);
                formData.append('_csrf_token', CONFIG.csrf);

                const response = await fetch(`${CONFIG.base}/teacher/class?id=${CONFIG.classId}`, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) throw new Error(`Server ${response.status}`);
                const result = await response.json();
                saveQueue--;

                if (result.success) {
                    row.classList.remove('saving');
                    if (saveQueue === 0) updateIndicator('saved');
                } else {
                    row.classList.remove('saving');
                    row.classList.add('error');
                    updateIndicator('error', result.message || 'Failed to save');
                }
            } catch (err) {
                saveQueue--;
                row.classList.remove('saving');
                row.classList.add('error');
                updateIndicator('error', err.message || 'Connection lost');
            }
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
