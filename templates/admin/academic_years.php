<?php
/**
 * Academic Years & Terms Management View
 * Uaddara Basic School — SBA Management System
 * HCI/UX: Modal-driven CRUD, empty states, active state badges, confirmations
 */
$pageTitle = 'Academic Years & Terms';
include __DIR__ . '/../layout/header.php';

global $academicYears;
$base         = defined('APP_BASE') ? APP_BASE : '';
$years        = $academicYears ?? [];
$termsByYear  = [];

// Pre-load all terms grouped by year (avoids N+1 queries in view)
if (!empty($years)) {
    $yearIds = array_column($years, 'id');
    $inList  = implode(',', array_fill(0, count($yearIds), '?'));
    $terms   = DB::query(
        "SELECT * FROM terms WHERE academic_year_id IN ({$inList}) ORDER BY academic_year_id, term_number",
        $yearIds
    );
    foreach ($terms as $t) {
        $termsByYear[$t['academic_year_id']][] = $t;
    }
}

$MONTH = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
function fmtDate(?string $d): string {
    if (!$d) return '—';
    [$y,$m,$dd] = explode('-', $d);
    global $MONTH;
    return "{$dd} {$MONTH[(int)$m-1]} {$y}";
}
?>

<div class="flex justify-between items-center mb-8 gap-4 flex-wrap">
  <div style="flex:1; min-width:300px;">
    <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Academic Years & Terms</h1>
    <p class="text-muted m-0" style="font-size:var(--text-sm); max-width:600px;">
      Configure the school calendar. Set the <strong>Active Term</strong> to enable score entry and report card generation across the system.
    </p>
  </div>
  <div class="flex gap-2">
    <a href="<?= $base ?>/admin/transition" class="btn btn-outline" style="background:#fff;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" class="mr-2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
      Session Transition
    </a>
    <button class="btn btn-primary shadow-purple" onclick="openModal('modal-year')" aria-haspopup="dialog">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
      New Academic Year
    </button>
  </div>
</div>

<?php if (empty($years)): ?>
<!-- ── Empty State ──────────────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed; background:var(--clr-surface-2);">
  <div style="background:var(--clr-surface); padding:2rem; border-radius:var(--radius-full); box-shadow:var(--shadow-lg); margin-bottom:2rem;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary-300)"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text); margin-bottom:0.5rem;">No Academic Years Setup</h2>
  <p class="text-muted" style="max-width:320px; margin:0 auto 2rem;">You need at least one academic year (e.g. 2025/2026) to manage terms and scores.</p>
  <button class="btn btn-primary btn-lg" onclick="openModal('modal-year')">
    Build First Academic Year
  </button>
</div>

