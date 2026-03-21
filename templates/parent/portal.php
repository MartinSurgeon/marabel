<?php
/**
 * Parent Portal View
 * HCI/UX: Child selector → Term tabs → Published scores with proficiency indicators
 * Follows cognitive load reduction principles: progressive disclosure, clear hierarchy
 */
$pageTitle = 'Parent Portal';
include __DIR__ . '/../layout/header.php';

global $parentActiveTerm, $parentChildren, $parentChildData;
$base       = defined('APP_BASE') ? APP_BASE : '';
$children   = $parentChildren   ?? [];
$childData  = $parentChildData  ?? [];
$activeTerm = $parentActiveTerm ?? null;

// Proficiency level labels & colors
$proficiencyMap = [
    1 => ['label' => 'Beginning',     'color' => '#ef4444', 'bg' => '#fef2f2'],
    2 => ['label' => 'Approaching',   'color' => '#f97316', 'bg' => '#fff7ed'],
    3 => ['label' => 'Developing',    'color' => '#eab308', 'bg' => '#fefce8'],
    4 => ['label' => 'Proficient',    'color' => '#22c55e', 'bg' => '#f0fdf4'],
    5 => ['label' => 'Advanced',      'color' => '#6366f1', 'bg' => '#eef2ff'],
];

function getGradeClass(float $score): string {
    if ($score >= 80) return '#22c55e';
    if ($score >= 65) return '#3b82f6';
    if ($score >= 50) return '#f97316';
    return '#ef4444';
}
?>

<?php if (empty($children)): ?>
<!-- ── No Children Linked ─────────────────────────────────────── -->
<div class="card flex flex-col items-center justify-center" style="padding:6rem 2rem; text-align:center; border-style:dashed;">
  <div style="background:var(--clr-primary-50); padding:2rem; border-radius:var(--radius-full); margin-bottom:2rem;">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="64" height="64" style="color:var(--clr-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
  </div>
  <h2 style="font-weight:800; color:var(--clr-text); margin-bottom:0.5rem;">No children linked to your account</h2>
  <p class="text-muted" style="max-width:380px; margin:0 auto;">Please contact the school administration to link your ward's record to your phone number.</p>
</div>
<?php else: ?>

<!-- ── Page Header ─────────────────────────────────────────────── -->
<div class="mb-6">
  <h1 class="m-0" style="font-size:var(--text-2xl); font-weight:800; letter-spacing:-0.03em; color:var(--clr-text);">Parent Portal</h1>
  <p class="text-muted m-0" style="font-size:var(--text-sm); margin-top:0.25rem;">
    <?php if ($activeTerm): ?>
      <span style="display:inline-flex; align-items:center; gap:6px; font-weight:600;">
        <span style="width:8px; height:8px; background:#22c55e; border-radius:50%; display:inline-block;"></span>
        <?= htmlspecialchars($activeTerm['year_name'] . ' · ' . $activeTerm['term_name']) ?> — Active Term
      </span>
    <?php else: ?>
      No active term set — contact the school administration.
    <?php endif; ?>
  </p>
</div>

<!-- ── Child Selector ─────────────────────────────────────────── -->
<?php if (count($children) > 1): ?>
<div class="flex gap-3 mb-6 flex-wrap" id="child-tabs">
  <?php foreach ($children as $i => $child): ?>
  <button
    class="child-tab-btn <?= $i === 0 ? 'active' : '' ?>"
    onclick="switchChild('child-<?= $child['id'] ?>', this)"
    style="display:flex; align-items:center; gap:10px; padding:10px 20px; border-radius:var(--radius-full); border:2px solid <?= $i === 0 ? 'var(--clr-primary)' : 'var(--clr-border)' ?>; background:<?= $i === 0 ? 'var(--clr-primary-50)' : 'var(--clr-surface)' ?>; cursor:pointer; font-weight:700; font-size:var(--text-sm); color:<?= $i === 0 ? 'var(--clr-primary)' : 'var(--clr-text-muted)' ?>; transition:all 0.2s;">
    <div style="width:36px; height:36px; border-radius:50%; background:var(--clr-primary); color:white; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:14px; flex-shrink:0;">
      <?= strtoupper(substr($child['full_name'], 0, 1)) ?>
    </div>
    <div style="text-align:left;">
      <div><?= htmlspecialchars($child['full_name']) ?></div>
      <div style="font-size:10px; font-weight:600; color:var(--clr-text-muted); text-transform:uppercase;"><?= htmlspecialchars($child['class_name'] . ' ' . $child['section']) ?></div>
    </div>
  </button>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Child Panels ────────────────────────────────────────────── -->
