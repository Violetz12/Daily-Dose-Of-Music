-- PixelMusic Database Schema
-- Run this file to set up the database

CREATE DATABASE IF NOT EXISTS pixelmusic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pixelmusic;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    avatar_color VARCHAR(7) DEFAULT '#6366f1',
    bio TEXT DEFAULT NULL,
    exp_points INT DEFAULT 0,
    level INT DEFAULT 1,
    discord_connected TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Genre preferences (many-to-many)
CREATE TABLE IF NOT EXISTS genres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#6366f1'
);

CREATE TABLE IF NOT EXISTS user_genres (
    user_id INT NOT NULL,
    genre_id INT NOT NULL,
    PRIMARY KEY (user_id, genre_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (genre_id) REFERENCES genres(id) ON DELETE CASCADE
);

-- Artists table
CREATE TABLE IF NOT EXISTS artists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    genre VARCHAR(50),
    avatar_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Songs table
CREATE TABLE IF NOT EXISTS songs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    artist_id INT NOT NULL,
    album VARCHAR(200),
    cover_url VARCHAR(255),
    duration_seconds INT DEFAULT 0,
    play_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (artist_id) REFERENCES artists(id) ON DELETE CASCADE
);

-- Playlists table
CREATE TABLE IF NOT EXISTS playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_public TINYINT(1) DEFAULT 0,
    cover_color VARCHAR(7) DEFAULT '#4f46e5',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Playlist songs (junction)
CREATE TABLE IF NOT EXISTS playlist_songs (
    playlist_id INT NOT NULL,
    song_id INT NOT NULL,
    position INT DEFAULT 0,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (playlist_id, song_id),
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);

-- Liked songs (special playlist per user)
CREATE TABLE IF NOT EXISTS liked_songs (
    user_id INT NOT NULL,
    song_id INT NOT NULL,
    liked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, song_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);

-- Recently played
CREATE TABLE IF NOT EXISTS recently_played (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    song_id INT NOT NULL,
    played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE
);

-- Badges
CREATE TABLE IF NOT EXISTS badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    emoji VARCHAR(10) NOT NULL,
    description VARCHAR(200)
);

CREATE TABLE IF NOT EXISTS user_badges (
    user_id INT NOT NULL,
    badge_id INT NOT NULL,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (badge_id) REFERENCES badges(id) ON DELETE CASCADE
);

-- Seed genres
INSERT IGNORE INTO genres (name, color) VALUES
('Pop', '#f472b6'),
('Rock', '#f87171'),
('Hip-Hop', '#fb923c'),
('R&B', '#a78bfa'),
('Electronic', '#34d399'),
('Jazz', '#60a5fa'),
('Classical', '#fbbf24'),
('K-Pop', '#f9a8d4'),
('Indie', '#86efac'),
('Metal', '#94a3b8'),
('Country', '#fcd34d'),
('Lo-Fi', '#93c5fd'),
('Reggae', '#4ade80'),
('Latin', '#fb7185');

-- Seed badges
INSERT IGNORE INTO badges (name, emoji, description) VALUES
('Early Bird', '🎨', 'One of the first users to join'),
('Playlist Master', '🌴', 'Created 5+ playlists'),
('Music Lover', '🎵', 'Liked 50+ songs'),
('Explorer', '🔭', 'Discovered 10+ new artists'),
('Social Butterfly', '🦋', 'Connected Discord');

-- Seed artists
INSERT IGNORE INTO artists (name, genre) VALUES
('Taylor Swift', 'Pop'),
('Bruno Mars', 'Pop'),
('BLACKPINK', 'K-Pop'),
('LISA', 'K-Pop'),
('Stray Kids', 'K-Pop'),
('Lady Gaga', 'Pop'),
('Rosé', 'K-Pop'),
('Bon Iver', 'Indie');

-- Seed songs
INSERT IGNORE INTO songs (title, artist_id, album, duration_seconds) VALUES
('APT', 2, 'Collab', 195),
('Die with a Smile', 2, 'Single', 251),
('Shake It Off', 1, '1989', 219),
('Anti-Hero', 1, 'Midnights', 200),
('Pink Venom', 3, 'Born Pink', 173),
('MONEY', 4, 'LALISA', 183),
('God\'s Menu', 5, 'GO生', 220),
('Bloody Mary', 6, 'Chromatica', 211);
