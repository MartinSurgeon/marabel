<?php
$title = "Data Export | " . SCHOOL_NAME;
$current_page = "export";
require_once __DIR__ . '/../layout/header.php';
?>

<div class="px-4 pb-20 sm:px-6 lg:px-8 mt-6">
    <!-- Header -->
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Data Export</h1>
            <p class="mt-1 text-sm text-gray-500">Extract structured data from the system for backups and reporting.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Export Configuration Panel -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
                    <h2 class="font-semibold text-gray-800">Export Configuration</h2>
                </div>
                
                <form id="exportForm" action="<?= APP_BASE ?>/admin/export" method="POST" class="p-6 space-y-8">
                    <?= CSRF::field() ?>
                    <input type="hidden" name="_action" value="generate_export">
                    
                    <!-- 1. Data Type Selection -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3 border-b border-gray-100 pb-2">1. Select Data Type</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Student List -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none data-type-label border-primary ring-1 ring-primary transition-all">
                                <input type="radio" name="export_type" value="students" class="sr-only" checked onchange="toggleExportPanels('students')">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Student Master List</span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Rosters, demographics, parent contacts.</span>
                                    </span>
                                </span>
                                <svg class="h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                            </label>

                            <!-- Academic Results -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none data-type-label border-gray-300 hover:border-gray-400 opacity-60 transition-all">
                                <input type="radio" name="export_type" value="results" class="sr-only" onchange="toggleExportPanels('results')">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Academic Results</span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Scores, aggregates, proficiencies.</span>
                                    </span>
                                </span>
                            </label>

                            <!-- Staff Directory -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none data-type-label border-gray-300 hover:border-gray-400 opacity-60 transition-all">
                                <input type="radio" name="export_type" value="staff" class="sr-only" onchange="toggleExportPanels('staff')">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Staff/Teacher Directory</span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Contact lists, assigned roles.</span>
                                    </span>
                                </span>
                            </label>
                            
                            <!-- Attendance -->
                            <label class="relative flex cursor-pointer rounded-lg border bg-white p-4 shadow-sm focus:outline-none data-type-label border-gray-300 hover:border-gray-400 opacity-60 transition-all">
                                <input type="radio" name="export_type" value="attendance" class="sr-only" onchange="toggleExportPanels('attendance')">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Termly Attendance</span>
                                        <span class="mt-1 flex items-center text-xs text-gray-500">Days present, absenteeism rates.</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Filters -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3 border-b border-gray-100 pb-2">2. Filters</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <!-- Class Filter -->
                            <div class="filter-group" id="filterClass">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Classroom</label>
                                <select name="class_id" class="input w-full">
                                    <option value="all">All Classes</option>
                                    <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name'] . ($c['section'] ? ' ' . $c['section'] : '')) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Term Filter (Hidden for Students/Staff) -->
                            <div class="filter-group hidden" id="filterTerm">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Academic Term</label>
                                <select name="term_id" class="input w-full">
                                    <?php 
                                    $activeFound = false;
                                    foreach ($terms as $t): 
                                        $selected = $t['is_active'] ? 'selected' : '';
                                        if ($t['is_active']) $activeFound = true;
                                    ?>
                                    <option value="<?= $t['id'] ?>" <?= $selected ?>><?= htmlspecialchars($t['year_name'] . ' - ' . $t['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Student Status Filter -->
                            <div class="filter-group" id="filterStatus">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                                <select name="status" class="input w-full">
                                    <option value="active" selected>Active Only</option>
                                    <option value="all">All (Including Transferred)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Column Selection (Checkboxes) -->
                    <div>
                        <div class="flex items-center justify-between mb-3 border-b border-gray-100 pb-2">
                            <h3 class="text-sm font-semibold text-gray-900">3. Select Columns to Export</h3>
                            <div class="flex gap-4">
                                <button type="button" onclick="toggleAllCheckboxes(true)" class="text-[10px] font-semibold text-primary hover:text-primary-dark transition-colors uppercase tracking-wider">Select All</button>
                                <button type="button" onclick="toggleAllCheckboxes(false)" class="text-[10px] font-semibold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-wider">Uncheck All</button>
                            </div>
                        </div>
                        
                        <!-- Columns: Students -->
                        <div id="colsStudents" class="export-cols grid grid-cols-2 md:grid-cols-3 gap-3">
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="student_id_number" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Student ID</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="full_name" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Full Name</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="gender" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Gender</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="date_of_birth" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Date of Birth</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="class_name" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Current Class</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="status" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer">
                                <span>Enrollment Status</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="parent_name" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Parent/Guardian Name</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="parent_phone" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Parent Phone No.</span>
                            </label>
                        </div>
                        
                        <!-- Columns: Results (Hidden initially) -->
                        <div id="colsResults" class="export-cols hidden grid grid-cols-2 md:grid-cols-3 gap-3">
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="student_id_number" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked disabled>
                                <span class="opacity-70">Student ID (Required)</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="full_name" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked disabled>
                                <span class="opacity-70">Full Name (Required)</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="class_name" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Classroom</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="aggregate_score" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Total Aggregate Score</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="class_position" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Overall Position</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="subject_breakdown" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer">
                                <span>Include Individual Subject Scores</span>
                            </label>
                        </div>

                        <!-- Columns: Staff (Hidden initially) -->
                        <div id="colsStaff" class="export-cols hidden grid grid-cols-2 md:grid-cols-3 gap-3">
                             <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="full_name" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Full Name</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="email" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Email Address</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="phone" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Phone Number</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="role" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>System Role</span>
                            </label>
                             <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="assigned_classes" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Assigned Classes</span>
                            </label>
                        </div>

                        <!-- Columns: Attendance (Hidden initially) -->
                        <div id="colsAttendance" class="export-cols hidden grid grid-cols-2 md:grid-cols-3 gap-3">
                             <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="student_id_number" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked disabled>
                                <span class="opacity-70">Student ID (Required)</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="full_name" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked disabled>
                                <span class="opacity-70">Full Name (Required)</span>
                            </label>
                             <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="class_name" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Classroom</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="days_present" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Days Present</span>
                            </label>
                            <label class="flex items-center space-x-2 text-sm text-gray-700 cursor-pointer p-2 hover:bg-gray-50 rounded">
                                <input type="checkbox" name="columns[]" value="days_absent" class="rounded border-gray-300 text-primary w-4 h-4 cursor-pointer" checked>
                                <span>Days Absent</span>
                            </label>
                        </div>
                    </div>

                    <!-- 4. Format Selection -->
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3 border-b border-gray-100 pb-2">4. Select Export Format</h3>
                        <div class="max-w-xs">
                            <select name="export_format" class="input w-full">
                                <option value="excel">Formatted Excel (.xls) - Recommended</option>
                                <option value="csv">Standard CSV (.csv)</option>
                            </select>
                            <p class="mt-1.5 text-[10px] text-gray-500">Excel format automatically fits column widths for better readability.</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-gray-100 flex items-center justify-end">
                        <button type="submit" class="btn btn-primary shadow-sm group">
                            <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Generate Export File
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar Hints -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-gray-50 border border-gray-100 rounded-xl p-5 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="bg-blue-100 text-blue-600 p-2 rounded-lg shrink-0 mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-1">About Data Export</h4>
                        <p class="text-xs text-gray-600 leading-relaxed mb-3">
                            The requested export will be generated as a universally compatible CSV format.
                        </p>
                        <ul class="text-xs text-gray-600 space-y-1.5 list-disc pl-4">
                            <li>Checkboxes let you exclude columns you don't need.</li>
                            <li>CSV files can be opened in Excel, Google Sheets, or imported into other software.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleExportPanels(type) {
    // 1. Update Radio styling
    document.querySelectorAll('.data-type-label').forEach(label => {
        label.classList.remove('border-primary', 'ring-1', 'ring-primary');
        label.classList.add('border-gray-300', 'opacity-60');
        const icon = label.querySelector('svg');
        if (icon) icon.remove();
    });

    const activeInput = document.querySelector(`input[value="${type}"]`);
    const activeLabel = activeInput.closest('label');
    activeLabel.classList.remove('border-gray-300', 'opacity-60');
    activeLabel.classList.add('border-primary', 'ring-1', 'ring-primary');

    // Add check icon
    if (!activeLabel.querySelector('svg')) {
        activeLabel.insertAdjacentHTML('beforeend', '<svg class="h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>');
    }

    // 2. Toggle Column Checkbox Groups
    document.querySelectorAll('.export-cols').forEach(el => {
        el.classList.add('hidden');
        // Uncheck everything in hidden panels so they don't submit
        el.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(cb => cb.checked = false);
    });
    
    const activeGroup = document.getElementById('cols' + type.charAt(0).toUpperCase() + type.slice(1));
    if (activeGroup) {
        activeGroup.classList.remove('hidden');
        // Check everything in active panel
        activeGroup.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(cb => cb.checked = true);
    }

    // 3. Toggle Filters
    const classFilter = document.getElementById('filterClass');
    const termFilter = document.getElementById('filterTerm');
    const statusFilter = document.getElementById('filterStatus');

    if (type === 'students') {
        classFilter.classList.remove('hidden');
        termFilter.classList.add('hidden');
        statusFilter.classList.remove('hidden');
    } else if (type === 'staff') {
        classFilter.classList.add('hidden');
        termFilter.classList.add('hidden');
        statusFilter.classList.remove('hidden');
    } else {
        // Results & Attendance
        classFilter.classList.remove('hidden');
        termFilter.classList.remove('hidden');
        statusFilter.classList.remove('hidden');
    }
}

function toggleAllCheckboxes(checked) {
    // Only target the currently visible groups
    document.querySelectorAll('.export-cols:not(.hidden) input[type="checkbox"]:not(:disabled)').forEach(cb => {
        cb.checked = checked;
    });
}

// Initialize on load to ensure hidden checkboxes are unchecked
document.addEventListener('DOMContentLoaded', function() {
    toggleExportPanels('students');
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
