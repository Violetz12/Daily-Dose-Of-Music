<?php
// includes/auth.php

require_once __DIR__ . '/db.php';

session_start();

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getCurrentUser(): ?array {
    if (!isLoggedIn()) return null;
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function loginUser(string $email, string $password): bool {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    return false;
}

function registerUser(string $username, string $displayName, string $email, string $password, array $genreIds, string $customGenre = ''): array {
    $pdo = getDB();

    // Check unique
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
    $stmt->execute([strtolower(trim($email)), strtolower(trim($username))]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email or username already taken.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $colors = ['#6366f1','#ec4899','#06b6d4','#10b981','#f59e0b','#ef4444'];
    $color  = $colors[array_rand($colors)];

    $stmt = $pdo->prepare("INSERT INTO users (username, display_name, email, password_hash, avatar_color, exp_points, level) VALUES (?,?,?,?,?,10,1)");
    $stmt->execute([strtolower(trim($username)), trim($displayName), strtolower(trim($email)), $hash, $color]);
    $userId = (int)$pdo->lastInsertId();

    // Save genre preferences
    if (!empty($genreIds)) {
        $ins = $pdo->prepare("INSERT IGNORE INTO user_genres (user_id, genre_id) VALUES (?,?)");
        foreach ($genreIds as $gid) {
            $ins->execute([$userId, (int)$gid]);
        }
    }

    // Handle custom genre
    if (!empty(trim($customGenre))) {
        $names = array_map('trim', explode(',', $customGenre));
        foreach ($names as $gname) {
            if (!$gname) continue;
            $pdo->prepare("INSERT IGNORE INTO genres (name) VALUES (?)")->execute([$gname]);
            $gRow = $pdo->prepare("SELECT id FROM genres WHERE name = ?");
            $gRow->execute([$gname]);
            $gid = $gRow->fetchColumn();
            if ($gid) {
                $pdo->prepare("INSERT IGNORE INTO user_genres (user_id, genre_id) VALUES (?,?)")->execute([$userId, $gid]);
            }
        }
    }

    // Give early bird badge
    $badge = $pdo->prepare("SELECT id FROM badges WHERE name = 'Early Bird'");
    $badge->execute();
    $bid = $badge->fetchColumn();
    if ($bid) {
        $pdo->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?,?)")->execute([$userId, $bid]);
    }

    // Create default Liked Songs playlist
    $pdo->prepare("INSERT INTO playlists (user_id, name, is_public, cover_color) VALUES (?,?,0,'#6366f1')")->execute([$userId, 'Liked Songs']);

    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = strtolower(trim($username));
    return ['success' => true];
}

function getUserPlaylists(int $userId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT p.*, COUNT(ps.song_id) as song_count
        FROM playlists p
        LEFT JOIN playlist_songs ps ON p.id = ps.playlist_id
        WHERE p.user_id = ?
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getPlaylistSongs(int $playlistId): array {
    $pdo = getDB();
    $stmt = $pdo->prepare("
        SELECT s.*, a.name as artist_name, ps.position
        FROM playlist_songs ps
        JOIN songs s ON s.id = ps.song_id
        JOIN artists a ON a.id = s.artist_id
        WHERE ps.playlist_id = ?
        ORDER BY ps.position
    ");
    $stmt->execute([$playlistId]);
    return $stmt->fetchAll();
}

function getAllGenres(): array {
    return getDB()->query("SELECT * FROM genres ORDER BY name")->fetchAll();
}

function getUserGenres(int $userId): array {
    $stmt = getDB()->prepare("SELECT g.* FROM genres g JOIN user_genres ug ON g.id = ug.genre_id WHERE ug.user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getUserBadges(int $userId): array {
    $stmt = getDB()->prepare("SELECT b.* FROM badges b JOIN user_badges ub ON b.id = ub.badge_id WHERE ub.user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getRecentSongs(): array {
    return getDB()->query("SELECT s.*, a.name as artist_name FROM songs s JOIN artists a ON a.id = s.artist_id ORDER BY s.id DESC LIMIT 8")->fetchAll();
}

function logoutUser(): void {
    session_destroy();
    header('Location: login.php');
    exit;
}
