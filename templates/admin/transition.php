<?php
/**
 * Academic Session Transition Dashboard
 */

$pageTitle = 'Session Transition Manager';
include __DIR__ . '/../layout/header.php';

global $activeTerm, $academicYears, $termStats;
$base = defined('APP_BASE') ? APP_BASE : '';
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="m-0" style="font-size: var(--text-2xl); font-weight: 800; letter-spacing: -0.03em; color: var(--clr-text);">Session Transition Manager</h1>
        <p class="text-muted m-0">Transition your data between terms or implement full academic year promotions.</p>
    </div>
    <div class="flex gap-2">
        <a href="<?= $base ?>/admin/years" class="btn btn-ghost">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" class="mr-2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Sessions
        </a>
    </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 2rem;">

    <!-- ── Term Transition Section ────────────────────────────── -->
    <div class="card" style="padding: 2rem; border-color: var(--clr-primary-100);">
        <div style="width: 48px; height: 48px; background: var(--clr-primary-50); color: var(--clr-primary); border-radius: 12px; display: flex; align-items:center; justify-content:center; margin-bottom: 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
        </div>
        <h2 style="font-weight: 800; margin-bottom: 0.5rem;">Term-to-Term Transition</h2>
        <p class="text-muted" style="font-size: 14px; margin-bottom: 2rem;">Move from the current term to the next within the same academic year. This process clones all teacher-subject assignments automatically.</p>

        <?php if ($activeTerm): ?>
        <div style="background: var(--clr-surface-2); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <div style="font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--clr-text-muted); margin-bottom: 1rem;">Current Session Status</div>
            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-muted">Active Term:</span>
                    <span style="font-weight: 700; color: var(--clr-primary);"><?= htmlspecialchars($activeTerm['name']) ?> (<?= htmlspecialchars($activeTerm['year_name']) ?>)</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-muted">Teacher Assignments:</span>
                    <span style="font-weight: 700; color: var(--clr-text);"><?= $termStats['assignments'] ?> records</span>
                </div>
            </div>
        </div>

        <form action="<?= $base ?>/admin/transition" method="POST" onsubmit="event.preventDefault(); confirmAction({title: 'Confirm Term Transition', message: 'BE CAREFUL: This will deactivate the current term and activate the selected target term. Continue?', type: 'warning', confirmText: 'Proceed'}, () => this.submit());">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="term_transition">
            <input type="hidden" name="source_term_id" value="<?= $activeTerm['id'] ?>">

            <div class="form-group">
                <label class="form-label">Next Target Term</label>
                <select name="target_term_id" class="form-control" required>
                    <option value="">— Select Target Term —</option>
                    <?php 
                    $otherTerms = DB::query("SELECT t.* FROM terms t WHERE t.academic_year_id = ? AND t.id != ? ORDER BY t.term_number ASC", [$activeTerm['academic_year_id'], $activeTerm['id']]);
                    foreach ($otherTerms as $ot):
                    ?>
                    <option value="<?= $ot['id'] ?>"><?= htmlspecialchars($ot['name']) ?> (<?= htmlspecialchars($activeTerm['year_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Choose the next term to activate and populate with assignments.</div>
            </div>

            <button type="submit" class="btn btn-primary w-full" style="justify-content: center; padding: 1rem;">
                Start Term Transition
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" class="ml-2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>
        <?php else: ?>
        <p style="color: var(--clr-danger); font-weight: 700;">⚠ No active term found to transition from.</p>
        <?php endif; ?>
    </div>

    <!-- ── Year Transition Section ────────────────────────────── -->
    <div class="card" style="padding: 2rem; border-color: var(--clr-success);">
        <div style="width: 48px; height: 48px; background: var(--clr-success-bg); color: var(--clr-success); border-radius: 12px; display: flex; align-items:center; justify-content:center; margin-bottom: 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <h2 style="font-weight: 800; margin-bottom: 0.5rem;">End-of-Year Transition</h2>
        <p class="text-muted" style="font-size: 14px; margin-bottom: 2rem;">A complete session migration. This clones classes to the new year, promotes students, and migrates assignments to Term 1.</p>

        <div style="background: var(--clr-warning-bg); border: 1px dashed var(--clr-warning); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <div style="font-weight: 800; color: var(--clr-warning); display: flex; align-items: center; gap: 0.5rem; font-size: 13px; margin-bottom: 0.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                PRE-REQUISITES
            </div>
            <ul style="margin: 0; padding-left: 1.5rem; font-size: 12px; line-height: 1.6; color: #92400e;">
                <li>Ensure all marks for the current year are published.</li>
                <li>Ensure <a href="<?= $base ?>/admin/promotions" style="text-decoration: underline; font-weight: 700; color: inherit;">Class Promotions</a> are finalized.</li>
                <li>Create the new target Academic Year first.</li>
            </ul>
        </div>

        <form action="<?= $base ?>/admin/transition" method="POST" onsubmit="event.preventDefault(); confirmAction({title: 'HIGH IMPACT ACTION', message: 'This will perform a full year migration, promoting students and cloning structures.<br><br><b>This CANNOT be easily undone.</b> Proceed?', type: 'danger', confirmText: 'Execute Migration'}, () => this.submit());">
            <?= CSRF::field() ?>
            <input type="hidden" name="_action" value="year_transition">

            <div class="form-group">
                <label class="form-label">Source Year (Closing)</label>
                <select name="source_year_id" class="form-control" required>
                    <option value="">— Select Closing Year —</option>
                    <?php foreach ($academicYears as $ay): ?>
                    <option value="<?= $ay['id'] ?>" <?= ($activeTerm['academic_year_id'] ?? 0) == $ay['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ay['year_name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Target Year (Opening)</label>
                <select name="target_year_id" class="form-control" required id="target_year_select" onchange="loadTargetTerms(this.value)">
                    <option value="">— Select New Year —</option>
                    <?php foreach ($academicYears as $ay): ?>
                    <option value="<?= $ay['id'] ?>"><?= htmlspecialchars($ay['year_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Initial Term (Usually Term 1)</label>
                <select name="target_term_id" class="form-control" required id="target_term_select">
                    <option value="">— Select Year First —</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success w-full" style="justify-content: center; padding: 1rem; background: var(--clr-success);">
                Execute Full Year Migration
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" class="ml-2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </button>
        </form>
    </div>

</div>

<script>
async function loadTargetTerms(yearId) {
    const select = document.getElementById('target_term_select');
    if (!yearId) {
        select.innerHTML = '<option value="">— Select Year First —</option>';
        return;
    }
    select.innerHTML = '<option value="">Loading...</option>';
    
    try {
        const resp = await fetch(`<?= $base ?>/admin/transition/terms?year_id=${yearId}`);
        const data = await resp.json();
        
        if (data.terms && data.terms.length > 0) {
            select.innerHTML = data.terms.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
        } else {
            select.innerHTML = '<option value="">No terms found in that year</option>';
        }
    } catch (e) {
        select.innerHTML = '<option value="">Error loading terms</option>';
    }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
