<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
$user = require_login();
$db   = get_db();

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['title']      ?? '');
    $target_url = trim($_POST['target_url'] ?? '');
    $notes      = $_POST['notes']           ?? '';   // stored raw (no escaping on input)

    if (!$title || !$target_url) {
        $error = 'Title and Target URL are required.';
    } else {
        $stmt = $db->prepare(
            "INSERT INTO reports (user_id, title, target_url, notes) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$user['id'], $title, $target_url, $notes]);
        $success = 'Report submitted! Our triage bot will review it shortly.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Report – BugBoard Analytics</title>
<link rel="stylesheet" href="/static/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/topbar.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Submit New Bug Report</h1>
    <p>Use this form to submit a new vulnerability report to the program.</p>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="alert alert-success">
    <?= htmlspecialchars($success) ?>
    <a href="/reports.php">View all reports →</a>
  </div>
  <?php endif; ?>

  <div class="card" style="max-width:720px;">
    <h2>📝 Report Details</h2>
    <form method="POST" action="/new_report.php">

      <div class="form-group">
        <label for="title">Bug Title *</label>
        <input type="text" id="title" name="title" maxlength="200"
               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
               placeholder="e.g. Reflected XSS in /search via q parameter" required>
      </div>

      <div class="form-group">
        <label for="target_url">Target URL *</label>
        <input type="text" id="target_url" name="target_url"
               value="<?= htmlspecialchars($_POST['target_url'] ?? '') ?>"
               placeholder="https://target.example.com/vulnerable-endpoint" required>
      </div>

      <div class="form-group">
        <label for="sev">Severity</label>
        <select id="sev" name="severity">
          <option>Critical</option>
          <option>High</option>
          <option selected>Medium</option>
          <option>Low</option>
          <option>Informational</option>
        </select>
      </div>

      <div class="form-group">
        <label for="notes">Notes / PoC
          <span style="font-weight:400; color:var(--muted);">(Markdown/HTML allowed for formatting)</span>
        </label>
        <textarea id="notes" name="notes" rows="10"
                  placeholder="Describe steps to reproduce, impact, and attach PoC code or screenshots...
&#10;HTML is supported, e.g.:
&#10;<b>Affected parameter:</b> <code>q</code>
&#10;<img src=&quot;https://example.com/screenshot.png&quot; alt=&quot;PoC&quot;>"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
        <div class="form-hint">⚠️ HTML is rendered for rich PoC documentation. Sanitized for safety.</div>
      </div>

      <div style="display:flex; gap:12px; align-items:center;">
        <button type="submit" class="btn btn-success">🚀 Submit Report</button>
        <a href="/reports.php" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</main>
</body>
</html>
