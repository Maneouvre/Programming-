<?php
require_once __DIR__ . '/products_session.php';
require_once __DIR__ . '/../../admin/require-admin.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $Product_name = trim((string)($_POST["product_name"] ?? ""));
    $Price_cents  = (string)($_POST["price_cents"] ?? "");
    $keywords     = trim((string)($_POST["keywords"] ?? ""));
    $product_image = $_FILES["product_image"] ?? null;
    $rating_stars = (string)($_POST["rating_stars"] ?? "0");
    $rating_count = (string)($_POST["rating_count"] ?? "0");
    $target_filepath = null;
    
    try {
        // Importing internal script dependencies
        require_once("products_upload_dbase_connection.php");
        require_once("products_model.php");
        require_once("products_controller.php");

        $errors = [];
        
        $csrfToken = (string)($_POST['csrf_token'] ?? '');
        if (!isset($_SESSION['admin_upload_csrf']) || !hash_equals($_SESSION['admin_upload_csrf'], $csrfToken)) {
            $_SESSION["errors_input"] = ["csrf" => "Your admin session expired. Refresh the page and try again."];
            header("Location: ./products_upload.php");
            exit();
        }

        $priceValue = filter_var($Price_cents, FILTER_VALIDATE_INT);
        if ($priceValue === false || $priceValue < 1) {
            $errors["invalid_price"] = "Enter a positive product price in cents.";
        }
        $ratingValue = filter_var($rating_stars, FILTER_VALIDATE_FLOAT);
        if ($ratingValue === false || $ratingValue < 0 || $ratingValue > 5) {
            $errors["invalid_rating"] = "Rating must be between 0 and 5 stars.";
        }
        $ratingCountValue = filter_var($rating_count, FILTER_VALIDATE_INT);
        if ($ratingCountValue === false || $ratingCountValue < 0) {
            $errors["invalid_rating_count"] = "Rating count must be zero or greater.";
        }
        
        if (is_image_empty($product_image)) {
            $errors["empty_image"] = "Please upload a product image";
        } else if (is_image_invalid($product_image)) {
            $errors["invalid_image"] = "Please upload a valid image (JPEG, PNG, or WEBP only)";
        }

        $result = get_product_by_name($pdo, $Product_name);
        
        if (is_product_name_empty($Product_name)) {
            $errors["empty_name"] = "Please fill in the name fields";
        } else if (does_product_name_match($result, $Product_name)) {
            $errors["duplicate_product"] = "The product name you entered already exists";
        }
        if ($errors) {
            $_SESSION["errors_input"] = $errors;
            
            // Saves current input values to keep the form sticky
            $product_data = [
                "product_name" => $Product_name,
                "price_cents"  => $Price_cents,
                "keywords"     => $keywords,
                "rating_stars" => $rating_stars,
                "rating_count" => $rating_count
            ];
            $_SESSION["product_data"] = $product_data;
            header("Location: ./products_upload.php");
            die();
        }

        if (empty($errors)) {
            $upload_dir = "uploads/";
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $mimeType = mime_content_type($product_image["tmp_name"]);
            $extensions = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];
            if (!isset($extensions[$mimeType]) || $product_image["size"] > 5 * 1024 * 1024) {
                $_SESSION["errors_input"] = ["invalid_image" => "Upload a JPEG, PNG, or WebP image no larger than 5 MB."];
                header("Location: ./products_upload.php");
                exit();
            }
            $file_ext = $extensions[$mimeType];
            $target_filepath = $upload_dir . uniqid("prod_", true) . "." . $file_ext;

            // Move the file from temporary staging to your uploads folder
            if (move_uploaded_file($product_image["tmp_name"], $target_filepath)) {
                
                $final_keywords = format_keywords_for_db($keywords);
                
                upload_product(
                    $pdo, 
                    $Product_name, 
                    (int)$priceValue,
                    $final_keywords, 
                    $target_filepath, 
                    (float)$ratingValue,
                    (int)$ratingCountValue
                );

                unset($_SESSION['admin_upload_csrf']);
                header("Location: ./products_upload.php?upload=success");
                exit();
            } else {
                $_SESSION["errors_input"] = ["upload_system" => "Failed to save the image file to the server folder."];
                header("Location: ./products_upload.php");
                exit();
            }
        }

    } catch (Throwable $e) {
        if (is_string($target_filepath) && is_file($target_filepath)) {
            unlink($target_filepath);
        }
        error_log("Product upload failed: " . $e->getMessage());
        http_response_code(503);
        echo "Product upload is temporarily unavailable. Check the database connection and try again.";
        exit();
    }
} else {
    header("Location: ./products_upload.php");
    exit();
}
