<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
$user = require_login();
$db   = get_db();

$total_reports  = $db->query("SELECT COUNT(*) FROM reports")->fetchColumn();
$open_reports   = max(0, $total_reports - 2);
$triaged        = min(2, $total_reports);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard – BugBoard Analytics</title>
<link rel="stylesheet" href="/static/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
</head>
<body>

<?php include __DIR__ . '/includes/topbar.php'; ?>
<?php include __DIR__ . '/includes/sidebar.php'; ?>

<main class="main">
  <div class="page-header">
    <h1>Dashboard</h1>
    <p>Welcome back, <?= htmlspecialchars($user['email']) ?> · <?= ucfirst($user['role']) ?></p>
  </div>

  <!-- Stat cards -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="label">Total Reports</div>
      <div class="value"><?= $total_reports ?></div>
      <div class="delta up">↑ 12% this week</div>
    </div>
    <div class="stat-card">
      <div class="label">Open / Pending</div>
      <div class="value" style="color:var(--yellow)"><?= $open_reports ?></div>
      <div class="delta">Awaiting triage</div>
    </div>
    <div class="stat-card">
      <div class="label">Triaged</div>
      <div class="value" style="color:var(--green)"><?= $triaged ?></div>
      <div class="delta up">↑ 5% vs last week</div>
    </div>
    <div class="stat-card">
      <div class="label">Avg. CVSS</div>
      <div class="value" style="color:var(--orange)">6.4</div>
      <div class="delta down">↓ 0.3 this month</div>
    </div>
    <div class="stat-card">
      <div class="label">Mean Time to Triage</div>
      <div class="value">18h</div>
      <div class="delta up">↑ improved by 3h</div>
    </div>
  </div>

  <!-- Charts row -->
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;">
    <div class="card">
      <h2>Severity Breakdown</h2>
      <canvas id="sevChart" height="200"></canvas>
    </div>
    <div class="card">
      <h2>Reports Over Time (last 7 days)</h2>
      <canvas id="timeChart" height="200"></canvas>
    </div>
  </div>

  <!-- Recent reports table -->
  <div class="card">
    <h2>Recent Reports</h2>
    <table>
      <thead>
        <tr>
          <th>#</th><th>Title</th><th>Target</th><th>Severity</th><th>Status</th><th>Created</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $rows = $db->query("SELECT * FROM reports ORDER BY created_at DESC LIMIT 10")->fetchAll();
        $severities = ['critical','high','medium','low','info'];
        $statuses   = ['Open','Triaged','In Review'];
        foreach ($rows as $i => $r): ?>
        <tr>
          <td style="color:var(--muted)">#<?= $r['id'] ?></td>
          <td><a href="/reports.php"><?= htmlspecialchars($r['title']) ?></a></td>
          <td style="color:var(--muted); font-size:0.8rem"><?= htmlspecialchars($r['target_url']) ?></td>
          <td><span class="badge badge-<?= $severities[$i % 5] ?>"><?= ucfirst($severities[$i % 5]) ?></span></td>
          <td style="color:var(--muted)"><?= $statuses[$i % 3] ?></td>
          <td style="color:var(--muted); font-size:0.8rem"><?= $r['created_at'] ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="margin-top:12px;">
      <a href="/reports.php" class="btn btn-ghost" style="font-size:0.82rem;">View all reports →</a>
    </div>
  </div>

  <!-- SLA progress -->
  <div class="card">
    <h2>SLA Compliance</h2>
    <?php $sla_data = [
      ['Critical (24h)', 88], ['High (72h)', 94], ['Medium (7d)', 97], ['Low (30d)', 99]
    ]; foreach ($sla_data as [$label, $pct]): ?>
    <div style="margin-bottom:12px;">
      <div style="display:flex; justify-content:space-between; font-size:0.82rem; margin-bottom:4px;">
        <span><?= $label ?></span>
        <span style="color:var(--green)"><?= $pct ?>%</span>
      </div>
      <div class="progress-bar"><div class="progress-fill" style="width:<?= $pct ?>%"></div></div>
    </div>
    <?php endforeach; ?>
  </div>
</main>

<script>
// Severity doughnut chart
new Chart(document.getElementById('sevChart'), {
  type: 'doughnut',
  data: {
    labels: ['Critical','High','Medium','Low','Info'],
    datasets: [{
      data: [3, 8, 14, 22, 7],
      backgroundColor: ['#6e1a18','#4a2100','#3a2900','#0d2a12','#0c2044'],
      borderColor:     ['#f85149','#e3b341','#d29922','#3fb950','#58a6ff'],
      borderWidth: 2
    }]
  },
  options: { plugins: { legend: { labels: { color: '#8b949e', font: { size: 11 } } } } }
});

// Reports over time line chart
const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
new Chart(document.getElementById('timeChart'), {
  type: 'line',
  data: {
    labels: days,
    datasets: [{
      label: 'Reports submitted',
      data: [4, 7, 3, 9, 5, 2, 6],
      borderColor: '#58a6ff', backgroundColor: 'rgba(88,166,255,0.1)',
      tension: 0.4, fill: true, pointBackgroundColor: '#58a6ff'
    }]
  },
  options: {
    scales: {
      x: { ticks: { color: '#8b949e' }, grid: { color: '#30363d' } },
      y: { ticks: { color: '#8b949e' }, grid: { color: '#30363d' } }
    },
    plugins: { legend: { labels: { color: '#8b949e', font: { size: 11 } } } }
  }
});
</script>
</body>
</html>
