<?php
session_start();
require __DIR__ . '/admin-config.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === $adminConfig['username'] && $password === $adminConfig['password']) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $adminConfig['username'];
        $_SESSION['admin_name'] = $adminConfig['name'];
        header('Location: index.php');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Login</title>
    <style>
      * { box-sizing: border-box; }
      body {
        margin: 0;
        min-height: 100vh;
        display: grid;
        place-items: center;
        font-family: Arial, sans-serif;
        background: linear-gradient(135deg, #e5f7ea, #f7faf7);
        color: #111827;
      }
      .login-box {
        width: min(420px, 90vw);
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
        padding: 28px;
      }
      .logo-wrap {
        display: flex;
        justify-content: center;
        margin-bottom: 18px;
      }
      .logo-wrap img {
        width: 150px;
        height: auto;
      }
      h1 {
        text-align: center;
        margin: 0 0 18px;
        font-size: 28px;
      }
      .form-group {
        margin-bottom: 18px;
      }
      label {
        display: block;
        font-weight: 700;
        margin-bottom: 8px;
      }
      input {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font-size: 15px;
      }
      input:focus {
        outline: 2px solid rgba(37, 99, 235, 0.2);
        border-color: #2563eb;
      }
      button {
        width: 100%;
        border: none;
        background: #0f172a;
        color: #fff;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        padding: 12px;
        cursor: pointer;
      }
      button:hover {
        background: #1e293b;
      }
      .error {
        background: #fee2e2;
        color: #991b1b;
        border-left: 4px solid #dc2626;
        padding: 10px 12px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-weight: 600;
      }
    </style>
  </head>
  <body>
    <div class="login-box">
      <div class="logo-wrap">
        <img src="<?php echo htmlspecialchars($adminConfig['logo_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Maneouver logo" />
      </div>
      <h1><?php echo htmlspecialchars($adminConfig['site_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
      <?php if ($error !== ''): ?>
        <div class="error"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>
      <form method="POST" action="login.php">
        <div class="form-group">
          <label for="username">Username</label>
          <input id="username" name="username" type="text" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required />
        </div>
        <button type="submit">Login to Dashboard</button>
      </form>
    </div>
  </body>
</html>
