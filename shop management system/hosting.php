<?php
declare(strict_types=1);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f5f7f4">
    <title>Database &amp; hosting setup — SokoFlow</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .hosting-shell { min-height: 100vh; padding: 28px 20px 60px; }
        .hosting-wrap { width: min(940px, 100%); margin: 0 auto; }
        .hosting-top { display: flex; align-items: center; justify-content: space-between; }
        .hosting-hero { margin: 56px 0 25px; }
        .hosting-hero h1 { margin: 0; font: 800 clamp(30px, 5vw, 46px)/1.12 "Manrope", sans-serif; letter-spacing: -1.6px; }
        .hosting-hero > p:last-child { max-width: 610px; margin: 12px 0 0; color: #78877f; font-size: 13px; line-height: 1.7; }
        .hosting-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .hosting-card { padding: 20px; border: 1px solid #e8ede9; border-radius: 11px; background: #fff; }
        .hosting-card h2 { margin: 0 0 10px; font: 700 16px "Manrope", sans-serif; }
        .hosting-card p, .hosting-card li { color: #75837c; font-size: 11px; line-height: 1.75; }
        .hosting-card ol, .hosting-card ul { margin: 11px 0 0; padding-left: 19px; }
        .hosting-card li + li { margin-top: 6px; }
        .hosting-card code { overflow-wrap: anywhere; padding: 2px 5px; border-radius: 4px; background: #f2f5f2; color: #48665a; font: 10px Consolas, monospace; }
        .hosting-card pre { overflow-x: auto; margin: 12px 0; padding: 13px; border-radius: 7px; background: #20352e; color: #e4f2e9; font: 10px/1.7 Consolas, monospace; }
        .hosting-wide { grid-column: 1 / -1; }
        .hosting-note { margin-top: 15px; padding: 12px 14px; border: 1px solid #f0e5c9; border-radius: 8px; background: #fffaf0; color: #846c42; font-size: 10px; line-height: 1.65; }
        .hosting-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 20px; color: #87948d; font-size: 10px; }
        @media (max-width: 650px) {
            .hosting-shell { padding: 19px 13px 40px; }
            .hosting-hero { margin-top: 38px; }
            .hosting-grid { grid-template-columns: 1fr; }
            .hosting-wide { grid-column: auto; }
            .hosting-footer { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
<main class="hosting-shell">
    <div class="hosting-wrap">
        <header class="hosting-top">
            <a class="brand" href="index.php"><span class="brand-mark">s<span>.</span></span><span class="brand-name">soko<span>flow</span></span></a>
            <a class="secondary-button" href="index.php">← Back to sign in</a>
        </header>
        <section class="hosting-hero">
            <p class="eyebrow">GETTING YOUR SHOP ONLINE</p>
            <h1>Database &amp; hosting<br>setup<span class="heading-dot">.</span></h1>
            <p>SokoFlow runs on standard PHP hosting with MySQL or MariaDB. This guide explains what to prepare and where to configure it—your database password is never displayed on this page.</p>
        </section>
        <div class="hosting-grid">
            <section class="hosting-card">
                <h2>Before you choose hosting</h2>
                <ul>
                    <li>PHP 8.1 or later, with <code>PDO</code>, <code>pdo_mysql</code>, and <code>mbstring</code> enabled.</li>
                    <li>MySQL 8+ or a supported MariaDB server, plus permission to create tables and import an SQL file.</li>
                    <li>HTTPS/SSL enabled for the shop domain and a private database account for this app.</li>
                    <li>Apache hosting with <code>.htaccess</code> support is recommended. If your host uses Nginx, ask them to deny web access to the config directory and SQL schema.</li>
                </ul>
            </section>
            <section class="hosting-card">
                <h2>1. Create a database</h2>
                <ol>
                    <li>In your hosting control panel, create a MySQL database and a dedicated database user.</li>
                    <li>Grant that user access to the new database. Save the database name, username, password, and host shown by your provider.</li>
                    <li>Open phpMyAdmin for that database and import <code>schema.sql</code>. If your host prefixes database names, use the full prefixed name in the config.</li>
                </ol>
            </section>
            <section class="hosting-card">
                <h2>2. Set the database config</h2>
                <p>On your local machine, copy <code>config/config.local.example.php</code> to <code>config/config.local.php</code> and enter your local database details. That file is ignored by Git and overrides environment values.</p>
                <p>On shared hosting, either create a private <code>config/config.local.php</code> with the host's database values, or set <code>SHOP_DB_HOST</code>, <code>SHOP_DB_NAME</code>, <code>SHOP_DB_USER</code>, and <code>SHOP_DB_PASSWORD</code> in the hosting environment. If using the local file, upload it separately and do not overwrite it on later deployments.</p>
                <pre>return [
    'env' =&gt; 'production',
    'db' =&gt; [
        'host' =&gt; 'your-database-host',
        'name' =&gt; 'your-account_sokoflow',
        'user' =&gt; 'your-account_shopuser',
        'password' =&gt; 'your-database-password',
    ],
];</pre>
                <p><code>config/config.php</code> is committed, reads environment variables, and fails loudly if required production settings are absent. It never falls back to root credentials or an empty production password.</p>
            </section>
            <section class="hosting-card">
                <h2>3. Upload and finish setup</h2>
                <ol>
                    <li>Where your host allows it, store <code>config/config.local.php</code> outside the public document root. Otherwise keep it out of public Git repositories and confirm direct requests are denied.</li>
                    <li>Confirm the supplied <code>.htaccess</code> is uploaded and Apache accepts it. It blocks direct requests to the config directory and SQL schema.</li>
                    <li>Visit your app URL over HTTPS. The first-run screen creates the owner and cashier accounts; use unique passwords of at least 10 characters.</li>
                </ol>
            </section>
            <section class="hosting-card hosting-wide">
                <h2>Hosting checklist &amp; troubleshooting</h2>
                <ul>
                    <li><b>“Database is not ready”:</b> verify the provider’s exact database host/name/user, password, PDO MySQL extension, and that <code>schema.sql</code> was imported into that database.</li>
                    <li><b>Missing database settings:</b> the app returns the missing setting name and tells you to configure <code>config/config.local.php</code> or the corresponding <code>SHOP_DB_*</code> variable. It does not guess development credentials in production.</li>
                    <li><b>Page source or config exposure:</b> stop using the site until PHP handling and web-server deny rules are confirmed. Apache reads <code>.htaccess</code>; Nginx ignores it and must be configured by the host.</li>
                    <li><b>Protect your business data:</b> enable HTTPS, use a unique DB user with access only to this database, update PHP regularly, restrict hosting-panel access, and take regular off-site database backups.</li>
                </ul>
                <div class="hosting-note">Login passwords are stored as password hashes in the database, not in the database connection config. This version has no in-app password reset; changing a database password in config only changes the database connection. The app does not provide automatic M-Pesa STK payments, offline mode, or automatic database backups.</div>
            </section>
        </div>
        <footer class="hosting-footer"><span>Keep database credentials private. Never send them in chat or publish them in a repository.</span><a class="text-button" href="README.md" target="_blank" rel="noopener">Full installation notes ↗</a></footer>
    </div>
</main>
</body>
</html>
