<?php
require __DIR__ . '/../db.php';
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $ok = hash_equals(ADMIN_USER, (string)($_POST['user'] ?? ''))
       && hash_equals(ADMIN_PASSWORD, (string)($_POST['password'] ?? ''));
    if ($ok) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: index.php'); exit;
    }
    sleep(1);
    $err = 'Wrong username or password.';
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Admin login</title>
<link rel="stylesheet" href="../style.css">
</head><body>
<div class="wrap" style="max-width:420px">
  <div class="box" style="margin-top:40px">
    <h2>Admin login</h2>
    <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <label for="u">Username</label><input id="u" name="user" autocomplete="username" required>
      <label for="p">Password</label><input id="p" type="password" name="password" autocomplete="current-password" required>
      <p><button type="submit" style="width:100%">Log in</button></p>
    </form>
  </div>
</div>
</body></html>
