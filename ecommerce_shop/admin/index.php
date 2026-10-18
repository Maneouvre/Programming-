<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <style>
      * { box-sizing: border-box; }
      body {
        margin: 0;
        font-family: Arial, sans-serif;
        background: #f3f4f6;
        color: #111827;
      }
      .topbar {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #0f172a;
        padding: 14px 20px;
      }
      .topbar a {
        color: #fff;
        text-decoration: none;
        padding: 8px 12px;
        border-radius: 6px;
        font-weight: 600;
      }
      .topbar a.active {
        background: #2563eb;
      }
      .topbar a:hover {
        background: rgba(255,255,255,0.08);
      }
      .page {
        min-height: calc(100vh - 56px);
      }
      iframe {
        width: 100%;
        min-height: calc(100vh - 56px);
        border: 0;
        display: block;
        background: white;
      }
      .user-pill {
        margin-left: auto;
        color: #fff;
        font-size: 14px;
        font-weight: 700;
      }
    </style>
  </head>
  <body>
    <div class="topbar">
      <a class="active" href="index.php">Dashboard</a>
      <a href="add-product.php">Add Product</a>
      <a href="../index.html">Storefront</a>
      <span class="user-pill"><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?></span>
      <a href="logout.php">Logout</a>
    </div>
    <div class="page">
      <iframe src="../Admin_dashboard_php_sql/delete_edit_products/delete_edit.php" title="Admin dashboard"></iframe>
    </div>
  </body>
</html>
