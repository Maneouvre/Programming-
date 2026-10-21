<?php
require __DIR__ . '/../db.php';
require_admin();

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $data = slideshow_read();
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($title === '') {
            $error = 'Enter an announcement title.';
        } else {
            [$image, $imageError] = save_slideshow_image($_FILES['image'] ?? null);
            if ($imageError) {
                $error = $imageError;
            } else {
                $data['items'][] = [
                    'id' => $data['next']++,
                    'title' => cut($title, 120),
                    'description' => cut($description, 500),
                    'image' => $image,
                ];
                if (slideshow_write($data)) {
                    $message = 'Announcement added.';
                } else {
                    delete_slideshow_image($image);
                    $error = 'Could not save the announcement. Check that the data folder is writable.';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $imageToDelete = null;
        foreach ($data['items'] as $key => $slide) {
            if ((int)$slide['id'] === $id) {
                $imageToDelete = $slide['image'];
                unset($data['items'][$key]);
                break;
            }
        }

        $data['items'] = array_values($data['items']);
        if ($imageToDelete !== null && slideshow_write($data)) {
            delete_slideshow_image($imageToDelete);
            $message = 'Announcement deleted.';
        } elseif ($imageToDelete === null) {
            $error = 'Announcement not found.';
        } else {
            $error = 'Could not delete the announcement. Please try again.';
        }
    }
}

$slides = slideshow_read()['items'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Announcements | <?= e(SHOP_NAME) ?></title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<main class="admin-wrap">
  <header class="admin-header">
    <div>
      <p class="eyebrow">Storefront content</p>
      <h1>Shop announcements</h1>
      <p class="meta">Add a banner for anniversaries, offers, new arrivals, and other shop news.</p>
    </div>
    <div class="row">
      <a class="btn alt" href="index.php">Manage products</a>
      <a class="btn" href="../index.php">View shop</a>
    </div>
  </header>

  <?php if ($message): ?><div class="msg"><?= e($message) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>

  <section class="box">
    <h2>Create an announcement</h2>
    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add">
      <label for="slide-title">Title</label>
      <input id="slide-title" name="title" maxlength="120" required placeholder="e.g. Celebrating our shop anniversary">
      <label for="slide-description">Description (optional)</label>
      <textarea id="slide-description" name="description" maxlength="500" rows="3" placeholder="Share a short announcement or offer"></textarea>
      <label for="slide-image">Announcement image</label>
      <input id="slide-image" type="file" name="image" accept="image/jpeg,image/png,image/webp" required>
      <p class="meta">JPG, PNG or WEBP · Maximum 8 MB. These images are stored separately from product photos.</p>
      <button type="submit">Add announcement</button>
    </form>
  </section>

  <section class="box">
    <h2>Current announcements (<?= count($slides) ?>)</h2>
    <?php if (!$slides): ?>
      <p class="meta">No announcements yet. Add one above to show a slideshow on the shop home page.</p>
    <?php else: ?>
      <div class="slide-admin-list">
        <?php foreach ($slides as $slide): ?>
        <article class="slide-admin-card">
          <img src="slideshow_uploads/<?= e($slide['image']) ?>" alt="">
          <div>
            <h3><?= e($slide['title']) ?></h3>
            <?php if (!empty($slide['description'])): ?><p class="meta"><?= nl2br(e($slide['description'])) ?></p><?php endif; ?>
            <form method="post" onsubmit="return confirm('Delete this announcement?')">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$slide['id'] ?>">
              <button class="danger" type="submit">Delete announcement</button>
            </form>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
