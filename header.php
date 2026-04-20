<?php
require_once __DIR__ . '/auth.php';
$ACTIVE   = $ACTIVE   ?? '';
$PAGETITLE = $PAGETITLE ?? 'die Ragebaiters';
$u = current_user();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($PAGETITLE) ?> | die Ragebaiters</title>
<link rel="icon" type="image/png" href="images/logo.png">
<link rel="stylesheet" href="styles.css">
<link rel="stylesheet" href="app.css">
</head>
<body>

<div class="top-banner">
  <img src="images/banner2.png" alt="die Ragebaiters Banner">
</div>

<header class="container">
  <nav class="nav">
    <div style="font-weight: 900; font-size: 1.6rem;">die Ragebaiters</div>
    <div class="nav-links">
      <a class="<?= $ACTIVE==='home'?'active':'' ?>" href="index.html">Startseite</a>
      <a class="<?= $ACTIVE==='team'?'active':'' ?>" href="team.html">Team</a>
      <a class="<?= $ACTIVE==='mediathek'?'active':'' ?>" href="mediathek.php">Mediathek</a>
      <a class="<?= $ACTIVE==='impressum'?'active':'' ?>" href="impressum.html">Impressum</a>
      <?php if ($u): ?>
        <a class="<?= $ACTIVE==='dashboard'?'active':'' ?>" href="dashboard.php">Dashboard</a>
        <a href="logout.php" title="Abmelden">Logout</a>
      <?php else: ?>
        <a class="<?= $ACTIVE==='login'?'active':'' ?>" href="login.php">Login</a>
      <?php endif; ?>
      <a class="social-icon" href="https://www.youtube.com/@RagebaitersAS" target="_blank" rel="noopener" aria-label="YouTube" title="YouTube">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M23.498 6.186a2.999 2.999 0 0 0-2.112-2.122C19.505 3.5 12 3.5 12 3.5s-7.505 0-9.386.564A2.999 2.999 0 0 0 .502 6.186C0 8.074 0 12 0 12s0 3.926.502 5.814a2.999 2.999 0 0 0 2.112 2.122C4.495 20.5 12 20.5 12 20.5s7.505 0 9.386-.564a2.999 2.999 0 0 0 2.112-2.122C24 15.926 24 12 24 12s0-3.926-.502-5.814zM9.75 15.568V8.432L15.818 12 9.75 15.568z"/>
        </svg>
      </a>
      <a class="social-icon" href="https://www.instagram.com/die_ragebaiters/" target="_blank" rel="noopener" aria-label="Instagram" title="Instagram">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
          <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/>
          <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/>
        </svg>
      </a>
    </div>
  </nav>
</header>

<main class="container">
