<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Product</title>
    <link rel="stylesheet" href="products_upload.css">
</head>
<body>

<div class="form-container">
    <h2>Add New Product</h2>
    
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <div class="form-layout">
            
            <!-- Left Column -->
            <div class="form-column">
                <div class="form-group">
                    <label>Product Name:</label>
                    <input type="text" name="product_name" placeholder="e.g. Premium Pro Blender 1200W" required>
                </div>

                <div class="form-group">
                    <label>Price (in Cents - e.g., 10747):</label>
                    <div class="price-input-wrapper">
                        <input type="number" name="price_cents" placeholder="e.g. 10747" required>
                        <span class="currency-addon">¢ USD</span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Keywords (comma-separated):</label>
                    <input type="text" name="keywords" placeholder="kitchen, appliances, blender">
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
                        <input type="number" name="rating_stars" step="0.1" min="0" max="5" value="4.5">
                    </div>

                    <div class="form-group">
                        <label>Rating Count:</label>
                        <input type="number" name="rating_count" value="128">
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Form Submission Sectionton  -->
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
