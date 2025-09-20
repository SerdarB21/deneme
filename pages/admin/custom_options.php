<?php
$title = 'Talepli Ürün Seçenekleri';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
    } else {
        $action = $_POST['action'] ?? '';
        $pdo = getPDO();
        if ($action === 'create') {
            $pdo->prepare('INSERT INTO custom_request_options (title, price, created_at, updated_at) VALUES (?,?,?,?)')
                ->execute([trim($_POST['title']), (float) $_POST['price'], now(), now()]);
            setFlash('success', 'Seçenek eklendi');
        } elseif ($action === 'update') {
            $pdo->prepare('UPDATE custom_request_options SET title=?, price=?, updated_at=? WHERE id=?')
                ->execute([trim($_POST['title']), (float) $_POST['price'], now(), (int) $_POST['id']]);
            setFlash('success', 'Seçenek güncellendi');
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM custom_request_options WHERE id=?')->execute([(int) $_POST['id']]);
            setFlash('success', 'Seçenek silindi');
        }
    }
    header('Location: index.php?page=admin_options');
    exit;
}
$options = getPDO()->query('SELECT * FROM custom_request_options ORDER BY title')->fetchAll();
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$editOption = null;
foreach ($options as $opt) {
    if ($opt['id'] == $editId) {
        $editOption = $opt;
        break;
    }
}
require __DIR__ . '/../partials/header.php';
?>
<section class="grid">
    <div class="card">
        <h2><?= $editOption ? 'Seçeneği Düzenle' : 'Yeni Seçenek' ?></h2>
        <form method="post" class="grid" style="gap:1rem;">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="action" value="<?= $editOption ? 'update' : 'create' ?>">
            <?php if ($editOption): ?><input type="hidden" name="id" value="<?= $editOption['id'] ?>"><?php endif; ?>
            <div class="form-group">
                <label>Başlık</label>
                <input type="text" name="title" value="<?= htmlspecialchars($editOption['title'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Fiyat</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($editOption['price'] ?? '0') ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Kaydet</button>
        </form>
    </div>
</section>
<section class="card" style="margin-top:2rem;">
    <h2>Seçenekler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Başlık</th>
            <th>Fiyat</th>
            <th>İşlem</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($options as $option): ?>
            <tr>
                <td><?= htmlspecialchars($option['title']) ?></td>
                <td><?= formatCurrency((float)$option['price']) ?></td>
                <td class="table-actions">
                    <a class="btn btn-secondary" href="index.php?page=admin_options&edit=<?= $option['id'] ?>">Düzenle</a>
                    <form method="post" onsubmit="return confirm('Silinsin mi?');">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $option['id'] ?>">
                        <button class="btn btn-outline" type="submit">Sil</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
