<?php
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/migrations.php';

function now(): string
{
    return date('Y-m-d H:i:s');
}

function getConfig(): array
{
    static $config;
    if (!$config) {
        $config = require __DIR__ . '/config.php';
    }
    return $config;
}

function initializeSystem(): void
{
    runMigrations();
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users WHERE role = 'admin'");
    $count = (int) $stmt->fetchColumn();
    if ($count === 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (name, email, password, role, balance, credit_limit, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?)")
            ->execute(['Yönetici', 'admin@bayi.local', $password, 'admin', 0, 0, now(), now()]);
    }
    if (!file_exists(getConfig()['upload_path'])) {
        mkdir(getConfig()['upload_path'], 0777, true);
    }
}

function getCurrentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    return getUserById((int) $_SESSION['user_id']);
}

function getUserById(int $id): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function findUserByEmail(string $email): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function authenticate(string $email, string $password): ?array
{
    $user = findUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return null;
}

function loginUser(array $user): void
{
    $_SESSION['user_id'] = $user['id'];
}

function logoutUser(): void
{
    unset($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!getCurrentUser()) {
        header('Location: index.php');
        exit;
    }
}

function requireRole(string $role): void
{
    $user = getCurrentUser();
    if (!$user || $user['role'] !== $role) {
        header('Location: index.php');
        exit;
    }
}

function setFlash(string $key, string $message): void
{
    $_SESSION['flash'][$key] = $message;
}

function getFlash(string $key): ?string
{
    if (!empty($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

function formatCurrency(float $amount): string
{
    return number_format($amount, 2, ',', '.') . ' ₺';
}

function getBayiList(): array
{
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM users WHERE role = 'bayi' ORDER BY name");
    return $stmt->fetchAll();
}

function createBayi(array $data): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, balance, credit_limit, telegram_chat_id, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?)');
    $stmt->execute([
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        'bayi',
        $data['balance'] ?? 0,
        $data['credit_limit'] ?? 0,
        $data['telegram_chat_id'] ?? null,
        now(),
        now()
    ]);
}

function updateBayi(int $id, array $data): void
{
    $pdo = getPDO();
    $fields = ['name', 'email', 'balance', 'credit_limit', 'telegram_chat_id'];
    $set = [];
    $values = [];
    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $set[] = "$field = ?";
            $values[] = $data[$field];
        }
    }
    if (!empty($data['password'])) {
        $set[] = 'password = ?';
        $values[] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    $set[] = 'updated_at = ?';
    $values[] = now();
    $values[] = $id;
    $sql = 'UPDATE users SET ' . implode(',', $set) . ' WHERE id = ?';
    $pdo->prepare($sql)->execute($values);
}

function deleteBayi(int $id): void
{
    $pdo = getPDO();
    $pdo->prepare('DELETE FROM users WHERE id = ? AND role = "bayi"')->execute([$id]);
}

function getProducts(): array
{
    $pdo = getPDO();
    $stmt = $pdo->query('SELECT * FROM products ORDER BY category, name');
    return $stmt->fetchAll();
}

function getProduct(int $id): ?array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    return $product ?: null;
}

function saveProduct(array $data, ?int $id = null): int
{
    $pdo = getPDO();
    if ($id) {
        $stmt = $pdo->prepare('UPDATE products SET name=?, category=?, description=?, price=?, stock=?, image_path=?, require_credentials=?, updated_at=? WHERE id=?');
        $stmt->execute([
            $data['name'],
            $data['category'],
            $data['description'] ?? null,
            $data['price'],
            $data['stock'],
            $data['image_path'] ?? null,
            $data['require_credentials'] ?? 0,
            now(),
            $id
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO products (name, category, description, price, stock, image_path, require_credentials, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $data['name'],
            $data['category'],
            $data['description'] ?? null,
            $data['price'],
            $data['stock'],
            $data['image_path'] ?? null,
            $data['require_credentials'] ?? 0,
            now(),
            now()
        ]);
        $id = (int) $pdo->lastInsertId();
    }
    return $id;
}

function deleteProduct(int $id): void
{
    $pdo = getPDO();
    $pdo->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
}

function setCustomPrice(int $productId, int $userId, float $price): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('INSERT INTO product_custom_prices (product_id, user_id, price) VALUES (?,?,?) ON CONFLICT(product_id, user_id) DO UPDATE SET price = excluded.price');
    $stmt->execute([$productId, $userId, $price]);
}

function removeCustomPrice(int $productId, int $userId): void
{
    $pdo = getPDO();
    $pdo->prepare('DELETE FROM product_custom_prices WHERE product_id = ? AND user_id = ?')->execute([$productId, $userId]);
}

