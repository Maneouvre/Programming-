<?php
declare(strict_types=1);
function get_product_by_name(object $pdo, string $Product_name) {
    $query = "SELECT * FROM products WHERE LOWER(product_name) = LOWER(:Product_name);";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':Product_name' => trim($Product_name)]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}


function set_product(object $pdo, string $Product_name, int $Price_cents, string $keywords, string $product_image, float $rating_stars, int $rating_count) {
    $query = "INSERT INTO products (product_name, image_path, rating_stars, rating_count, price_cents, keywords) 
              VALUES (:product_name, :image_path, :rating_stars, :rating_count, :price_cents, :keywords);";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":product_name", $Product_name);
    $stmt->bindParam(":image_path", $product_image); 
    $stmt->bindParam(":rating_stars", $rating_stars);
    $stmt->bindParam(":rating_count", $rating_count);
    $stmt->bindParam(":price_cents", $Price_cents);
    $stmt->bindParam(":keywords", $keywords);
    
    $stmt->execute();
}
