<?php
require_once __DIR__ . '/includes/auth.php';
$user = require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Import XML – BugBoard Analytics</title>
<link rel="stylesheet" href="/static/style.css">
</head>
<body>

<?php include __DIR__ . '/includes/topbar.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Import Bug Reports</h1>
    <p>Migrate your existing reports from legacy XML exports. Supports our standard <code>&lt;bugreport&gt;</code> XML format.</p>
  </div>

  <div class="card" style="max-width:760px;">
    <h2>📥 XML Import (Beta)</h2>
    <p style="font-size:0.85rem; color:var(--muted); margin-bottom:16px;">
      Paste XML content below. The importer will validate and preview the report before storing it.
    </p>

    <div class="alert alert-info">
      <strong>Tip:</strong> Expected format:
      <pre style="margin-top:8px; margin-bottom:0;">&lt;bugreport&gt;
  &lt;title&gt;Reflected XSS in /search&lt;/title&gt;
  &lt;severity&gt;high&lt;/severity&gt;
  &lt;description&gt;Steps to reproduce...&lt;/description&gt;
&lt;/bugreport&gt;</pre>
    </div>

    <div class="form-group" style="margin-top:16px;">
      <label for="xml-input">XML Content</label>
      <textarea id="xml-input" rows="14" placeholder="Paste XML here...">&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;bugreport&gt;
  &lt;title&gt;SQL Injection in /api/user&lt;/title&gt;
  &lt;severity&gt;critical&lt;/severity&gt;
  &lt;description&gt;The id parameter is not sanitised. UNION-based injection confirmed.&lt;/description&gt;
&lt;/bugreport&gt;</textarea>
    </div>

    <button class="btn btn-primary" onclick="sendXML()">
      ✅ Validate &amp; Preview Import
    </button>

    <div id="xml-result">
      <div style="font-size:0.75rem; color:var(--muted); margin-bottom:8px;">RAW JSON RESPONSE</div>
      <code id="raw-json"></code>
      <div id="xml-preview">
        <div class="preview-label">Title</div>
        <div class="preview-value" id="prev-title"></div>
        <div class="preview-label" style="margin-top:10px;">Description Preview</div>
        <div class="preview-value" id="prev-desc"></div>
      </div>
    </div>
  </div>

  <div class="card" style="max-width:760px;">
    <h2>📖 Format Documentation</h2>
    <p style="font-size:0.85rem; color:var(--muted); margin-bottom:12px;">Full list of supported elements:</p>
    <table>
      <thead><tr><th>Element</th><th>Required</th><th>Description</th></tr></thead>
      <tbody>
        <tr><td><code>&lt;title&gt;</code></td><td>✅ Yes</td><td>Short bug title (max 200 chars)</td></tr>
        <tr><td><code>&lt;severity&gt;</code></td><td>No</td><td>critical / high / medium / low / info</td></tr>
        <tr><td><code>&lt;description&gt;</code></td><td>No</td><td>Full description (first 120 chars previewed)</td></tr>
        <tr><td><code>&lt;target&gt;</code></td><td>No</td><td>Affected URL or asset</td></tr>
      </tbody>
    </table>
  </div>
</main>

<script>
async function sendXML() {
  const xml = document.getElementById('xml-input').value.trim();
  if (!xml) { alert('Please paste some XML first.'); return; }

  const resultBox = document.getElementById('xml-result');
  resultBox.style.display = 'none';

  try {
    const resp = await fetch('/api/import_xml.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/xml' },
      body: xml
    });
    const data = await resp.json();

    document.getElementById('raw-json').textContent = JSON.stringify(data, null, 2);
    document.getElementById('prev-title').textContent = data.parsedTitle  || '(empty)';
    document.getElementById('prev-desc').textContent  = data.parsedDesc   || '(empty)';
    resultBox.style.display = 'block';
  } catch (e) {
    document.getElementById('raw-json').textContent = 'Error: ' + e.message;
    document.getElementById('prev-title').textContent = '';
    document.getElementById('prev-desc').textContent  = '';
    resultBox.style.display = 'block';
  }
}
</script>
</body>
</html>
