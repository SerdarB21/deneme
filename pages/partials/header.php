<?php
$token = generateCsrfToken();
$balanceText = $currentUser ? formatCurrency((float) $currentUser['balance']) : '';
$unreadCount = $currentUser ? unreadNotificationCount($currentUser['id']) : 0;
$currentUri = $_SERVER['REQUEST_URI'] ?? 'index.php';
$toggleLink = htmlspecialchars($currentUri . (strpos($currentUri, '?') === false ? '?' : '&') . 'toggle_theme=1');
?>
<!DOCTYPE html>
<html lang="tr" class="<?= themeClass() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Bayilik Sistemi') ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="app-header">
    <div class="logo">Bayilik Sistemi</div>
    <nav class="main-nav">
        <?php if ($currentUser && $currentUser['role'] === 'admin'): ?>
            <a href="index.php?page=admin_dashboard">Genel Bakış</a>
            <a href="index.php?page=admin_bayiler">Bayiler</a>
            <a href="index.php?page=admin_products">Ürünler</a>
            <a href="index.php?page=admin_orders">Siparişler</a>
            <a href="index.php?page=admin_custom_requests">Talepli Ürünler</a>
            <a href="index.php?page=admin_support">Destek</a>
            <a href="index.php?page=admin_payments">Ödeme Bildirimleri</a>
            <a href="index.php?page=admin_notifications">Bildirimler</a>
            <a href="index.php?page=admin_options">Talep Seçenekleri</a>
            <a href="index.php?page=admin_transactions">Bakiye Hareketleri</a>
        <?php elseif ($currentUser && $currentUser['role'] === 'bayi'): ?>
            <a href="index.php?page=bayi_dashboard">Panel</a>
            <a href="index.php?page=bayi_products">Ürünler</a>
            <a href="index.php?page=bayi_orders">Siparişler</a>
            <a href="index.php?page=bayi_custom_requests">Talepli Ürün</a>
            <a href="index.php?page=bayi_support">Destek</a>
            <a href="index.php?page=bayi_payments">Ödeme Bildirimi</a>
            <a href="index.php?page=bayi_notifications">Bildirimler<?php if ($unreadCount > 0): ?><span class="badge"><?= $unreadCount ?></span><?php endif; ?></a>
        <?php endif; ?>
    </nav>
    <div class="header-actions">
        <?php if ($currentUser): ?>
            <div class="balance">Bakiye: <strong><?= $balanceText ?></strong><small> Limit: <?= formatCurrency((float)$currentUser['credit_limit']) ?></small></div>
            <a class="btn btn-secondary" href="<?= $toggleLink ?>">Tema Değiştir</a>
            <a class="btn btn-outline" href="index.php?page=logout">Çıkış</a>
        <?php endif; ?>
    </div>
</header>
<main class="app-content">
    <?php if ($flash = getFlash('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($flash = getFlash('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
