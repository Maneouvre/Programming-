<?php
declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $data, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function require_user(?string $role = null): array
{
    $user = $_SESSION['user'] ?? null;
    if (!is_array($user)) {
        respond(['error' => 'Please sign in to continue.'], 401);
    }
    if ($role !== null && $user['role'] !== $role) {
        respond(['error' => 'You do not have permission to do that.'], 403);
    }
    return $user;
}

function required_text(array $input, string $key, int $maxLength = 180): string
{
    $value = clean_text($input[$key] ?? null, $maxLength);
    if ($value === '') {
        respond(['error' => 'Please complete all required fields.'], 422);
    }
    return $value;
}

function product_payload(array $input): array
{
    $name = required_text($input, 'name', 120);
    $category = clean_text($input['category'] ?? '', 80);
    $price = valid_money($input['price'] ?? null);
    $cost = valid_money($input['cost'] ?? null);
    $stock = filter_var($input['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000000]]);
    $lowStock = filter_var($input['low_stock'] ?? 5, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 10000000]]);

    if ($price === null || $cost === null || $stock === false || $lowStock === false) {
        respond(['error' => 'Enter valid prices and whole-number stock quantities.'], 422);
    }
    if ((float)$price <= 0 || (float)$cost > (float)$price) {
        respond(['error' => 'Selling price must be greater than zero and at least the buying price.'], 422);
    }

    return [$name, $category, $price, $cost, $stock, $lowStock];
}

