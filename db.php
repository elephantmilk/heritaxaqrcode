<?php
function getDB(): PDO {
    static $db = null;
    if ($db !== null) return $db;

    $db = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS qr_codes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            short_code TEXT NOT NULL UNIQUE,
            target_url TEXT NOT NULL,
            title TEXT DEFAULT '',
            dot_style TEXT DEFAULT 'square',
            dot_color TEXT DEFAULT '#ffffff',
            bg_color TEXT DEFAULT '#0a0a0f',
            corner_square_style TEXT DEFAULT 'square',
            corner_square_color TEXT DEFAULT '#ffffff',
            corner_dot_style TEXT DEFAULT 'square',
            corner_dot_color TEXT DEFAULT '#ffffff',
            logo_data TEXT DEFAULT NULL,
            logo_size REAL DEFAULT 0.4,
            scan_count INTEGER DEFAULT 0,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");

    $db->exec("CREATE INDEX IF NOT EXISTS idx_qr_codes_short_code ON qr_codes(short_code)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_qr_codes_user_id ON qr_codes(user_id)");

    // Seed admin user if no users exist
    $count = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count == 0) {
        $hash = password_hash('admin', PASSWORD_BCRYPT);
        $db->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)')
           ->execute(['admin', $hash]);
    }

    return $db;
}
