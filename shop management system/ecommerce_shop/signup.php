<?php
declare(strict_types=1);

session_start();

function signupReturnTarget(?string $target): string
{
    return in_array($target, ['place-order.html', 'orders.html'], true) ? $target : 'index.html';
}

function escapeSignupHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$returnTo = signupReturnTarget($_POST['return_to'] ?? $_GET['return_to'] ?? null);
$errorMessage = '';
$usernameValue = trim((string)($_POST['username'] ?? ''));
$emailValue = trim((string)($_POST['email'] ?? ''));

if (isset($_SESSION['user_id']) && (string)$_SESSION['user_id'] !== '') {
    header('Location: ' . $returnTo);
    exit;
}

if (!isset($_SESSION['signup_csrf'])) {
    $_SESSION['signup_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string)($_POST['password'] ?? '');
    $csrfToken = (string)($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['signup_csrf'], $csrfToken)) {
        $errorMessage = 'Your sign-up session expired. Refresh this page and try again.';
    } elseif (!preg_match('/^[A-Za-z0-9_.]{3,32}$/', $usernameValue)) {
        $errorMessage = 'Username must be 3–32 characters and contain only letters, numbers, periods, or underscores.';
    } elseif (!filter_var($emailValue, FILTER_VALIDATE_EMAIL) || strlen($emailValue) > 254) {
        $errorMessage = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $errorMessage = 'Choose a password with at least 8 characters.';
    } else {
        try {
            require_once __DIR__ . '/auth/database.php';
            $pdo = connectShopUsers();
            $check = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
            $check->execute(['username' => $usernameValue, 'email' => $emailValue]);
            if ($check->fetch(PDO::FETCH_ASSOC)) {
                $errorMessage = 'That username or email is already registered. Sign in or use different details.';
            } else {
                $insert = $pdo->prepare('INSERT INTO users (username, pwd, email) VALUES (:username, :pwd, :email)');
                $insert->execute([
                    'username' => $usernameValue,
                    'pwd' => password_hash($password, PASSWORD_DEFAULT),
                    'email' => $emailValue
                ]);
                session_regenerate_id(true);
                $_SESSION['user_id'] = (string)$pdo->lastInsertId();
                $_SESSION['user_username'] = $usernameValue;
                $_SESSION['user_email'] = $emailValue;
                unset($_SESSION['signup_csrf']);
                header('Location: ' . $returnTo);
                exit;
            }
        } catch (PDOException $error) {
            error_log('Shop account registration failed: ' . $error->getMessage());
            http_response_code(503);
            $errorMessage = 'Account registration is temporarily unavailable. Check that MySQL is running, then try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account | Maneouver</title>
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
        <h1>Create your account</h1>
        <p>Create an account to continue to checkout. Your password is stored as a secure hash.</p>
        <?php if ($errorMessage !== ''): ?>
          <p class="login-error" role="alert"><?= escapeSignupHtml($errorMessage) ?></p>
        <?php endif; ?>
        <form method="post" action="signup.php" autocomplete="on">
          <input type="hidden" name="return_to" value="<?= escapeSignupHtml($returnTo) ?>">
          <input type="hidden" name="csrf_token" value="<?= escapeSignupHtml($_SESSION['signup_csrf']) ?>">
          <label for="username">Username</label>
          <input id="username" name="username" type="text" autocomplete="username" minlength="3" maxlength="32" pattern="[A-Za-z0-9_.]+" required value="<?= escapeSignupHtml($usernameValue) ?>">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" autocomplete="email" maxlength="254" required value="<?= escapeSignupHtml($emailValue) ?>">
          <label for="password">Password (at least 8 characters)</label>
          <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
          <button type="submit">Create account and continue</button>
        </form>
        <a class="login-switch" href="login.php?return_to=<?= rawurlencode($returnTo) ?>">Already have an account? Sign in</a>
      </section>
    </main>
    <script type="module" src="data/site-header.js"></script>
  </body>
</html>
