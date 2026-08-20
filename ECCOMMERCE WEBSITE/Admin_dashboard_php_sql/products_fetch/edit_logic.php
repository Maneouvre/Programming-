<?php
// Enforce strict JSON output communications across browser interfaces
header('Content-Type: application/json');

$host   = "localhost";
$dbname = "ecommerce_db";
$user   = "root";
$pass   = "";

try {
    // 1. Initialize safe PDO connection instance structures
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // ===================================================
    // ROUTE A: DELETION INTERCEPT CHANNELS
    // ===================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
        $productId = intval($_POST['delete_id']);

        $stmt = $pdo->prepare("DELETE FROM `products` WHERE `id` = :id");
        $stmt->execute(['id' => $productId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Product removed from database catalog successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Product identifier was not located inside system tables.']);
        }
        exit;
    }

    // ===================================================
    // ROUTE B: EDIT UPDATE INTERCEPT CHANNELS
    // ===================================================
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
        
        $id        = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
        $name      = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
        $priceIn   = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
        $priceCels = intval($priceIn * 100); 
        $rawKeywords = isset($_POST['keywords']) ? trim($_POST['keywords']) : '';

        // Validate crucial fields are not transmitted empty
        if (empty($id) || empty($name)) {
            echo json_encode(['success' => false, 'message' => 'Validation error: Vital fields are empty.']);
            exit;
        }

        // Clean spaces and safely turn comma list into strict JSON arrays format
        $kwArray = [];
        if (!empty($rawKeywords)) {
            $exploded = explode(',', $rawKeywords);
            foreach ($exploded as $kw) {
                $trimmed = trim($kw);
                if ($trimmed !== '') {
                    $kwArray[] = $trimmed;
                }
            }
        }
        $jsonKeywords = json_encode($kwArray);

        // Prepare parameterized query statement against execution injection vectors
        $stmt = $pdo->prepare("UPDATE `products` SET `product_name` = :name, `price_cents` = :price, `keywords` = :keywords WHERE `id` = :id");
        $stmt->execute([
            'name'     => $name,
            'price'    => $priceCels,
            'keywords' => $jsonKeywords, 
            'id'       => $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Product records updated successfully inside your database context!']);
        exit;
    }

    // Default response if accessed directly without proper payload flags
    echo json_encode(['success' => false, 'message' => 'No active route request intercepts triggered.']);
    exit;

} catch (PDOException $e) {
    // FIX: This explicitly catches database exceptions and fixes the structural crash
    echo json_encode(['success' => false, 'message' => 'Database failure error exception: ' . $e->getMessage()]);
    exit;
}
