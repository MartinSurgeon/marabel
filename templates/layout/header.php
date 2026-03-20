<?php
/**
 * Header Layout Partial
 * Uaddara Basic School — SBA Management System
 *
 * Usage: include this file from within app templates.
 * Variables expected: $pageTitle (string), $actions (string HTML — optional)
 */
$pageTitle  = $pageTitle  ?? 'Dashboard';
$pageActions= $pageActions ?? '';
$base = defined('APP_BASE') ? APP_BASE : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> — Uaddara SBA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
  <link rel="icon" type="image/png" href="<?= $base ?>/assets/img/school-logo.png">
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

        <!-- Notification bell placeholder -->
        <button class="btn btn-ghost btn-sm" aria-label="Notifications" title="Notifications">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" width="20" height="20"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        </button>
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