<?php foreach ($children as $i => $child):
  $sid  = $child['id'];
  $data = $childData[$sid] ?? ['terms' => [], 'termScores' => []];
  $terms = $data['terms'];
  $termScores = $data['termScores'];
  // Default to latest published term
  $defaultTermId = null;
  foreach (array_reverse($terms) as $t) {
      if (!empty($termScores[$t['id']]['published'])) {
          $defaultTermId = $t['id'];
          break;
      }
  }
  if (!$defaultTermId && !empty($terms)) {
      $defaultTermId = end($terms)['id'];
  }
?>
<div id="child-<?= $sid ?>" class="child-panel" style="<?= $i > 0 ? 'display:none;' : '' ?>">

  <!-- Student Summary Card -->
  <div class="card mb-6" style="padding:1.5rem; background:linear-gradient(135deg, var(--clr-primary), #7c3aed);">
    <div class="flex items-center gap-4 flex-wrap">
      <div style="width:72px; height:72px; border-radius:var(--radius-full); background:rgba(255,255,255,0.2); border:3px solid rgba(255,255,255,0.4); display:flex; align-items:center; justify-content:center; font-size:2rem; font-weight:800; color:white; flex-shrink:0; overflow:hidden;">
        <?php if ($child['photo_path']): ?>
          <img src="<?= $base . '/' . htmlspecialchars($child['photo_path']) ?>" alt="<?= htmlspecialchars($child['full_name']) ?>" style="width:100%; height:100%; object-fit:cover;">
        <?php else: ?>
          <?= strtoupper(substr($child['full_name'], 0, 1)) ?>
        <?php endif; ?>
      </div>
      <div style="flex:1; min-width:200px;">
        <div style="font-size:1.5rem; font-weight:800; color:white; line-height:1.2;"><?= htmlspecialchars($child['full_name']) ?></div>
        <div style="font-size:var(--text-sm); color:rgba(255,255,255,0.8); font-weight:600; margin-top:2px;">
          <?= htmlspecialchars($child['class_name'] . ' ' . $child['section']) ?> &nbsp;·&nbsp; <?= htmlspecialchars($child['level_name']) ?>
        </div>
        <div style="font-size:11px; color:rgba(255,255,255,0.6); margin-top:4px; font-family:var(--font-mono);">
          ID: <?= htmlspecialchars($child['student_id_number']) ?>
        </div>
      </div>
      <?php if ($child['relationship']): ?>
      <div style="background:rgba(255,255,255,0.15); border-radius:var(--radius-full); padding:6px 14px; font-size:11px; font-weight:700; color:white; text-transform:uppercase; letter-spacing:0.05em; white-space:nowrap;">
        <?= htmlspecialchars($child['relationship']) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Term Tabs -->
  <?php if (!empty($terms)): ?>
  <div style="border-bottom:2px solid var(--clr-border); margin-bottom:1.5rem; overflow-x:auto; white-space:nowrap;">
    <?php foreach ($terms as $term):
      $ts = $termScores[$term['id']] ?? [];
      $isActive = $term['id'] === $defaultTermId;
      $isPublished = !empty($ts['published']);
    ?>
    <button
      class="term-tab-btn" id="termtab-<?= $sid ?>-<?= $term['id'] ?>"
      onclick="switchTerm('<?= $sid ?>', '<?= $term['id'] ?>', this)"
      style="display:inline-flex; align-items:center; gap:6px; padding:10px 20px; border:none; border-bottom:3px solid <?= $isActive ? 'var(--clr-primary)' : 'transparent' ?>; background:transparent; cursor:pointer; font-size:var(--text-sm); font-weight:<?= $isActive ? '800' : '600' ?>; color:<?= $isActive ? 'var(--clr-primary)' : 'var(--clr-text-muted)' ?>; transition:all 0.2s; white-space:nowrap; margin-bottom:-2px;">
      <?= htmlspecialchars($term['name']) ?>
      <?php if ($isPublished): ?>
        <span style="width:7px; height:7px; background:#22c55e; border-radius:50%; display:inline-block;" title="Results published"></span>
      <?php else: ?>
        <span style="width:7px; height:7px; background:var(--clr-text-muted); border-radius:50%; display:inline-block; opacity:0.4;" title="Awaiting publication"></span>
      <?php endif; ?>
    </button>
    <?php endforeach; ?>
  </div>

  <!-- Term Score Panels -->
  <?php foreach ($terms as $term):
    $ts = $termScores[$term['id']] ?? [];
    $isDefault = $term['id'] === $defaultTermId;
  ?>
  <div id="termpanel-<?= $sid ?>-<?= $term['id'] ?>" class="term-panel" style="<?= !$isDefault ? 'display:none;' : '' ?>">

    <?php if (empty($ts['published'])): ?>
    <!-- Not Published -->
    <div class="card" style="padding:4rem 2rem; text-align:center; border-style:dashed; background:var(--clr-surface-2);">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="48" height="48" style="color:var(--clr-text-muted); margin:0 auto 1rem; display:block;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <h3 style="font-weight:700; color:var(--clr-text-muted);">Results Not Yet Published</h3>
      <p class="text-muted" style="font-size:var(--text-sm);">Results for <?= htmlspecialchars($term['name']) ?> have not been released yet.<br>You will be notified by SMS when they are available.</p>
    </div>

    <?php else: ?>
    <!-- Published Results -->

    <!-- Quick Stats Row -->
    <?php $agg = $ts['aggregate'] ?? []; $att = $ts['attendance'] ?? null; ?>
    <div class="grid mb-5" style="grid-template-columns:repeat(auto-fill, minmax(140px, 1fr)); gap:1rem;">
      <div class="card" style="padding:1.25rem; text-align:center; background:linear-gradient(135deg, #f0fdf4, #dcfce7); border:1px solid #bbf7d0;">
        <div style="font-size:1.75rem; font-weight:900; color:#16a34a;"><?= number_format($agg['aggregate_score'] ?? 0, 1) ?></div>
        <div style="font-size:10px; font-weight:700; color:#166534; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Aggregate</div>
      </div>
      <div class="card" style="padding:1.25rem; text-align:center; background:linear-gradient(135deg, #eff6ff, #dbeafe); border:1px solid #bfdbfe;">
        <div style="font-size:1.75rem; font-weight:900; color:#1d4ed8;"><?= $ts['position'] ? $ts['position'] . getOrdinal($ts['position']) : '—' ?></div>
        <div style="font-size:10px; font-weight:700; color:#1e40af; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Class Position</div>
      </div>
      <div class="card" style="padding:1.25rem; text-align:center; background:linear-gradient(135deg, #fdf4ff, #fae8ff); border:1px solid #e9d5ff;">
        <div style="font-size:1.75rem; font-weight:900; color:#7e22ce;"><?= count($ts['scores']) ?></div>
        <div style="font-size:10px; font-weight:700; color:#6b21a8; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Subjects</div>
      </div>
      <?php if ($att): ?>
      <div class="card" style="padding:1.25rem; text-align:center; background:linear-gradient(135deg, #fff7ed, #fed7aa); border:1px solid #fdba74;">
        <?php $attPct = $att['total_school_days'] > 0 ? round(($att['days_present'] / $att['total_school_days']) * 100) : 0; ?>
        <div style="font-size:1.75rem; font-weight:900; color:#c2410c;"><?= $attPct ?>%</div>
        <div style="font-size:10px; font-weight:700; color:#9a3412; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;"><?= $att['days_present'] ?>/<?= $att['total_school_days'] ?> Days</div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Subject Score Cards -->
    <?php if (!empty($ts['scores'])): ?>
    <div class="card mb-5" style="padding:0; overflow:hidden; border:1px solid var(--clr-border);">
      <div style="padding:1rem 1.5rem; border-bottom:1px solid var(--clr-border); background:var(--clr-surface-2); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:0.5rem;">
        <h3 class="m-0" style="font-size:var(--text-sm); font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--clr-text);">Subject Performance</h3>
        <a href="<?= $base ?>/report?student=<?= $sid ?>&term=<?= $term['id'] ?>" target="_blank" class="btn btn-primary btn-xs shadow-purple" style="gap:6px;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="14" height="14"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          View Report Card
        </a>
      </div>
      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; min-width:500px;">
          <thead>
            <tr style="background:var(--clr-surface-2); border-bottom:1px solid var(--clr-border);">
              <th style="padding:0.75rem 1.5rem; text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--clr-text-muted);">Subject</th>
              <th style="padding:0.75rem; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--clr-text-muted);">SBA (50%)</th>
              <th style="padding:0.75rem; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--clr-text-muted);">Exam (50%)</th>
              <th style="padding:0.75rem; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--clr-text-muted);">Total (100)</th>
              <th style="padding:0.75rem 1.5rem; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:var(--clr-text-muted);">Level</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ts['scores'] as $score):
              $total   = floatval($score['overall_total'] ?? 0);
              $clr     = getGradeClass($total);
              $prof    = $proficiencyMap[$score['proficiency_level']] ?? null;
            ?>
            <tr style="border-bottom:1px solid var(--clr-border); transition:background 0.15s;" onmouseover="this.style.background='var(--clr-surface-2)'" onmouseout="this.style.background=''">
              <td style="padding:0.875rem 1.5rem;">
                <div style="font-weight:700; color:var(--clr-text); font-size:var(--text-sm);"><?= htmlspecialchars($score['subject_name']) ?></div>
                <?php if ($score['subject_code']): ?>
                  <div style="font-size:10px; color:var(--clr-text-muted); font-family:var(--font-mono);"><?= htmlspecialchars($score['subject_code']) ?></div>
                <?php endif; ?>
              </td>
              <td style="padding:0.875rem; text-align:center; font-weight:700; color:var(--clr-text);"><?= number_format($score['class_score'] ?? 0, 1) ?></td>
              <td style="padding:0.875rem; text-align:center; font-weight:700; color:var(--clr-text);"><?= number_format($score['exam_score'] ?? 0, 1) ?></td>
              <td style="padding:0.875rem; text-align:center;">
                <span style="font-size:1.1rem; font-weight:900; color:<?= $clr ?>;"><?= number_format($total, 1) ?></span>
                <!-- Score bar -->
                <div style="margin-top:4px; background:var(--clr-border); border-radius:8px; height:4px; width:60px; margin:4px auto 0;">
                  <div style="background:<?= $clr ?>; height:4px; border-radius:8px; width:<?= min(100, $total) ?>%;"></div>
                </div>
              </td>
              <td style="padding:0.875rem 1.5rem; text-align:center;">
                <?php if ($prof): ?>
                  <span style="display:inline-block; padding:3px 10px; border-radius:var(--radius-full); background:<?= $prof['bg'] ?>; color:<?= $prof['color'] ?>; font-size:10px; font-weight:800; white-space:nowrap;">
                    <?= $prof['label'] ?>
                  </span>
                <?php else: ?>
                  <span style="color:var(--clr-text-muted);">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Remarks Panel -->
    <?php $rmk = $ts['remarks'] ?? null; if ($rmk): ?>
    <div class="card mb-5" style="padding:1.5rem; border:1px solid var(--clr-border);">
      <h3 style="font-size:var(--text-sm); font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:var(--clr-text); margin-bottom:1rem;">Teacher Remarks</h3>
      <div class="grid" style="grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:1rem; margin-bottom:1rem;">
        <?php if ($rmk['conduct_character']): ?>
        <div style="background:var(--clr-surface-2); padding:1rem; border-radius:var(--radius-md);">
          <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Conduct & Character</div>
          <?php echo renderStars($rmk['conduct_character']); ?>
        </div>
        <?php endif; ?>
        <?php if ($rmk['attitude']): ?>
        <div style="background:var(--clr-surface-2); padding:1rem; border-radius:var(--radius-md);">
          <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Attitude</div>
          <?php echo renderStars($rmk['attitude']); ?>
        </div>
        <?php endif; ?>
      </div>
      <?php if ($rmk['teacher_remark']): ?>
      <div style="background:var(--clr-surface-2); padding:1rem; border-radius:var(--radius-md); margin-bottom:0.75rem;">
        <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Class Teacher</div>
        <p style="margin:0; color:var(--clr-text); font-size:var(--text-sm); line-height:1.6; font-style:italic;">"<?= htmlspecialchars($rmk['teacher_remark']) ?>"</p>
      </div>
      <?php endif; ?>
      <?php if ($rmk['headmaster_remark']): ?>
      <div style="background:var(--clr-surface-2); padding:1rem; border-radius:var(--radius-md);">
        <div style="font-size:10px; font-weight:700; color:var(--clr-text-muted); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Headmaster</div>
        <p style="margin:0; color:var(--clr-text); font-size:var(--text-sm); line-height:1.6; font-style:italic;">"<?= htmlspecialchars($rmk['headmaster_remark']) ?>"</p>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; // end published ?>
  </div><!-- end termpanel -->
  <?php endforeach; ?>
  <?php endif; // end !empty($terms) ?>

