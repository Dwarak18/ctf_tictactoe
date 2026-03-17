<?php $current = basename($_SERVER['PHP_SELF']); ?>
<nav class="sidebar">
  <div class="sidebar-section">Overview</div>
  <a href="/dashboard.php" class="<?= $current==='dashboard.php'?'active':'' ?>">
    <span class="icon">📊</span> Dashboard
  </a>
  <a href="/reports.php" class="<?= $current==='reports.php'?'active':'' ?>">
    <span class="icon">📋</span> Reports
  </a>
  <a href="/new_report.php" class="<?= $current==='new_report.php'?'active':'' ?>">
    <span class="icon">➕</span> New Report
  </a>

  <div class="sidebar-section" style="margin-top:12px;">Integrations</div>
  <a href="/import.php" class="<?= $current==='import.php'?'active':'' ?>">
    <span class="icon">📥</span> Import XML
  </a>

  <div class="sidebar-section" style="margin-top:12px;">Account</div>
  <a href="/logout.php">
    <span class="icon">🚪</span> Sign out
  </a>
</nav>
