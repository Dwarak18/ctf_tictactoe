<?php
/**
 * /reports.php
 *
 * Lists all bug reports and renders the Notes field as raw HTML.
 *
 * INTENTIONAL VULNERABILITY: Stored XSS (CWE-79)
 *   - Title and Target URL are properly HTML-escaped.
 *   - Notes undergo a naive "sanitisation" that only strips <script> tags.
 *   - Event-handler attributes (onerror, onload, onfocus, etc.) are NOT removed.
 *   - An attacker can inject:
 *       <img src=x onerror="fetch('/collect?c='+encodeURIComponent(document.cookie))">
 *     and when the admin triager bot loads this page their FLAG_XSS cookie is exfiltrated.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
$user = require_login();
$db   = get_db();

$reports = $db->query("SELECT * FROM reports ORDER BY created_at DESC")->fetchAll();

/**
 * "Sanitise" notes – naive implementation that only strips <script> tags.
 * Event handlers (onerror, onload, etc.) pass through unchanged.
 */
function sanitize_notes(string $notes): string {
    // Only strips <script>...</script> blocks – trivially bypassed
    return preg_replace('/<\s*script\b[^>]*>[\s\S]*?<\/\s*script\s*>/i', '', $notes);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reports – BugBoard Analytics</title>
<link rel="stylesheet" href="/static/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/topbar.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header" style="display:flex; align-items:center; justify-content:space-between;">
    <div>
      <h1>Bug Reports</h1>
      <p><?= count($reports) ?> report<?= count($reports) !== 1 ? 's' : '' ?> in program</p>
    </div>
    <a href="/new_report.php" class="btn btn-success">➕ New Report</a>
  </div>

  <?php if (empty($reports)): ?>
  <div class="card" style="text-align:center; padding:48px; color:var(--muted);">
    <div style="font-size:2rem; margin-bottom:12px;">📋</div>
    <p>No reports yet. <a href="/new_report.php">Submit the first one →</a></p>
  </div>
  <?php else: ?>

  <?php foreach ($reports as $r): ?>
  <div class="card" style="margin-bottom:16px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
      <div>
        <!-- Title and URL are safely HTML-escaped -->
        <h3 style="font-size:1rem; font-weight:600;"><?= htmlspecialchars($r['title']) ?></h3>
        <div style="font-size:0.8rem; color:var(--muted); margin-top:4px;">
          🎯 <?= htmlspecialchars($r['target_url']) ?>
          &nbsp;·&nbsp; 📅 <?= htmlspecialchars($r['created_at']) ?>
        </div>
      </div>
      <span class="badge badge-medium">Medium</span>
    </div>

    <!-- Notes are rendered as raw HTML after naive sanitisation – XSS SINK -->
    <div class="report-notes">
      <?= sanitize_notes($r['notes']) ?>
    </div>
  </div>
  <?php endforeach; ?>

  <?php endif; ?>
</main>
</body>
</html>
