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
    // Standardized dot combination prevents array quote evaluation confusion
    $text = "Hello, is this still available?\n\n" . $p['name'] . "\nPrice: " . CURRENCY . " " . number_format($p['price']);
    
    if ($p['size'] !== '') {
        $text .= "\nSize: " . $p['size'];
    }
    
    if ($p['image']) {
        $text .= "\n" . $root . '/uploads/' . $p['image'];
    }
    
    // FIXED: Added the mandatory forward slash right before your WHATSAPP country code string variable
    return 'https://wa.me' . WHATSAPP . '?text=' . rawurlencode($text);
}
// Collect all existing random product images for the inline slideshow pool from root uploads/
$slideshow_images = [];
foreach ($products as $p) {
    if (!empty($p['image'])) {
        $slideshow_images[] = 'uploads/' . $p['image'];
    }
}

$user_instructions = "Tap 'ORDER ON WHATSAPP' on any item to ask us on WhatsApp. Use the search field to filter items by keywords instantly.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e(SHOP_NAME) ?> | Catalog</title>
<link rel="stylesheet" href="style.css">
<style>
/* Optimized inline rectangular slideshow styles */
.shop-banner-slideshow {
    position: relative;
    width: 100%;
    height: 450px;
    margin: 15px 0;
    background-color: #111111;
    overflow: hidden;
    border-radius: 8px;
}
.slideshow-track-item {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 1s ease-in-out;
    z-index: 1;
}
.slideshow-track-item.active {
    opacity: 1;
    z-index: 2;
}
.slide-bg-blur {
    position: absolute;
    top: -10%;
    left: -10%;
    width: 120%;
    height: 120%;
    object-fit: cover;
    filter: blur(15px) brightness(0.5);
    z-index: 1;
    pointer-events: none;
}
.slide-main-image {
    position: relative;
    width: 100%;
    height: 100%;
    object-fit: contain;
    z-index: 2;
}
</style>
</head>
<body>
<div class="wrap">
  
  <header>
    <div class="header-top">
      <div class="brand-container">
        <a href="index.php" class="logo-link" aria-label="Home">
          <img src="shop_images/logo.jpg" alt="Logo" class="shop-logo" onerror="this.style.display='none'">
          <span class="logo-placeholder-box"></span>
        </a>
        <h1 class="shop-title"><?= e(SHOP_NAME) ?></h1>
      </div>
      
      <div class="header-actions">
        <form class="search" method="get">
          <input type="search" name="q" value="<?= e($q) ?>" placeholder="Search catalog..." aria-label="Search">
          <button type="submit">Search</button>
        </form>
        <a href="aboutme.php" class="btn alt">About Us</a>
      </div>
    </div>
  </header>

  <?php if (!empty($slideshow_images)): ?>
    <!-- INLINE RECTANGULAR AUTOMATIC SLIDESHOW -->
    <div class="shop-banner-slideshow" id="shopInlineSlideshow"></div>
  <?php endif; ?>

  <?php if (!empty($user_instructions)): ?>
    <div class="instructions-box">
      <strong>Instructions:</strong> <?= e($user_instructions) ?>
    </div>
  <?php endif; ?>

  <?php if (!$products): ?>
    <div class="empty"><?= $q !== '' ? 'No items match your search.' : 'New stock coming soon.' ?></div>
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
          <a class="btn" href="<?= e(wa_link($p, $root)) ?>" target="_blank" rel="noopener">ORDER ON WHATSAPP</a>
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

<?php if (!empty($slideshow_images)): ?>
<script>
    const imagePool = <?php echo json_encode($slideshow_images); ?>;
    const slideshowContainer = document.getElementById('shopInlineSlideshow');
    let currentActiveIndex = 0;

    function randomizeSlides(array) {
        for (let i = array.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [array[i], array[j]] = [array[j], array[i]];
        }
    }

    function buildSlideshow() {
        if (!slideshowContainer || imagePool.length === 0) return;
        randomizeSlides(imagePool);

        imagePool.forEach((imgSrc, index) => {
            const itemWrapper = document.createElement('div');
            itemWrapper.classList.add('slideshow-track-item');
            if (index === 0) itemWrapper.classList.add('active');

            const bgBlurImage = document.createElement('img');
            bgBlurImage.src = imgSrc;
            bgBlurImage.classList.add('slide-bg-blur');
            itemWrapper.appendChild(bgBlurImage);

            const mainProductImage = document.createElement('img');
            mainProductImage.src = imgSrc;
            mainProductImage.classList.add('slide-main-image');
            itemWrapper.appendChild(mainProductImage);

            slideshowContainer.appendChild(itemWrapper);
        });

        setInterval(changeSlide, 5000);
    }

    function changeSlide() {
        const structuralSlides = document.querySelectorAll('.slideshow-track-item');
        if (structuralSlides.length <= 1) return;

        structuralSlides[currentActiveIndex].classList.remove('active');
        currentActiveIndex = (currentActiveIndex + 1) % structuralSlides.length;
        structuralSlides[currentActiveIndex].classList.add('active');
    }

    window.addEventListener('DOMContentLoaded', buildSlideshow);
</script>
<?php endif; ?>

</body>
</html>
