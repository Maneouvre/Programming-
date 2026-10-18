<?php
require __DIR__ . '/../db.php';
require_admin();
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? '';
    $d = db_read();

    if ($a === 'add') {
        $name  = trim($_POST['name'] ?? '');
        $price = $_POST['price'] ?? '';
        $size  = trim($_POST['size'] ?? '');
        $desc  = trim($_POST['description'] ?? '');
        if ($name === '' || !is_numeric($price) || $price < 0) {
            $err = 'Enter a product name and a valid price.';
        } else {
            [$img, $ierr] = save_image($_FILES['image'] ?? null);
            if ($ierr) { $err = $ierr; }
            else {
                $d['items'][] = ['id' => $d['next']++, 'name' => cut($name, 120), 'price' => (float)$price,
                    'size' => cut($size, 50), 'description' => cut($desc, 500), 'image' => $img, 'in_stock' => 1];
                if (db_write($d)) $msg = 'Product added.';
                else $err = 'Could not save. Make sure the data folder exists in htdocs.';
            }
        }
    } elseif ($a === 'toggle') {
        foreach ($d['items'] as &$it) if ($it['id'] === (int)($_POST['id'] ?? 0)) $it['in_stock'] = $it['in_stock'] ? 0 : 1;
        unset($it);
        db_write($d);
    } elseif ($a === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        foreach ($d['items'] as $k => $it) if ($it['id'] === $id) { delete_image($it['image']); unset($d['items'][$k]); }
        $d['items'] = array_values($d['items']);
        if (db_write($d)) $msg = 'Product deleted.';
    }
}
$products = array_reverse(db_read()['items']);
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Admin | <?= e(SHOP_NAME) ?></title>
<link rel="stylesheet" href="../style.css">
</head><body>
<div class="wrap">
  <header class="row" style="justify-content:space-between">
    <h1>Manage products</h1>;
    <div class="row"><a class="btn alt" href="admin_slideshow.php" target="_blank">Slideshow Content</a><a class="btn alt" href="../index.php" target="_blank">View shop</a><a class="btn alt" href="logout.php">Log out</a></div>
  </header>

  <?php if ($msg): ?><div class="msg"><?= e($msg) ?></div><?php endif; ?>
  <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>

  <div class="box">
    <h2>Add a product</h2>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="action" value="add">
      <label for="n">Name</label><input id="n" name="name" maxlength="120" required placeholder="e.g. Floral summer dress">
      <label for="pr">Price (<?= e(CURRENCY) ?>)</label><input id="pr" name="price" type="number" min="0" step="1" inputmode="numeric" required>
      <label for="s">Size (optional)</label><input id="s" name="size" maxlength="50" placeholder="e.g. M, L or 38-40">
      <label for="d">Short description (optional)</label><textarea id="d" name="description" maxlength="500" rows="2"></textarea>
      <label for="i">Photo</label><input id="i" type="file" name="image" accept="image/*">
      <p><button type="submit" style="width:100%">Add product</button></p>
    </form>
  </div>

  <div class="box">
    <h2>Your products (<?= count($products) ?>)</h2>
    <?php if (!$products): ?><p class="meta">Nothing yet. Add your first product above.</p><?php endif; ?>
    <?php foreach ($products as $p): ?>
    <div class="item">
      <?php if ($p['image']): ?><img src="../uploads/<?= e($p['image']) ?>" alt=""><?php else: ?><div class="ph"></div><?php endif; ?>
      <div class="t">
        <b><?= e($p['name']) ?></b>
        <span class="meta"><?= e(CURRENCY) ?> <?= number_format($p['price']) ?> · <?= $p['in_stock'] ? 'In stock' : 'Sold out' ?></span>
        <div class="row" style="margin-top:6px">
          <a class="btn alt" style="padding:8px 10px;font-size:.85rem" href="edit.php?id=<?= (int)$p['id'] ?>">Edit</a>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="alt" type="submit"><?= $p['in_stock'] ? 'Mark sold out' : 'Mark in stock' ?></button></form>
          <form method="post" onsubmit="return confirm('Delete this product?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="danger" type="submit">Delete</button></form>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</body></html>
