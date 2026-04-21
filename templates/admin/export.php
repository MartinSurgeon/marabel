<?php
/**
 * Premium Data Export View
 * Uaddara Basic School — SBA Management System
 * 
 * DESIGN: 4-Step Interactive Workflow, Glassmorphism, Icon-rich Demographic Cards.
 */
$pageTitle = "Data Export Tool";
require_once __DIR__ . '/../layout/header.php';

global $classes, $terms;
$base = defined('APP_BASE') ? APP_BASE : '';
?>

<!-- Premium Header Section -->
<div class="mb-10 animate-fade-in" style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); padding: 3rem 2.5rem; border-radius: var(--radius-2xl); color: white; position: relative; overflow: hidden; box-shadow: var(--shadow-xl);">
    <!-- Decorative Accents -->
    <div style="position: absolute; top: -20px; right: -20px; width: 140px; height: 140px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
    <div style="position: absolute; bottom: -40px; left: 10%; width: 100px; height: 100px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>

    <div style="position: relative; z-index: 1;">
        <div style="display:inline-flex; align-items: center; padding: 0.5rem 1rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--radius-full); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 1.5rem; backdrop-filter: blur(4px);">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="12" height="12" style="margin-right: 8px; opacity: 0.9;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            System Administration
        </div>
        <h1 style="font-size: clamp(2rem, 5vw, 2.5rem); font-weight: 900; margin: 0; line-height: 1; letter-spacing: -0.05em;">Data Export Centre</h1>
        <p style="margin-top: 1rem; font-size: 1rem; opacity: 0.8; max-width: 600px; font-weight: 500;">
            Extract comprehensive school data into structured Excel or CSV formats for external reporting and archival.
        </p>
    </div>
</div>

