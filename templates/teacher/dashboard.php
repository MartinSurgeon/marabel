<?php
/**
 * Teacher Dashboard Template (Redesigned v2)
 */

$pageTitle = 'Teacher Dashboard';
include __DIR__ . '/../layout/header.php';

global $activeTerm, $myClasses, $mySubjects, $userRoles, $dashboardStats;
$base = defined('APP_BASE') ? APP_BASE : '';
$userName = Session::get('user_name', 'Teacher');
?>

<!-- ── Hero Section ────────────────────────────────────────── -->
<div class="mb-10 animate-fade-in">
    <div style="background-color: var(--clr-primary-700); background: linear-gradient(135deg, var(--clr-primary-600) 0%, var(--clr-primary-800) 100%); padding: 3.5rem 2.5rem; border-radius: var(--radius-2xl); color: white; position: relative; overflow: hidden; box-shadow: var(--shadow-xl);">
        <!-- Decorative Elements -->
        <div style="position: absolute; top: -20px; right: -20px; width: 180px; height: 180px; background: rgba(255,255,255,0.07); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -40px; left: 8%; width: 120px; height: 120px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>

        <div style="position: relative; z-index: 1;">
            <div style="display:inline-flex; align-items: center; padding: 0.5rem 1rem; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.15); border-radius: var(--radius-full); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 2rem; backdrop-filter: blur(4px);">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="12" height="12" style="margin-right: 8px; opacity: 0.9;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <?= implode(' & ', $userRoles) ?>
            </div>
            <h1 style="font-size: clamp(2rem, 5vw, 3rem); font-weight: 900; margin: 0; line-height: 1; letter-spacing: -0.05em;">
                Welcome back, <br/>
                <span style="color:#fff; text-shadow: 0 2px 10px rgba(0,0,0,0.1);"><?= htmlspecialchars($userName) ?></span> 
                <span style="font-size: 0.8em; margin-left: 4px; display: inline-block; vertical-align: middle;">👋</span>
            </h1>
            <div style="margin: 2rem 0 0; display: flex; align-items: center; gap: 1rem; opacity: 0.9;">
                <span style="display: flex; align-items: center; gap: 6px; font-size: 0.95rem; font-weight: 600; background: rgba(0,0,0,0.15); padding: 4px 12px; border-radius: 8px;">
                     <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"/></svg>
                     <?= htmlspecialchars($activeTerm['year_name'] ?? 'Active Year') ?>
                </span>
                <span style="width: 4px; height: 4px; background: rgba(255,255,255,0.4); border-radius: 50%;"></span>
                <span style="font-size: 0.95rem; font-weight: 600;"><?= htmlspecialchars($activeTerm['name'] ?? 'Active Term') ?></span>
            </div>
        </div>
    </div>
</div>

<!-- ── Summary Cards ───────────────────────────────────────── -->
<div class="grid mb-10 animate-fade-in" style="grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem;">
    <!-- Students -->
    <div class="card hover-lift" style="display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; border: 1px solid var(--clr-border);">
        <div style="background: var(--clr-primary-50); color: var(--clr-primary); width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
        </div>
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                   <div style="font-size: 1.75rem; font-weight: 900; color: var(--clr-text); line-height: 1;"><?= number_format($dashboardStats['students']) ?></div>
                   <div style="font-size: 11px; font-weight: 700; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.35rem;">Total Students</div>
                </div>
                <!-- Gender Breakdown -->
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 2px;">
                    <div style="font-size: 10px; font-weight: 800; color: var(--clr-info); display: flex; align-items: center; gap: 4px; background: rgba(14, 165, 233, 0.08); padding: 2px 6px; border-radius: 4px;">
                        <svg fill="currentColor" viewBox="0 0 24 24" width="10" height="10"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <?= number_format($dashboardStats['students_male']) ?> M
                    </div>
                    <div style="font-size: 10px; font-weight: 800; color: #ec4899; display: flex; align-items: center; gap: 4px; background: rgba(236, 72, 153, 0.08); padding: 2px 6px; border-radius: 4px;">
                        <svg fill="currentColor" viewBox="0 0 24 24" width="10" height="10"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        <?= number_format($dashboardStats['students_female']) ?> F
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subjects -->
    <div class="card hover-lift" style="display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; border: 1px solid var(--clr-border);">
        <div style="background: #ede9fe; color: #7c3aed; width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
        </div>
        <div>
            <div style="font-size: 1.75rem; font-weight: 900; color: var(--clr-text); line-height: 1;"><?= $dashboardStats['subjects'] ?></div>
            <div style="font-size: 12px; font-weight: 700; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Subjects Assigned</div>
        </div>
    </div>

    <!-- Classes -->
    <div class="card hover-lift" style="display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; border: 1px solid var(--clr-border);">
        <div style="background: #fff7ed; color: #f97316; width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div>
            <div style="font-size: 1.75rem; font-weight: 900; color: var(--clr-text); line-height: 1;"><?= $dashboardStats['classes'] ?></div>
            <div style="font-size: 12px; font-weight: 700; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Classes Taught</div>
        </div>
    </div>

    <!-- Managed -->
    <div class="card hover-lift" style="display: flex; align-items: center; gap: 1.25rem; padding: 1.5rem; border: 1px solid var(--clr-border);">
        <div style="background: #fff1f2; color: #e11d48; width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        </div>
        <div>
            <div style="font-size: 1.75rem; font-weight: 900; color: var(--clr-text); line-height: 1;"><?= $dashboardStats['managed'] ?></div>
            <div style="font-size: 12px; font-weight: 700; color: var(--clr-text-muted); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 0.25rem;">Form Classes</div>
        </div>
    </div>
</div>

