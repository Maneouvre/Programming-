<?php
declare(strict_types=1);

require_once __DIR__ . '/admin-session.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function adminRespond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if (!adminSessionIsValid()) {
    adminRespond(401, ['error' => 'Please sign in to the admin dashboard.']);
}

if (!isset($_SESSION['admin_csrf'])) {
    $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
}

try {
    require_once __DIR__ . '/database.php';
    $pdo = connectAdminDatabase();
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $action = (string)($_GET['action'] ?? '');
        if ($action === 'dashboard') {
            require_once __DIR__ . '/../auth/database.php';
            $users = connectShopUsers();
            $orders = $pdo->query(
                'SELECT COUNT(*) AS order_count,
                        COALESCE(SUM(total_cents), 0) AS revenue_cents,
                        COALESCE(SUM(order_status = "Order received"), 0) AS new_order_count
                 FROM shop_orders'
            )->fetch();
            $latestOrders = $pdo->query('SELECT * FROM shop_orders ORDER BY created_at DESC LIMIT 5')->fetchAll();
            foreach ($latestOrders as &$latestOrder) {
                $latestOrder['items'] = json_decode((string)$latestOrder['items_json'], true, 512, JSON_THROW_ON_ERROR);
            }
            unset($latestOrder);
            $result = [
                'products' => (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
                'orders' => (int)$orders['order_count'],
                'newOrders' => (int)$orders['new_order_count'],
                'revenueCents' => (int)$orders['revenue_cents'],
                'clients' => (int)$users->query('SELECT COUNT(*) FROM users')->fetchColumn(),
                'csrfToken' => $_SESSION['admin_csrf'],
                'latestOrders' => $latestOrders
            ];
            adminRespond(200, $result);
        }

        if ($action === 'orders') {
            $statement = $pdo->query('SELECT * FROM shop_orders ORDER BY created_at DESC');
            $orders = $statement->fetchAll();
            foreach ($orders as &$order) {
                $order['items'] = json_decode((string)$order['items_json'], true, 512, JSON_THROW_ON_ERROR);
                $order['user_id'] = (int)$order['user_id'];
                $order['subtotal_cents'] = (int)$order['subtotal_cents'];
                $order['tax_cents'] = (int)$order['tax_cents'];
                $order['total_cents'] = (int)$order['total_cents'];
                $order['pickup_fee_cents'] = (int)$order['pickup_fee_cents'];
                unset($order['items_json']);
            }
            unset($order);
            adminRespond(200, ['orders' => $orders]);
        }

        if ($action === 'clients') {
            require_once __DIR__ . '/../auth/database.php';
            $users = connectShopUsers();
            $clients = $users->query('SELECT id, username, email, created_at FROM users ORDER BY created_at DESC')->fetchAll();
            $ordersByUser = [];
            foreach ($pdo->query(
                'SELECT user_id, COUNT(*) AS order_count, COALESCE(SUM(total_cents), 0) AS spend_cents
                 FROM shop_orders GROUP BY user_id'
            )->fetchAll() as $summary) {
                $ordersByUser[(string)$summary['user_id']] = [
                    'orders' => (int)$summary['order_count'],
                    'spendCents' => (int)$summary['spend_cents']
                ];
            }
            foreach ($clients as &$client) {
                $client['id'] = (int)$client['id'];
                $summary = $ordersByUser[(string)$client['id']] ?? ['orders' => 0, 'spendCents' => 0];
                $client['orders'] = $summary['orders'];
                $client['spendCents'] = $summary['spendCents'];
            }
            unset($client);
            adminRespond(200, ['clients' => $clients]);
        }

        if ($action === 'products') {
            $products = $pdo->query(
                'SELECT id, product_name, image_path, rating_stars, rating_count, price_cents, keywords, is_sold_out
                 FROM products ORDER BY id DESC'
            )->fetchAll();
            foreach ($products as &$product) {
                $decodedKeywords = json_decode((string)($product['keywords'] ?? ''), true);
                $product['keywords'] = is_array($decodedKeywords) ? $decodedKeywords : [];
                $product['price_cents'] = (int)$product['price_cents'];
                $product['is_sold_out'] = (bool)$product['is_sold_out'];
            }
            unset($product);
            adminRespond(200, ['products' => $products]);
        }

        adminRespond(404, ['error' => 'Unknown admin data request.']);
    }

    if ($method === 'POST') {
        $input = json_decode((string)file_get_contents('php://input'), true);
        if (!is_array($input)) {
            adminRespond(400, ['error' => 'The admin request is not valid JSON.']);
        }
        if (!hash_equals($_SESSION['admin_csrf'], (string)($input['csrfToken'] ?? ''))) {
            adminRespond(403, ['error' => 'Your admin session expired. Refresh and try again.']);
        }
        if (($input['action'] ?? '') === 'update-product') {
            $productId = filter_var($input['productId'] ?? null, FILTER_VALIDATE_INT);
            $name = trim((string)($input['productName'] ?? ''));
            $price = filter_var($input['priceCents'] ?? null, FILTER_VALIDATE_INT);
            $rating = filter_var($input['ratingStars'] ?? null, FILTER_VALIDATE_FLOAT);
            $ratingCount = filter_var($input['ratingCount'] ?? null, FILTER_VALIDATE_INT);
            $keywords = (string)($input['keywords'] ?? '');
            if (
                $productId === false || $productId < 1 ||
                $name === '' || strlen($name) > 255 || preg_match('//u', $name) !== 1 ||
                $price === false || $price < 1 ||
                $rating === false || $rating < 0 || $rating > 5 ||
                $ratingCount === false || $ratingCount < 0 ||
                strlen($keywords) > 1000 || preg_match('//u', $keywords) !== 1
            ) {
                adminRespond(422, ['error' => 'Check the product name, price, rating, and keywords.']);
            }
            $duplicate = $pdo->prepare(
                'SELECT id FROM products WHERE LOWER(product_name) = LOWER(:name) AND id <> :id LIMIT 1'
            );
            $duplicate->execute(['name' => $name, 'id' => $productId]);
            if ($duplicate->fetchColumn()) {
                adminRespond(409, ['error' => 'Another product already uses that name.']);
            }
            $update = $pdo->prepare(
                'UPDATE products
                 SET product_name = :name, price_cents = :price, rating_stars = :rating,
                     rating_count = :rating_count, keywords = :keywords
                 WHERE id = :id'
            );
            $update->execute([
                'name' => $name,
                'price' => $price,
                'rating' => $rating,
                'rating_count' => $ratingCount,
                'keywords' => json_encode(array_values(array_filter(array_map('trim', explode(',', $keywords)))), JSON_THROW_ON_ERROR),
                'id' => $productId
            ]);
            if ($update->rowCount() === 0) {
                $exists = $pdo->prepare('SELECT 1 FROM products WHERE id = :id');
                $exists->execute(['id' => $productId]);
                if (!$exists->fetchColumn()) {
                    adminRespond(404, ['error' => 'That product could not be found.']);
                }
            }
            adminRespond(200, ['success' => true]);
        }

        if (($input['action'] ?? '') === 'set-product-availability') {
            $productId = filter_var($input['productId'] ?? null, FILTER_VALIDATE_INT);
            $soldOut = $input['soldOut'] ?? null;
            if ($productId === false || $productId < 1 || !is_bool($soldOut)) {
                adminRespond(422, ['error' => 'The product availability update is invalid.']);
            }
            $update = $pdo->prepare('UPDATE products SET is_sold_out = :sold_out WHERE id = :id');
            $update->execute(['sold_out' => $soldOut ? 1 : 0, 'id' => $productId]);
            if ($update->rowCount() === 0) {
                $exists = $pdo->prepare('SELECT 1 FROM products WHERE id = :id');
                $exists->execute(['id' => $productId]);
                if (!$exists->fetchColumn()) {
                    adminRespond(404, ['error' => 'That product could not be found.']);
                }
            }
            adminRespond(200, ['success' => true, 'soldOut' => $soldOut]);
        }

        if (($input['action'] ?? '') === 'delete-product') {
            $productId = filter_var($input['productId'] ?? null, FILTER_VALIDATE_INT);
            if ($productId === false || $productId < 1) {
                adminRespond(422, ['error' => 'The product identifier is invalid.']);
            }
            foreach ($pdo->query('SELECT items_json FROM shop_orders')->fetchAll(PDO::FETCH_COLUMN) as $itemsJson) {
                $items = json_decode((string)$itemsJson, true);
                foreach (is_array($items) ? $items : [] as $item) {
                    if ((string)($item['productId'] ?? '') === (string)$productId) {
                        adminRespond(409, ['error' => 'This product is part of a client order and cannot be deleted.']);
                    }
                }
            }
            $delete = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $delete->execute(['id' => $productId]);
            if ($delete->rowCount() !== 1) {
                adminRespond(404, ['error' => 'That product could not be found.']);
            }
            adminRespond(200, ['success' => true]);
        }

        if (($input['action'] ?? '') !== 'update-order-status') {
            adminRespond(404, ['error' => 'Unknown admin action.']);
        }

        $orderId = trim((string)($input['orderId'] ?? ''));
        $status = (string)($input['status'] ?? '');
        $allowedStatuses = ['Order received', 'Processing', 'Shipped', 'Being delivered', 'Delivered', 'Cancelled'];
        if ($orderId === '' || strlen($orderId) > 64 || !in_array($status, $allowedStatuses, true)) {
            adminRespond(422, ['error' => 'The order status update is invalid.']);
        }
        $update = $pdo->prepare('UPDATE shop_orders SET order_status = :status WHERE order_id = :order_id');
        $update->execute(['status' => $status, 'order_id' => $orderId]);
        if ($update->rowCount() === 0) {
            $exists = $pdo->prepare('SELECT 1 FROM shop_orders WHERE order_id = :order_id');
            $exists->execute(['order_id' => $orderId]);
            if (!$exists->fetchColumn()) {
                adminRespond(404, ['error' => 'That order could not be found.']);
            }
        }
        adminRespond(200, ['success' => true, 'status' => $status]);
    }

    header('Allow: GET, POST');
    adminRespond(405, ['error' => 'Method not allowed.']);
} catch (Throwable $error) {
    error_log('Admin API failed: ' . $error->getMessage());
    adminRespond(503, ['error' => 'The admin data service is unavailable. Check the ecommerce database and try again.']);
}
