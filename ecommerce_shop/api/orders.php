<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function respond(int $status, array $payload): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

if (!isset($_SESSION['user_id']) || !ctype_digit((string)$_SESSION['user_id'])) {
    respond(401, ['error' => 'Please sign in before accessing orders.']);
}

try {
    require_once __DIR__ . '/../admin/database.php';
    $pdo = connectAdminDatabase();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if (!isset($_SESSION['order_csrf'])) {
            $_SESSION['order_csrf'] = bin2hex(random_bytes(32));
        }

        $statement = $pdo->prepare('SELECT * FROM shop_orders WHERE user_id = :user_id ORDER BY created_at DESC');
        $statement->execute(['user_id' => (int)$_SESSION['user_id']]);
        $orders = $statement->fetchAll();
        foreach ($orders as &$order) {
            $order['id'] = (string)$order['order_id'];
            $order['items'] = json_decode((string)$order['items_json'], true, 512, JSON_THROW_ON_ERROR);
            $order['createdAt'] = (string)$order['created_at'];
            $order['status'] = (string)$order['order_status'];
            $order['total'] = (int)$order['total_cents'];
            unset(
                $order['items_json'],
                $order['created_at'],
                $order['updated_at'],
                $order['order_status'],
                $order['total_cents']
            );
        }
        unset($order);
        respond(200, ['orders' => $orders, 'csrf_token' => $_SESSION['order_csrf']]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: GET, POST');
        respond(405, ['error' => 'Method not allowed.']);
    }

    $input = json_decode((string)file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(400, ['error' => 'The order request is not valid JSON.']);
    }
    $csrfToken = (string)($input['csrf_token'] ?? '');
    if (!isset($_SESSION['order_csrf']) || !hash_equals($_SESSION['order_csrf'], $csrfToken)) {
        respond(403, ['error' => 'Your checkout session expired. Refresh and try again.']);
    }

    $customerName = trim((string)($input['customer']['name'] ?? ''));
    $email = trim((string)($_SESSION['user_email'] ?? ''));
    $countyKey = (string)($input['pickup']['countyKey'] ?? '');
    $stationId = (string)($input['pickup']['stationId'] ?? '');
    $paymentMethod = (string)($input['paymentMethod'] ?? '');
    $requestedItems = $input['items'] ?? null;

    $stations = [
        'nairobi' => [
            'nbi-cbd' => ['county' => 'Nairobi', 'station' => 'Nairobi CBD - Tom Mboya St Hub (G4S)', 'fee' => 200],
            'nbi-west' => ['county' => 'Nairobi', 'station' => 'Westlands - Sarit Centre Agent Desk', 'fee' => 350]
        ],
        'mombasa' => [
            'msa-digo' => ['county' => 'Mombasa', 'station' => 'Mombasa CBD - Digo Road Agent Node', 'fee' => 400]
        ],
        'kisumu' => [
            'ksm-mega' => ['county' => 'Kisumu', 'station' => 'Kisumu - Mega Plaza Main Hub (Wells Fargo)', 'fee' => 450]
        ],
        'uasin' => [
            'eld-cbd' => ['county' => 'Uasin Gishu (Eldoret)', 'station' => 'Eldoret CBD - Zion Mall Ground Station', 'fee' => 450]
        ]
    ];
    $allowedPayments = ['M-Pesa', 'Card', 'Bank transfer', 'PayPal'];
    if (
        $customerName === '' || strlen($customerName) > 480 || preg_match('//u', $customerName) !== 1 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        !isset($stations[$countyKey][$stationId]) ||
        !in_array($paymentMethod, $allowedPayments, true) ||
        !is_array($requestedItems) || count($requestedItems) < 1 || count($requestedItems) > 50
    ) {
        respond(422, ['error' => 'Enter valid customer, pickup, payment, and cart details.']);
    }

    $deliveryOptions = json_decode(
        (string)file_get_contents(__DIR__ . '/../backend/deliveryOptions.json'),
        true,
        512,
        JSON_THROW_ON_ERROR
    );
    $deliveryPrices = [];
    $deliveryDays = [];
    foreach ($deliveryOptions as $option) {
        $deliveryPrices[(string)$option['id']] = (int)$option['priceCents'];
        $deliveryDays[(string)$option['id']] = (int)$option['deliveryDays'];
    }

    $productIds = [];
    foreach ($requestedItems as $item) {
        if (!is_array($item) || !isset($item['productId'], $item['quantity'], $item['deliveryOptionId'])) {
            respond(422, ['error' => 'One or more cart items are invalid.']);
        }
        $productId = filter_var($item['productId'], FILTER_VALIDATE_INT);
        $quantity = filter_var($item['quantity'], FILTER_VALIDATE_INT);
        $deliveryId = (string)$item['deliveryOptionId'];
        if ($productId === false || $productId < 1 || $quantity === false || $quantity < 1 || $quantity > 10 || !isset($deliveryPrices[$deliveryId])) {
            respond(422, ['error' => 'One or more cart items have invalid quantities or delivery options.']);
        }
        $productIds[(string)$productId] = ['quantity' => $quantity, 'delivery_id' => $deliveryId];
    }

    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $productQuery = $pdo->prepare("SELECT id, product_name, image_path, price_cents, is_sold_out FROM products WHERE id IN ({$placeholders})");
    $productQuery->execute(array_map('intval', array_keys($productIds)));
    $products = [];
    foreach ($productQuery->fetchAll() as $product) {
        $products[(string)$product['id']] = $product;
    }
    if (count($products) !== count($productIds)) {
        respond(422, ['error' => 'A product in your cart is no longer available. Refresh the cart and try again.']);
    }
    foreach ($products as $product) {
        if ((bool)$product['is_sold_out']) {
            respond(409, ['error' => 'A product in your cart has sold out. Remove it from your cart and try again.']);
        }
    }

    $subtotal = 0;
    $items = [];
    $createdAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    foreach ($productIds as $productId => $details) {
        $product = $products[$productId];
        $price = (int)$product['price_cents'];
        $subtotal += ($price * $details['quantity']) + $deliveryPrices[$details['delivery_id']];
        $items[] = [
            'productId' => $productId,
            'name' => (string)$product['product_name'],
            'image' => (string)$product['image_path'],
            'price' => $price,
            'quantity' => $details['quantity'],
            'deliveryOptionId' => $details['delivery_id'],
            'estimatedDeliveryAt' => $createdAt
                ->modify('+' . $deliveryDays[$details['delivery_id']] . ' days')
                ->format(DateTimeInterface::ATOM)
        ];
    }
    $pickup = $stations[$countyKey][$stationId];
    $subtotal += $pickup['fee'];
    $tax = (int)round($subtotal * 0.1);
    $total = $subtotal + $tax;
    $orderId = 'MN-' . strtoupper(bin2hex(random_bytes(8)));

    require_once __DIR__ . '/../auth/database.php';
    $userPdo = connectShopUsers();
    $userQuery = $userPdo->prepare('SELECT email FROM users WHERE id = :id LIMIT 1');
    $userQuery->execute(['id' => (int)$_SESSION['user_id']]);
    $user = $userQuery->fetch();
    if (!$user) {
        respond(401, ['error' => 'Your customer account is unavailable. Please sign in again.']);
    }

    $insert = $pdo->prepare(
        'INSERT INTO shop_orders
            (order_id, user_id, customer_name, customer_email, county, pickup_station, pickup_fee_cents,
             payment_method, subtotal_cents, tax_cents, total_cents, order_status, items_json)
         VALUES
            (:order_id, :user_id, :customer_name, :customer_email, :county, :pickup_station, :pickup_fee,
             :payment_method, :subtotal, :tax, :total, :status, :items)'
    );
    $insert->execute([
        'order_id' => $orderId,
        'user_id' => (int)$_SESSION['user_id'],
        'customer_name' => $customerName,
        'customer_email' => (string)$user['email'],
        'county' => $pickup['county'],
        'pickup_station' => $pickup['station'],
        'pickup_fee' => $pickup['fee'],
        'payment_method' => $paymentMethod,
        'subtotal' => $subtotal,
        'tax' => $tax,
        'total' => $total,
        'status' => 'Order received',
        'items' => json_encode($items, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES)
    ]);

    unset($_SESSION['order_csrf']);
    respond(201, [
        'order' => [
            'id' => $orderId,
            'createdAt' => $createdAt->format(DateTimeInterface::ATOM),
            'customer' => ['name' => $customerName, 'email' => (string)$user['email']],
            'pickup' => $pickup,
            'paymentMethod' => $paymentMethod,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'Order received',
            'items' => $items
        ]
    ]);
} catch (Throwable $error) {
    error_log('Order endpoint failed: ' . $error->getMessage());
    respond(503, ['error' => 'Orders are temporarily unavailable. Please try again.']);
}
