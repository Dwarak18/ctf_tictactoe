<?php
/**
 * /loot.php  –  View all data collected by /collect.php
 *
 * Check here after the XSS fires to see the admin's stolen cookie.
 */

$loot_file = '/var/www/data/loot.json';
$entries   = [];

if (file_exists($loot_file)) {
    foreach (file($loot_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $e = json_decode($line, true);
        if ($e) $entries[] = $e;
    }
}
$entries = array_reverse($entries); // newest first
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Loot – BugBoard Analytics Attacker View</title>
<link rel="stylesheet" href="/static/style.css">
<style>
  body { background: var(--bg); }
  .loot-wrap { max-width: 860px; margin: 40px auto; padding: 0 20px; }
  h1 { font-size: 1.4rem; margin-bottom: 8px; }
  .subtitle { color: var(--muted); font-size: 0.88rem; margin-bottom: 24px; }
  .refresh { font-size: 0.82rem; }
</style>
</head>
<body>
<div class="loot-wrap">
  <h1>💀 Exfil Loot</h1>
  <p class="subtitle">
    Data received at <code>/collect</code> · <?= count($entries) ?> entr<?= count($entries) === 1 ? 'y' : 'ies' ?>
    &nbsp;·&nbsp; <a class="refresh" href="/loot.php">🔄 Refresh</a>
    &nbsp;·&nbsp; <a class="refresh" href="/dashboard.php">← Back to app</a>
  </p>

  <?php if (empty($entries)): ?>
  <div class="card" style="text-align:center; padding:40px; color:var(--muted);">
    <div style="font-size:2rem; margin-bottom:10px;">🎣</div>
    <p>Nothing caught yet. Set your XSS bait and wait for the admin bot to bite.</p>
  </div>
  <?php else: ?>

  <div class="alert alert-info" style="margin-bottom:20px;">
    Use your XSS payload to POST to <code><?= htmlspecialchars('http://CHALLENGE_IP:8080/collect?c='+urlencode('...')) ?></code>
    or simply <code>/collect?c=...</code> (same origin works perfectly).
  </div>

  <?php foreach ($entries as $e): ?>
  <div class="loot-entry">
    <div class="loot-time">
      🕐 <?= htmlspecialchars($e['time'] ?? '') ?>
      &nbsp;·&nbsp; 🌐 <?= htmlspecialchars($e['ip'] ?? '') ?>
      <?php if (!empty($e['ua'])): ?>
      &nbsp;·&nbsp; 🤖 <span style="font-size:0.75rem; color:var(--muted)"><?= htmlspecialchars(substr($e['ua'],0,80)) ?></span>
      <?php endif; ?>
    </div>
    <div class="loot-data"><?= htmlspecialchars($e['data'] ?? '') ?></div>
  </div>
  <?php endforeach; ?>

  <?php endif; ?>
</div>
</body>
</html>