</div><!-- end child-panel -->
<?php endforeach; ?>

<?php endif; // end !empty($children) ?>

<?php include __DIR__ . '/../layout/footer.php'; ?>

<style>
.child-tab-btn:hover { filter: brightness(0.97); }
.term-tab-btn:hover { color: var(--clr-primary) !important; border-bottom-color: var(--clr-primary-200) !important; }
</style>

<script>
function switchChild(panelId, btn) {
  document.querySelectorAll('.child-panel').forEach(p => p.style.display = 'none');
  document.getElementById(panelId).style.display = 'block';

  document.querySelectorAll('.child-tab-btn').forEach(b => {
    b.style.borderColor = 'var(--clr-border)';
    b.style.background  = 'var(--clr-surface)';
    b.style.color       = 'var(--clr-text-muted)';
  });
  btn.style.borderColor = 'var(--clr-primary)';
  btn.style.background  = 'var(--clr-primary-50)';
  btn.style.color       = 'var(--clr-primary)';
}

function switchTerm(sid, termId, btn) {
  // Hide all term panels for this child
  document.querySelectorAll(`[id^="termpanel-${sid}-"]`).forEach(p => p.style.display = 'none');
  document.getElementById(`termpanel-${sid}-${termId}`).style.display = 'block';

  // Reset all term tabs for this child
  document.querySelectorAll(`[id^="termtab-${sid}-"]`).forEach(b => {
    b.style.borderBottomColor = 'transparent';
    b.style.color             = 'var(--clr-text-muted)';
    b.style.fontWeight        = '600';
  });
  btn.style.borderBottomColor = 'var(--clr-primary)';
  btn.style.color             = 'var(--clr-primary)';
  btn.style.fontWeight        = '800';
}
</script>

<?php
function getOrdinal(int $n): string {
    if ($n % 100 >= 11 && $n % 100 <= 13) return 'th';
    return match ($n % 10) { 1 => 'st', 2 => 'nd', 3 => 'rd', default => 'th' };
}
function renderStars(int $rating): string {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $stars .= $i <= $rating
            ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16" style="color:#f59e0b; display:inline-block;"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>'
            : '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="16" height="16" style="color:#d1d5db; display:inline-block;"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.562.562 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>';
    }
    return $stars;
}
?>
