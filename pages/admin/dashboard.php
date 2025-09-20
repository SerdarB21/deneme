<?php
$title = 'Yönetici Paneli';
$stats = getDashboardStats();
$orders = getOrders(['status' => null]);
$recentOrders = array_slice($orders, 0, 5);
$recentRequests = array_slice(getCustomRequests(), 0, 5);
require __DIR__ . '/../partials/header.php';
?>
<section class="grid grid-3">
    <div class="card">
        <h2>Bayi Sayısı</h2>
        <p class="stat"><?= $stats['bayi_count'] ?></p>
    </div>
    <div class="card">
        <h2>Ürün Sayısı</h2>
        <p class="stat"><?= $stats['product_count'] ?></p>
    </div>
    <div class="card">
        <h2>Toplam Sipariş</h2>
        <p class="stat"><?= $stats['order_count'] ?></p>
    </div>
</section>
<section class="grid grid-3" style="margin-top: 1.5rem;">
    <div class="card">
        <h2>Açık Talepler</h2>
        <p class="stat"><?= $stats['open_requests'] ?></p>
    </div>
    <div class="card">
        <h2>Açık Destek</h2>
        <p class="stat"><?= $stats['open_tickets'] ?></p>
    </div>
    <div class="card">
        <h2>Açık Ödeme Bildirimleri</h2>
        <p class="stat"><?= $stats['open_payments'] ?></p>
    </div>
</section>
<section class="card" style="margin-top: 2rem;">
    <h2>Son Siparişler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Bayi</th>
            <th>Ürün</th>
            <th>Adet</th>
            <th>Tutar</th>
            <th>Durum</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recentOrders as $order): ?>
            <tr>
                <td>#<?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['user_name']) ?></td>
                <td><?= htmlspecialchars($order['product_name']) ?></td>
                <td><?= $order['quantity'] ?></td>
                <td><?= formatCurrency((float)$order['total_price']) ?></td>
                <td><span class="status-pill status-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="card" style="margin-top: 2rem;">
    <h2>Son Talepli Ürünler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Bayi</th>
            <th>Seçenek</th>
            <th>Durum</th>
            <th>Tutar</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recentRequests as $req): ?>
            <tr>
                <td>#<?= $req['id'] ?></td>
                <td><?= htmlspecialchars($req['user_name']) ?></td>
                <td><?= htmlspecialchars($req['option_title']) ?></td>
                <td><?= htmlspecialchars($req['status']) ?></td>
                <td><?= formatCurrency((float)$req['total_price']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