<form id="exportForm" action="<?= $base ?>/admin/export" method="POST" onsubmit="Loader.show()">
    <?= CSRF::field() ?>
    <input type="hidden" name="_action" value="generate_export">

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Column: Main Workflow -->
        <div class="lg:col-span-8 flex flex-col gap-8">
            
            <!-- STEP 1: Choose Source -->
            <div class="card p-8 shadow-md border-t-4" style="border-top-color: var(--clr-primary);">
                <div class="flex items-center gap-3 mb-8">
                    <div style="width:32px; height:32px; background:var(--clr-primary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:14px;">1</div>
                    <h2 style="font-size: 1.125rem; font-weight: 800; margin: 0;">Select Data Source</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Student Card -->
                    <label class="export-type-card active" onclick="selectExportType('students')">
                        <input type="radio" name="export_type" value="students" class="hidden" checked onchange="toggleExportPanels('students')">
                        <div class="icon-box" style="background:var(--clr-success-bg); color:var(--clr-success);">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div class="card-content">
                            <div class="title">Students Master List</div>
                            <div class="desc">Rosters, demographics, & parental contacts.</div>
                        </div>
                        <div class="check-mark">
                           <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                        </div>
                    </label>

                    <!-- Staff Card -->
                    <label class="export-type-card" onclick="selectExportType('staff')">
                        <input type="radio" name="export_type" value="staff" class="hidden" onchange="toggleExportPanels('staff')">
                        <div class="icon-box" style="background:var(--clr-info-bg); color:var(--clr-info);">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="card-content">
                            <div class="title">Staff Directory</div>
                            <div class="desc">Teacher profiles & professional assignments.</div>
                        </div>
                        <div class="check-mark">
                           <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                        </div>
                    </label>

                    <!-- Results Card -->
                    <label class="export-type-card" onclick="selectExportType('results')">
                        <input type="radio" name="export_type" value="results" class="hidden" onchange="toggleExportPanels('results')">
                        <div class="icon-box" style="background:#f5f3ff; color:#7c3aed;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div class="card-content">
                            <div class="title">Academic Results</div>
                            <div class="desc">Grading summaries, averages, & positions.</div>
                        </div>
                        <div class="check-mark">
                           <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                        </div>
                    </label>

                    <!-- Attendance Card -->
                    <label class="export-type-card" onclick="selectExportType('attendance')">
                        <input type="radio" name="export_type" value="attendance" class="hidden" onchange="toggleExportPanels('attendance')">
                        <div class="icon-box" style="background:var(--clr-warning-bg); color:var(--clr-warning);">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="card-content">
                            <div class="title">Attendance Data</div>
                            <div class="desc">Absence rates & termly attendance totals.</div>
                        </div>
                        <div class="check-mark">
                           <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                        </div>
                    </label>

                    <!-- SMS History Card -->
                    <label class="export-type-card" onclick="selectExportType('sms')">
                        <input type="radio" name="export_type" value="sms" class="hidden" onchange="toggleExportPanels('sms')">
                        <div class="icon-box" style="background:#fdf2f8; color:#db2777;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        </div>
                        <div class="card-content">
                            <div class="title">SMS History</div>
                            <div class="desc">All broadcast & notification logs.</div>
                        </div>
                        <div class="check-mark">
                           <svg fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                        </div>
                    </label>
                </div>
            </div>

            <!-- STEP 2: Configure Parameters -->
            <div class="card p-8 shadow-md">
                <div class="flex items-center gap-3 mb-8">
                    <div style="width:32px; height:32px; background:var(--clr-secondary); color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:14px;">2</div>
                    <h2 style="font-size: 1.125rem; font-weight: 800; margin: 0;">Configure Parameters</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div id="filterClass">
                        <label class="form-label block mb-2" style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--clr-text-muted);">Classroom</label>
                        <select name="class_id" class="form-control h-12">
                            <option value="all">All Classrooms</option>
                            <?php
                            $exportByLevel = [];
                            foreach ($classes as $c) {
                                $exportByLevel[$c['level_name']][] = $c;
                            }
                            foreach ($exportByLevel as $lvl => $lvlClasses):
                            ?>
                            <optgroup label="<?= htmlspecialchars($lvl) ?>">
                              <?php foreach ($lvlClasses as $c): ?>
                              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['class_name'] . ($c['section'] ? ' ' . $c['section'] : '')) ?></option>
                              <?php endforeach; ?>
                            </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="filterTerm" class="hidden">
                        <label class="form-label block mb-2" style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--clr-text-muted);">Academic Term</label>
                        <select name="term_id" class="form-control h-12">
                            <?php foreach ($terms as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $t['is_active'] ? 'selected' : '' ?>><?= htmlspecialchars($t['year_name'] . ' — ' . $t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="filterStatus">
                        <label class="form-label block mb-2" style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--clr-text-muted);">Record Status</label>
                        <select name="status" class="form-control h-12">
                            <option value="active" selected>Active Records Only</option>
                            <option value="all">Include Inactive/Transferred</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Column Selection -->
            <div class="card p-8 shadow-md relative overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-3">
                        <div style="width:32px; height:32px; background:#6366f1; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:14px;">3</div>
                        <h2 style="font-size: 1.125rem; font-weight: 800; margin: 0;">Selection Mapping</h2>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="toggleAllCheckboxes(true)" class="btn btn-ghost btn-xs font-bold text-primary">SELECT ALL</button>
                        <button type="button" onclick="toggleAllCheckboxes(false)" class="btn btn-ghost btn-xs font-bold text-muted">NONE</button>
                    </div>
                </div>

                <!-- Column Group: Students -->
                <div id="colsStudents" class="export-cols-grid animate-fade-in">
                    <?php 
                    $studentCols = [
                        'student_id_number' => 'Student ID', 
                        'full_name' => 'Full Name', 
                        'gender' => 'Gender', 
                        'date_of_birth' => 'Date of Birth', 
                        'class_name' => 'Current Class',
                        'status' => 'Enrollment Status',
                        'parent_name' => 'Parent/Guardian',
                        'parent_phone' => 'Parent Phone'
                    ];
                    foreach ($studentCols as $val => $lbl): ?>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="<?= $val ?>" checked>
                        <span><?= $lbl ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <!-- Column Group: Staff -->
                <div id="colsStaff" class="export-cols-grid hidden animate-fade-in">
                    <?php 
                    $staffCols = [
                        'full_name'       => 'Full Name', 
                        'gender'          => 'Gender',
                        'email'           => 'Email Address', 
                        'phone'           => 'Phone Number', 
                        'role'            => 'System Role',
                        'assigned_classes'=> 'Form Classes'
                    ];
                    foreach ($staffCols as $val => $lbl): ?>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="<?= $val ?>" checked>
                        <span><?= $lbl ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <!-- Column Group: Results -->
                <div id="colsResults" class="export-cols-grid hidden animate-fade-in">
                    <label class="col-picker disabled">
                        <input type="checkbox" name="columns[]" value="student_id_number" checked disabled>
                        <span>Student ID (Req)</span>
                    </label>
                    <label class="col-picker disabled">
                        <input type="checkbox" name="columns[]" value="full_name" checked disabled>
                        <span>Full Name (Req)</span>
                    </label>
                    <?php 
                    $resultCols = [
                        'class_name' => 'Classroom', 
                        'aggregate_score' => 'Total Aggregate', 
                        'class_position' => 'Overall Position',
                        'subject_breakdown' => 'Individual Subjects'
                    ];
                    foreach ($resultCols as $val => $lbl): ?>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="<?= $val ?>" checked>
                        <span><?= $lbl ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>

                <!-- Column Group: Attendance -->
                <div id="colsAttendance" class="export-cols-grid hidden animate-fade-in">
                    <label class="col-picker disabled">
                        <input type="checkbox" name="columns[]" value="student_id_number" checked disabled>
                        <span>Student ID (Req)</span>
                    </label>
                    <label class="col-picker disabled">
                        <input type="checkbox" name="columns[]" value="full_name" checked disabled>
                        <span>Full Name (Req)</span>
                    </label>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="class_name" checked>
                        <span>Classroom</span>
                    </label>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="days_present" checked>
                        <span>Days Present</span>
                    </label>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="days_absent" checked>
                        <span>Days Absent</span>
                    </label>
                </div>

                <!-- Column Group: SMS History -->
                <div id="colsSms" class="export-cols-grid hidden animate-fade-in">
                    <label class="col-picker disabled">
                        <input type="checkbox" name="columns[]" value="sent_at" checked disabled>
                        <span>Date &amp; Time (Req)</span>
                    </label>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="sms_type" checked>
                        <span>Message Type</span>
                    </label>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="recipient_phone" checked>
                        <span>Recipient Phone</span>
                    </label>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="status" checked>
                        <span>Delivery Status</span>
                    </label>
                    <label class="col-picker">
                        <input type="checkbox" name="columns[]" value="message" checked>
                        <span>Message Preview</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Right Column: Sidebar Options -->
        <div class="lg:col-span-4 flex flex-col gap-8">
            
            <!-- STEP 4: Format selection -->
            <div class="card p-8 shadow-md">
                <div class="flex items-center gap-3 mb-8">
                    <div style="width:32px; height:32px; background:#10b981; color:white; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:14px;">4</div>
                    <h2 style="font-size: 1.125rem; font-weight: 800; margin: 0;">Output Format</h2>
                </div>

                <div class="flex flex-col gap-4">
                    <label class="format-option active">
                        <input type="radio" name="export_format" value="excel" checked class="hidden">
                        <div class="flex items-center gap-3">
                            <div class="fmt-icon">XLS</div>
                            <div>
                                <div style="font-weight:800; font-size:14px; color:var(--clr-text);">Microsoft Excel</div>
                                <div style="font-size:11px; color:var(--clr-text-muted);">Formatted spreadsheets</div>
                            </div>
                        </div>
                    </label>

                    <label class="format-option">
                        <input type="radio" name="export_format" value="csv" class="hidden">
                        <div class="flex items-center gap-3">
                            <div class="fmt-icon" style="background:var(--clr-surface-2); color:var(--clr-text-muted);">CSV</div>
                            <div>
                                <div style="font-weight:800; font-size:14px; color:var(--clr-text);">Standard CSV</div>
                                <div style="font-size:11px; color:var(--clr-text-muted);">Plain comma-separated data</div>
                            </div>
                        </div>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-full mt-8 btn-lg shadow-purple" style="height:56px; gap:10px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-2.25M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    START EXPORT
                </button>
            </div>

            <!-- Export Tips info -->
            <div style="background: var(--clr-surface-2); border-radius: var(--radius-xl); padding: 2rem; border:1px solid var(--clr-border);">
                <div style="font-weight: 800; font-size: 13px; text-transform: uppercase; letter-spacing: 0.1em; color: var(--clr-text-muted); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 8px;">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16" style="color:var(--clr-primary);"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                     Quick Guide
                </div>
                <ul class="flex flex-col gap-4 p-0 m-0" style="list-style:none;">
                    <li class="flex items-start gap-3">
                        <div style="width:18px; height:18px; border-radius:50%; background:var(--clr-success); color:white; display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0; margin-top:2px;">✓</div>
                        <div style="font-size:13px; color:var(--clr-text); line-height:1.4;"><strong>Excel</strong> is recommended for viewing data directly.</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div style="width:18px; height:18px; border-radius:50%; background:var(--clr-success); color:white; display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0; margin-top:2px;">✓</div>
                        <div style="font-size:13px; color:var(--clr-text); line-height:1.4;"><strong>CSV</strong> is best for importing data into other school software.</div>
                    </li>
                    <li class="flex items-start gap-3">
                        <div style="width:18px; height:18px; border-radius:50%; background:var(--clr-success); color:white; display:flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0; margin-top:2px;">✓</div>
                        <div style="font-size:13px; color:var(--clr-text); line-height:1.4;">Selection is persistent. The columns you check are what will appear in your file.</div>
                    </li>
                </ul>
            </div>

        </div>
    </div>
</form>

<style>
/* Export Specific Premium Styles */
.export-type-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1.5rem;
    background: var(--clr-surface);
    border: 2px solid var(--clr-border);
    border-radius: var(--radius-xl);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}
