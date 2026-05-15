<?php
// chill.php — Playlists & song view
require_once __DIR__ . '/includes/auth.php';

$user       = getCurrentUser();
$activePage = 'chill';

// If viewing a specific playlist
$playlistId   = isset($_GET['playlist']) ? (int)$_GET['playlist'] : null;
$playlistData = null;
$songs        = [];

if ($playlistId && $user) {
    $pdo  = getDB();
    $stmt = $pdo->prepare("SELECT * FROM playlists WHERE id = ? AND (user_id = ? OR is_public = 1)");
    $stmt->execute([$playlistId, $user['id']]);
    $playlistData = $stmt->fetch();
    if ($playlistData) {
        $songs = getPlaylistSongs($playlistId);
    }
}

$playlists = $user ? getUserPlaylists($user['id']) : [];

// Handle add playlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_playlist']) && $user) {
    $name = trim($_POST['playlist_name'] ?? '');
    if ($name) {
        $pdo = getDB();
        $pdo->prepare("INSERT INTO playlists (user_id, name, is_public, cover_color) VALUES (?, ?, 0, '#4f46e5')")->execute([$user['id'], $name]);
        header('Location: chill.php');
        exit;
    }
}

$boxEmojis = ['📦', '🎁', '📫', '🗃️'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PixelMusic — Chill</title>
<link rel="stylesheet" href="css/style.css">
<style>
  .split-layout { display: flex; gap: 2rem; }
  .split-left   { flex: 1; }
  .split-right  { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 1.5rem; padding-top: 1rem; }
  .add-playlist-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--bg-card);
    border: 2px dashed var(--border);
    border-radius: var(--radius-sm);
    padding: 0.5rem 1rem;
    color: var(--text-muted);
    font-size: 0.85rem;
    cursor: pointer;
    transition: border-color 0.2s, color 0.2s;
  }
  .add-playlist-btn:hover { border-color: var(--accent2); color: var(--text); }
  .modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 200;
    align-items: center; justify-content: center;
  }
  .modal-overlay.open { display: flex; }
  .modal-box {
    background: var(--bg-card);
    border: 3px solid var(--border);
    border-radius: var(--radius);
    padding: 2rem;
    width: 340px;
  }
  .modal-title { font-family: var(--pixel); font-size: 0.6rem; color: var(--accent3); margin-bottom: 1rem; }
  .modal-input {
    width: 100%;
    background: var(--bg-deep);
    border: 2px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 0.65rem 0.9rem;
    color: var(--text);
    font-family: var(--body);
    font-size: 0.9rem;
    outline: none;
    margin-bottom: 1rem;
    transition: border-color 0.2s;
  }
  .modal-input:focus { border-color: var(--accent2); }
