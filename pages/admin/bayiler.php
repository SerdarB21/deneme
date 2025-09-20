<?php
$title = 'Bayiler';
require __DIR__ . '/../partials/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
        header('Location: index.php?page=admin_bayiler');
        exit;
    }
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'create') {
            createBayi([
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'],
                'balance' => (float) $_POST['balance'],
                'credit_limit' => (float) $_POST['credit_limit'],
                'telegram_chat_id' => trim($_POST['telegram_chat_id']) ?: null,
            ]);
            setFlash('success', 'Bayi oluşturuldu');
        } elseif ($action === 'update') {
            updateBayi((int) $_POST['id'], [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => $_POST['password'] ?? '',
                'balance' => (float) $_POST['balance'],
                'credit_limit' => (float) $_POST['credit_limit'],
                'telegram_chat_id' => trim($_POST['telegram_chat_id']) ?: null,
            ]);
            setFlash('success', 'Bayi güncellendi');
        } elseif ($action === 'delete') {
            deleteBayi((int) $_POST['id']);
            setFlash('success', 'Bayi silindi');
        } elseif ($action === 'topup') {
            topUpBalance((int) $_POST['id'], (float) $_POST['amount'], trim($_POST['description']) ?: 'Bakiye güncelleme');
            setFlash('success', 'Bakiye güncellendi');
        }
    } catch (Exception $e) {
        setFlash('error', 'İşlem sırasında hata oluştu: ' . $e->getMessage());
    }
    header('Location: index.php?page=admin_bayiler');
    exit;
}

$bayiler = getBayiList();
$editId = isset($_GET['edit']) ? (int) $_GET['edit'] : null;
$topupId = isset($_GET['topup']) ? (int) $_GET['topup'] : null;
?>
<section class="grid">
    <div class="card">
        <h2>Yeni Bayi Ekle</h2>
        <form method="post" class="grid" style="gap:1rem;">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="action" value="create">
            <div class="form-group-inline">
                <div class="form-group">
                    <label>Ad Soyad</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>E-posta</label>
                    <input type="email" name="email" required>
                </div>
            </div>
            <div class="form-group-inline">
                <div class="form-group">
                    <label>Şifre</label>
                    <input type="text" name="password" required>
                </div>
                <div class="form-group">
                    <label>Başlangıç Bakiyesi</label>
                    <input type="number" step="0.01" name="balance" value="0">
                </div>
            </div>
            <div class="form-group-inline">
                <div class="form-group">
                    <label>Eksi Bakiye Limiti</label>
                    <input type="number" step="0.01" name="credit_limit" value="0">
                    <small>Negatif değer girerek eksi limit tanımlayabilirsiniz.</small>
                </div>
                <div class="form-group">
                    <label>Telegram Chat ID</label>
                    <input type="text" name="telegram_chat_id">
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Bayi Oluştur</button>
        </form>
    </div>
</section>
<section class="card" style="margin-top:2rem;">
    <h2>Bayiler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Ad</th>
            <th>E-posta</th>
            <th>Bakiye</th>
            <th>Eksi Limit</th>
            <th>Telegram</th>
            <th>İşlemler</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($bayiler as $bayi): ?>
            <tr>
                <td><?= htmlspecialchars($bayi['name']) ?></td>
                <td><?= htmlspecialchars($bayi['email']) ?></td>
                <td><?= formatCurrency((float)$bayi['balance']) ?></td>
                <td><?= formatCurrency((float)$bayi['credit_limit']) ?></td>
                <td><?= htmlspecialchars($bayi['telegram_chat_id'] ?? '-') ?></td>
                <td class="table-actions">
                    <a class="btn btn-secondary" href="index.php?page=admin_bayiler&edit=<?= $bayi['id'] ?>">Düzenle</a>
                    <a class="btn btn-secondary" href="index.php?page=admin_bayiler&topup=<?= $bayi['id'] ?>">Bakiye</a>
                    <form method="post" onsubmit="return confirm('Bu bayiyi silmek istediğinize emin misiniz?');">
                        <input type="hidden" name="csrf_token" value="<?= $token ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $bayi['id'] ?>">
                        <button class="btn btn-outline" type="submit">Sil</button>
                    </form>
                </td>
            </tr>
            <?php if ($editId === (int)$bayi['id']): ?>
                <tr>
                    <td colspan="6">
                        <form method="post" class="grid" style="gap:1rem;">
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= $bayi['id'] ?>">
                            <div class="form-group-inline">
                                <div class="form-group">
                                    <label>Ad Soyad</label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($bayi['name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>E-posta</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($bayi['email']) ?>" required>
                                </div>
                            </div>
                            <div class="form-group-inline">
                                <div class="form-group">
                                    <label>Yeni Şifre</label>
                                    <input type="text" name="password" placeholder="Değiştirmek için doldurun">
                                </div>
                                <div class="form-group">
                                    <label>Bakiye</label>
                                    <input type="number" step="0.01" name="balance" value="<?= $bayi['balance'] ?>">
                                </div>
                            </div>
                            <div class="form-group-inline">
                                <div class="form-group">
                                    <label>Eksi Bakiye Limiti</label>
                                    <input type="number" step="0.01" name="credit_limit" value="<?= $bayi['credit_limit'] ?>">
                                </div>
                                <div class="form-group">
                                    <label>Telegram Chat ID</label>
                                    <input type="text" name="telegram_chat_id" value="<?= htmlspecialchars($bayi['telegram_chat_id'] ?? '') ?>">
                                </div>
                            </div>
                            <button class="btn btn-primary" type="submit">Kaydet</button>
                        </form>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if ($topupId === (int)$bayi['id']): ?>
                <tr>
                    <td colspan="6">
                        <form method="post" class="form-group-inline">
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            <input type="hidden" name="action" value="topup">
                            <input type="hidden" name="id" value="<?= $bayi['id'] ?>">
                            <div class="form-group">
                                <label>Tutar</label>
                                <input type="number" step="0.01" name="amount" required>
                            </div>
                            <div class="form-group">
                                <label>Açıklama</label>
                                <input type="text" name="description" placeholder="Opsiyonel">
                            </div>
                            <button class="btn btn-primary" type="submit">Uygula</button>
                        </form>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
