<?php
declare(strict_types=1);
function get_product_by_name(object $pdo, string $Product_name) {
    $query = "SELECT * FROM products WHERE LOWER(product_name) = LOWER(:Product_name);";
    $stmt = $pdo->prepare($query);
    //Execute with the sanitized variable string
    $stmt->execute([':product_name' => trim($Product_name)]);
    //Fetch the record if it exists
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result;
}
function set_product(object $pdo, string $product_name, int $price_cents, string $keywords, string $product_image, float $rating_stars, int $rating_count) {
    $query = "INSERT INTO products (product_name, price_cents, keywords, product_image, rating_stars, rating_count) 
              VALUES (:product_name, :price_cents, :keywords, :product_image, :rating_stars, :rating_count);";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(":product_name", $Product_name);
    $stmt->bindParam(":price_cents", $Price_cents);
    $stmt->bindParam(":keywords", $keywords);
    $stmt->bindParam(":product_image", $product_image);
    $stmt->bindParam(":rating_stars", $rating_stars);
    $stmt->bindParam(":rating_count", $rating_count);
    
    $stmt->execute();
}