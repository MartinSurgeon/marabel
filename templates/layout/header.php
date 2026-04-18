<?php
/**
 * Branding: Dynamic via Config helper
 * Usage: include this file from within app templates.
 * Variables expected: $pageTitle (string), $actions (string HTML — optional)
 */
$pageTitle  = $pageTitle  ?? 'Dashboard';
$pageActions= $pageActions ?? '';
$base = defined('APP_BASE') ? APP_BASE : '';

// Notification Data
$unreadCount = 0;
$latestNotifs = [];
if (Session::isLoggedIn()) {
    $unreadCount = Notification::getUnreadCount(Session::userId());
    $latestNotifs = Notification::getLatest(Session::userId(), 5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — <?= htmlspecialchars(Config::get('school_name', 'Marabel SBA')) ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css?v=<?= time() ?>">
  <link rel="icon" type="image/png" href="<?= $base . Config::get('school_logo', '/assets/img/school-logo.png') ?>">
</head>
<body>
<div class="app-layout">

  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="app-content">

    <!-- Global Loading Bar -->
    <div id="global-loader" class="loader-bar" aria-hidden="true" style="display:none"></div>

    <!-- Top bar -->
    <header class="app-header" role="banner">
      <!-- Mobile menu toggle -->
      <button
        class="btn btn-ghost btn-sm"
        id="sidebar-toggle"
        aria-label="Toggle navigation"
        aria-expanded="false"
        aria-controls="sidebar"
        style="display:none; margin-right:0.5rem;"
      >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="22" height="22"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>

      <h1 class="header-title" id="page-heading"><?= htmlspecialchars($pageTitle) ?></h1>

      <div class="header-actions">
        <!-- Active term badge -->
        <?php
        $headerActiveTerm = Session::get('active_term');
        if ($headerActiveTerm): ?>
          <span class="badge badge-purple" title="Currently active term">
            <?= htmlspecialchars($headerActiveTerm) ?>
          </span>
        <?php endif; ?>

        <?= $pageActions ?>

        <!-- Notification Center -->
        <div class="relative inline-block text-left" id="notification-center">
          <button 
            class="btn btn-ghost btn-sm relative p-2" 
            id="notif-toggle" 
            aria-label="Notifications" 
            title="Notifications"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <?php if ($unreadCount > 0): ?>
              <span class="absolute top-1 right-1 flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
              </span>
            <?php endif; ?>
          </button>

          <!-- Dropdown menu -->
          <div 
            id="notif-dropdown" 
            class="hidden origin-top-right absolute right-0 mt-2 w-80 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 z-50 overflow-hidden"
            role="menu" 
            aria-orientation="vertical" 
            aria-labelledby="notif-toggle"
          >
            <div class="px-4 py-3 border-b bg-gray-50 flex justify-between items-center">
              <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
              <?php if ($unreadCount > 0): ?>
                <button id="mark-all-read" class="text-xs text-purple-600 hover:text-purple-800 font-medium tracking-tight">Mark all as read</button>
              <?php endif; ?>
            </div>
            
            <div class="max-h-96 overflow-y-auto" id="notif-list">
              <?php if (empty($latestNotifs)): ?>
                <div class="px-4 py-8 text-center text-sm text-gray-500">
                  No notifications yet.
                </div>
              <?php else: ?>
                <?php foreach ($latestNotifs as $n): ?>
                  <div class="px-4 py-3 hover:bg-gray-50 border-b last:border-0 transition <?= $n['is_read'] ? 'opacity-60' : 'bg-purple-50/30' ?>">
                    <div class="flex items-start">
                      <div class="flex-shrink-0 mt-1">
                        <?php if ($n['type'] === 'success'): ?>
                          <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M5 13l4 4L19 7"/></svg>
                        <?php elseif ($n['type'] === 'error'): ?>
                          <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?php else: ?>
                          <svg class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <?php endif; ?>
                      </div>
                      <div class="ml-3 flex-1">
                        <p class="text-xs font-semibold text-gray-900"><?= htmlspecialchars($n['title']) ?></p>
                        <p class="text-[11px] text-gray-600 mt-0.5 leading-snug"><?= htmlspecialchars($n['message']) ?></p>
                        <p class="text-[10px] text-gray-400 mt-1"><?= date('M j, g:i a', strtotime($n['created_at'])) ?></p>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <div class="p-2 border-t bg-gray-50 text-center">
              <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">Recent Activity</span>
            </div>
          </div>
        </div>

        <script>
          // Simple notification dropdown toggle
          document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('notif-toggle');
            const dropdown = document.getElementById('notif-dropdown');
            const markAll = document.getElementById('mark-all-read');

            if (toggle && dropdown) {
              toggle.addEventListener('click', function(e) {
                e.stopPropagation();
                dropdown.classList.toggle('hidden');
              });

              document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target) && !toggle.contains(e.target)) {
                  dropdown.classList.add('hidden');
                }
              });
            }

            if (markAll) {
              markAll.addEventListener('click', function(e) {
                e.preventDefault();
                fetch('<?= $base ?>/admin/notifications/read-all', {
                  method: 'POST',
                  headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(() => location.reload());
              });
            }
          });
        </script>
      </div>
    </header>

    <!-- Flash messages -->
    <?php if ($success = Session::flash('success')): ?>
      <div class="alert alert-success" data-auto-dismiss style="margin:1rem 1.5rem 0">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>
    <?php if ($error = Session::flash('error')): ?>
      <div class="alert alert-danger" data-auto-dismiss style="margin:1rem 1.5rem 0">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <main class="app-main animate-fade-in" id="main-content" role="main">

