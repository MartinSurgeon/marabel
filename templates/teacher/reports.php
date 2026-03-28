<?php
/**
 * Tabbed Report Selection Template
 * Redesigned for multiple classes using a tabbed interface.
 */
$pageTitle = 'Report Card Selection';
include __DIR__ . '/../layout/header.php';

global $reportGroups, $activeTerm;
$base = defined('APP_BASE') ? APP_BASE : '';

if (!$activeTerm) {
    echo '<div class="alert alert-warning">No active academic term found. Please contact the administrator.</div>';
    include __DIR__ . '/../layout/footer.php';
    exit;
}

$classes = array_keys($reportGroups);
$firstClass = !empty($classes) ? $classes[0] : null;
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 animate-fade-in">
  <div>
    <h1 class="text-3xl font-black text-gray-900 tracking-tight leading-none mb-2">Student Reports</h1>
    <p class="text-gray-500 font-medium">Grouped by class for easier navigation. Active Term: <strong><?= htmlspecialchars($activeTerm['year_name'] . ' · ' . $activeTerm['name']) ?></strong>.</p>
  </div>
</div>

<?php if (empty($reportGroups)): ?>
    <div class="card p-12 text-center shadow-xl border border-gray-100">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" width="40" height="40" class="text-gray-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-2">No Students Found</h3>
        <p class="text-gray-500 max-w-sm mx-auto">It seems you don't have any students assigned to your classes for this term yet.</p>
    </div>
<?php else: ?>
    <!-- ── Tab Navigation ────────────────────────────────────── -->
    <div class="mb-6 flex gap-2 overflow-x-auto pb-2 scrollbar-hide no-scrollbar" style="-ms-overflow-style: none; scrollbar-width: none;">
        <?php foreach ($classes as $index => $className): ?>
            <button 
                onclick="switchTab('tab-<?= md5($className) ?>')" 
                class="tab-btn whitespace-nowrap px-6 py-3 rounded-xl font-black text-sm transition-all flex items-center gap-2 shadow-sm
                       <?= $className === $firstClass ? 'active bg-purple-600 text-white' : 'bg-white text-gray-500 hover:bg-purple-50 hover:text-purple-600 border border-gray-100' ?>"
                id="btn-tab-<?= md5($className) ?>">
                <?= htmlspecialchars($className) ?>
                <span class="px-2 py-0.5 rounded-full text-[10px] <?= $className === $firstClass ? 'bg-purple-500 text-white' : 'bg-gray-100 text-gray-400' ?>">
                    <?= count($reportGroups[$className]) ?>
                </span>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- ── Student Tables ────────────────────────────────────── -->
    <div id="tab-container" class="animate-scale-in">
        <?php foreach ($reportGroups as $className => $students): ?>
            <div id="tab-<?= md5($className) ?>" class="tab-content <?= $className === $firstClass ? '' : 'hidden' ?>">
                <div class="card p-0 overflow-hidden shadow-xl border border-gray-100">
                    <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex justify-between items-center">
                        <span class="text-xs font-black text-gray-400 uppercase tracking-widest"><?= htmlspecialchars($className) ?> Student List</span>
                        <span class="text-xs font-bold text-purple-600 bg-purple-50 px-2 py-1 rounded-md"><?= count($students) ?> Students</span>
                    </div>
                    <div style="overflow-x:auto;">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50/30">
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">#</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">Name</th>
                                    <th class="px-6 py-4 text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
                                    <th class="px-6 py-4 text-right text-[10px] font-black text-gray-400 uppercase tracking-widest">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($students as $idx => $s): ?>
                                    <tr class="hover:bg-purple-50/20 transition-colors group">
                                        <td class="px-6 py-4 text-sm font-bold text-gray-300"><?= $idx + 1 ?></td>
                                        <td class="px-6 py-4 font-bold text-gray-800 group-hover:text-purple-700 transition-colors"><?= htmlspecialchars($s['full_name'] . ' ' . $s['surname']) ?></td>
                                        <td class="px-6 py-4"><span class="text-xs text-gray-500 font-mono"><?= htmlspecialchars($s['student_id_number']) ?></span></td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="<?= $base ?>/report?student=<?= $s['id'] ?>&term=<?= $activeTerm['id'] ?>" 
                                               target="_blank" 
                                               class="inline-flex items-center gap-2 bg-white hover:bg-purple-600 text-purple-600 hover:text-white border-2 border-purple-600 py-2 px-5 rounded-xl font-black text-[11px] transition-all shadow-sm active:scale-95">
                                                Preview Report
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6L21 12m0 0l-7.5 7.5M21 12H3"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function switchTab(tabId) {
    // Hide all contents
    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
    
    // Deactivate all buttons
    document.querySelectorAll('.tab-btn').forEach(el => {
        el.classList.remove('active', 'bg-purple-600', 'text-white');
        el.classList.add('bg-white', 'text-gray-500', 'border-gray-100');
        const badge = el.querySelector('span');
        if(badge) {
            badge.classList.remove('bg-purple-500', 'text-white');
            badge.classList.add('bg-gray-100', 'text-gray-400');
        }
    });
    
    // Show active content
    const activeContent = document.getElementById(tabId);
    if(activeContent) {
        activeContent.classList.remove('hidden');
        activeContent.classList.add('animate-scale-in');
    }
    
    // Activate clicked button
    const activeBtn = document.getElementById('btn-' + tabId);
    if(activeBtn) {
        activeBtn.classList.add('active', 'bg-purple-600', 'text-white');
        activeBtn.classList.remove('bg-white', 'text-gray-500', 'border-gray-100');
        const badge = activeBtn.querySelector('span');
        if(badge) {
            badge.classList.add('bg-purple-500', 'text-white');
            badge.classList.remove('bg-gray-100', 'text-gray-400');
        }
    }
}
</script>

<style>
.no-scrollbar::-webkit-scrollbar { display: none; }
.animate-scale-in {
    animation: scaleIn 0.3s ease-out;
}
@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.98); }
    to { opacity: 1; transform: scale(1); }
}
</style>

<?php include __DIR__ . '/../layout/footer.php'; ?>
