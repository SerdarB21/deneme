<?php
$title = 'Ürün Yönetimi';
$bayiler = getBayiList();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
        header('Location: index.php?page=admin_products');
        exit;
    }
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create_product' || $action === 'update_product') {
            $id = $action === 'update_product' ? (int) $_POST['id'] : null;
            $category = $_POST['category'];
            $product = $id ? getProduct($id) : null;
            $imagePath = $product['image_path'] ?? null;
            if (!empty($_FILES['image']['tmp_name'])) {
                $imagePath = handleUpload($_FILES['image']);
            }
            $requireCredentials = requiresCredentials($category) || !empty($_POST['require_credentials']);
            $productId = saveProduct([
                'name' => trim($_POST['name']),
                'category' => $category,
                'description' => trim($_POST['description']),
                'price' => (float) $_POST['price'],
                'stock' => (int) $_POST['stock'],
                'image_path' => $imagePath,
                'require_credentials' => $requireCredentials ? 1 : 0,
            ], $id);
            if (!empty($_POST['use_custom_prices']) && !empty($_POST['custom_price'])) {
                foreach ($_POST['custom_price'] as $bayiId => $price) {
                    if ($price === '') {
                        removeCustomPrice($productId, (int) $bayiId);
                    } else {
                        setCustomPrice($productId, (int) $bayiId, (float) $price);
                    }
                }
            } elseif ($id) {
                foreach ($bayiler as $bayi) {
                    removeCustomPrice($productId, (int) $bayi['id']);
                }
            }
            setFlash('success', $id ? 'Ürün güncellendi' : 'Ürün oluşturuldu');
        } elseif ($action === 'delete_product') {
            deleteProduct((int) $_POST['id']);
            setFlash('success', 'Ürün silindi');
        } elseif ($action === 'remove_custom_price') {
            removeCustomPrice((int) $_POST['product_id'], (int) $_POST['user_id']);
            setFlash('success', 'Özel fiyat kaldırıldı');
        } elseif ($action === 'bulk_import') {
            $count = importProductsFromCsv($_FILES['bulk_file'] ?? []);
            setFlash('success', $count . ' ürün içe aktarıldı');
        }
    } catch (Exception $e) {
        setFlash('error', 'Hata: ' . $e->getMessage());
    }
    header('Location: index.php?page=admin_products');
    exit;
}

$products = getProducts();
require __DIR__ . '/../partials/header.php';
?>
<section class="grid">
    <div class="card">
        <h2>Ürün Oluştur / Güncelle</h2>
        <?php $editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null; ?>
        <?php $editProduct = $editId ? getProduct($editId) : null; ?>
        <?php $customPrices = $editProduct ? getCustomPricesForProduct($editProduct['id']) : []; ?>
        <?php $hasCustom = !empty($customPrices); ?>
        <form method="post" enctype="multipart/form-data" class="grid" style="gap:1rem;">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="action" value="<?= $editProduct ? 'update_product' : 'create_product' ?>">
            <?php if ($editProduct): ?>
                <input type="hidden" name="id" value="<?= $editProduct['id'] ?>">
            <?php endif; ?>
            <div class="form-group">
                <label>Ürün Adı</label>
                <input type="text" name="name" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select name="category" required>
                    <?php foreach (categories() as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($editProduct['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Açıklama</label>
                <textarea name="description"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group-inline">
                <div class="form-group">
                    <label>Genel Fiyat</label>
                    <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($editProduct['price'] ?? '0') ?>" required>
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stock" value="<?= htmlspecialchars($editProduct['stock'] ?? '0') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Ürün Görseli</label>
                <input type="file" name="image" accept="image/*">
                <?php if (!empty($editProduct['image_path'])): ?>
                    <img src="uploads/<?= htmlspecialchars($editProduct['image_path']) ?>" alt="Ürün görseli" style="max-width:120px;margin-top:0.5rem;border-radius:12px;">
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="use_custom_prices" value="1" <?= ($editProduct && $hasCustom) ? 'checked' : '' ?> data-toggle-custom> Bayilere özel fiyatlandırma</label>
                <div class="grid <?= ($editProduct && $hasCustom) ? '' : 'hidden' ?>" style="gap:0.75rem;margin-top:0.75rem;" data-custom-prices>
                    <?php foreach ($bayiler as $bayi): ?>
                        <?php
                        $customPrice = null;
                        foreach ($customPrices as $cp) {
                            if ((int)$cp['user_id'] === (int)$bayi['id']) {
                                $customPrice = $cp['price'];
                                break;
                            }
                        }
                        ?>
                        <div class="form-group-inline">
                            <div class="form-group">
                                <label><?= htmlspecialchars($bayi['name']) ?></label>
                                <input type="number" step="0.01" name="custom_price[<?= $bayi['id'] ?>]" value="<?= $customPrice !== null ? htmlspecialchars($customPrice) : '' ?>" placeholder="Fiyat girin">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($editProduct): ?>
                <button class="btn btn-primary" type="submit">Ürünü Güncelle</button>
            <?php else: ?>
                <button class="btn btn-primary" type="submit">Ürün Oluştur</button>
            <?php endif; ?>
        </form>
    </div>
    <div class="card">
        <h2>Toplu Ürün Ekle</h2>
        <p>CSV dosyasını ; ile ayrılmış şu sırayla yükleyin: Ad, Kategori, Açıklama, Fiyat, Stok</p>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="action" value="bulk_import">
            <div class="form-group">
                <input type="file" name="bulk_file" accept="text/csv" required>
            </div>
            <button class="btn btn-primary" type="submit">Dosyayı Yükle</button>
        </form>
    </div>
</section>
<section class="card" style="margin-top:2rem;">
    <h2>Ürünler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Görsel</th>
            <th>Ad</th>
            <th>Kategori</th>
            <th>Fiyat</th>
            <th>Stok</th>
            <th>İşlemler</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?php if ($product['image_path']): ?><img src="uploads/<?= htmlspecialchars($product['image_path']) ?>" alt="" style="max-width:60px;border-radius:12px;"><?php endif; ?></td>
                <td><?= htmlspecialchars($product['name']) ?></td>
                <td><?= htmlspecialchars($product['category']) ?></td>
                <td><?= formatCurrency((float)$product['price']) ?></td>
                <td><?= $product['stock'] ?></td>
                <td class="table-actions">
                    <a class="btn btn-secondary" href="index.php?page=admin_products&edit=<?= $product['id'] ?>">Düzenle</a>
                    <form method="post" onsubmit="return confirm('Silmek istediğinize emin misiniz?');">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <input type="hidden" name="action" value="delete_product">
                        <input type="hidden" name="id" value="<?= $product['id'] ?>">
                        <button class="btn btn-outline" type="submit">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
