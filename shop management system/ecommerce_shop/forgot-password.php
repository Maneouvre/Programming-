<?php
declare(strict_types=1);

session_start();

function resetReturnTarget(?string $target): string
{
    return in_array($target, ['place-order.html', 'orders.html'], true) ? $target : 'index.html';
}

function escapeResetHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$returnTo = resetReturnTarget($_POST['return_to'] ?? $_GET['return_to'] ?? null);
$message = '';
$errorMessage = '';
$step = isset($_SESSION['password_reset_email']) ? 'verify' : 'request';

if (!isset($_SESSION['password_reset_csrf'])) {
    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string)($_POST['csrf_token'] ?? '');

    if (!hash_equals($_SESSION['password_reset_csrf'], $csrfToken)) {
        $errorMessage = 'Your password reset session expired. Refresh this page and try again.';
    } else {
        $action = (string)($_POST['action'] ?? '');

        if ($action === 'request') {
            $email = trim((string)($_POST['email'] ?? ''));
            if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
                unset($_SESSION['password_reset_email']);
                $step = 'request';
                $errorMessage = 'Enter a valid email address.';
            } else {
                $_SESSION['password_reset_email'] = $email;
                $step = 'verify';
                try {
                    require_once __DIR__ . '/auth/database.php';
                    $pdo = connectShopUsers();
                    $pdo->exec(
                        'CREATE TABLE IF NOT EXISTS password_reset_codes (
                            email VARCHAR(254) NOT NULL PRIMARY KEY,
                            code_hash VARCHAR(255) NOT NULL,
                            expires_at DATETIME NOT NULL,
                            attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
                            sent_at DATETIME NOT NULL
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
                    );

                    $userQuery = $pdo->prepare('SELECT email FROM users WHERE email = :email LIMIT 1');
                    $userQuery->execute(['email' => $email]);
                    $user = $userQuery->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        $email = (string)$user['email'];
                        $rateLimitQuery = $pdo->prepare(
                            'SELECT sent_at FROM password_reset_codes
                             WHERE email = :email AND sent_at > DATE_SUB(UTC_TIMESTAMP(), INTERVAL 60 SECOND)'
                        );
                        $rateLimitQuery->execute(['email' => $email]);
                        if (!$rateLimitQuery->fetch(PDO::FETCH_ASSOC)) {
                        $smtpHost = getenv('ECOMMERCE_SMTP_HOST') ?: 'smtp.gmail.com';
                        $smtpPort = getenv('ECOMMERCE_SMTP_PORT') ?: '587';
                        $smtpSecurity = strtolower(getenv('ECOMMERCE_SMTP_SECURITY') ?: 'tls');
                        $smtpUsername = getenv('ECOMMERCE_SMTP_USERNAME') ?: '';
                        $smtpPassword = getenv('ECOMMERCE_SMTP_PASSWORD') ?: '';
                        $fromAddress = getenv('ECOMMERCE_MAIL_FROM') ?: 'emmanuellokumu93@gmail.com';

                        if (
                            !ctype_digit($smtpPort) || !in_array((int)$smtpPort, [465, 587], true) ||
                            !in_array($smtpSecurity, ['tls', 'ssl'], true) ||
                            ((int)$smtpPort === 587 && $smtpSecurity !== 'tls') ||
                            ((int)$smtpPort === 465 && $smtpSecurity !== 'ssl') ||
                            $smtpUsername === '' || $smtpPassword === '' ||
                            !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)
                        ) {
                            throw new RuntimeException('Authenticated SMTP is not configured with valid Gmail TLS settings.');
                        }

                        $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $codeHash = password_hash($code, PASSWORD_DEFAULT);
                            $saveCode = $pdo->prepare(
                                'INSERT INTO password_reset_codes (email, code_hash, expires_at, attempts, sent_at)
                                 VALUES (:email, :code_hash, DATE_ADD(UTC_TIMESTAMP(), INTERVAL 10 MINUTE), 0, UTC_TIMESTAMP())
                                 ON DUPLICATE KEY UPDATE
                                   code_hash = VALUES(code_hash),
                                   expires_at = VALUES(expires_at),
                                   attempts = 0,
                                   sent_at = VALUES(sent_at)'
                            );
                            $saveCode->execute(['email' => $email, 'code_hash' => $codeHash]);

                            try {
                                require_once __DIR__ . '/vendor/autoload.php';
                                $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
                                $mailer->isSMTP();
                                $mailer->Host = $smtpHost;
                                $mailer->SMTPAuth = true;
                                $mailer->Username = $smtpUsername;
                                $mailer->Password = $smtpPassword;
                                $mailer->SMTPSecure = $smtpSecurity === 'ssl'
                                    ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                                    : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                                $mailer->Port = (int)$smtpPort;
                                $mailer->Timeout = 15;
                                $mailer->CharSet = PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;
                                $mailer->setFrom($fromAddress, 'Maneouver');
                                $mailer->addAddress($email);
                                $mailer->Subject = 'Your Maneouver password reset code';
                                $mailer->Body = "Your password reset code is: {$code}\n\n"
                                    . "This code expires in 10 minutes and can only be used once. "
                                    . "If you did not request a password reset, you can ignore this email.";
                                $mailer->send();
                            } catch (Throwable $error) {
                                $deleteUnsentCode = $pdo->prepare(
                                    'DELETE FROM password_reset_codes WHERE email = :email'
                                );
                                $deleteUnsentCode->execute(['email' => $email]);
                                throw $error;
                            }
                        }
                    }
                } catch (Throwable $error) {
                    error_log('Shop password reset request failed: ' . $error->getMessage());
                }

                $message = 'If that email address is registered, a verification code will be sent. Check your inbox and spam folder.';
            }
        } elseif ($action === 'verify') {
            $step = 'verify';
            $email = (string)($_SESSION['password_reset_email'] ?? '');
            $code = trim((string)($_POST['code'] ?? ''));
            $password = (string)($_POST['password'] ?? '');
            $passwordConfirmation = (string)($_POST['password_confirmation'] ?? '');

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                unset($_SESSION['password_reset_email']);
                $step = 'request';
                $errorMessage = 'Start by entering your account email address.';
            } elseif (!preg_match('/^\d{6}$/', $code)) {
                $errorMessage = 'Enter the six-digit code sent to your email.';
            } elseif (strlen($password) < 8) {
                $errorMessage = 'Choose a password with at least 8 characters.';
            } elseif ($password !== $passwordConfirmation) {
                $errorMessage = 'The new passwords do not match.';
            } else {
                try {
                    require_once __DIR__ . '/auth/database.php';
                    $pdo = connectShopUsers();
                    $codeQuery = $pdo->prepare(
                        'SELECT code_hash, attempts FROM password_reset_codes
                         WHERE email = :email AND expires_at > UTC_TIMESTAMP() LIMIT 1'
                    );
                    $codeQuery->execute(['email' => $email]);
                    $reset = $codeQuery->fetch(PDO::FETCH_ASSOC);

                    if (!$reset || (int)$reset['attempts'] >= 5) {
                        $errorMessage = 'That code is invalid or expired. Request a new code and try again.';
                    } elseif (!password_verify($code, (string)$reset['code_hash'])) {
                        $attemptQuery = $pdo->prepare(
                            'UPDATE password_reset_codes SET attempts = attempts + 1 WHERE email = :email'
                        );
                        $attemptQuery->execute(['email' => $email]);
                        $errorMessage = 'That code is invalid or expired. Request a new code and try again.';
                    } else {
                        $pdo->beginTransaction();
                        $updatePassword = $pdo->prepare('UPDATE users SET pwd = :pwd WHERE email = :email');
                        $updatePassword->execute([
                            'pwd' => password_hash($password, PASSWORD_DEFAULT),
                            'email' => $email
                        ]);
                        if ($updatePassword->rowCount() !== 1) {
                            throw new RuntimeException('The account password could not be updated.');
                        }
                        $deleteCode = $pdo->prepare('DELETE FROM password_reset_codes WHERE email = :email');
                        $deleteCode->execute(['email' => $email]);
                        $pdo->commit();

                        unset($_SESSION['password_reset_email'], $_SESSION['password_reset_csrf']);
                        header('Location: login.php?return_to=' . rawurlencode($returnTo) . '&password_reset=1');
                        exit;
                    }
                } catch (Throwable $error) {
                    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    error_log('Shop password reset verification failed: ' . $error->getMessage());
                    http_response_code(503);
                    $errorMessage = 'Password reset is temporarily unavailable. Please try again later.';
                }
            }
        } else {
            $errorMessage = 'Choose a valid password reset action.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password | Maneouver</title>
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
      .login-error, .login-message { margin: 14px 0; padding: 10px 12px; border-radius: 5px; }
      .login-error { background: #fff0f0; color: #9b1c1c; }
      .login-message { background: #effaf2; color: #176b31; }
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
        <h1>Reset your password</h1>
        <?php if ($step === 'request'): ?>
          <p>Enter the email address linked to your account. We’ll send a six-digit verification code.</p>
        <?php else: ?>
          <p>Enter the six-digit code sent to your email and choose a new password.</p>
        <?php endif; ?>
        <?php if ($message !== ''): ?>
          <p class="login-message" role="status"><?= escapeResetHtml($message) ?></p>
        <?php endif; ?>
        <?php if ($errorMessage !== ''): ?>
          <p class="login-error" role="alert"><?= escapeResetHtml($errorMessage) ?></p>
        <?php endif; ?>
        <?php if ($step === 'request'): ?>
          <form method="post" action="forgot-password.php" autocomplete="on">
            <input type="hidden" name="action" value="request">
            <input type="hidden" name="return_to" value="<?= escapeResetHtml($returnTo) ?>">
            <input type="hidden" name="csrf_token" value="<?= escapeResetHtml($_SESSION['password_reset_csrf']) ?>">
            <label for="email">Account email</label>
            <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= escapeResetHtml((string)($_POST['email'] ?? '')) ?>">
            <button type="submit">Send verification code</button>
          </form>
        <?php else: ?>
          <form method="post" action="forgot-password.php" autocomplete="off">
            <input type="hidden" name="action" value="verify">
            <input type="hidden" name="return_to" value="<?= escapeResetHtml($returnTo) ?>">
            <input type="hidden" name="csrf_token" value="<?= escapeResetHtml($_SESSION['password_reset_csrf']) ?>">
            <label for="code">Six-digit verification code</label>
            <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
            <label for="password">New password</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
            <button type="submit">Verify code and change password</button>
          </form>
          <form method="post" action="forgot-password.php">
            <input type="hidden" name="action" value="request">
            <input type="hidden" name="return_to" value="<?= escapeResetHtml($returnTo) ?>">
            <input type="hidden" name="csrf_token" value="<?= escapeResetHtml($_SESSION['password_reset_csrf']) ?>">
            <input type="hidden" name="email" value="<?= escapeResetHtml((string)($_SESSION['password_reset_email'] ?? '')) ?>">
            <button type="submit">Send another code</button>
          </form>
        <?php endif; ?>
        <a class="login-switch" href="login.php?return_to=<?= rawurlencode($returnTo) ?>">Back to sign in</a>
      </section>
    </main>
    <script type="module" src="data/site-header.js"></script>
  </body>
</html>
