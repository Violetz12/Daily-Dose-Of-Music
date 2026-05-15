<?php
// signup.php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username    = trim($_POST['username'] ?? '');
    $displayName = trim($_POST['display_name'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $password    = $_POST['password'] ?? '';
    $confirm     = $_POST['confirm_password'] ?? '';
    $genreIds    = $_POST['genres'] ?? [];
    $customGenre = trim($_POST['custom_genre'] ?? '');

    if (!$username || !$displayName || !$email || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (empty($genreIds) && empty($customGenre)) {
        $error = 'Please select at least one music genre.';
    } else {
        $result = registerUser($username, $displayName, $email, $password, $genreIds, $customGenre);
        if ($result['success']) {
            header('Location: index.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

$genres = getAllGenres();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PixelMusic — Sign Up</title>
<link rel="stylesheet" href="css/style.css">
<style>
  .auth-card { max-width: 540px; }
  .step-title {
    font-family: var(--pixel);
    font-size: 0.55rem;
    color: var(--yellow);
    letter-spacing: 2px;
    margin: 1.5rem 0 0.75rem;
    text-transform: uppercase;
  }
  .custom-genre-wrap { margin-top: 0.75rem; }
  .custom-genre-wrap input {
    width: 100%;
    background: var(--bg-deep);
    border: 2px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 0.6rem 0.9rem;
    color: var(--text);
    font-family: var(--body);
    font-size: 0.875rem;
    outline: none;
    transition: border-color 0.2s;
  }
  .custom-genre-wrap input:focus { border-color: var(--accent2); }
  .custom-genre-wrap label {
    display: block;
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0.4rem;
  }
  .stars { text-align: center; font-size: 1.5rem; margin-bottom: 0.5rem; }
</style>
</head>
<body>
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="stars">🎵 ⭐ 🎶</div>
    <div class="auth-logo">PIXEL MUSIC</div>
    <p class="auth-subtitle">Create your account &amp; tune in ✨</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="signup.php">

      <p class="step-title">// 01 — Your Identity</p>

      <div class="form-group">
        <label>Display Name *</label>
        <input type="text" name="display_name" placeholder="Jane Doe" value="<?= htmlspecialchars($_POST['display_name'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>Username *</label>
        <input type="text" name="username" placeholder="janedoe" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>Email *</label>
        <input type="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label>Password * <span style="color:var(--text-muted);font-family:var(--body);letter-spacing:0;">(min 6 chars)</span></label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>

      <div class="form-group">
        <label>Confirm Password *</label>
        <input type="password" name="confirm_password" placeholder="••••••••" required>
      </div>

      <p class="step-title">// 02 — Your Sound</p>
      <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:0.75rem;">Pick your favourite genres (choose as many as you like)</p>

      <div class="genre-grid">
        <?php foreach ($genres as $genre): ?>
          <div class="genre-chip">
            <input
              type="checkbox"
              name="genres[]"
              id="genre_<?= $genre['id'] ?>"
              value="<?= $genre['id'] ?>"
              <?= in_array($genre['id'], (array)($_POST['genres'] ?? [])) ? 'checked' : '' ?>
            >
            <label for="genre_<?= $genre['id'] ?>"><?= htmlspecialchars($genre['name']) ?></label>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="custom-genre-wrap">
        <label>Other genres? Type them here (comma-separated)</label>
        <input type="text" name="custom_genre" placeholder="e.g. Bossa Nova, Afrobeat, Shoegaze" value="<?= htmlspecialchars($_POST['custom_genre'] ?? '') ?>">
      </div>

      <button type="submit" class="btn-primary" style="margin-top:1.75rem;">▶ CREATE ACCOUNT</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="login.php">Log in →</a></p>
  </div>
</div>
</body>
</html>
