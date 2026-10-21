<?php
require __DIR__ . '/db.php';

$q = trim($_GET['q'] ?? '');
$products = db_read()['items'];
$slides = slideshow_read()['items'];
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
    if ($p['image']) $text .= "\n" . $root . '/admin/uploads/' . $p['image'];
    return 'https://wa.me' . WHATSAPP . '?text=' . rawurlencode($text);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e(SHOP_NAME) ?> | Catalog</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="wrap">
  <header>
    <div class="header-top">
      <div class="brand-container">
        <a href="index.php" class="logo-link" aria-label="Home">
          <img src="shop_images/logo1.png" alt="<?= e(SHOP_NAME) ?>" class="shop-logo">
        </a>
        <h1 class="shop-title"><?= e(SHOP_NAME) ?></h1>
      </div>
      <div class="header-actions">
        <form class="search" method="get">
          <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search products..." aria-label="Search products">
          <button type="submit">Search</button>
        </form>
        <a href="aboutme.php" class="btn alt">About Us</a>
      </div>
    </div>
  </header>

  <?php if (!empty($slides)): ?>
    <section class="announcement-slideshow" aria-label="Shop announcements">
      <?php foreach ($slides as $index => $slide): ?>
      <article class="announcement-slide<?= $index === 0 ? ' active' : '' ?>"<?= $index === 0 ? '' : ' aria-hidden="true"' ?>>
        <img src="admin/slideshow_uploads/<?= e($slide['image']) ?>" alt=""<?= $index === 0 ? '' : ' loading="lazy"' ?>>
        <div class="announcement-copy">
          <span class="announcement-label">From our shop</span>
          <h2><?= e($slide['title']) ?></h2>
          <?php if (!empty($slide['description'])): ?><p><?= nl2br(e($slide['description'])) ?></p><?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <section class="catalog-heading">
    <div>
      <p class="eyebrow">Thoughtfully picked for you</p>
      <h2><?= $q !== '' ? 'Search results' : 'Explore the collection' ?></h2>
      <p class="catalog-subtitle"><?= $q !== '' ? 'Showing matches for “' . e($q) . '”' : 'Find something you’ll love, then order directly on WhatsApp.' ?></p>
    </div>
    <span class="product-count"><?= count($products) ?> <?= count($products) === 1 ? 'item' : 'items' ?></span>
  </section>

  <?php if (!$products): ?>
    <div class="empty"><?= $q !== '' ? 'No items match your search.' : 'New stock coming soon.' ?></div>
  <?php else: ?>
  <div class="grid">
    <?php foreach ($products as $p): ?>
    <div class="card">
      <div class="pic">
        <?php if ($p['image']): ?><img src="admin/uploads/<?= e($p['image']) ?>" alt="<?= e($p['name']) ?>" loading="lazy"><?php else: ?><span class="image-placeholder">Photo coming soon</span><?php endif; ?>
        <?php if (!$p['in_stock']): ?><span class="tag">Sold out</span><?php endif; ?>
      </div>
      <div class="info">
        <h3><?= e($p['name']) ?></h3>
        <span class="price"><?= e(CURRENCY) ?> <?= number_format($p['price']) ?></span>
        <?php if ($p['size'] !== ''): ?><span class="meta">Size: <?= e($p['size']) ?></span><?php endif; ?>
        <?php if ($p['description'] !== ''): ?>
          <details class="product-description">
            <summary><span class="description-more">Read full description</span><span class="description-less">Show less</span></summary>
            <p><?= nl2br(e($p['description'])) ?></p>
          </details>
        <?php endif; ?>
        <?php if ($p['in_stock']): ?>
          <a class="btn" href="<?= e(wa_link($p, $root)) ?>" target="_blank" rel="noopener">Order on WhatsApp</a>
        <?php else: ?>
          <span class="btn off">Sold out</span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <footer>
    &copy; <?= date('Y') ?> <?= e(SHOP_NAME) ?>
  </footer>
</div>

<?php if (count($slides) > 1): ?>
<script>
  const slides = [...document.querySelectorAll('.announcement-slide')];
  let activeSlide = 0;
  window.setInterval(() => {
    slides[activeSlide].classList.remove('active');
    slides[activeSlide].setAttribute('aria-hidden', 'true');
    activeSlide = (activeSlide + 1) % slides.length;
    slides[activeSlide].classList.add('active');
    slides[activeSlide].removeAttribute('aria-hidden');
  }, 6000);
</script>
<?php endif; ?>
<script>
  /*document.addEventListener('contextmenu', event => event.preventDefault());*/
</script>

</body>
</html>
