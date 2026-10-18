<?php
// Look one level up (../) to find db.php in your root folder
require __DIR__ . '/../db.php';
require_admin();

// Handle Item / Slideshow Update & Image File Upload Processing Actions
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Fetch current datasets safely
    $data = db_read();
    $items = $data['items'] ?? [];

    // 2. Identify target action type
    $action = $_POST['action'] ?? '';
    $target_id = intval($_POST['id'] ?? 0);

    // ACTION: UPLOAD NEW IMAGE FOR SLIDESHOW ON AN EXISTING PRODUCT
    if ($action === 'upload_slide' && $target_id > 0) {
        // Leverages your central save_image() function to auto-resize files straight to the root uploads/ folder
        [$img, $ierr] = save_image($_FILES['slide_image'] ?? null);
        
        if ($ierr) {
            $message = "<div class='alert error'>" . e($ierr) . "</div>";
        } elseif ($img) {
            // Find old image file to delete from root folder before applying replacement string reference
            foreach ($items as &$p) {
                if (intval($p['id']) === $target_id) {
                    if (!empty($p['image'])) {
                        delete_image($p['image']);
                    }
                    $p['image'] = $img;
                    break;
                }
            }
            unset($p);
            $data['items'] = $items;
            db_write($data); // Persist modifications back to storage engine
            $message = "<div class='alert success'>Slide image uploaded and optimized successfully!</div>";
        } else {
            $message = "<div class='alert error'>Please select a valid image asset file.</div>";
        }
    }

    // ACTION: UPDATE DESCRIPTIVE METADATA (NAME / EXTRA TEXTS)
    if ($action === 'update_text' && $target_id > 0) {
        $updated_name = trim($_POST['product_name'] ?? '');
        $updated_desc = trim($_POST['product_desc'] ?? '');

        foreach ($items as &$p) {
            if (intval($p['id']) === $target_id) {
                $p['name'] = cut($updated_name, 120);
                $p['description'] = cut($updated_desc, 500);
                break;
            }
        }
        unset($p);
        $data['items'] = $items;
        db_write($data);
        $message = "<div class='alert success'>Texts updated successfully!</div>";
    }

    // Refresh memory tracking pool pointers
    $products = array_reverse(db_read()['items']);
} else {
    $products = array_reverse(db_read()['items']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slideshow & Content Manager Dashboard</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f1e7; 
            --card: #ffffff;
            --ink: #2b221a; 
            --muted: #7c6e62;
            --line: #e3dacd;
            --accent: #8b2612; 
            --ok: #8b2612;
            --primary: #25d366;
            --dark: #1e293b;
            --light: #f8fafc;
            --border: #cbd5e1;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            margin: 0;
            padding: 20px;
        }
        .admin-wrap {
            max-width: 1000px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--light);
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        h1 { margin: 0; font-size: 24px; color: var(--dark); }
        .back-btn {
            background-color: var(--dark);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert.success { background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .alert.error { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        
        .management-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .management-table th, .management-table td {
            padding: 14px;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }
        .management-table th { background-color: var(--light); font-weight: 600; }
        .slide-preview-box {
            width: 110px;
            height: 75px;
            background-color: #e2e8f0;
            border-radius: 6px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #64748b;
        }
        .slide-preview-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .action-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 10px;
        }
        .input-text {
            padding: 8px;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: 13px;
            width: 100%;
            box-sizing: border-box;
        }
        .textarea-meta { height: 50px; resize: vertical; }
        .file-selector { font-size: 12px; }
        .btn-submit {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            white-space: nowrap;
        }
        .btn-submit:hover { opacity: 0.9; }
        .btn-update-text { background-color: #3b82f6; }
    </style>
</head>
<body>

<div class="admin-wrap">
    <header>
        <div>
            <h1>Shop Showcase Content & Slideshow Manager</h1>
            <p style="margin: 5px 0 0 0; color: #64748b; font-size: 14px;">Upload full-screen slideshow pictures and control display text captions</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="index.php" class="back-btn">← Back To Upload Products</a>
            <a href="../index.php" class="back-btn" target="_blank">← View Live Shop</a>
        </div>
    </header>

    <?= $message ?>

    <table class="management-table">
        <thead>
            <tr>
                <th style="width: 15%;">Current Slide</th>
                <th style="width: 45%;">Slide Header & Description Text</th>
                <th style="width: 40%;">Replace / Upload New Random Slide</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <!-- COLUMN 1: VISUAL SLIDE PREVIEW -->
                <td>
                    <div class="slide-preview-box">
                        <?php if (!empty($p['image'])): ?>
                            <!-- Fixed: Point image preview backward into the unified root uploads folder -->
                            <img src="../uploads/<?= e($p['image']) ?>" alt="Slide Preview">
                        <?php else: ?>
                            No Image Set
                        <?php endif; ?>
                    </div>
                    <div style="font-size:11px; color:#94a3b8; margin-top:4px; text-transform:uppercase;">ID: <?= (int)$p['id'] ?></div>
                </td>

                <!-- COLUMN 2: CAPTION DATA FORMS -->
                <td>
                    <form method="POST" class="action-form">
                        <input type="hidden" name="action" value="update_text">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#475569;">Slide Main Title / Name:</label>
                            <input type="text" name="product_name" class="input-text" value="<?= e($p['name']) ?>" required>
                        </div>
                        <div>
                            <label style="font-size:11px; font-weight:600; color:#475569;">Slide Description / Price Meta:</label>
                            <textarea name="product_desc" class="input-text textarea-meta" placeholder="Add extra promotion text details here..."><?= e($p['description']) ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit btn-update-text">Save Changes</button>
                    </form>
                </td>

                <!-- COLUMN 3: FILE UPLOAD OPERATIONS -->
                <td>
                    <form method="POST" enctype="multipart/form-data" class="action-form" style="border-left: 2px dashed #e2e8f0; padding-left: 15px;">
                        <input type="hidden" name="action" value="upload_slide">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        
                        <label style="font-size:12px; font-weight:500;">Select Image File:</label>
                        <p style="font-size:11px; color:#64748b; margin: 4px 0 8px 0;">Files are compressed and saved directly into the main root uploads folder.</p>
                        
                        <!-- Added capture="environment" to natively trigger back camera on smartphones -->
                        <input type="file" name="slide_image" class="file-selector" accept="image/*" required capture="environment" style="margin-bottom: 10px;">
                        
                        <button type="submit" class="btn-submit">Upload & Replace Slide</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
