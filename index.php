<?php
// index.php — Home page
require_once __DIR__ . '/includes/auth.php';

$user       = getCurrentUser();
$activePage = 'index';

$recentSongs = getRecentSongs();
$userGenres  = $user ? getUserGenres($user['id']) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PixelMusic — Home</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="app-wrapper">

  <?php include __DIR__ . '/includes/nav.php'; ?>

  <div class="app-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-search">
        <span>🔍</span>
        <input type="text" placeholder="Search for playlists...">
        <span style="color:var(--accent3);cursor:pointer;font-size:1.2rem;">+</span>
      </div>

      <?php if ($user): ?>
        <!-- Liked Songs -->
        <a href="chill.php" class="sidebar-item">
          <div class="si-avatar si-liked">💜</div>
          <div class="si-info">
            <strong>Liked Songs</strong>
            <span>Playlist</span>
          </div>
        </a>

        <?php
        $playlists = getUserPlaylists($user['id']);
        foreach ($playlists as $pl):
        ?>
          <a href="chill.php?playlist=<?= $pl['id'] ?>" class="sidebar-item">
            <div class="si-avatar" style="background:<?= htmlspecialchars($pl['cover_color']) ?>;border-radius:8px;font-size:1.2rem;">📦</div>
            <div class="si-info">
              <strong><?= htmlspecialchars($pl['name']) ?></strong>
              <span>Playlist · <?= $pl['song_count'] ?> songs</span>
            </div>
          </a>
        <?php endforeach; ?>

      <?php else: ?>
        <div style="padding:1rem;text-align:center;">
          <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.75rem;">Sign in to see your library</p>
          <a href="login.php" style="color:var(--accent3);font-size:0.8rem;">Log in →</a>
        </div>
      <?php endif; ?>
    </aside>

    <!-- Main -->
    <main class="main-content">

      <!-- Hero -->
      <section class="hero">
        <div class="hero-pixel-card">
          <span class="pixel-character">🧍</span>
          <div class="pixel-stats">
            <?php if ($user): ?>
              <?= $user['exp_points'] ?> EXP<br>LV <?= $user['level'] ?>
            <?php else: ?>
              ?? EXP<br>LV ?
            <?php endif; ?>
          </div>
          <div class="exp-bar">
            <?php
            $filled = $user ? min(8, (int)($user['exp_points'] / 12)) : 4;
            for ($i = 0; $i < 8; $i++): ?>
              <div class="exp-pip <?= $i < $filled ? 'filled' : '' ?>"></div>
            <?php endfor; ?>
          </div>
          <div class="now-playing-mini">
            <div class="cover-placeholder">🎵</div>
            <button class="play-btn">▶</button>
          </div>
        </div>

        <div>
          <h1 class="hero-title">Daily&nbsp;Dose&nbsp;of<br>Music ⭐</h1>
          <?php if ($user): ?>
            <p style="color:var(--text-muted);margin-top:0.75rem;">Welcome back, <strong style="color:var(--accent3)"><?= htmlspecialchars($user['display_name']) ?></strong>!</p>
          <?php else: ?>
            <p style="color:var(--text-muted);margin-top:0.75rem;">
              <a href="signup.php" style="color:var(--accent3);">Join now</a> to unlock your full experience.
            </p>
          <?php endif; ?>
        </div>
      </section>

      <!-- Genre tags -->
      <?php if (!empty($userGenres)): ?>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.5rem;">
          <?php foreach ($userGenres as $g): ?>
            <span style="background:var(--accent);color:#fff;padding:0.3rem 0.85rem;border-radius:20px;font-size:0.78rem;"><?= htmlspecialchars($g['name']) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Recent Songs -->
      <p class="section-header">// FOR YOU</p>
      <div class="album-grid">
        <?php foreach ($recentSongs as $song): ?>
          <div class="album-card">
            <div class="album-card-inner">
              <div class="album-name"><?= htmlspecialchars($song['title']) ?></div>
              <div class="album-cover-placeholder">🎵</div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($recentSongs)): ?>
          <p style="color:var(--text-muted);font-size:0.85rem;">No songs yet — check back soon!</p>
        <?php endif; ?>
      </div>

      <?php if (!$user): ?>
        <div style="text-align:center;padding:2rem;border:2px dashed var(--border);border-radius:var(--radius);margin-top:1rem;">
          <p style="font-family:var(--pixel);font-size:0.6rem;color:var(--accent3);margin-bottom:0.75rem;">LEVEL UP YOUR EXPERIENCE</p>
          <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:1rem;">Sign up to save playlists, earn badges &amp; more!</p>
          <a href="signup.php" style="background:var(--accent);color:#fff;padding:0.6rem 1.5rem;border-radius:8px;text-decoration:none;font-family:var(--pixel);font-size:0.55rem;letter-spacing:1px;">▶ JOIN FREE</a>
        </div>
      <?php endif; ?>

    </main>
  </div>
</div>
</body>
</html>
