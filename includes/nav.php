<?php
// includes/nav.php
// Requires $user (current user array) and $activePage string

$pages = [
    'index'    => ['label' => 'Home',     'href' => 'index.php'],
    'discover' => ['label' => 'Discover', 'href' => 'discover.php'],
    'chill'    => ['label' => 'Chill',    'href' => 'chill.php'],
];
?>
<!-- Ticker -->
<div class="ticker-wrap">
  <span class="ticker-inner">
    🎵 Now Playing: Daily Dose of Music // Use headphones for best quality // Auto-queue enabled //
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    🎵 Now Playing: Daily Dose of Music // Use headphones for best quality // Auto-queue enabled //
  </span>
</div>

<!-- Top nav -->
<nav class="topbar">
  <div class="nav-links">
    <?php foreach ($pages as $key => $page): ?>
      <a href="<?= $page['href'] ?>" class="<?= ($activePage === $key) ? 'active' : '' ?>">
        <?= $page['label'] ?>
      </a>
    <?php endforeach; ?>
  </div>

  <?php if ($user): ?>
    <a href="profile.php" class="nav-avatar" title="Your profile"
       style="background:<?= htmlspecialchars($user['avatar_color']) ?>; color:#fff; font-family:var(--pixel); font-size:0.7rem; text-decoration:none;">
      <?= strtoupper(substr($user['display_name'], 0, 1)) ?>
    </a>
  <?php else: ?>
    <a href="login.php" class="nav-avatar" title="Log in" style="background:var(--accent);color:#fff;font-size:0.5rem;font-family:var(--pixel);">IN</a>
  <?php endif; ?>
</nav>
