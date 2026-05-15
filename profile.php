<?php
// profile.php
require_once __DIR__ . '/includes/auth.php';

requireLogin();

$user       = getCurrentUser();
$activePage = 'discover'; // none active

// Handle logout
if (isset($_GET['logout'])) {
    logoutUser();
}

// Handle bio update
$saveMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_bio'])) {
    $bio = trim($_POST['bio'] ?? '');
    $pdo = getDB();
    $pdo->prepare("UPDATE users SET bio = ? WHERE id = ?")->execute([$bio, $user['id']]);
    $user['bio'] = $bio;
    $saveMsg = 'Profile updated!';
}

$badges    = getUserBadges($user['id']);
$playlists = getUserPlaylists($user['id']);
$publicPls = array_filter($playlists, fn($p) => $p['is_public']);
$genres    = getUserGenres($user['id']);
$boxEmojis = ['📦', '🎁', '📫', '🗃️'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PixelMusic — <?= htmlspecialchars($user['display_name']) ?></title>
<link rel="stylesheet" href="css/style.css">
<style>
  .profile-two-col { display:flex; gap:2rem; flex-wrap:wrap; }
  .profile-left    { flex: 0 0 300px; }
  .profile-right   { flex: 1; }
  .genre-tag {
    display:inline-block;
    background:var(--accent);
    color:#fff;
    padding:0.25rem 0.75rem;
    border-radius:20px;
    font-size:0.78rem;
    margin:0.2rem;
  }
  .edit-area {
    width:100%;
    background:var(--bg-deep);
    border:2px solid var(--border);
    border-radius:var(--radius-sm);
    padding:0.65rem 0.9rem;
    color:var(--text);
    font-family:var(--body);
    font-size:0.9rem;
    outline:none;
    resize:vertical;
    transition:border-color 0.2s;
    margin-bottom:0.75rem;
  }
  .edit-area:focus { border-color:var(--accent2); }
</style>
</head>
<body>
<div class="app-wrapper">

  <?php include __DIR__ . '/includes/nav.php'; ?>

  <div class="app-layout">
    <main class="main-content">

      <?php if ($saveMsg): ?>
        <div class="alert alert-success" style="max-width:400px;"><?= htmlspecialchars($saveMsg) ?></div>
      <?php endif; ?>

      <!-- Profile header -->
      <div class="profile-header">
        <div class="profile-avatar-big" style="background:<?= htmlspecialchars($user['avatar_color']) ?>;color:#fff;">
          <?= strtoupper(substr($user['display_name'], 0, 1)) ?>
        </div>
        <div>
          <p style="font-size:0.8rem;color:var(--text-muted);letter-spacing:2px;text-transform:uppercase;margin-bottom:0.25rem;">USER</p>
          <h1 class="profile-username">
            <?= htmlspecialchars($user['display_name']) ?>
            <span class="pixel-sparkle" style="font-size:1.5rem;">✦</span>
          </h1>
          <p class="profile-meta">@<?= htmlspecialchars($user['username']) ?> · Level <?= $user['level'] ?> · <?= $user['exp_points'] ?> EXP</p>

          <div style="margin-top:0.75rem;display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
            <button onclick="document.getElementById('editModal').classList.add('open')"
                    style="background:var(--bg-card);border:2px solid var(--border);border-radius:8px;padding:0.4rem 0.85rem;color:var(--text-muted);cursor:pointer;font-size:0.85rem;transition:border-color 0.2s;"
                    onmouseover="this.style.borderColor='var(--accent2)'" onmouseout="this.style.borderColor='var(--border)'">
              ••• Edit
            </button>
            <a href="profile.php?logout=1"
               style="background:rgba(239,68,68,0.1);border:2px solid rgba(239,68,68,0.3);border-radius:8px;padding:0.4rem 0.85rem;color:#fca5a5;cursor:pointer;font-size:0.85rem;text-decoration:none;">
              Log out
            </a>
          </div>
        </div>
      </div>

      <div class="profile-two-col">

        <!-- Left: About & Badges -->
        <div class="profile-left">
          <div class="about-box">
            <p class="about-title">// About</p>
            <p class="about-text">
              <?= $user['bio'] ? nl2br(htmlspecialchars($user['bio'])) : 'No bio yet — click Edit to add one!' ?>
            </p>

            <?php if (!empty($genres)): ?>
              <div style="margin-top:0.75rem;">
                <?php foreach ($genres as $g): ?>
                  <span class="genre-tag"><?= htmlspecialchars($g['name']) ?></span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <?php if (!empty($badges)): ?>
              <p class="about-title" style="margin-top:1rem;">// Badges</p>
              <div class="badges-row">
                <?php foreach ($badges as $b): ?>
                  <span class="badge-item" title="<?= htmlspecialchars($b['description'] ?? $b['name']) ?>">
                    <?= $b['emoji'] ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Right: Public Playlists -->
        <div class="profile-right">
          <p class="about-title" style="font-family:var(--pixel);font-size:0.6rem;color:var(--accent3);letter-spacing:3px;margin-bottom:1rem;">// Public Playlists</p>

          <?php if (empty($publicPls)): ?>
            <p style="color:var(--text-muted);font-size:0.9rem;">No public playlists yet.</p>
          <?php else: ?>
            <div class="playlist-grid">
              <?php $i = 0; foreach ($publicPls as $pl): ?>
                <a href="chill.php?playlist=<?= $pl['id'] ?>" class="playlist-card">
                  <div class="playlist-box" style="border-color:<?= htmlspecialchars($pl['cover_color']) ?>;">
                    <?= $boxEmojis[$i % count($boxEmojis)] ?>
                  </div>
                  <div class="playlist-name"><?= htmlspecialchars($pl['name']) ?></div>
                  <div class="playlist-count"><?= $pl['song_count'] ?> songs</div>
                </a>
              <?php $i++; endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Stats -->
          <div style="margin-top:2rem;display:flex;gap:1.5rem;flex-wrap:wrap;">
            <div style="background:var(--bg-card);border:2px solid var(--border);border-radius:var(--radius-sm);padding:1rem 1.5rem;text-align:center;">
              <div style="font-family:var(--pixel);font-size:1rem;color:var(--yellow);"><?= count($playlists) ?></div>
              <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.3rem;">Playlists</div>
            </div>
            <div style="background:var(--bg-card);border:2px solid var(--border);border-radius:var(--radius-sm);padding:1rem 1.5rem;text-align:center;">
              <div style="font-family:var(--pixel);font-size:1rem;color:var(--green);"><?= $user['exp_points'] ?></div>
              <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.3rem;">EXP</div>
            </div>
            <div style="background:var(--bg-card);border:2px solid var(--border);border-radius:var(--radius-sm);padding:1rem 1.5rem;text-align:center;">
              <div style="font-family:var(--pixel);font-size:1rem;color:var(--pink);"><?= count($badges) ?></div>
              <div style="font-size:0.8rem;color:var(--text-muted);margin-top:0.3rem;">Badges</div>
            </div>
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal" onclick="if(event.target===this)this.classList.remove('open')"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:200;align-items:center;justify-content:center;">
  <div style="background:var(--bg-card);border:3px solid var(--border);border-radius:var(--radius);padding:2rem;width:380px;max-width:90vw;">
    <p style="font-family:var(--pixel);font-size:0.6rem;color:var(--accent3);margin-bottom:1rem;">// EDIT PROFILE</p>
    <form method="POST" action="profile.php">
      <input type="hidden" name="update_bio" value="1">
      <label style="font-size:0.75rem;color:var(--text-muted);display:block;margin-bottom:0.4rem;">Bio</label>
      <textarea name="bio" class="edit-area" rows="4" placeholder="Tell the world about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
      <button type="submit" class="btn-primary">▶ SAVE</button>
    </form>
  </div>
</div>

<script>
// Make the modal-overlay use flex when open
document.getElementById('editModal').style.display = 'none';
document.querySelectorAll('.modal-overlay').forEach(m => {
  // override inline display style toggle
});

function openModal(id) {
  const el = document.getElementById(id);
  el.style.display = 'flex';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

// Wire up buttons
document.querySelectorAll('[onclick*="classList.add"]').forEach(btn => {
  btn.addEventListener('click', e => {
    const match = btn.getAttribute('onclick').match(/getElementById\('([^']+)'\)/);
    if (match) openModal(match[1]);
  });
  btn.removeAttribute('onclick');
});

document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => {
    if (e.target === m) m.style.display = 'none';
  });
});
</script>
</body>
</html>