<!-- ── Dashboard Content ────────────────────────────────────── -->
<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem;">

    <!-- Role Card: Class Teacher -->
    <?php if (in_array('Class Teacher', $userRoles)): ?>
    <div class="card hover-lift" style="padding: 2rem; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--clr-border);">
        <div>
            <div style="width: 48px; height: 48px; background: var(--clr-primary-50); color: var(--clr-primary); border-radius: 12px; display: flex; align-items:center; justify-content:center; margin-bottom: 1.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0 0 0.5rem; color: var(--clr-text);">Class Teacher Account</h2>
            <p class="text-muted" style="font-size: 14px; line-height: 1.5; margin-bottom: 1.5rem;">
                You are currently managing <strong><?= count($myClasses) ?></strong> classroom<?= count($myClasses) > 1 ? 's' : '' ?>. Maintain attendance and remarks here.
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 2rem;">
                <?php foreach ($myClasses as $c): ?>
                    <span class="badge" style="background: var(--clr-surface-2); color: var(--clr-text); font-weight: 700; padding: 0.5rem 0.75rem; border: 1px solid var(--clr-border);">
                        <?= htmlspecialchars($c['class_name'] . ($c['section'] ? " ({$c['section']})" : '')) ?>
                    </span>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <?php foreach ($myClasses as $c): ?>
            <a href="<?= $base ?>/teacher/class?id=<?= $c['id'] ?>" class="btn btn-primary" style="justify-content: center; font-size: 13px;">Manage <?= htmlspecialchars($c['class_name']) ?></a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Role Card: Subject Teacher -->
    <?php if (in_array('Subject Teacher', $userRoles)): ?>
    <div class="card hover-lift" style="padding: 2rem; display: flex; flex-direction: column; justify-content: space-between; border: 1px solid var(--clr-border);">
        <div>
            <div style="width: 48px; height: 48px; background: var(--clr-success-bg); color: var(--clr-success); border-radius: 12px; display: flex; align-items:center; justify-content:center; margin-bottom: 1.5rem;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="24" height="24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0 0 0.5rem; color: var(--clr-text);">Subject Teacher Account</h2>
            <p class="text-muted" style="font-size: 14px; line-height: 1.5; margin-bottom: 1.5rem;">
                You are assigned to teach <strong><?= count($mySubjects) ?></strong> subject-class combination<?= count($mySubjects) > 1 ? 's' : '' ?>.
            </p>
            <div style="max-height: 180px; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1.5rem;">
                <?php foreach ($mySubjects as $s): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid var(--clr-border); font-size: 13px;">
                    <div style="display:flex; flex-direction:column;">
                        <span style="font-weight: 700; color: var(--clr-text);"><?= htmlspecialchars($s['subject_name']) ?></span>
                        <span class="text-muted" style="font-size:11px;"><?= htmlspecialchars($s['class_name'] . ($s['section'] ? " ({$s['section']})" : '')) ?></span>
                    </div>
                    <!-- Per-subject print button -->
                    <a href="<?= $base ?>/teacher/export-scores?id=<?= $s['class_subject_id'] ?>&format=pdf"
                       target="_blank"
                       title="Print class list for <?= htmlspecialchars($s['subject_name']) ?>"
                       style="display:inline-flex; align-items:center; justify-content:center; width:30px; height:30px; border-radius:7px; background:#f5f3ff; border:1.5px solid #ede9fe; color:#6d28d9; flex-shrink:0; transition:background 0.15s;"
                       onmouseover="this.style.background='#ede9fe'" onmouseout="this.style.background='#f5f3ff'">
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" width="14" height="14">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                      </svg>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- CTA Buttons -->
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <?php if (count($mySubjects) > 1): ?>
          <!-- Bulk Print All -->
          <a href="<?= $base ?>/teacher/export-scores?id=all&format=pdf"
             target="_blank"
             style="display:flex; align-items:center; justify-content:center; gap:8px; padding:10px 16px; font-size:12px; font-weight:700; color:#6d28d9; background:#f5f3ff; border:1.5px solid #ede9fe; border-radius:var(--radius-lg); text-decoration:none; transition:background 0.15s;"
             onmouseover="this.style.background='#ede9fe'" onmouseout="this.style.background='#f5f3ff'">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" width="15" height="15">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print All Class Lists (<?= count($mySubjects) ?> Subjects)
          </a>
          <?php endif; ?>
          <a href="<?= $base ?>/teacher/scores" class="btn btn-primary" style="justify-content: center;">
              Enter Scores &amp; Track Progress
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="16" height="16" class="ml-2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
          </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Stats / Actions -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card" style="padding: 1.5rem; background: var(--clr-surface-2); border: 1px dashed var(--clr-border);">
            <h3 style="font-size: 0.875rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--clr-text-muted); margin: 0 0 1rem;">System Navigation</h3>
            <div class="flex flex-col gap-2">
                <a href="<?= $base ?>/teacher/import" class="btn btn-ghost" style="justify-content: flex-start; gap: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Bulk Score Import
                </a>
                <a href="<?= $base ?>/teacher/reports" class="btn btn-ghost" style="justify-content: flex-start; gap: 0.75rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Report Card Preview
                </a>
            </div>
        </div>

        <div style="background: var(--clr-info-bg); border-radius: var(--radius-xl); padding: 1.5rem; color: var(--clr-info); font-size: 13px; line-height: 1.6;">
            <div style="font-weight: 800; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.05em;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Helpful Tip
            </div>
            Detailed progress tracking for each of your subjects has been moved to the <a href="<?= $base ?>/teacher/scores" style="font-weight:800; text-decoration:underline;">Enter Scores</a> section to keep your dashboard clean.
        </div>
    </div>

</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
