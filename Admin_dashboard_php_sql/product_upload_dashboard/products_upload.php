<?php
require_once("products_session.php");
require_once("products_viewer.php");
require_once("products_model.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product</title>
    <style>
        /* --- ERROR ALERTS LAYOUT --- */
.error-container {
    background-color: #fef2f2;
    border-left: 4px solid #ef4444;
    border-radius: 6px;
    padding: 12px 16px;
    margin: 20px 0;
}

.error-message-line {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
}

.error-message-line:last-child {
    margin-bottom: 0;
}

.error-icon {
    font-size: 14px;
    flex-shrink: 0;
}

.error-text {
    color: #991b1b;
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    margin: 0;
}

/* --- SUCCESS ALERTS LAYOUT --- */
.success-container {
    background-color: #f0fdf4;
    border-left: 4px solid #22c55e;
    border-radius: 6px;
    padding: 14px 16px;
    margin: 20px 0;
}

.form-success {
    color: #166534;
    font-family: inherit;
    font-size: 14px;
    font-weight: 600;
    margin: 0;
}

    </style>
    <link rel="stylesheet" href="products_upload.css">
</head>
<body>

<div class="form-container">
    <h2>Add New Product</h2>
    
    <form action="products_upload_formhandler.php" method="POST" enctype="multipart/form-data">
        <div class="form-layout">
            
            <!-- Left Column -->
            <div class="form-column">
                <div class="form-group">
                    <label>Product Name:</label>
                    <?php
                    if (isset($_SESSION["product_data"]["product_name"]) && !isset($_SESSION["errors_upload"]["product_exists"])) {
                        echo '<input type="text" name="product_name" placeholder="e.g. Premium Pro Blender 1200W" value="' . htmlspecialchars($_SESSION["product_data"]["product_name"]) . '" required>';
                        unset($_SESSION["product_data"]["product_name"]);
                    } else {
                        echo '<input type="text" name="product_name" placeholder="e.g. Premium Pro Blender 1200W" required>';
                    }
                    ?>
                </div>

                <div class="form-group">
                    <label>Price (in Cents - e.g., 10747):</label>
                    <div class="price-input-wrapper">
                        <?php
                        if (isset($_SESSION["product_data"]["price_cents"])) {
                            echo '<input type="number" name="price_cents" placeholder="e.g. 10747" value="' . htmlspecialchars((string)$_SESSION["product_data"]["price_cents"]) . '" required>';
                            unset($_SESSION["product_data"]["price_cents"]);
                        } else {
                            echo '<input type="number" name="price_cents" placeholder="e.g. 10747" required>';
                        }
                        ?>
                        <span class="currency-addon">¢ USD</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Keywords (comma-separated):</label>
                    <?php
                    if (isset($_SESSION["product_data"]["keywords"])) {
                        echo '<input type="text" name="keywords" placeholder="kitchen, appliances, blender" value="' . htmlspecialchars($_SESSION["product_data"]["keywords"]) . '">';
                        unset($_SESSION["product_data"]["keywords"]);
                    } else {
                        echo '<input type="text" name="keywords" placeholder="kitchen, appliances, blender">';
                    }
                    ?>
                </div>
            </div>

            <!-- Right Column -->
            <div class="form-column">
                <div class="form-group">
                    <label>Product Image:</label>
                    <div class="upload-wrapper">
                        <input type="file" name="product_image" id="file_input" accept="image/*" required>
                        
                        <div class="upload-dropzone">
                            <div class="upload-icon-circle">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <div class="upload-main-text" id="upload_text">Drag & drop or click to upload</div>
                            <div class="upload-sub-text">Supports JPG, PNG, and WebP formats up to 5MB</div>
                        </div>
                    </div>
                </div>

                <div class="rating-row">
                    <div class="form-group">
                        <label>Rating (Stars):</label>
                        <?php
                        if (isset($_SESSION["product_data"]["rating_stars"])) {
                            echo '<input type="number" name="rating_stars" step="0.1" min="0" max="5" value="' . htmlspecialchars((string)$_SESSION["product_data"]["rating_stars"]) . '">';
                            unset($_SESSION["product_data"]["rating_stars"]);
                        } else {
                            echo '<input type="number" name="rating_stars" step="0.1" min="0" max="5" value="4.5">';
                        }
                        ?>
                    </div>

                    <div class="form-group">
                        <label>Rating Count:</label>
                        <?php
                        if (isset($_SESSION["product_data"]["rating_count"])) {
                            echo '<input type="number" name="rating_count" value="' . htmlspecialchars((string)$_SESSION["product_data"]["rating_count"]) . '">';
                            unset($_SESSION["product_data"]["rating_count"]);
                        } else {
                            echo '<input type="number" name="rating_count" value="128">';
                        }
                        ?>
                    </div>
                </div>
            </div>
            
        </div>

        <?php
        check_upload_errors();
        ?>

        <!-- Form Submission Section  -->
        <div class="form-footer">
            <button type="button" class="btn-cancel" onclick="window.history.back();">Cancel</button>
            <button type="submit">Upload Product</button>
        </div>
    </form>
</div>

<script>
    const fileInput = document.getElementById('file_input');
    const uploadText = document.getElementById('upload_text');
    
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            uploadText.innerText = "Selected: " + this.files[0].name;
        }
    });
</script>

</body>
</html>
