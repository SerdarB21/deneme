<?php
$title = 'Siparişler';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
    } else {
        $orderId = (int) $_POST['order_id'];
        $status = $_POST['status'];
        updateOrderStatus($orderId, $status);
        setFlash('success', 'Sipariş durumu güncellendi');
    }
    header('Location: index.php?page=admin_orders');
    exit;
}
$orders = getOrders();
require __DIR__ . '/../partials/header.php';
?>
<section class="card">
    <h2>Sipariş Listesi</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Bayi</th>
            <th>Ürün</th>
            <th>Adet</th>
            <th>Tutar</th>
            <th>Durum</th>
            <th>Panel Bilgileri</th>
            <th>İşlem</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td>#<?= $order['id'] ?></td>
                <td><?= htmlspecialchars($order['user_name']) ?></td>
                <td><?= htmlspecialchars($order['product_name']) ?></td>
                <td><?= $order['quantity'] ?></td>
                <td><?= formatCurrency((float)$order['total_price']) ?></td>
                <td><span class="status-pill status-<?= htmlspecialchars($order['status']) ?>"><?= htmlspecialchars($order['status']) ?></span></td>
                <td>
                    <?php if ($order['panel_link']): ?>
                        <div>Panel: <a href="<?= htmlspecialchars($order['panel_link']) ?>" target="_blank">Link</a></div>
                    <?php endif; ?>
                    <?php if ($order['panel_username']): ?>
                        <div>Kullanıcı: <?= htmlspecialchars($order['panel_username']) ?> <button type="button" class="btn btn-secondary" data-copy="<?= htmlspecialchars($order['panel_username']) ?>">Kopyala</button></div>
                    <?php endif; ?>
                    <?php if ($order['panel_password']): ?>
                        <div>Şifre: <?= htmlspecialchars($order['panel_password']) ?> <button type="button" class="btn btn-secondary" data-copy="<?= htmlspecialchars($order['panel_password']) ?>">Kopyala</button></div>
                    <?php endif; ?>
                </td>
                <td>
                    <form method="post" class="form-group">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status">
                            <option value="beklemede" <?= $order['status'] === 'beklemede' ? 'selected' : '' ?>>Beklemede</option>
                            <option value="tamamlandi" <?= $order['status'] === 'tamamlandi' ? 'selected' : '' ?>>Tamamlandı</option>
                            <option value="iptal" <?= $order['status'] === 'iptal' ? 'selected' : '' ?>>İptal</option>
                        </select>
                        <button class="btn btn-primary" type="submit">Güncelle</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
