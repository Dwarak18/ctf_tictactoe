<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$pdo = getQuizDb();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    // Intentional vulnerability for CTF: no sanitization.
    $query = "SELECT * FROM users WHERE username='$u' AND password='$p'";
    $result = $pdo->query($query);
    $user = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;

    if ($user) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['id'] = $user['id'];

        if ($user['role'] === 'admin') {
            header('Location: admin.php');
        } else {
            header('Location: leaderboard.php');
        }
        exit;
    }

    $error = 'Invalid credentials. Please try again.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Vulnix Quiz</title>
  <style>
    body {
      background: #0d1117;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      color: #c9d1d9;
      padding: 20px;
    }
    .card {
      background: #161b22;
      border: 1px solid #30363d;
      border-radius: 10px;
      padding: 32px;
      width: 360px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
    }
    h2 {
      color: #58a6ff;
      margin-bottom: 8px;
      font-size: 1.5rem;
    }
    .subtitle {
      font-size: 0.85rem;
      color: #8b949e;
      margin-bottom: 20px;
    }
    label {
      display: block;
      font-size: 0.85rem;
      color: #8b949e;
      margin-bottom: 4px;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 10px 12px;
      background: #0d1117;
      border: 1px solid #30363d;
      border-radius: 6px;
      color: #c9d1d9;
      font-size: 0.95rem;
      margin-bottom: 14px;
      outline: none;
    }
    input:focus { border-color: #58a6ff; }
    button {
      width: 100%;
      padding: 11px;
      background: #238636;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
    }
    button:hover { background: #2ea043; }
    .error {
      background: #2d1b1b;
      border: 1px solid #f78166;
      color: #f78166;
      padding: 10px 12px;
      border-radius: 6px;
      font-size: 0.88rem;
      margin-bottom: 14px;
    }
    .footer-link {
      text-align: center;
      margin-top: 16px;
      font-size: 0.82rem;
      color: #484f58;
    }
    .footer-link a { color: #58a6ff; text-decoration: none; }
  </style>
</head>
<body>
  <div class="card">
    <h2>Player Login</h2>
    <p class="subtitle">Enter your credentials to access the quiz platform.</p>

    <?php if ($error): ?>
      <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" placeholder="Enter username" autocomplete="off"/>

      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Enter password"/>

      <button type="submit">Login</button>
    </form>

    <!-- dev-note: query built with string concat, see db_connect.php -->

    <div class="footer-link">
      <a href="leaderboard.php">View Leaderboard</a> &middot;
      <a href="search.php">Search</a> &middot;
      <a href="index.html">Home</a>
    </div>
  </div>
</body>
</html>