function getCustomPricesForProduct(int $productId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT pcp.*, u.name as user_name FROM product_custom_prices pcp JOIN users u ON pcp.user_id = u.id WHERE product_id = ?');
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getPriceForUser(array $product, array $user): float
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT price FROM product_custom_prices WHERE product_id = ? AND user_id = ?');
    $stmt->execute([$product['id'], $user['id']]);
    $price = $stmt->fetchColumn();
    if ($price !== false) {
        return (float) $price;
    }
    return (float) $product['price'];
}

function createOrder(array $user, array $product, int $quantity, array $fields = []): ?int
{
    $pdo = getPDO();
    $unitPrice = getPriceForUser($product, $user);
    $total = $unitPrice * $quantity;
    $newBalance = $user['balance'] - $total;
    $creditLimit = (float) $user['credit_limit'];
    if ($newBalance < $creditLimit) {
        return null;
    }
    if ($product['stock'] < $quantity) {
        return null;
    }
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO orders (user_id, product_id, quantity, unit_price, total_price, status, panel_link, panel_username, panel_password, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute([
            $user['id'],
            $product['id'],
            $quantity,
            $unitPrice,
            $total,
            'beklemede',
            $fields['panel_link'] ?? null,
            $fields['panel_username'] ?? null,
            $fields['panel_password'] ?? null,
            now(),
            now()
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $pdo->prepare('UPDATE users SET balance = ?, updated_at = ? WHERE id = ?')
            ->execute([$newBalance, now(), $user['id']]);

        $pdo->prepare('UPDATE products SET stock = stock - ?, updated_at = ? WHERE id = ?')
            ->execute([$quantity, now(), $product['id']]);

        $pdo->prepare('INSERT INTO transactions (user_id, amount, type, description, related_id, related_type, created_at) VALUES (?,?,?,?,?,?,?)')
            ->execute([$user['id'], -$total, 'siparis', $product['name'], $orderId, 'order', now()]);

        $pdo->commit();
        sendOrderNotifications($orderId);
        return $orderId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updateOrderStatus(int $orderId, string $status): void
{
    $pdo = getPDO();
    $pdo->prepare('UPDATE orders SET status = ?, updated_at = ? WHERE id = ?')
        ->execute([$status, now(), $orderId]);
    if ($status === 'iptal') {
        $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
        if ($order) {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('UPDATE users SET balance = balance + ?, updated_at = ? WHERE id = ?')
                    ->execute([$order['total_price'], now(), $order['user_id']]);
                $pdo->prepare('UPDATE products SET stock = stock + ?, updated_at = ? WHERE id = ?')
                    ->execute([$order['quantity'], now(), $order['product_id']]);
                $pdo->prepare('INSERT INTO transactions (user_id, amount, type, description, related_id, related_type, created_at) VALUES (?,?,?,?,?,?,?)')
                    ->execute([$order['user_id'], $order['total_price'], 'iade', 'Sipariş iptali #' . $orderId, $orderId, 'order', now()]);
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    }
}

function getOrders(array $filters = []): array
{
    $pdo = getPDO();
    $sql = 'SELECT o.*, p.name as product_name, u.name as user_name FROM orders o JOIN products p ON o.product_id = p.id JOIN users u ON o.user_id = u.id';
    $conditions = [];
    $params = [];
    if (!empty($filters['user_id'])) {
        $conditions[] = 'o.user_id = ?';
        $params[] = $filters['user_id'];
    }
    if (!empty($filters['status'])) {
        $conditions[] = 'o.status = ?';
        $params[] = $filters['status'];
    }
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY o.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function createCustomRequest(array $user, int $optionId, string $message): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM custom_request_options WHERE id = ?');
    $stmt->execute([$optionId]);
    $option = $stmt->fetch();
    if (!$option) {
        throw new InvalidArgumentException('Seçenek bulunamadı');
    }
    $pdo->prepare('INSERT INTO custom_requests (user_id, option_id, status, message, total_price, created_at, updated_at) VALUES (?,?,?,?,?,?,?)')
        ->execute([$user['id'], $optionId, 'acik', $message, $option['price'], now(), now()]);
    $requestId = (int) $pdo->lastInsertId();
    addCustomRequestMessage($requestId, $user['id'], $message, null);
    sendCustomRequestNotification($requestId);
    return $requestId;
}

function addCustomRequestMessage(int $requestId, int $senderId, string $message, ?string $attachmentPath): void
{
    $pdo = getPDO();
    $pdo->prepare('INSERT INTO custom_request_messages (request_id, sender_id, message, attachment_path, created_at) VALUES (?,?,?,?,?)')
        ->execute([$requestId, $senderId, $message, $attachmentPath, now()]);
}

function getCustomRequests(array $filters = []): array
{
    $pdo = getPDO();
    $sql = 'SELECT cr.*, cro.title as option_title, u.name as user_name FROM custom_requests cr JOIN custom_request_options cro ON cr.option_id = cro.id JOIN users u ON cr.user_id = u.id';
    $conditions = [];
    $params = [];
    if (!empty($filters['user_id'])) {
        $conditions[] = 'cr.user_id = ?';
        $params[] = $filters['user_id'];
    }
    if (!empty($filters['status'])) {
        $conditions[] = 'cr.status = ?';
        $params[] = $filters['status'];
    }
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY cr.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getCustomRequestMessages(int $requestId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT crm.*, u.name as sender_name, u.role as sender_role FROM custom_request_messages crm JOIN users u ON crm.sender_id = u.id WHERE crm.request_id = ? ORDER BY crm.created_at ASC');
    $stmt->execute([$requestId]);
    return $stmt->fetchAll();
}

function updateCustomRequestStatus(int $requestId, string $status): void
{
    $pdo = getPDO();
    $pdo->prepare('UPDATE custom_requests SET status = ?, updated_at = ? WHERE id = ?')
        ->execute([$status, now(), $requestId]);
}

function createTicket(int $userId, string $subject, string $type = 'destek'): int
{
    $pdo = getPDO();
    $pdo->prepare('INSERT INTO support_tickets (user_id, subject, status, type, created_at, updated_at) VALUES (?,?,?,?,?,?)')
        ->execute([$userId, $subject, 'acik', $type, now(), now()]);
    return (int) $pdo->lastInsertId();
}

function addTicketMessage(int $ticketId, int $senderId, string $message, ?string $attachmentPath = null): void
{
    $pdo = getPDO();
    $pdo->prepare('INSERT INTO support_messages (ticket_id, sender_id, message, attachment_path, created_at) VALUES (?,?,?,?,?)')
        ->execute([$ticketId, $senderId, $message, $attachmentPath, now()]);
}

function getTickets(array $filters = []): array
{
    $pdo = getPDO();
    $sql = 'SELECT st.*, u.name as user_name FROM support_tickets st JOIN users u ON st.user_id = u.id';
    $conditions = [];
    $params = [];
    if (!empty($filters['user_id'])) {
        $conditions[] = 'st.user_id = ?';
        $params[] = $filters['user_id'];
    }
    if (!empty($filters['type'])) {
        $conditions[] = 'st.type = ?';
        $params[] = $filters['type'];
    }
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY st.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getTicketMessages(int $ticketId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT sm.*, u.name as sender_name, u.role as sender_role FROM support_messages sm JOIN users u ON sm.sender_id = u.id WHERE sm.ticket_id = ? ORDER BY sm.created_at ASC');
    $stmt->execute([$ticketId]);
    return $stmt->fetchAll();
}

function updateTicketStatus(int $ticketId, string $status): void
{
    $pdo = getPDO();
    $pdo->prepare('UPDATE support_tickets SET status = ?, updated_at = ? WHERE id = ?')
        ->execute([$status, now(), $ticketId]);
}

function createNotification(string $title, string $body, int $adminId): void
{
    $pdo = getPDO();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('INSERT INTO notifications (title, body, created_at) VALUES (?,?,?)')
            ->execute([$title, $body, now()]);
        $notificationId = (int) $pdo->lastInsertId();
        $bayiler = getBayiList();
        foreach ($bayiler as $bayi) {
            $pdo->prepare('INSERT INTO notification_user (notification_id, user_id, created_at) VALUES (?,?,?)')
                ->execute([$notificationId, $bayi['id'], now()]);
            sendTelegramMessage($bayi['telegram_chat_id'] ?? null, "Yeni bildirim: $title\n$body");
        }
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getUserNotifications(int $userId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT nu.*, n.title, n.body FROM notification_user nu JOIN notifications n ON nu.notification_id = n.id WHERE nu.user_id = ? ORDER BY n.created_at DESC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function markNotificationAsRead(int $notificationUserId): void
{
    $pdo = getPDO();
    $pdo->prepare('UPDATE notification_user SET is_read = 1 WHERE id = ?')->execute([$notificationUserId]);
}

function unreadNotificationCount(int $userId): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM notification_user WHERE user_id = ? AND is_read = 0');
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

function sendTelegramMessage(?string $chatId, string $message): void
{
    if (!$chatId) {
        return;
    }
    $config = getConfig();
    if (empty($config['telegram_bot_token'])) {
        return;
    }
    $token = $config['telegram_bot_token'];
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $payload = [
        'chat_id' => $chatId,
        'text' => $message
    ];
    $context = stream_context_create([
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($payload),
            'timeout' => 3
        ]
    ]);
    @file_get_contents($url, false, $context);
}

function sendOrderNotifications(int $orderId): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT o.*, u.name as user_name, u.telegram_chat_id, p.name as product_name FROM orders o JOIN users u ON o.user_id = u.id JOIN products p ON o.product_id = p.id WHERE o.id = ?');
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order) {
        return;
    }
    $message = sprintf("Yeni sipariş #%d\nBayi: %s\nÜrün: %s\nAdet: %d\nTutar: %s", $orderId, $order['user_name'], $order['product_name'], $order['quantity'], formatCurrency((float) $order['total_price']));
    sendTelegramMessage($order['telegram_chat_id'] ?? null, $message);
    $config = getConfig();
    if (!empty($config['telegram_admin_chat_id'])) {
        sendTelegramMessage($config['telegram_admin_chat_id'], $message);
    }
}

function sendCustomRequestNotification(int $requestId): void
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT cr.*, u.name as user_name, u.telegram_chat_id, cro.title FROM custom_requests cr JOIN users u ON cr.user_id = u.id JOIN custom_request_options cro ON cr.option_id = cro.id WHERE cr.id = ?');
    $stmt->execute([$requestId]);
    $req = $stmt->fetch();
    if (!$req) {
        return;
    }
    $message = sprintf("Yeni talepli ürün isteği #%d\nBayi: %s\nSeçenek: %s\nNot: %s", $requestId, $req['user_name'], $req['title'], $req['message']);
    sendTelegramMessage($req['telegram_chat_id'] ?? null, 'Talebiniz alındı. Talep numarası #' . $requestId);
    $config = getConfig();
    if (!empty($config['telegram_admin_chat_id'])) {
        sendTelegramMessage($config['telegram_admin_chat_id'], $message);
    }
}

function handleUpload(array $file): ?string
{
    if (empty($file['tmp_name'])) {
        return null;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return null;
    }
    $config = getConfig();
    if ($file['size'] > $config['max_upload_size']) {
        throw new RuntimeException('Dosya boyutu çok büyük');
    }
    $mime = mime_content_type($file['tmp_name']);
    $allowed = array_merge($config['allowed_image_types'], ['application/pdf', 'application/zip']);
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Dosya türü desteklenmiyor');
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('upload_', true) . '.' . $ext;
    $target = $config['upload_path'] . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Dosya yüklenemedi');
    }
    return $filename;
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return $token && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function categories(): array
{
    return [
        'Grafik Tasarım',
        'SEO ve Yazılım',
        'Windows Office',
        'WordPress Eklenti ve Tema',
        'Antivirüs ve VPN',
        'Diğer'
    ];
}

function requiresCredentials(string $category): bool
{
    return in_array($category, ['WordPress Eklenti ve Tema'], true);
}

function themeClass(): string
{
    return $_SESSION['theme'] ?? 'light-theme';
}

function toggleTheme(): void
{
    $_SESSION['theme'] = themeClass() === 'light-theme' ? 'dark-theme' : 'light-theme';
}

function getDashboardStats(): array
{
    $pdo = getPDO();
    return [
        'bayi_count' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='bayi'")->fetchColumn(),
        'product_count' => (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),
        'order_count' => (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
        'open_requests' => (int) $pdo->query("SELECT COUNT(*) FROM custom_requests WHERE status='acik'")->fetchColumn(),
        'open_tickets' => (int) $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='acik' AND type='destek'")->fetchColumn(),
        'open_payments' => (int) $pdo->query("SELECT COUNT(*) FROM support_tickets WHERE status='acik' AND type='odeme'")->fetchColumn(),
    ];
}

function topUpBalance(int $userId, float $amount, string $description): void
{
    $pdo = getPDO();
    $pdo->beginTransaction();
    try {
        $pdo->prepare('UPDATE users SET balance = balance + ?, updated_at = ? WHERE id = ?')
            ->execute([$amount, now(), $userId]);
        $pdo->prepare('INSERT INTO transactions (user_id, amount, type, description, created_at) VALUES (?,?,?,?,?)')
            ->execute([$userId, $amount, 'yukleme', $description, now()]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function getTransactions(int $userId): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function importProductsFromCsv(array $file): int
{
    if (empty($file['tmp_name'])) {
        return 0;
    }
    $handle = fopen($file['tmp_name'], 'r');
    if (!$handle) {
        return 0;
    }
    $pdo = getPDO();
    $count = 0;
    while (($row = fgetcsv($handle, 1000, ';')) !== false) {
        if (count($row) < 5) {
            continue;
        }
        [$name, $category, $description, $price, $stock] = $row;
        $stmt = $pdo->prepare('INSERT INTO products (name, category, description, price, stock, created_at, updated_at) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$name, $category, $description, (float) $price, (int) $stock, now(), now()]);
        $count++;
    }
    fclose($handle);
    return $count;
}

function getCustomRequestOptions(): array
{
    $pdo = getPDO();
    return $pdo->query('SELECT * FROM custom_request_options ORDER BY title')->fetchAll();
}
