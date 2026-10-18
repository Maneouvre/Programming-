<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Gets the values from the form inputs
    $Product_name = $_POST["product_name"] ?? "";
    $Price_cents  = $_POST["price_cents"] ?? "";
    $keywords     = $_POST["keywords"] ?? "";
    $product_image = $_FILES["product_image"] ?? null;
    $rating_stars = $_POST["rating_stars"] ?? "0";
    $rating_count = $_POST["rating_count"] ?? "0";
    
    try {
        // Importing internal script dependencies
        require_once("products_upload_dbase_connection.php");
        require_once("products_model.php");
        require_once("products_controller.php");

        // ERROR HANDLERS
        $errors = [];
        
        // FIX 1: Passed $Price_cents here instead of $Product_name
        if (is_price_empty($Price_cents)) {
            $errors["empty_price"] = "Please Enter the cost of product";
        }
        
        if (is_keywords_empty($keywords)) {
            $errors["empty_keywords"] = "Please Enter the keyword";
        }
        
        if (is_image_empty($product_image)) {
            $errors["empty_image"] = "Please upload a product image";
        } else if (is_image_invalid($product_image)) {
            $errors["invalid_image"] = "Please upload a valid image (JPEG, PNG, or WEBP only)";
        }

        // Running duplicate checks against existing names
        $result = get_product_by_name($pdo, $Product_name);
        
        if (is_product_name_empty($Product_name)) {
            $errors["empty_name"] = "Please fill in the name fields";
        } else if (does_product_name_match($result, $Product_name)) {
            $errors["duplicate_product"] = "The product name you entered already exists";
        }
                     
        require_once("products_session.php");
        
        // Checks if validation errors exist
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
            
            // AUTOMATIC FIX: Build the uploads path directory safely if missing from disk
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_ext = pathinfo($product_image["name"], PATHINFO_EXTENSION);
            $target_filepath = $upload_dir . uniqid("prod_", true) . "." . $file_ext;

            // Move the file from temporary staging to your uploads folder
            if (move_uploaded_file($product_image["tmp_name"], $target_filepath)) {
                
                // FIX 2: Format keywords string to valid JSON to pass your DB constraint rule safely
                $final_keywords = format_keywords_for_db($keywords);
                
                upload_product(
                    $pdo, 
                    $Product_name, 
                    (int)$Price_cents, 
                    $final_keywords, 
                    $target_filepath, 
                    (float)$rating_stars, 
                    (int)$rating_count
                );

                header("Location: ./products_upload.php?upload=success");
                exit();
            } else {
                $_SESSION["errors_input"]["upload_system"] = "Failed to save the image file to the server folder.";
                header("Location: ./products_upload.php");
                exit();
            }
        }

    } catch (PDOException $e) {
        die("Query Failed: " . $e->getMessage());
    }
} else {
    header("Location: ./products_upload.php");
    exit();
}