</style>
</head>
<body>
<div class="app-wrapper">

  <?php include __DIR__ . '/includes/nav.php'; ?>

  <div class="app-layout">

    <main class="main-content">

      <?php if (!$user): ?>
        <div style="text-align:center;padding:4rem 2rem;">
          <p style="font-family:var(--pixel);font-size:0.8rem;color:var(--accent3);margin-bottom:1rem;">PLAYER 1, INSERT COIN</p>
          <p style="color:var(--text-muted);margin-bottom:1.5rem;">Log in to access your playlists!</p>
          <a href="login.php" style="background:var(--accent);color:#fff;padding:0.7rem 1.5rem;border-radius:8px;text-decoration:none;font-family:var(--pixel);font-size:0.55rem;">▶ LOG IN</a>
        </div>

      <?php elseif ($playlistData): ?>
        <!-- ── Song list view ── -->
        <div class="breadcrumb">
          <a href="chill.php">Playlists</a>
          <span>/</span>
          <?= htmlspecialchars($playlistData['name']) ?>
        </div>

        <h2 style="font-family:var(--pixel);font-size:0.8rem;color:#fff;margin-bottom:1.5rem;">
          📋 <?= htmlspecialchars($playlistData['name']) ?>
        </h2>

        <div class="split-layout">
          <div class="split-left">
            <?php if (empty($songs)): ?>
              <p style="color:var(--text-muted);font-size:0.9rem;">This playlist is empty. Add songs from Discover!</p>
            <?php else: ?>
              <div class="song-list">
                <?php foreach ($songs as $i => $song): ?>
                  <div class="song-row" id="song-<?= $song['id'] ?>">
                    <span class="song-number"><?= $i + 1 ?></span>
                    <div class="song-cover">🎵</div>
                    <div class="song-info">
                      <strong><?= htmlspecialchars($song['title']) ?></strong>
                      <span><?= htmlspecialchars($song['artist_name']) ?></span>
                    </div>
                    <button class="song-like-btn" onclick="toggleLike(this, <?= $song['id'] ?>)" title="Like">💜</button>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Mini player -->
            <div style="margin-top:2rem;">
              <div class="mini-player">
                <div class="player-cover">🎵</div>
                <div class="player-progress">
                  <div class="player-bar" id="playerBar"></div>
                </div>
                <div class="player-controls">
                  <button class="ctrl-btn">⏮</button>
                  <button class="ctrl-btn" id="playPauseBtn" onclick="togglePlay()">⏸</button>
                  <button class="ctrl-btn">⏭</button>
                </div>
              </div>
            </div>
          </div>

          <div class="split-right">
            <!-- Big turntable -->
            <div style="font-size:7rem;animation:turntable-spin 4s linear infinite;" id="turntable">💿</div>
            <!-- Discord badge -->
            <div class="discord-badge">
              <span>⊕</span>
              <span>Discord connected</span>
              <div class="discord-dot"></div>
            </div>
          </div>
        </div>

      <?php else: ?>
        <!-- ── Playlist grid view ── -->
        <div class="split-layout">
          <div class="split-left">
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem;">
              <h2 style="font-family:var(--pixel);font-size:0.75rem;color:#fff;">Playlists</h2>
              <button class="add-playlist-btn" onclick="document.getElementById('addModal').classList.add('open')">
                + New
              </button>
            </div>

            <div class="playlist-grid">
              <?php foreach ($playlists as $i => $pl): ?>
                <a href="chill.php?playlist=<?= $pl['id'] ?>" class="playlist-card">
                  <div class="playlist-box" style="border-color:<?= htmlspecialchars($pl['cover_color']) ?>;">
                    <?= $boxEmojis[$i % count($boxEmojis)] ?>
                  </div>
                  <div class="playlist-name"><?= htmlspecialchars($pl['name']) ?></div>
                  <div class="playlist-count"><?= $pl['song_count'] ?> songs</div>
                </a>
              <?php endforeach; ?>

              <?php if (empty($playlists)): ?>
                <p style="color:var(--text-muted);font-size:0.9rem;grid-column:1/-1;">You have no playlists yet. Create one!</p>
              <?php endif; ?>
            </div>
          </div>

          <div class="split-right">
            <!-- Big pixel turntable -->
            <div style="font-size:9rem;animation:turntable-spin 6s linear infinite;">💿</div>
            <div class="discord-badge">
              <span>⊕</span>
              <span>Discord connected</span>
              <div class="discord-dot"></div>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </main>
  </div>
</div>

<!-- Add Playlist Modal -->
<?php if ($user): ?>
<div class="modal-overlay" id="addModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <p class="modal-title">// NEW PLAYLIST</p>
    <form method="POST" action="chill.php">
      <input type="hidden" name="new_playlist" value="1">
      <input type="text" name="playlist_name" class="modal-input" placeholder="Playlist name..." required autofocus>
      <button type="submit" class="btn-primary">▶ CREATE</button>
    </form>
  </div>
</div>
<?php endif; ?>

<script>
let playing = true;

function togglePlay() {
  playing = !playing;
  const btn = document.getElementById('playPauseBtn');
  const bar = document.getElementById('playerBar');
  const tt  = document.getElementById('turntable');
  if (btn) btn.textContent = playing ? '⏸' : '▶';
  if (bar) bar.style.animationPlayState = playing ? 'running' : 'paused';
  if (tt)  tt.style.animationPlayState  = playing ? 'running' : 'paused';
}

function toggleLike(btn, songId) {
  btn.textContent = btn.textContent === '💜' ? '🤍' : '💜';
  // In a real app: fetch('/api/like.php', {method:'POST', body: JSON.stringify({song_id: songId})})
}
</script>
</body>
</html>
