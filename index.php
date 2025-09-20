<?php
require __DIR__ . '/init.php';

$page = $_GET['page'] ?? ($currentUser ? ($currentUser['role'] === 'admin' ? 'admin_dashboard' : 'bayi_dashboard') : 'login');

if (!$currentUser && !in_array($page, ['login'], true)) {
    header('Location: index.php?page=login');
    exit;
}

if ($page === 'logout') {
    logoutUser();
    header('Location: index.php?page=login');
    exit;
}

switch ($page) {
    case 'login':
        require __DIR__ . '/pages/auth/login.php';
        break;
    case 'admin_dashboard':
        requireRole('admin');
        require __DIR__ . '/pages/admin/dashboard.php';
        break;
    case 'admin_bayiler':
        requireRole('admin');
        require __DIR__ . '/pages/admin/bayiler.php';
        break;
    case 'admin_products':
        requireRole('admin');
        require __DIR__ . '/pages/admin/products.php';
        break;
    case 'admin_orders':
        requireRole('admin');
        require __DIR__ . '/pages/admin/orders.php';
        break;
    case 'admin_custom_requests':
        requireRole('admin');
        require __DIR__ . '/pages/admin/custom_requests.php';
        break;
    case 'admin_support':
        requireRole('admin');
        require __DIR__ . '/pages/admin/support.php';
        break;
    case 'admin_payments':
        requireRole('admin');
        require __DIR__ . '/pages/admin/payments.php';
        break;
    case 'admin_notifications':
        requireRole('admin');
        require __DIR__ . '/pages/admin/notifications.php';
        break;
    case 'admin_options':
        requireRole('admin');
        require __DIR__ . '/pages/admin/custom_options.php';
        break;
    case 'admin_transactions':
        requireRole('admin');
        require __DIR__ . '/pages/admin/transactions.php';
        break;
    case 'bayi_dashboard':
        requireRole('bayi');
        require __DIR__ . '/pages/bayi/dashboard.php';
        break;
    case 'bayi_products':
        requireRole('bayi');
        require __DIR__ . '/pages/bayi/products.php';
        break;
    case 'bayi_orders':
        requireRole('bayi');
        require __DIR__ . '/pages/bayi/orders.php';
        break;
    case 'bayi_custom_requests':
        requireRole('bayi');
        require __DIR__ . '/pages/bayi/custom_requests.php';
        break;
    case 'bayi_support':
        requireRole('bayi');
        require __DIR__ . '/pages/bayi/support.php';
        break;
    case 'bayi_payments':
        requireRole('bayi');
        require __DIR__ . '/pages/bayi/payments.php';
        break;
    case 'bayi_notifications':
        requireRole('bayi');
        require __DIR__ . '/pages/bayi/notifications.php';
        break;
    default:
        http_response_code(404);
        echo 'Sayfa bulunamadı';
}
