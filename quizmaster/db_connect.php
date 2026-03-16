<?php

function getQuizDb(): PDO
{
    $dbFile = __DIR__ . '/database.db';
    $dsn = 'sqlite:' . $dbFile;

    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec('CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, username TEXT, password TEXT, role TEXT, token TEXT)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS scores (id INTEGER PRIMARY KEY, username TEXT, score INTEGER, round TEXT)');
    $pdo->exec('CREATE TABLE IF NOT EXISTS flags (id INTEGER PRIMARY KEY, name TEXT, value TEXT)');

    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($userCount === 0) {
        $pdo->exec("INSERT INTO users (id, username, password, role, token) VALUES (1, 'admin', 'Adm!n@Vulnix#9', 'admin', 'tok_8f3a91bc')");
        $pdo->exec("INSERT INTO users (id, username, password, role, token) VALUES (2, 'player1', 'play123', 'user', 'tok_1a2b3c4d')");
        $pdo->exec("INSERT INTO users (id, username, password, role, token) VALUES (3, 'player2', 'qwerty99', 'user', 'tok_deadbeef')");
    }

    $scoreCount = (int) $pdo->query('SELECT COUNT(*) FROM scores')->fetchColumn();
    if ($scoreCount === 0) {
        $pdo->exec("INSERT INTO scores (id, username, score, round) VALUES (1, '0xDark', 9850, 'Final')");
        $pdo->exec("INSERT INTO scores (id, username, score, round) VALUES (2, 'n00bSlayer', 8720, 'Final')");
        $pdo->exec("INSERT INTO scores (id, username, score, round) VALUES (3, 'SQLpwner', 7640, 'Semi')");
        $pdo->exec("INSERT INTO scores (id, username, score, round) VALUES (4, 'xss_queen', 6300, 'Semi')");
        $pdo->exec("INSERT INTO scores (id, username, score, round) VALUES (5, 'bufferOvfl', 5100, 'Qual')");
    }

    $flagCount = (int) $pdo->query('SELECT COUNT(*) FROM flags')->fetchColumn();
    if ($flagCount === 0) {
        $pdo->exec("INSERT INTO flags (id, name, value) VALUES (1, 'admin_flag', 'VULNIX{5ql_1nj3c710n_m4573r}')");
    }

    return $pdo;
}
