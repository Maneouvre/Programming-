<?php
// Enforce strict JSON output communications
header('Content-Type: application/json');

// Check if a valid POST request arrived with our deletion target keys
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    
    $host   = "localhost";
    $dbname = "ecommerce_db";
    $user   = "root";
    $pass   = "";

    try {
        // Initialize secure PDO connections configurations
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $productId = intval($_POST['delete_id']);

        // Prepare query statements against SQL parameter injection vectors
        $stmt = $pdo->prepare("DELETE FROM `products` WHERE `id` = :id");
        $stmt->execute(['id' => $productId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Product removed from your database catalog successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product identifier was not located inside system tables.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database failure: ' . $e->getMessage()]);
    }
    exit;
}
