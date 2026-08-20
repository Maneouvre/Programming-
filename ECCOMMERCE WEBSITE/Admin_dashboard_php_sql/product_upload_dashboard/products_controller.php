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

function is_price_empty($price_cents): bool {
    if (empty($price_cents) && $price_cents !== 0 && $price_cents !== 0.0) {
        return true;
    }
    return false;
}

function is_keywords_empty(string $keywords): bool {
    if (empty($keywords) || trim($keywords) === "") {
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

// FIX: Added string $Product_name parameter so the function can access it
function does_product_name_match($result, string $Product_name){
    if ($result && strtolower($result['product_name']) === strtolower(trim($Product_name))) {
        return true; // The strings match
    } else {
        return false; // No match found
    }
}

// FIX FOR CONSTRAINT VIOLATION: Converts comma text string into clean binary JSON array layout
function format_keywords_for_db(string $keywords): string {
    if (empty($keywords) || trim($keywords) === "") {
        return "[]"; 
    }
    $keywords_array = array_map('trim', explode(',', $keywords));
    return json_encode($keywords_array);
}

function upload_product(object $pdo, string $Product_name, int $Price_cents, string $keywords, string $product_image, float $rating_stars, int $rating_count) {
   set_product($pdo, $Product_name, $Price_cents, $keywords, $product_image, $rating_stars, $rating_count);
}
