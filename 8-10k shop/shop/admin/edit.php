<?php
require __DIR__ . '/../db.php';
require_admin();
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$d = db_read();
$idx = null;
foreach ($d['items'] as $k => $it) if ($it['id'] === $id) $idx = $k;
if ($idx === null) { header('Location: index.php'); exit; }
$p = $d['items'][$idx];
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
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
            if ($img) { delete_image($p['image']); } else { $img = $p['image']; }
            $d['items'][$idx] = array_merge($p, ['name' => cut($name, 120), 'price' => (float)$price,
                'size' => cut($size, 50), 'description' => cut($desc, 500), 'image' => $img]);
            if (db_write($d)) { header('Location: index.php'); exit; }
            $err = 'Could not save changes.';
        }
    }
    $p['name'] = $name; $p['size'] = $size; $p['description'] = $desc; $p['price'] = $price;
}
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Edit product</title>
<link rel="stylesheet" href="../style.css">
</head><body>
<div class="wrap" style="max-width:520px">
  <div class="box" style="margin-top:20px">
    <h2>Edit product</h2>
    <?php if ($err): ?><div class="err"><?= e($err) ?></div><?php endif; ?>
    <?php if ($p['image']): ?><img src="../uploads/<?= e($p['image']) ?>" alt="" style="width:120px;border-radius:10px"><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
      <label for="n">Name</label><input id="n" name="name" maxlength="120" required value="<?= e($p['name']) ?>">
      <label for="pr">Price (<?= e(CURRENCY) ?>)</label><input id="pr" name="price" type="number" min="0" step="1" inputmode="numeric" required value="<?= e($p['price'] + 0) ?>">
      <label for="s">Size</label><input id="s" name="size" maxlength="50" value="<?= e($p['size']) ?>">
      <label for="d">Description</label><textarea id="d" name="description" maxlength="500" rows="2"><?= e($p['description']) ?></textarea>
      <label for="i">Replace photo (optional)</label><input id="i" type="file" name="image" accept="image/*">
      <p class="row"><button type="submit">Save changes</button><a class="btn alt" href="index.php">Cancel</a></p>
    </form>
  </div>
</div>
</body></html>
