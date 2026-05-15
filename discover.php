<?php
// discover.php
require_once __DIR__ . '/includes/auth.php';

$user       = getCurrentUser();
$activePage = 'discover';

$pdo = getDB();

// Search
$q       = trim($_GET['q'] ?? '');
$songs   = [];
$artists = [];

if ($q !== '') {
    $like = '%' . $q . '%';
    $stmt = $pdo->prepare("SELECT s.*, a.name AS artist_name FROM songs s JOIN artists a ON a.id = s.artist_id WHERE s.title LIKE ? OR a.name LIKE ? ORDER BY s.play_count DESC LIMIT 20");
    $stmt->execute([$like, $like]);
    $songs = $stmt->fetchAll();

    $aStmt = $pdo->prepare("SELECT * FROM artists WHERE name LIKE ? LIMIT 10");
    $aStmt->execute([$like]);
    $artists = $aStmt->fetchAll();
} else {
    $songs = getRecentSongs();

    $artists = $pdo->query("SELECT * FROM artists ORDER BY id LIMIT 6")->fetchAll();
}

$allGenres = getAllGenres();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PixelMusic — Discover</title>
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
        <a href="chill.php" class="sidebar-item">
          <div class="si-avatar si-liked">💜</div>
          <div class="si-info">
            <strong>Liked Songs</strong>
            <span>Playlist · 10 songs</span>
          </div>
        </a>
        <?php foreach ($artists as $art): ?>
          <a href="discover.php?q=<?= urlencode($art['name']) ?>" class="sidebar-item">
            <div class="si-avatar" style="background:var(--accent);font-size:1.2rem;">🎤</div>
            <div class="si-info">
              <strong><?= htmlspecialchars($art['name']) ?></strong>
              <span>Artist</span>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="padding:1rem;text-align:center;color:var(--text-muted);font-size:0.8rem;">
          <a href="login.php" style="color:var(--accent3);">Log in</a> to see your library
        </div>
      <?php endif; ?>
    </aside>

    <!-- Main -->
    <main class="main-content">

      <!-- Search bar -->
      <form method="GET" action="discover.php" style="display:flex;gap:0.75rem;align-items:center;margin-bottom:1.5rem;">
        <div style="flex:1;display:flex;align-items:center;gap:0.6rem;background:var(--bg-card);border:2px solid var(--border);border-radius:var(--radius-sm);padding:0.65rem 1rem;">
          <span style="font-size:1.1rem;">🔍</span>
          <input
            type="text"
            name="q"
            placeholder="Search for songs, artists..."
            value="<?= htmlspecialchars($q) ?>"
            style="background:transparent;border:none;outline:none;color:var(--text);font-family:var(--body);font-size:0.95rem;width:100%;"
          >
        </div>
        <button type="submit" style="background:var(--accent);border:none;border-radius:var(--radius-sm);padding:0.65rem 1.25rem;color:#fff;font-family:var(--pixel);font-size:0.5rem;cursor:pointer;letter-spacing:1px;">GO</button>
      </form>

      <!-- Genre filter chips -->
      <div style="display:flex;gap:0.4rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        <a href="discover.php" style="background:<?= $q === '' ? 'var(--accent)' : 'var(--bg-card)' ?>;color:#fff;padding:0.3rem 0.85rem;border-radius:20px;font-size:0.78rem;text-decoration:none;border:2px solid var(--border);">All</a>
        <?php foreach ($allGenres as $g): ?>
          <a href="discover.php?q=<?= urlencode($g['name']) ?>"
             style="background:<?= ($q === $g['name']) ? 'var(--accent)' : 'var(--bg-card)' ?>;color:#fff;padding:0.3rem 0.85rem;border-radius:20px;font-size:0.78rem;text-decoration:none;border:2px solid var(--border);">
            <?= htmlspecialchars($g['name']) ?>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($q): ?>
        <p class="section-header">// RESULTS FOR "<?= htmlspecialchars(strtoupper($q)) ?>"</p>
      <?php else: ?>
        <p class="section-header">// RECENTLY PLAYED</p>
      <?php endif; ?>

      <div class="album-grid">
        <?php foreach ($songs as $song): ?>
          <div class="album-card" onclick="alert('Now playing: <?= htmlspecialchars(addslashes($song['title'])) ?> by <?= htmlspecialchars(addslashes($song['artist_name'])) ?>')">
            <div class="album-card-inner">
              <div class="album-name"><?= htmlspecialchars($song['title']) ?></div>
              <div class="album-cover-placeholder">🎵</div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($songs)): ?>
          <p style="color:var(--text-muted);font-size:0.9rem;grid-column:1/-1;">No songs found for "<?= htmlspecialchars($q) ?>"</p>
        <?php endif; ?>
      </div>

      <!-- Pixel turntable decoration -->
      <div style="text-align:right;margin-top:3rem;opacity:0.4;">
        <span style="font-size:5rem;display:inline-block;animation:turntable-spin 8s linear infinite;">💿</span>
      </div>

    </main>
  </div>
</div>
</body>
</html>
