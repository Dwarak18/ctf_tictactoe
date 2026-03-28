<header class="header">
  <div class="logo">
    🔒 BugBoard <span class="badge">BETA</span>
  </div>
  <div class="header-right">
    <span class="user-pill">
      <?= htmlspecialchars($user['email'] ?? 'unknown') ?>
      &nbsp;·&nbsp;<?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?>
    </span>
    <a href="/logout.php" class="btn-logout">Sign out</a>
  </div>
</header>