<?php if (Session::isLoggedIn()): ?>
<!-- ── Inactivity Timeout Modal ─────────────────────────────── -->
<div id="modal-idle" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="idle-modal-title" style="display:none;">
  <div class="modal w-full mx-4" style="max-width:400px; min-height: 0;">

    <div class="modal-header">
      <div style="display:flex; align-items:center; gap:0.75rem;">
        <div style="width:36px;height:36px;border-radius:50%;background:var(--clr-warning-bg,#fffbeb);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="18" height="18" style="color:var(--clr-warning,#f59e0b);">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
          </svg>
        </div>
        <h3 class="modal-title" id="idle-modal-title">Session Timeout Warning</h3>
      </div>
      <button class="modal-close" onclick="IdleTimer.reset()" aria-label="Stay logged in">&times;</button>
    </div>

    <div class="modal-body" style="text-align:center; padding-top:2rem; padding-bottom:1.5rem;">
      <p style="color:var(--clr-text-muted); font-size:0.875rem; margin:0 0 1.75rem; line-height:1.6;">
        You've been inactive for a while.<br>You'll be signed out automatically in:
      </p>

      <!-- Countdown ring -->
      <div style="position:relative; width:88px; height:88px; margin:0 auto 1.75rem;">
        <svg width="88" height="88" viewBox="0 0 88 88" style="transform:rotate(-90deg);">
          <circle cx="44" cy="44" r="38" fill="none" stroke="var(--clr-surface-2,#f3f4f6)" stroke-width="7"/>
          <circle id="idle-ring" cx="44" cy="44" r="38" fill="none"
            stroke="var(--clr-warning,#f59e0b)" stroke-width="7"
            stroke-linecap="round"
            stroke-dasharray="238.76"
            stroke-dashoffset="0"
            style="transition:stroke-dashoffset 1s linear, stroke 0.5s;"/>
        </svg>
        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
          <span id="idle-countdown" style="font-size:1.6rem;font-weight:900;color:var(--clr-text);font-variant-numeric:tabular-nums;letter-spacing:-0.03em;"></span>
        </div>
      </div>

      <p style="font-size:0.75rem; color:var(--clr-text-muted); margin:0;">
        Move your mouse or press any key to stay logged in.
      </p>
    </div>

    <div class="modal-footer">
      <a href="<?= $base ?>/logout" class="btn btn-ghost" id="idle-logout-btn">Sign Out Now</a>
      <button id="idle-stay-btn" class="btn btn-primary shadow-purple" onclick="IdleTimer.reset()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="16" height="16">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        I'm Still Here
      </button>
    </div>

  </div>
</div>

<script>
const IdleTimer = (() => {
  const WARN_AFTER  = 25 * 60; // 25 min of idle → show modal
  const WARN_WINDOW = 5  * 60; // 5 min countdown before logout
  const LOGOUT_URL  = '<?= $base ?>/logout?timeout=1';
  const totalDash   = 238.76;

  let idleSeconds   = 0;
  let countdownLeft = WARN_WINDOW;
  let warningShown  = false;

  const countEl = () => document.getElementById('idle-countdown');
  const ring    = () => document.getElementById('idle-ring');

  function showWarning() {
    warningShown  = true;
    countdownLeft = WARN_WINDOW;
    openModal('modal-idle');
    document.getElementById('idle-stay-btn')?.focus();
    tick();
  }

  function hideWarning() {
    closeModal('modal-idle');
  }

  function tick() {
    if (!warningShown) return;
    const m = Math.floor(countdownLeft / 60);
    const s = countdownLeft % 60;
    const el = countEl();
    const r  = ring();
    if (el) el.textContent = m > 0 ? m + ':' + String(s).padStart(2,'0') : String(s);
    if (r) {
      const ratio = countdownLeft / WARN_WINDOW;
      r.style.strokeDashoffset = totalDash * (1 - ratio);
      r.style.stroke = ratio > 0.5 ? 'var(--clr-warning,#f59e0b)' : ratio > 0.2 ? '#ef4444' : '#dc2626';
    }
    if (countdownLeft <= 0) { window.location.href = LOGOUT_URL; return; }
    countdownLeft--;
    setTimeout(tick, 1000);
  }

  function onActivity() {
    if (!warningShown) idleSeconds = 0;
  }

  function mainTick() {
    idleSeconds++;
    if (!warningShown && idleSeconds >= WARN_AFTER) showWarning();
  }

  function reset() {
    idleSeconds   = 0;
    warningShown  = false;
    countdownLeft = WARN_WINDOW;
    hideWarning();
  }

  function init() {
    ['mousemove','mousedown','keydown','touchstart','scroll','click'].forEach(e => {
      document.addEventListener(e, onActivity, { passive: true });
    });
    setInterval(mainTick, 1000);
  }

  return { init, reset };
})();

document.addEventListener('DOMContentLoaded', IdleTimer.init);
</script>
<?php endif; ?>

