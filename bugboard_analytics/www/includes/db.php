<?php
define('DB_PATH', '/var/www/data/database.db');

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $needs_init = !file_exists(DB_PATH);
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        if ($needs_init) {
            init_db($pdo);
        }
    }
    return $pdo;
}

function init_db(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'tester'
        );
        CREATE TABLE IF NOT EXISTS reports (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            target_url TEXT NOT NULL,
            notes TEXT DEFAULT '',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    $stmt = $pdo->prepare(
        "INSERT OR IGNORE INTO users (email, password_hash, role) VALUES (?, ?, ?)"
    );
    $stmt->execute(['tester@bugboard.local',  password_hash('test1234',           PASSWORD_DEFAULT), 'tester']);
    $stmt->execute(['admin@bugboard.local',   password_hash('Adm1n@BugBoard2024!', PASSWORD_DEFAULT), 'admin']);

    // Seed a couple of demo reports so the dashboard looks populated
    $pdo->exec("
        INSERT OR IGNORE INTO reports (id, user_id, title, target_url, notes) VALUES
        (1, 1, 'Reflected XSS in /search', 'https://target.example.com/search', '<p>Found a reflected XSS via the <code>q</code> parameter.</p>'),
        (2, 1, 'Open Redirect via returnUrl', 'https://target.example.com/login?returnUrl=', '<p>The <em>returnUrl</em> parameter accepts arbitrary external URLs.</p>');
    ");
}
