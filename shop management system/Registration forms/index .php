<?php
require __DIR__ . '/db.php';

$q = trim($_GET['q'] ?? '');
$products = db_read()['items'];
if ($q !== '') {
    $products = array_filter($products, fn($p) => stripos($p['name'], $q) !== false);
}
usort($products, fn($a, $b) => [$b['in_stock'], $b['id']] <=> [$a['in_stock'], $a['id']]);

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
$root = (SITE_URL !== '') ? rtrim(SITE_URL, '/')
      : ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

function wa_link($p, $root) {
    $text = "Hello, is this still available?\n\n" . $p['name'] . "\nPrice: " . CURRENCY . ' ' . number_format($p['price']);
    if ($p['size'] !== '') $text .= "\nSize: " . $p['size'];
    if ($p['image']) $text .= "\n" . $root . '/uploads/' . $p['image'];
    return 'https://wa.me/' . WHATSAPP . '?text=' . rawurlencode($text);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e(SHOP_NAME) ?> | Shop</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <header>
    <h1><?= e(SHOP_NAME) ?></h1>
    <p>Tap "PLACE ORDER" on any item to ask us on WhatsApp.</p>
    <form class="search" method="get">
      <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search clothes..." aria-label="Search">
      <button type="submit">Search</button>
    </form>
  </header>

  <?php if (!$products): ?>
    <div class="empty"><?= $q !== '' ? 'No clothes match your search.' : 'New stock coming soon.' ?></div>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($products as $p): ?>
    <div class="card">
      <div class="pic">
        <?php if ($p['image']): ?><img src="uploads/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php endif; ?>
        <?php if (!$p['in_stock']): ?><span class="tag">Sold out</span><?php endif; ?>
      </div>
      <div class="info">
        <h3><?= e($p['name']) ?></h3>
        <span class="price"><?= e(CURRENCY) ?> <?= number_format($p['price']) ?></span>
        <?php if ($p['size'] !== ''): ?><span class="meta">Size: <?= e($p['size']) ?></span><?php endif; ?>
        <?php if ($p['description'] !== ''): ?><span class="meta"><?= e($p['description']) ?></span><?php endif; ?>
        <?php if ($p['in_stock']): ?>
          <a class="btn" href="<?= e(wa_link($p, $root)) ?>" target="_blank" rel="noopener">PLACE ORDER</a>
        <?php else: ?>
          <span class="btn off">Sold out</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <footer>&copy; <?= date('Y') ?> <?= e(SHOP_NAME) ?></footer>
</div>
</body>
</html>
