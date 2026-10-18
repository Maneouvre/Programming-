<?php
declare(strict_types=1);

session_start();

function loginReturnTarget(?string $target): string
{
    return in_array($target, ['place-order.html', 'orders.html'], true) ? $target : 'index.html';
}

$returnTo = loginReturnTarget($_POST['return_to'] ?? $_GET['return_to'] ?? null);
$errorMessage = '';

if (isset($_SESSION['user_id']) && (string)$_SESSION['user_id'] !== '') {
    header('Location: ' . $returnTo);
    exit;
}

if (!isset($_SESSION['login_csrf'])) {
    $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identity = trim((string)($_POST['identity'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $csrfToken = (string)($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['login_csrf'], $csrfToken)) {
        $errorMessage = 'Your sign-in session expired. Refresh this page and try again.';
    } elseif ($identity === '' || $password === '') {
        $errorMessage = 'Enter your username or email and password.';
    } else {
        try {
            require_once __DIR__ . '/auth/database.php';
            $pdo = connectShopUsers();
            $statement = $pdo->prepare(
                'SELECT id, username, email, pwd FROM users WHERE username = :username OR email = :email LIMIT 1'
            );
            $statement->execute(['username' => $identity, 'email' => $identity]);
            $user = $statement->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, (string)$user['pwd'])) {
                $errorMessage = 'Those sign-in details were not recognized.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (string)$user['id'];
                $_SESSION['user_username'] = (string)$user['username'];
                $_SESSION['user_email'] = (string)$user['email'];
                unset($_SESSION['login_csrf']);
                header('Location: ' . $returnTo);
                exit;
            }
        } catch (Throwable $error) {
            error_log('Shop sign-in failed: ' . $error->getMessage());
            http_response_code(503);
            $errorMessage = 'Sign-in is temporarily unavailable. Check that the customer database is running, then try again.';
        }
    }
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in | Maneouver</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/shared/general.css">
    <link rel="stylesheet" href="styles/shared/header.css">
    <style>
      .login-page { max-width: 440px; margin: 100px auto 48px; padding: 24px; }
      .login-card { padding: 28px; border: 1px solid #dedede; border-radius: 8px; background: #fff; }
      .login-card h1 { margin: 0 0 8px; font-size: 25px; }
      .login-card > p { margin-bottom: 22px; color: #555; line-height: 1.5; }
      .login-card label { display: block; margin: 16px 0 6px; font-weight: 500; }
      .login-card input { box-sizing: border-box; width: 100%; padding: 11px 12px; border: 1px solid #aaa; border-radius: 5px; font-size: 16px; }
      .login-card button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 5px; background: #08712f; color: #fff; font-size: 16px; font-weight: 700; }
      .login-error { margin: 14px 0; padding: 10px 12px; border-radius: 5px; background: #fff0f0; color: #9b1c1c; }
      .login-switch { display: block; margin-top: 18px; color: #08712f; text-align: center; }
      @media (max-width: 520px) { .login-page { margin-top: 84px; padding: 16px; } }
    </style>
  </head>
  <body>
    <header class="header">
      <div class="left-section">
        <a href="index.html" class="header-link" aria-label="Maneouver home">
          <img class="logo" src="images/logo-white.png" alt="Maneouver">
          <img class="mobile-logo" src="images/mobile-logo-white.png" alt="Maneouver">
        </a>
      </div>
      <nav class="right-section" aria-label="Shop">
        <a class="orders-link header-link" href="orders.html" aria-label="Track your orders"><span class="orders-text">Track orders</span></a>
        <a class="account-link header-link" href="login.php" aria-label="Sign in">
          <svg class="account-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9Zm0 2c-4.4 0-8 2.2-8 5v2h16v-2c0-2.8-3.6-5-8-5Z"/></svg>
          <span class="account-text">Account</span>
        </a>
        <a class="cart-link header-link" href="checkout.html">
          <img class="cart-icon" src="images/icons/cart-icon.png" alt="">
          <span class="cart-quantity js-cart-quantity">0</span>
          <span class="cart-text">Cart</span>
        </a>
      </nav>
    </header>
    <main class="login-page">
      <section class="login-card">
        <h1>Sign in to your account</h1>
        <p>Sign in to continue checkout or view and track your orders. Use your existing shop username or email.</p>
        <?php if ($errorMessage !== ''): ?>
          <p class="login-error" role="alert"><?= escapeHtml($errorMessage) ?></p>
        <?php endif; ?>
        <form method="post" action="login.php" autocomplete="on">
          <input type="hidden" name="return_to" value="<?= escapeHtml($returnTo) ?>">
          <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['login_csrf']) ?>">
          <label for="identity">Username or email</label>
          <input id="identity" name="identity" type="text" autocomplete="username" required value="<?= escapeHtml((string)($_POST['identity'] ?? '')) ?>">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" autocomplete="current-password" required>
          <button type="submit">Sign in and continue</button>
        </form>
        <a class="login-switch" href="signup.php?return_to=<?= rawurlencode($returnTo) ?>">No account yet? Create one</a>
      </section>
    </main>
    <script type="module" src="data/site-header.js"></script>
  </body>
</html>