.export-type-card:hover {
    border-color: var(--clr-primary-300);
    transform: translateY(-3px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
}
.export-type-card.active {
    border-color: var(--clr-primary);
    background: var(--clr-primary-50);
}
.export-type-card .icon-box {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.export-type-card .icon-box svg { width: 24px; height: 24px; }
.export-type-card .card-content .title { font-weight: 800; font-size: 15px; color: var(--clr-text); }
.export-type-card .card-content .desc { font-size: 12px; color: var(--clr-text-muted); margin-top: 2px; }
.export-type-card .check-mark {
    position: absolute; top: 12px; right: 12px;
    width: 18px; height: 18px; color: var(--clr-primary);
    opacity: 0; transform: scale(0.5); visibility: hidden;
    transition: all 0.2s;
}
.export-type-card.active .check-mark {
    opacity: 1; transform: scale(1); visibility: visible;
}

.export-cols-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
}
.col-picker {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 1rem; border: 1px solid var(--clr-border);
    border-radius: var(--radius-lg);
    cursor: pointer; transition: all 0.2s;
    background: var(--clr-surface);
}
.col-picker:hover { background: var(--clr-surface-2); border-color: var(--clr-primary-200); }
.col-picker input { width: 18px; height: 18px; accent-color: var(--clr-primary); cursor: pointer; }
.col-picker span { font-size: 13px; font-weight: 600; color: var(--clr-text); }
.col-picker.disabled { opacity: 0.6; cursor: not-allowed; background: var(--clr-surface-2); }

.format-option {
    padding: 1.25rem; border: 2px solid var(--clr-border);
    border-radius: var(--radius-xl); cursor: pointer; transition: all 0.2s;
}
.format-option:hover { border-color: var(--clr-primary-200); }
.format-option.active { border-color: var(--clr-success); background: var(--clr-success-bg); }
.format-option .fmt-icon {
    width: 40px; height: 40px; border-radius: 8px;
    background: white; color: var(--clr-success); border: 1px solid rgba(0,0,0,0.05);
    display: flex; align-items: center; justify-content: center;
    font-weight: 900; font-size: 10px;
}
</style>

<script>
function selectExportType(type) {
    const cards = document.querySelectorAll('.export-type-card');
    cards.forEach(card => {
        const input = card.querySelector('input');
        if (input.value === type) {
            input.checked = true;
            card.classList.add('active');
        } else {
            card.classList.remove('active');
        }
    });

    // Update Format options styling (sync with active)
    document.querySelectorAll('.format-option').forEach(fmt => {
        fmt.addEventListener('click', function() {
            document.querySelectorAll('.format-option').forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input').checked = true;
        });
    });
}

function toggleExportPanels(type) {
    // 1. Toggle Column Groups
    document.querySelectorAll('.export-cols-grid').forEach(el => {
        el.classList.add('hidden');
        // Uncheck all in hidden panels so they don't submit unnecessary data
        el.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(cb => cb.checked = false);
    });
    
    const activeGroup = document.getElementById('cols' + type.charAt(0).toUpperCase() + type.slice(1));
    if (activeGroup) {
        activeGroup.classList.remove('hidden');
        activeGroup.querySelectorAll('input[type="checkbox"]:not(:disabled)').forEach(cb => cb.checked = true);
    }

    // 2. Toggle Conditional Parameter Filters
    const classFilter  = document.getElementById('filterClass');
    const termFilter   = document.getElementById('filterTerm');
    const statusFilter = document.getElementById('filterStatus');

    if (type === 'students') {
        classFilter.style.display  = 'block';
        termFilter.style.display   = 'none';
        statusFilter.style.display = 'block';
    } else if (type === 'staff') {
        classFilter.style.display  = 'none';
        termFilter.style.display   = 'none';
        statusFilter.style.display = 'block';
    } else if (type === 'sms') {
        classFilter.style.display  = 'none';
        termFilter.style.display   = 'none';
        statusFilter.style.display = 'none';
    } else {
        // Results & Attendance
        classFilter.style.display  = 'block';
        termFilter.style.display   = 'block';
        statusFilter.style.display = 'block';
    }
}

function toggleAllCheckboxes(checked) {
    document.querySelectorAll('.export-cols-grid:not(.hidden) input[type="checkbox"]:not(:disabled)').forEach(cb => {
        cb.checked = checked;
    });
}

// Initial state
document.addEventListener('DOMContentLoaded', () => {
    toggleExportPanels('students');
    
    // Wire up format option clicks
    document.querySelectorAll('.format-option').forEach(fmt => {
        fmt.addEventListener('click', function() {
            document.querySelectorAll('.format-option').forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            this.querySelector('input').checked = true;
        });
    });
});
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
