<?php
session_start();
require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$pdo = getQuizDb();
$adminToken = (string) $pdo->query("SELECT token FROM users WHERE role='admin' LIMIT 1")->fetchColumn();

$flag = '';
$message = 'Retrieve your authorization token from the database to unlock the flag vault.';
$submittedToken = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['token'] ?? '';
    if ($submittedToken === $adminToken) {
        $flag = (string) $pdo->query("SELECT value FROM flags WHERE name='admin_flag' LIMIT 1")->fetchColumn();
        $message = 'Token accepted. Vault unlocked.';
    } else {
        $message = 'Invalid token. Access denied.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Panel - Vulnix</title>
  <style>
    body {
      background: #0d1117;
      font-family: 'Segoe UI', sans-serif;
      color: #c9d1d9;
      padding: 40px;
    }
    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      gap: 12px;
      flex-wrap: wrap;
    }
    h1 { color: #f0883e; font-size: 1.8rem; }
    .badge {
      background: #3d1a00;
      border: 1px solid #f0883e;
      color: #f0883e;
      padding: 5px 14px;
      border-radius: 20px;
      font-size: 0.8rem;
    }
    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 16px;
      margin-bottom: 24px;
    }
    .card {
      background: #161b22;
      border: 1px solid #30363d;
      border-radius: 10px;
      padding: 20px;
    }
    .card h3 { color: #58a6ff; margin-bottom: 8px; }
    .card p { color: #8b949e; font-size: 0.9rem; }
    .card .stat {
      font-size: 1.8rem;
      color: #3fb950;
      margin-top: 8px;
      font-weight: bold;
    }
    .flag-box {
      background: #0a1628;
      border: 2px solid #58a6ff;
      border-radius: 10px;
      padding: 24px;
      margin-top: 10px;
      max-width: 840px;
    }
    .flag-box h2 { color: #58a6ff; margin-bottom: 10px; }
    .message {
      color: #8b949e;
      margin-bottom: 14px;
      line-height: 1.4;
    }
    .flag {
      font-family: monospace;
      font-size: 1.1rem;
      color: #3fb950;
      background: #0d1117;
      border: 1px dashed #3fb950;
      padding: 12px 14px;
      border-radius: 6px;
      margin-top: 10px;
      word-break: break-all;
    }
    .token-form {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 10px;
    }
    .token-form input {
      flex: 1;
      min-width: 260px;
      padding: 10px;
      background: #0d1117;
      border: 1px solid #30363d;
      color: #c9d1d9;
      border-radius: 6px;
    }
    .token-form button {
      padding: 10px 14px;
      background: #238636;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
    }
    .warning {
      margin-top: 24px;
      background: #161b22;
      border-left: 4px solid #d29922;
      padding: 14px 16px;
      border-radius: 6px;
      font-size: 0.88rem;
      color: #8b949e;
      max-width: 840px;
    }
    .nav { margin-top: 20px; }
    .nav a { color: #58a6ff; text-decoration: none; margin-right: 10px; }
  </style>
</head>
<body>
  <div class="header">
    <h1>Admin Control Panel</h1>
    <span class="badge">ADMIN SESSION</span>
  </div>

  <div class="grid">
    <div class="card">
      <h3>Total Players</h3>
      <p>Registered participants</p>
      <div class="stat">142</div>
    </div>
    <div class="card">
      <h3>Quizzes Active</h3>
      <p>Currently live rounds</p>
      <div class="stat">3</div>
    </div>
    <div class="card">
      <h3>Top Score</h3>
      <p>Leaderboard leader</p>
      <div class="stat">9850</div>
    </div>
    <div class="card">
      <h3>Login Anomalies</h3>
      <p>Suspicious auth attempts</p>
      <div class="stat" style="color:#f85149;">1</div>
    </div>
  </div>

  <div class="flag-box">
    <h2>System Integrity Vault</h2>
    <p class="message"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>

    <form method="POST" class="token-form">
      <input type="text" name="token" value="<?php echo htmlspecialchars($submittedToken, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Enter admin authorization token" />
      <button type="submit">Unlock</button>
    </form>

    <?php if ($flag !== ''): ?>
      <div class="flag"><?php echo htmlspecialchars($flag, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
  </div>

  <div class="warning">
    <strong>Security Audit Log:</strong> Anomalous login detected at
    <?php echo date('Y-m-d H:i:s'); ?> from session ID
    <code><?php echo htmlspecialchars(session_id(), ENT_QUOTES, 'UTF-8'); ?></code>.
    Input contained SQL metacharacters. Review <code>login.php</code> immediately.
  </div>

  <div class="nav">
    <a href="leaderboard.php">Leaderboard</a>
    <a href="search.php">Search</a>
    <a href="profile.php?id=1">Profile</a>
    <a href="login.php">Back to login</a>
  </div>
</body>
</html>
