<?php
session_start();
require_once __DIR__ . '/db_connect.php';

$pdo = getQuizDb();
$q = $_GET['q'] ?? '';
$rows = [];
$error = '';

if ($q !== '') {
    // Intentional vulnerability for CTF: direct interpolation into SQL.
    $sql = "SELECT username, score FROM scores WHERE username LIKE '%$q%'";
    try {
        $result = $pdo->query($sql);
        if ($result) {
            $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        $error = 'Search query failed.';
    }
}

function clipCell(string $value): string
{
    return substr($value, 0, 20);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Search - Vulnix Quiz</title>
  <style>
    body { background: #0d1117; color: #c9d1d9; font-family: 'Segoe UI', sans-serif; padding: 40px; }
    h1 { color: #58a6ff; margin-bottom: 16px; }
    input { width: 320px; max-width: 100%; padding: 10px; background: #161b22; border: 1px solid #30363d; color: #c9d1d9; }
    button { padding: 10px 14px; margin-left: 8px; background: #238636; color: #fff; border: none; }
    table { margin-top: 24px; width: 100%; max-width: 720px; border-collapse: collapse; }
    th, td { border-bottom: 1px solid #30363d; text-align: left; padding: 10px; }
    th { color: #8b949e; }
    .error { color: #f78166; margin-top: 12px; }
    a { color: #58a6ff; }
  </style>
</head>
<body>
  <h1>Score Search</h1>
  <p>Search by username to view score history.</p>

  <form method="GET" action="search.php">
    <input name="q" value="<?php echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search players" />
    <button type="submit">Search</button>
  </form>

  <?php if ($error): ?>
    <p class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
  <?php endif; ?>

  <?php if ($q !== ''): ?>
    <table>
      <tr><th>Username</th><th>Score</th></tr>
      <?php foreach ($rows as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars(clipCell((string) ($row['username'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
        <td><?php echo htmlspecialchars(clipCell((string) ($row['score'] ?? '')), ENT_QUOTES, 'UTF-8'); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>

  <p style="margin-top: 18px;"><a href="index.html">Home</a> | <a href="leaderboard.php">Leaderboard</a></p>
</body>
</html>