function api(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        respond(['error' => 'Method not allowed.'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) {
        respond(['error' => 'Invalid request data.'], 400);
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        respond(['error' => 'Your session expired. Refresh the page and try again.'], 419);
    }

    $action = clean_text($input['action'] ?? '', 40);
    $pdo = db();

    if ($action === 'status') {
        $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        $user = $_SESSION['user'] ?? null;
        respond([
            'setup_required' => $count === 0,
            'user' => $user,
            'csrf_token' => $_SESSION['csrf_token'],
        ]);
    }

    if ($action === 'setup') {
        if ((int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() !== 0) {
            respond(['error' => 'Setup has already been completed.'], 409);
        }
        $ownerName = required_text($input, 'owner_name', 100);
        $ownerUsername = strtolower(required_text($input, 'owner_username', 40));
        $cashierName = required_text($input, 'cashier_name', 100);
        $cashierUsername = strtolower(required_text($input, 'cashier_username', 40));
        $ownerPassword = $input['owner_password'] ?? '';
        $cashierPassword = $input['cashier_password'] ?? '';
        foreach ([$ownerUsername, $cashierUsername] as $username) {
            if (!preg_match('/^[a-z0-9._-]{3,40}$/', $username)) {
                respond(['error' => 'Usernames must be 3–40 characters using letters, numbers, dots, dashes, or underscores.'], 422);
            }
        }
        if ($ownerUsername === $cashierUsername) {
            respond(['error' => 'Owner and cashier must have different usernames.'], 422);
        }
        if (!is_string($ownerPassword) || strlen($ownerPassword) < 10 || !is_string($cashierPassword) || strlen($cashierPassword) < 10) {
            respond(['error' => 'Both passwords must be at least 10 characters long.'], 422);
        }

        $pdo->beginTransaction();
        try {
            $insert = $pdo->prepare('INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)');
            $insert->execute([$ownerName, $ownerUsername, password_hash($ownerPassword, PASSWORD_DEFAULT), 'owner']);
            $insert->execute([$cashierName, $cashierUsername, password_hash($cashierPassword, PASSWORD_DEFAULT), 'cashier']);
            $pdo->commit();
        } catch (Throwable $exception) {
            $pdo->rollBack();
            throw $exception;
        }
        respond(['message' => 'Accounts created. Sign in with your owner or cashier username.']);
    }

    if ($action === 'login') {
        $username = strtolower(required_text($input, 'username', 40));
        $password = $input['password'] ?? '';
        if (!is_string($password)) {
            respond(['error' => 'Enter a valid password.'], 422);
        }
        $statement = $pdo->prepare('SELECT id, full_name, username, password_hash, role FROM users WHERE username = ?');
        $statement->execute([$username]);
        $user = $statement->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            respond(['error' => 'Incorrect username or password.'], 401);
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'name' => $user['full_name'],
            'username' => $user['username'],
            'role' => $user['role'],
        ];
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        respond(['user' => $_SESSION['user'], 'csrf_token' => $_SESSION['csrf_token']]);
    }

    if ($action === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        respond(['message' => 'Signed out.']);
    }

    $user = require_user();

    if ($action === 'products') {
        $statement = $pdo->query('SELECT id, name, category, price, cost, stock, low_stock FROM products ORDER BY name');
        respond(['products' => $statement->fetchAll()]);
    }

    if ($action === 'save_product') {
        require_user('owner');
        [$name, $category, $price, $cost, $stock, $lowStock] = product_payload($input);
        $id = isset($input['id']) ? valid_id($input['id']) : null;
        if (isset($input['id']) && $id === null) {
            respond(['error' => 'Invalid product.'], 422);
        }
        if ($id === null) {
            $statement = $pdo->prepare('INSERT INTO products (name, category, price, cost, stock, low_stock) VALUES (?, ?, ?, ?, ?, ?)');
            $statement->execute([$name, $category, $price, $cost, $stock, $lowStock]);
        } else {
            $statement = $pdo->prepare('UPDATE products SET name = ?, category = ?, price = ?, cost = ?, stock = ?, low_stock = ? WHERE id = ?');
            $statement->execute([$name, $category, $price, $cost, $stock, $lowStock, $id]);
            $exists = $pdo->prepare('SELECT id FROM products WHERE id = ?');
            $exists->execute([$id]);
            if (!$exists->fetchColumn()) {
                respond(['error' => 'Product not found.'], 404);
            }
        }
        respond(['message' => 'Product saved.']);
    }

    if ($action === 'delete_product') {
        require_user('owner');
        $id = valid_id($input['id'] ?? null);
        if ($id === null) {
            respond(['error' => 'Invalid product.'], 422);
        }
        try {
            $statement = $pdo->prepare('DELETE FROM products WHERE id = ?');
            $statement->execute([$id]);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                respond(['error' => 'This item is in a sale record and cannot be deleted. You can set its stock to zero instead.'], 409);
            }
            throw $exception;
        }
        respond(['message' => 'Product deleted.']);
    }

    if ($action === 'create_sale') {
        $items = $input['items'] ?? null;
        $method = clean_text($input['payment_method'] ?? '', 10);
        $customer = clean_text($input['customer'] ?? '', 120);
        $received = valid_money($input['amount_received'] ?? '0');
        if (!is_array($items) || count($items) < 1 || count($items) > 100) {
            respond(['error' => 'Add at least one product to the sale.'], 422);
        }
        if (!in_array($method, ['cash', 'mpesa', 'credit'], true) || $received === null) {
            respond(['error' => 'Choose a valid payment method and amount.'], 422);
        }
        if ($method === 'credit' && $customer === '') {
            respond(['error' => 'Enter the customer name for a credit sale.'], 422);
        }

        $quantities = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                respond(['error' => 'One of the cart items is invalid.'], 422);
            }
            $id = valid_id($item['id'] ?? null);
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000000]]);
            if ($id === null || $quantity === false) {
                respond(['error' => 'One of the cart items is invalid.'], 422);
            }
            $quantities[$id] = ($quantities[$id] ?? 0) + $quantity;
            if ($quantities[$id] > 10000000) {
                respond(['error' => 'The quantity for an item is too large.'], 422);
            }
        }

        $pdo->beginTransaction();
        try {
            $find = $pdo->prepare('SELECT id, name, price, cost, stock FROM products WHERE id = ? FOR UPDATE');
            $lines = [];
            $totalCents = 0;
            $profitCents = 0;
            foreach ($quantities as $id => $quantity) {
                $find->execute([$id]);
                $product = $find->fetch();
                if (!$product || (int)$product['stock'] < $quantity) {
                    $pdo->rollBack();
                    respond(['error' => 'Stock changed for an item in your cart. Refresh products and try again.'], 409);
                }
                $unitCents = (int)round((float)$product['price'] * 100);
                $costCents = (int)round((float)$product['cost'] * 100);
                $lineCents = $unitCents * $quantity;
                if ($lineCents > 9999999999999 - $totalCents) {
                    $pdo->rollBack();
                    respond(['error' => 'This sale exceeds the maximum supported total.'], 422);
                }
                $totalCents += $lineCents;
                $profitCents += ($unitCents - $costCents) * $quantity;
                $lines[] = ['product' => $product, 'quantity' => $quantity, 'unit_cents' => $unitCents];
            }

            $receivedCents = $method === 'credit' ? 0 : (int)round((float)$received * 100);
            if ($method !== 'credit' && $receivedCents < $totalCents) {
                $pdo->rollBack();
                respond(['error' => 'The amount received is less than the sale total.'], 422);
            }
            $total = number_format($totalCents / 100, 2, '.', '');
            $change = number_format(($receivedCents - $totalCents) / 100, 2, '.', '');
            $statement = $pdo->prepare('INSERT INTO sales (user_id, total, profit, payment_method, amount_received, change_due, customer_name) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $statement->execute([$user['id'], $total, number_format($profitCents / 100, 2, '.', ''), $method, number_format($receivedCents / 100, 2, '.', ''), $method === 'credit' ? '0.00' : $change, $method === 'credit' ? $customer : null]);
            $saleId = (int)$pdo->lastInsertId();

            $lineInsert = $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, product_name, quantity, unit_price, unit_cost) VALUES (?, ?, ?, ?, ?, ?)');
            $stockUpdate = $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?');
            foreach ($lines as $line) {
                $product = $line['product'];
                $lineInsert->execute([$saleId, $product['id'], $product['name'], $line['quantity'], number_format($line['unit_cents'] / 100, 2, '.', ''), $product['cost']]);
                $stockUpdate->execute([$line['quantity'], $product['id']]);
            }
            if ($method === 'credit') {
                $pdo->prepare('INSERT INTO debts (sale_id, customer_name, amount_due, amount_paid) VALUES (?, ?, ?, 0)')->execute([$saleId, $customer, $total]);
            }
            $pdo->commit();

            respond([
                'message' => 'Sale completed.',
                'receipt' => [
                    'id' => $saleId,
                    'date' => date('Y-m-d H:i:s'),
                    'cashier' => $user['name'],
                    'items' => array_map(static function (array $line): array {
                        return [
                            'name' => $line['product']['name'],
                            'quantity' => $line['quantity'],
                            'price' => number_format($line['unit_cents'] / 100, 2, '.', ''),
                            'line_total' => number_format($line['unit_cents'] * $line['quantity'] / 100, 2, '.', ''),
                        ];
                    }, $lines),
                    'total' => $total,
                    'payment_method' => $method,
                    'customer' => $method === 'credit' ? $customer : '',
                    'received' => $method === 'credit' ? '0.00' : number_format($receivedCents / 100, 2, '.', ''),
                    'change' => $method === 'credit' ? '0.00' : $change,
                ],
            ]);
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    if ($action === 'reports') {
        require_user('owner');
        $date = clean_text($input['date'] ?? date('Y-m-d'), 10);
        $parsed = DateTime::createFromFormat('!Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            respond(['error' => 'Choose a valid report date.'], 422);
        }
        $statement = $pdo->prepare(
            "SELECT COUNT(*) AS sale_count, COALESCE(SUM(total), 0) AS total_sales,
             COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN total ELSE 0 END), 0) AS cash_sales,
             COALESCE(SUM(CASE WHEN payment_method = 'mpesa' THEN total ELSE 0 END), 0) AS mpesa_sales,
             COALESCE(SUM(profit), 0) AS estimated_profit
             FROM sales WHERE DATE(created_at) = ?"
        );
        $statement->execute([$date]);
        $totals = $statement->fetch();
        $collections = $pdo->prepare(
            "SELECT COALESCE(SUM(CASE WHEN payment_method = 'cash' THEN amount ELSE 0 END), 0) AS cash_collections,
             COALESCE(SUM(CASE WHEN payment_method = 'mpesa' THEN amount ELSE 0 END), 0) AS mpesa_collections
             FROM debt_payments WHERE DATE(created_at) = ?"
        );
        $collections->execute([$date]);
        $collected = $collections->fetch();
        $totals['cash_sales'] = number_format((float)$totals['cash_sales'] + (float)$collected['cash_collections'], 2, '.', '');
        $totals['mpesa_sales'] = number_format((float)$totals['mpesa_sales'] + (float)$collected['mpesa_collections'], 2, '.', '');
        $lowStock = $pdo->query('SELECT id, name, stock, low_stock FROM products WHERE stock <= low_stock ORDER BY stock, name')->fetchAll();
        $latest = $pdo->prepare('SELECT s.id, s.total, s.payment_method, s.created_at, u.full_name AS cashier FROM sales s JOIN users u ON u.id = s.user_id WHERE DATE(s.created_at) = ? ORDER BY s.id DESC LIMIT 10');
        $latest->execute([$date]);
        respond(['totals' => $totals, 'low_stock' => $lowStock, 'latest_sales' => $latest->fetchAll()]);
    }

    if ($action === 'debts') {
        $statement = $pdo->query('SELECT id, sale_id, customer_name, amount_due, amount_paid, amount_due - amount_paid AS balance, created_at FROM debts WHERE amount_due > amount_paid ORDER BY created_at');
        respond(['debts' => $statement->fetchAll()]);
    }

    if ($action === 'pay_debt') {
        $debtId = valid_id($input['id'] ?? null);
        $amount = valid_money($input['amount'] ?? null);
        $method = clean_text($input['method'] ?? '', 10);
        if ($debtId === null || $amount === null || (float)$amount <= 0 || !in_array($method, ['cash', 'mpesa'], true)) {
            respond(['error' => 'Enter a valid payment amount and method.'], 422);
        }
        $pdo->beginTransaction();
        try {
            $find = $pdo->prepare('SELECT amount_due, amount_paid FROM debts WHERE id = ? FOR UPDATE');
            $find->execute([$debtId]);
            $debt = $find->fetch();
            $amountCents = (int)round((float)$amount * 100);
            $balanceCents = $debt ? (int)round(((float)$debt['amount_due'] - (float)$debt['amount_paid']) * 100) : 0;
            if (!$debt || $amountCents > $balanceCents) {
                $pdo->rollBack();
                respond(['error' => 'Payment is greater than the outstanding balance. Refresh the debt list and try again.'], 409);
            }
            $pdo->prepare('UPDATE debts SET amount_paid = amount_paid + ? WHERE id = ?')->execute([$amount, $debtId]);
            $pdo->prepare('INSERT INTO debt_payments (debt_id, user_id, amount, payment_method) VALUES (?, ?, ?, ?)')->execute([$debtId, $user['id'], $amount, $method]);
            $pdo->commit();
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
        respond(['message' => 'Payment recorded.']);
    }

    respond(['error' => 'Unknown action.'], 404);
}

try {
    require_once __DIR__ . '/db.php';
    api();
} catch (RuntimeException $exception) {
    error_log('SokoFlow configuration error: ' . $exception->getMessage());
    respond(['error' => $exception->getMessage()], 500);
} catch (PDOException $exception) {
    error_log('SokoFlow database error: ' . $exception->getMessage());
    if ($exception->getCode() === '23000') {
        respond(['error' => 'That username is already in use. Choose a different username.'], 409);
    }
    respond(['error' => 'The database is not ready. Check your PHP PDO MySQL settings and import schema.sql in phpMyAdmin.'], 500);
} catch (Throwable $exception) {
    error_log('SokoFlow application error: ' . $exception->getMessage());
    respond(['error' => 'Something went wrong. Please try again.'], 500);
}
