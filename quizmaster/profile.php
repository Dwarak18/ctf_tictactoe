<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$pdo = getQuizDb();
$id = $_GET['id'] ?? '1';
$user = null;

// Intentional vulnerability for CTF: unsanitized numeric parameter.
$sql = "SELECT username, role FROM users WHERE id=$id";
try {
    $result = $pdo->query($sql);
    if ($result) {
        $user = $result->fetch(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {
    $user = null;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Profile - Vulnix Quiz</title>
  <style>
    body { background: #0d1117; color: #c9d1d9; font-family: 'Segoe UI', sans-serif; padding: 40px; }
    .card { background: #161b22; border: 1px solid #30363d; border-radius: 8px; padding: 24px; max-width: 520px; }
    h1 { color: #f0883e; margin-bottom: 14px; }
    a { color: #58a6ff; }
  </style>
</head>
<body>
  <div class="card">
    <h1>Player Profile</h1>
    <?php if ($user): ?>
      <h2>Player: <?php echo htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8'); ?></h2>
      <p>Profile visibility is limited.</p>
    <?php else: ?>
      <h2>Player not found</h2>
    <?php endif; ?>
    <p><a href="leaderboard.php">Back to leaderboard</a></p>
  </div>
</body>
</html>
