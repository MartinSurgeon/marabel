<?php
/**
 * Student Portal View — Coming Soon
 * Follows HCI principles: Clear visual hierarchy, prevents cognitive overload, responsive design.
 */
$pageTitle = 'Student Portal';
$base = defined('APP_BASE') ? APP_BASE : '';
global $studentData, $activeTerm, $activeYear;

$schoolName  = Config::get('school_name', 'Uaddara Basic School');
$accentColor = Config::get('brand_accent_color', '#9633cc');
$studentName = $studentData['full_name'] ?? Session::get('user_name') ?? 'Student';
$studentId   = $studentData['student_id_number'] ?? 'N/A';
$className   = trim(($studentData['class_name'] ?? '') . ' ' . ($studentData['section'] ?? ''));
$yearName    = $studentData['year_name'] ?? ($activeYear['year_name'] ?? 'Current Academic Year');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Portal — <?= htmlspecialchars($schoolName) ?></title>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <style>
    :root {
      --clr-primary: <?= $accentColor ?>;
      --clr-primary-50: color-mix(in srgb, <?= $accentColor ?> 10%, #ffffff);
      --clr-primary-600: <?= $accentColor ?>;
    }
    body {
      background: #f8fafc;
      font-family: var(--font-sans, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);
      color: #1e293b;
      margin: 0;
      padding: 0;
    }
    .student-portal-wrapper {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .portal-card {
      background: #ffffff;
      border-radius: 1.25rem;
      border: 1px solid #e2e8f0;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
      max-width: 560px;
      width: 100%;
      overflow: hidden;
      text-align: center;
    }
    .portal-banner {
      background: linear-gradient(135deg, var(--clr-primary), #6366f1);
      padding: 2.5rem 2rem;
      color: white;
      position: relative;
    }
    .badge-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(8px);
      padding: 6px 14px;
      border-radius: 9999px;
      font-size: 0.75rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin-bottom: 1rem;
    }
    .info-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1rem;
      padding: 1.5rem;
      background: #f8fafc;
      border-top: 1px solid #e2e8f0;
      border-bottom: 1px solid #e2e8f0;
      text-align: left;
    }
    .info-item label {
      display: block;
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-weight: 700;
      color: #64748b;
      margin-bottom: 2px;
    }
    .info-item span {
      font-size: 0.95rem;
      font-weight: 700;
      color: #0f172a;
    }
  </style>
</head>
<body>

<div class="student-portal-wrapper">
  <div class="portal-card">
    <div class="portal-banner">
      <div class="badge-pill">
        <span style="width:8px; height:8px; background:#4ade80; border-radius:50%;"></span>
        Feature In Active Development
      </div>
      <h1 style="margin:0 0 0.5rem; font-size:1.75rem; font-weight:900; letter-spacing:-0.03em;">Student Portal</h1>
      <p style="margin:0; opacity:0.9; font-size:0.95rem; line-height:1.4;">
        Welcome, <strong><?= htmlspecialchars($studentName) ?></strong>!
      </p>
    </div>

    <!-- Student Details Summary -->
    <div class="info-grid">
      <div class="info-item">
        <label>Student ID</label>
        <span><?= htmlspecialchars($studentId) ?></span>
      </div>
      <div class="info-item">
        <label>Class / Form</label>
        <span><?= htmlspecialchars($className ?: 'Not Assigned') ?></span>
      </div>
      <div class="info-item">
        <label>Academic Session</label>
        <span><?= htmlspecialchars($yearName) ?></span>
      </div>
      <div class="info-item">
        <label>Status</label>
        <span style="color:#16a34a;">Active Pupil</span>
      </div>
    </div>

    <!-- Coming Soon Message -->
    <div style="padding: 2rem 1.5rem;">
      <div style="width:54px; height:54px; border-radius:50%; background:var(--clr-primary-50); color:var(--clr-primary); display:flex; align-items:center; justify-content:center; margin: 0 auto 1.25rem;">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="28" height="28">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
      </div>
      <h2 style="font-size:1.15rem; font-weight:800; color:#0f172a; margin:0 0 0.5rem;">Online Student Terminal Launching Soon</h2>
      <p style="font-size:0.875rem; color:#64748b; line-height:1.5; margin:0 auto 1.5rem; max-width:420px;">
        Your interactive student learning portal is currently undergoing final setup. Soon you will be able to review continuous assessments, check SBA scores, and track your proficiency levels directly here.
      </p>

      <div style="display:flex; justify-content:center; gap:0.75rem;">
        <a href="<?= $base ?>/logout" class="btn btn-outline" style="border-radius:9999px; padding:8px 24px; font-weight:700;">
          Sign Out
        </a>
      </div>
    </div>

    <!-- Footer -->
    <div style="padding:1rem; background:#f8fafc; border-top:1px solid #f1f5f9; font-size:0.75rem; color:#94a3b8;">
      <?= htmlspecialchars($schoolName) ?> &bull; Continuous Assessment & SBA Engine
    </div>
  </div>
</div>

</body>
</html>
