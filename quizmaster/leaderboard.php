<?php
$players = [
    ['rank' => 1, 'name' => '0xDark', 'score' => 9850, 'badge' => '1'],
    ['rank' => 2, 'name' => 'n00bSlayer', 'score' => 8720, 'badge' => '2'],
    ['rank' => 3, 'name' => 'SQLpwner', 'score' => 7640, 'badge' => '3'],
    ['rank' => 4, 'name' => 'xss_queen', 'score' => 6300, 'badge' => '4'],
    ['rank' => 5, 'name' => 'bufferOvfl', 'score' => 5100, 'badge' => '5'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Leaderboard - Vulnix Quiz</title>
  <style>
    body { background: #0d1117; color: #c9d1d9; font-family: 'Segoe UI', sans-serif; padding: 40px; }
    h1 { color: #f0883e; margin-bottom: 22px; }
    table { width: 100%; border-collapse: collapse; max-width: 640px; }
    th { background: #161b22; color: #8b949e; padding: 10px 14px; text-align: left; border-bottom: 1px solid #30363d; font-size: 0.85rem; }
    td { padding: 12px 14px; border-bottom: 1px solid #21262d; }
    tr:hover td { background: #161b22; }
    .rank1 td { color: #f0883e; font-weight: bold; }
    .back { color: #58a6ff; text-decoration: none; font-size: 0.9rem; }
  </style>
</head>
<body>
  <h1>Leaderboard</h1>
  <table>
    <tr><th>#</th><th>Player</th><th>Score</th></tr>
    <?php foreach ($players as $p): ?>
    <tr class="<?php echo $p['rank'] === 1 ? 'rank1' : ''; ?>">
      <td><?php echo htmlspecialchars((string) $p['badge'], ENT_QUOTES, 'UTF-8'); ?></td>
      <td><?php echo htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8'); ?></td>
      <td><?php echo number_format($p['score']); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <br/>
  <a class="back" href="login.php">Login to Play</a>
</body>
</html>