<?php else: ?>
<!-- ── Year List ────────────────────────────────────────────── -->
<div class="flex flex-col" style="gap:2rem;">
  <?php foreach ($years as $year): ?>
  <?php $yearTerms = $termsByYear[$year['id']] ?? []; ?>

  <div class="card" style="padding:0; overflow:hidden;" data-year-id="<?= $year['id'] ?>">
    <!-- Year Header -->
    <div class="card-header flex justify-between items-center" style="padding:1.5rem 2rem; background:<?= $year['is_active'] ? 'var(--clr-primary-50)' : 'var(--clr-surface)' ?>; margin:0; border-bottom:1px solid var(--clr-border);">
      <div class="flex items-center" style="gap:1.25rem;">
        <div style="background:var(--clr-primary-600); color:#fff; width:52px; height:52px; display:flex; align-items:center; justify-content:center; border-radius:var(--radius-md); box-shadow:var(--shadow-purple);">
           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
          <h3 class="m-0" style="font-size:1.25rem; font-weight:800; letter-spacing:-0.02em;"><?= htmlspecialchars($year['year_name']) ?></h3>
          <div class="flex items-center gap-3">
             <span class="text-muted" style="font-size:var(--text-xs); font-weight:600; text-transform:uppercase; letter-spacing:0.05em;"><?= count($yearTerms) ?> Term<?= count($yearTerms) != 1 ? 's' : '' ?></span>
             <?php if ($year['is_active']): ?>
               <span class="badge badge-purple" style="padding:0.25rem 0.6rem; font-size:10px;">ACTIVE SESSION</span>
             <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="flex" style="gap:.75rem;">
        <?php if (!$year['is_active']): ?>
        <form method="POST" action="<?= $base ?>/admin/years" onsubmit="Loader.show()">
          <?= CSRF::field() ?>
          <input type="hidden" name="_action" value="year_activate">
          <input type="hidden" name="year_id" value="<?= $year['id'] ?>">
          <button type="submit" class="btn btn-outline btn-sm" style="background:#fff;">Set Active</button>
        </form>
        <?php endif; ?>
        <button class="btn btn-outline btn-sm" style="background:#fff;" onclick="editYear(<?= $year['id'] ?>, '<?= htmlspecialchars($year['year_name'], ENT_QUOTES) ?>')">Edit</button>
        <button class="btn btn-ghost btn-sm text-danger" onclick="confirmDeleteYear(<?= $year['id'] ?>, '<?= htmlspecialchars($year['year_name'], ENT_QUOTES) ?>')">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </button>
        <button class="btn btn-primary btn-sm" onclick="openTermModal(<?= $year['id'] ?>, '<?= htmlspecialchars($year['year_name'], ENT_QUOTES) ?>')">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="14" height="14" class="mr-1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Add Term
        </button>
      </div>
    </div>

    <!-- Terms table -->
    <div class="card-body" style="padding:0;">
      <?php if (empty($yearTerms)): ?>
      <div class="flex flex-col items-center justify-center" style="padding:3rem 1rem; color:var(--clr-text-muted);">
        <p class="m-0" style="font-size:var(--text-sm);">No terms added to this year yet.</p>
      </div>
      <?php else: ?>
      <div class="table-wrapper">
        <table class="table" style="margin-bottom:0;">
          <thead>
            <tr style="background:var(--clr-surface-2);">
              <th style="padding-left:2rem; width:120px;">Term</th>
              <th>Term Name</th>
              <th>Period</th>
              <th class="text-center">Days</th>
              <th>Next Term</th>
              <th style="width:100px;">Status</th>
              <th style="width:140px;" class="text-right pr-8">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($yearTerms as $term): ?>
            <tr class="<?= $term['is_active'] ? 'row-active' : '' ?>">
              <td style="padding-left:2rem;">
                <span style="font-weight:800; color:var(--clr-primary-700);">NO. 0<?= $term['term_number'] ?></span>
              </td>
              <td><span style="font-weight:600;"><?= htmlspecialchars($term['name']) ?></span></td>
              <td class="text-muted" style="font-size:var(--text-sm);">
                <?= fmtDate($term['start_date']) ?> — <?= fmtDate($term['end_date']) ?>
              </td>
              <td class="text-center"><span class="badge" style="background:var(--clr-surface-2); color:var(--clr-text); font-weight:700;"><?= $term['total_school_days'] ?></span></td>
              <td class="text-muted" style="font-size:var(--text-sm);"><?= fmtDate($term['next_term_begins']) ?></td>
              <td>
                <?php if ($term['is_active']): ?>
                  <span class="badge badge-purple">LIVE</span>
                <?php else: ?>
                  <span class="text-muted" style="font-size:10px; font-weight:700; text-transform:uppercase;">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="text-right pr-8">
                <div class="flex justify-end gap-2">
                  <?php if (!$term['is_active']): ?>
                  <form method="POST" action="<?= $base ?>/admin/years" onsubmit="Loader.show()">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="_action" value="term_activate">
                    <input type="hidden" name="term_id" value="<?= $term['id'] ?>">
                    <button type="submit" class="btn btn-ghost btn-xs text-primary" data-tooltip="Set as Live Term">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </button>
                  </form>
                  <?php endif; ?>
                  <button class="btn btn-ghost btn-xs" onclick="editTerm(<?= htmlspecialchars(json_encode($term), ENT_QUOTES) ?>)" data-tooltip="Edit Term">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  </button>
                  <button class="btn btn-ghost btn-xs text-danger" onclick="confirmDeleteTerm(<?= $term['id'] ?>, '<?= htmlspecialchars($term['name'], ENT_QUOTES) ?>')" data-tooltip="Delete Term">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<!-- ══════════════════════════════════════════════════════════
     MODALS
  ══════════════════════════════════════════════════════════ -->

<!-- Academic Year Modal -->
<div id="modal-year" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-year-title" style="display:none;">
  <div class="modal w-full max-w-md mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-year-title">Academic Year</h3>
      <button class="modal-close" onclick="closeModal('modal-year')" aria-label="Close dialog">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/years" id="form-year" onsubmit="Loader.show()">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="year_store">
      <input type="hidden" name="year_id" id="year-id-field" value="">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label" for="year-name-input">Academic Year Name <span class="required">*</span></label>
          <input type="text" id="year-name-input" name="year_name" class="form-control"
            placeholder="e.g. 2025/2026" required maxlength="20"
            pattern="^\d{4}/\d{4}$" autocomplete="off">
          <p class="form-text">Format: YYYY/YYYY (e.g. 2025/2026)</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-year')">Cancel</button>
        <button type="submit" class="btn btn-primary" id="year-submit-btn">Save Academic Year</button>
      </div>
    </form>
  </div>
