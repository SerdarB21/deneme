<?php
$title = 'Siparişlerim';
$user = $currentUser;
$orders = getOrders(['user_id' => $user['id']]);
require __DIR__ . '/../partials/header.php';
?>
<section class="card">
    <h2>Siparişler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Ürün</th>
            <th>Adet</th>
            <th>Tutar</th>
            <th>Durum</th>
            <th>Panel Bilgileri</th>
            <th>Tarih</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['product_name']) ?></td>
                <td><?= $order['quantity'] ?></td>
                <td><?= formatCurrency((float)$order['total_price']) ?></td>
                <td><span class="status-pill status-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                <td>
                    <?php if ($order['panel_link']): ?><div>Panel: <a href="<?= htmlspecialchars($order['panel_link']) ?>" target="_blank">Link</a></div><?php endif; ?>
                    <?php if ($order['panel_username']): ?><div>Kullanıcı: <?= htmlspecialchars($order['panel_username']) ?></div><?php endif; ?>
                    <?php if ($order['panel_password']): ?><div>Şifre: <?= htmlspecialchars($order['panel_password']) ?></div><?php endif; ?>
                </td>
                <td><?= htmlspecialchars($order['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
