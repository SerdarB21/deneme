<?php
$title = 'Bayi Paneli';
$user = $currentUser;
$orders = getOrders(['user_id' => $user['id']]);
$recentOrders = array_slice($orders, 0, 5);
$requests = getCustomRequests(['user_id' => $user['id']]);
$notifications = getUserNotifications($user['id']);
require __DIR__ . '/../partials/header.php';
?>
<section class="grid grid-3">
    <div class="card">
        <h2>Toplam Sipariş</h2>
        <p class="stat"><?= count($orders) ?></p>
    </div>
    <div class="card">
        <h2>Açık Talepler</h2>
        <p class="stat"><?= count(array_filter($requests, fn($r) => $r['status'] !== 'kapatildi')) ?></p>
    </div>
    <div class="card">
        <h2>Bildirimler</h2>
        <p class="stat"><?= $notifications ? unreadNotificationCount($user['id']) . ' yeni' : '0' ?></p>
    </div>
</section>
<section class="card" style="margin-top:2rem;">
    <h2>Son Siparişler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
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
                <td><?= htmlspecialchars($order['product_name']) ?></td>
                <td><?= $order['quantity'] ?></td>
                <td><?= formatCurrency((float)$order['total_price']) ?></td>
                <td><span class="status-pill status-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<section class="card" style="margin-top:2rem;">
    <h2>Bildirimler</h2>
    <ul>
        <?php foreach (array_slice($notifications, 0, 5) as $note): ?>
            <li><strong><?= htmlspecialchars($note['title']) ?></strong> - <?= htmlspecialchars($note['body']) ?> (<?= htmlspecialchars($note['created_at']) ?>)</li>
        <?php endforeach; ?>
    </ul>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
