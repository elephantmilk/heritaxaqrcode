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
            description TEXT DEFAULT '',
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

    // Migrate: add description column if missing
    $cols = $db->query("PRAGMA table_info(qr_codes)")->fetchAll();
    $colNames = array_column($cols, 'name');
    if (!in_array('description', $colNames)) {
        $db->exec("ALTER TABLE qr_codes ADD COLUMN description TEXT DEFAULT ''");
    }

    // Migrate: add gradient columns if missing
    $gradientCols = [
        'dot_gradient_enabled' => 'INTEGER DEFAULT 0',
        'dot_gradient_type' => "TEXT DEFAULT 'linear'",
        'dot_gradient_rotation' => 'REAL DEFAULT 0',
        'dot_gradient_color1' => "TEXT DEFAULT '#000000'",
        'dot_gradient_color2' => "TEXT DEFAULT '#888888'",
    ];
    foreach ($gradientCols as $col => $def) {
        if (!in_array($col, $colNames)) {
            $db->exec("ALTER TABLE qr_codes ADD COLUMN {$col} {$def}");
        }
    }

    $db->exec("
        CREATE TABLE IF NOT EXISTS qr_presets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            name TEXT NOT NULL,
            settings TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_qr_presets_user_id ON qr_presets(user_id)");

    // Seed admin user if no users exist
    $count = $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count == 0) {
        $adminUser = getenv('ADMIN_USER') ?: 'admin';
        $adminPass = getenv('ADMIN_PASS') ?: 'admin';
        $hash = password_hash($adminPass, PASSWORD_BCRYPT);
        $db->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)')
           ->execute([$adminUser, $hash]);
    }

    return $db;
}
