<?php
declare(strict_types=1);

// Set headers so browsers know to parse this strictly as JSON data
header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store");

try {
    // Reuse your existing connection file wrapper configuration
    require_once("products_fetch_dbase_connection.php");

    // Fetch all columns, including your binary JSON keywords block
    $query = "SELECT id, product_name, image_path, rating_stars, rating_count, price_cents, keywords, is_sold_out FROM products ORDER BY id DESC;";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Loop through the data to decode your binary JSON keywords back into clean arrays
    foreach ($products as $key => $product) {
        if (!empty($product['keywords'])) {
            // FIX: ONLY json_decode here. This converts the database string into a real native PHP array/object, 
            // so that the final json_encode() down below packages everything together smoothly.
            $products[$key]['keywords'] = json_decode($product['keywords']);
        } else {
            // Safe fallback if keywords are blank
            $products[$key]['keywords'] = [];
        }
    }

    // Output the clean data payload array to the javascript handler script
    echo json_encode($products);

} catch (Throwable $e) {
    error_log("Product feed failed: " . $e->getMessage());
    http_response_code(503);
    echo json_encode(["error" => "Products are temporarily unavailable. Check the ecommerce database and try again."]);
}
