# 🎮 PixelMusic — Setup Guide

A retro pixel-art music streaming app with PHP + MySQL backend.

---

## 📁 File Structure

```
pixelmusic/
├── index.php          ← Home page
├── discover.php       ← Discover / search page
├── chill.php          ← Playlists & song view
├── profile.php        ← User profile page
├── login.php          ← Login page
├── signup.php         ← Sign up (with genre preferences)
├── schema.sql         ← Database schema + seed data
├── css/
│   └── style.css      ← All styles
└── includes/
    ├── db.php         ← PDO database connection
    ├── auth.php       ← Auth helpers & DB queries
    └── nav.php        ← Shared navigation partial
```

---

## ⚡ Quick Setup

### 1. Requirements
- PHP 8.0+
- MySQL 5.7+ or MariaDB
- A web server (Apache/Nginx) or just `php -S localhost:8000`

### 2. Create the Database

Open **phpMyAdmin** (or MySQL CLI) and run:

```sql
SOURCE /path/to/pixelmusic/schema.sql;
```

Or via CLI:
```bash
mysql -u root -p < schema.sql
```

### 3. Configure DB Credentials

Edit `includes/db.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');       // your MySQL username
define('DB_PASS', '');           // your MySQL password
define('DB_NAME', 'pixelmusic');
```

### 4. Run the App

**Option A — PHP built-in server (quickest):**
```bash
cd pixelmusic
php -S localhost:8000
```
Then open: http://localhost:8000

**Option B — XAMPP / WAMP / Laragon:**
- Copy the `pixelmusic/` folder into your `htdocs/` (XAMPP) or `www/` (WAMP)
- Open: http://localhost/pixelmusic/

---

## 🎮 Pages

| Page | URL | Description |
|------|-----|-------------|
| Home | `index.php` | Hero, now-playing card, song grid |
| Discover | `discover.php` | Search songs/artists, genre filter |
| Chill | `chill.php` | Playlists, playlist songs + mini player |
| Profile | `profile.php` | User profile, badges, public playlists |
| Sign Up | `signup.php` | Register with genre preferences |
| Log In | `login.php` | Log in |

---

## 🗄️ Database Tables

| Table | Purpose |
|-------|---------|
| `users` | User accounts, EXP, level, avatar color |
| `genres` | Music genres |
| `user_genres` | User ↔ genre preferences |
| `artists` | Artist records |
| `songs` | Song catalogue |
| `playlists` | User playlists |
| `playlist_songs` | Songs in playlists |
| `liked_songs` | Liked/hearted songs |
| `recently_played` | Play history |
| `badges` | Available badges |
| `user_badges` | Badges earned by users |

---

## ✨ Features

- **Sign up** with display name, username, email, password
- **Genre preferences** — pick from 14 preset genres or type your own
- **Pixel-art retro UI** matching the prototype (dark navy, Press Start 2P font)
- **Scrolling ticker** in the navbar
- **Discover page** — search songs & filter by genre
- **Chill page** — view/create playlists, song list with mini player
- **Profile page** — bio, badges, public playlists, stats
- **Level & EXP system** displayed on the home hero card
- **Discord connected** badge indicator
- Sessions via PHP `session_start()`
- Passwords hashed with `password_hash()` (bcrypt)
- Prepared statements (PDO) — safe from SQL injection
