<?php
$title = 'Ürünler';
$user = $currentUser;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
    } else {
        $product = getProduct((int) $_POST['product_id']);
        if ($product) {
            $quantity = max(1, (int) $_POST['quantity']);
            $fields = [
                'panel_link' => trim($_POST['panel_link'] ?? ''),
                'panel_username' => trim($_POST['panel_username'] ?? ''),
                'panel_password' => trim($_POST['panel_password'] ?? ''),
            ];
            $orderId = createOrder($user, $product, $quantity, $fields);
            if ($orderId) {
                setFlash('success', 'Siparişiniz oluşturuldu (#' . $orderId . ')');
            } else {
                setFlash('error', 'Sipariş oluşturulamadı. Bakiye veya stok yetersiz olabilir.');
            }
        }
    }
    header('Location: index.php?page=bayi_products');
    exit;
}
$products = getProducts();
$grouped = [];
foreach ($products as $product) {
    $grouped[$product['category']][] = $product;
}
require __DIR__ . '/../partials/header.php';
?>
<?php foreach ($grouped as $category => $items): ?>
<section class="card" style="margin-bottom:2rem;">
    <h2><?= htmlspecialchars($category) ?></h2>
    <div class="grid" style="gap:1.5rem;">
        <?php foreach ($items as $product): ?>
            <?php $price = getPriceForUser($product, $user); ?>
            <div class="card" style="box-shadow:none;border:1px solid var(--color-border);">
                <?php if ($product['image_path']): ?>
                    <img src="uploads/<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%;max-height:160px;object-fit:cover;border-radius:12px;">
                <?php endif; ?>
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <p><strong>Fiyat:</strong> <?= formatCurrency((float)$price) ?></p>
                <p><strong>Stok:</strong> <?= $product['stock'] ?></p>
                <form method="post" class="grid" style="gap:0.75rem;">
                    <input type="hidden" name="csrf_token" value="<?= $token ?>">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <div class="form-group">
                        <label>Adet</label>
                        <input type="number" name="quantity" value="1" min="1">
                    </div>
                    <?php if ($product['require_credentials']): ?>
                        <div class="form-group">
                            <label>Panel Linki</label>
                            <input type="text" name="panel_link" required>
                        </div>
                        <div class="form-group">
                            <label>Panel Kullanıcı Adı</label>
                            <input type="text" name="panel_username" required>
                        </div>
                        <div class="form-group">
                            <label>Panel Şifresi</label>
                            <input type="text" name="panel_password" required>
                        </div>
                    <?php endif; ?>
                    <button class="btn btn-primary" type="submit">Sipariş Ver</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
