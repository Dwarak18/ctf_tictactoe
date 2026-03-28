<?php
require_once __DIR__ . '/includes/auth.php';

// Already logged in? Go to dashboard
if (current_user()) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    if ($email && $password) {
        $user = login($email, $password);
        if ($user) {
            header('Location: /dashboard.php');
            exit;
        }
        $error = 'Invalid credentials. Please try again.';
    } else {
        $error = 'Email and password are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In – BugBoard Analytics</title>
<link rel="stylesheet" href="/static/style.css">
</head>
<body>
<div class="login-wrap">
  <div class="login-box">
    <div class="login-header">
      <h1>🔒 BugBoard Analytics</h1>
      <p>Sign in to your bug bounty program portal</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="/login.php">
      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email"
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
               placeholder="you@example.com" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; padding:10px;">
        Sign in
      </button>
    </form>

    <p style="text-align:center; margin-top:20px; font-size:0.8rem; color:var(--muted);">
      Private beta · Access by invitation only
    </p>
  </div>
</div>
</body>
</html>
