<?php
/**
 * 404 Not Found Error Page
 * Uaddara Basic School — SBA Management System
 */
if (!defined('ROOT_PATH')) { http_response_code(404); exit('404 Not Found'); }
http_response_code(404);
$base = defined('APP_BASE') ? APP_BASE : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 — Page Not Found | Uaddara SBA</title>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/app.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--clr-bg);">
  <div style="text-align:center;padding:2rem;max-width:480px;">
    <div style="font-size:6rem;font-weight:900;color:var(--clr-primary-200);line-height:1;">404</div>
    <h1 style="font-size:1.5rem;font-weight:700;margin:.75rem 0 .5rem;color:var(--clr-text);">Page Not Found</h1>
    <p style="color:var(--clr-text-muted);margin-bottom:2rem;">The page you're looking for doesn't exist or you don't have permission to view it.</p>
    <a href="javascript:history.back()" class="btn btn-secondary" style="margin-right:.5rem;">← Go Back</a>
    <a href="<?= $base ?>/login" class="btn btn-primary">Go to Login</a>
  </div>
</body>
</html>