</div>

<!-- Term Modal -->
<div id="modal-term" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-term-title" style="display:none;">
  <div class="modal w-full max-w-lg mx-4">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-term-title">Add Term</h3>
      <button class="modal-close" onclick="closeModal('modal-term')" aria-label="Close dialog">&times;</button>
    </div>
    <form method="POST" action="<?= $base ?>/admin/years" id="form-term" onsubmit="Loader.show()">
      <?= CSRF::field() ?>
      <input type="hidden" name="_action" value="term_store">
      <input type="hidden" name="term_id" id="term-id-field" value="">
      <input type="hidden" name="academic_year_id" id="term-year-id" value="">
      <div class="modal-body">
        <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
          <div class="form-group">
            <label class="form-label">Term Number <span class="required">*</span></label>
            <select name="term_number" id="term-number" class="form-control" required>
              <option value="1">Term 1</option>
              <option value="2">Term 2</option>
              <option value="3">Term 3</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Total School Days <span class="required">*</span></label>
            <input type="number" id="term-days" name="total_school_days" class="form-control" value="65" min="1" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Term Display Name <span class="required">*</span></label>
          <input type="text" id="term-name" name="name" class="form-control" placeholder="e.g. First Term" required>
        </div>

        <div class="grid" style="grid-template-columns:1fr 1fr; gap:1.5rem;">
          <div class="form-group">
            <label class="form-label">Start Date</label>
            <input type="date" id="term-start" name="start_date" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">End Date</label>
            <input type="date" id="term-end" name="end_date" class="form-control">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Next Term Resumes</label>
          <input type="date" id="term-next" name="next_term_begins" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal('modal-term')">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Term Details</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Form -->
<form method="POST" action="<?= $base ?>/admin/years" id="form-delete" style="display:none">
  <?= CSRF::field() ?>
  <input type="hidden" name="_action" id="delete-action">
  <input type="hidden" name="year_id" id="delete-year-id">
  <input type="hidden" name="term_id" id="delete-term-id">
</form>



<script>
function editYear(id, name) {
  document.getElementById('year-id-field').value = id;
  document.getElementById('year-name-input').value = name;
  document.getElementById('modal-year-title').textContent = 'Edit Academic Year';
  document.getElementById('year-submit-btn').textContent = 'Update Year';
  openModal('modal-year');
}

function openTermModal(yearId, yearName) {
  document.getElementById('form-term').reset();
  document.getElementById('term-id-field').value = '';
  document.getElementById('term-year-id').value = yearId;
  document.getElementById('modal-term-title').textContent = 'New Term — ' + yearName;
  openModal('modal-term');
}

function editTerm(t) {
  const f = document.getElementById('form-term');
  f.reset();
  document.getElementById('term-id-field').value = t.id;
  document.getElementById('term-year-id').value = t.academic_year_id;
  document.getElementById('term-number').value = t.term_number;
  document.getElementById('term-name').value = t.name;
  document.getElementById('term-days').value = t.total_school_days;
  document.getElementById('term-start').value = t.start_date || '';
  document.getElementById('term-end').value = t.end_date || '';
  document.getElementById('term-next').value = t.next_term_begins || '';
  document.getElementById('modal-term-title').textContent = 'Edit ' + t.name;
  openModal('modal-term');
}

function confirmDeleteYear(id, name) {
  confirmAction(`Are you sure you want to delete the academic year "${name}"?\n\nThis will permanently remove all associated terms and scores.`, () => {
    document.getElementById('delete-action').value = 'year_delete';
    document.getElementById('delete-year-id').value = id;
    document.getElementById('form-delete').submit();
  });
}

function confirmDeleteTerm(id, name) {
  confirmAction(`Delete "${name}"?\n\nThis will remove all scores recorded for this term.`, () => {
    document.getElementById('delete-action').value = 'term_delete';
    document.getElementById('delete-term-id').value = id;
    document.getElementById('form-delete').submit();
  });
}

// Auto-format year
document.getElementById('year-name-input')?.addEventListener('blur', function() {
  if (/^\d{4}$/.test(this.value)) this.value = this.value + '/' + (parseInt(this.value)+1);
});
</script>
