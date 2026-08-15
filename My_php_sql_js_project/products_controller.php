<?php
//error reporting
declare(strict_types=1);
function is_product_name_empty(string $Product_name){
    // trim() removes accidental blank spaces typed by the user
    if (empty($Product_name) || trim($Product_name) === ""){
        return true;
    }
    else{
        return false;
    }
}
function is_price_empty($price_cents) {
    // 0 can sometimes be seen as empty, so we check if it's null, empty string, or false
    if (empty($price_cents) && $price_cents !== 0 && $price_cents !== 0.0) {
        return true;
    }
    return false;
}

function is_image_empty($product_image){
    // Form uploads check the 'error' key for code 4 (UPLOAD_ERR_NO_FILE)
    if (empty($product_image) || !isset($product_image['error']) || $product_image['error'] === 4){
        return true;
    }
    else{
        return false;
    }
}
function is_image_invalid($product_image){
    // 1. Check if the file is completely missing or empty first
    if (empty($product_image) || !isset($product_image['tmp_name']) || $product_image['error'] === 4){
        return true;
    }
    else{
        // 2. Get the actual file type using PHP's built-in mime_content_type
        $file_type = mime_content_type($product_image['tmp_name']);
        
        // 3. Define your allowed formats
        $allowed_formats = ['image/jpeg', 'image/png', 'image/webp'];
        
        // 4. If the file type is NOT in our allowed list, return true (it is invalid)
        if (!in_array($file_type, $allowed_formats)) {
            return true;
        }
        else {
            return false;
        }
    }
}

function does_product_name_match($result){
if ($result && strtolower($result['product_name']) === strtolower(trim($Product_name))) {
        return true; // The strings match
    } else {
        return false; // No match found
    };}
function upload_product(object $pdo, string $product_name, int $price_cents, string $keywords, string $product_image, float $rating_stars, int $rating_count) {
   set_product($pdo, $product_name, $price_cents, $keywords, $product_image, $rating_stars, $rating_count);
}