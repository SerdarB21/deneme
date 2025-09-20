<?php
$title = 'Talepli Ürünler';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'update_status') {
            updateCustomRequestStatus((int) $_POST['id'], $_POST['status']);
            setFlash('success', 'Talep durumu güncellendi');
        } elseif ($action === 'add_message') {
            $attachment = null;
            if (!empty($_FILES['attachment']['tmp_name'])) {
                try {
                    $attachment = handleUpload($_FILES['attachment']);
                } catch (Exception $e) {
                    setFlash('error', $e->getMessage());
                }
            }
            addCustomRequestMessage((int) $_POST['request_id'], $currentUser['id'], trim($_POST['message']), $attachment);
            setFlash('success', 'Mesaj gönderildi');
        }
    }
    header('Location: index.php?page=admin_custom_requests' . (!empty($_POST['request_id']) ? '&view=' . (int) $_POST['request_id'] : ''));
    exit;
}
$requests = getCustomRequests();
$viewId = isset($_GET['view']) ? (int) $_GET['view'] : null;
$viewRequest = null;
$messages = [];
if ($viewId) {
    foreach ($requests as $req) {
        if ((int)$req['id'] === $viewId) {
            $viewRequest = $req;
            break;
        }
    }
    if ($viewRequest) {
        $messages = getCustomRequestMessages($viewId);
    }
}
require __DIR__ . '/../partials/header.php';
?>
<section class="card">
    <h2>Talepler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Bayi</th>
            <th>Seçenek</th>
            <th>Tutar</th>
            <th>Durum</th>
            <th>Oluşturma</th>
            <th>İşlemler</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $req): ?>
            <tr>
                <td>#<?= $req['id'] ?></td>
                <td><?= htmlspecialchars($req['user_name']) ?></td>
                <td><?= htmlspecialchars($req['option_title']) ?></td>
                <td><?= formatCurrency((float)$req['total_price']) ?></td>
                <td><?= htmlspecialchars($req['status']) ?></td>
                <td><?= htmlspecialchars($req['created_at']) ?></td>
                <td class="table-actions">
                    <a class="btn btn-secondary" href="index.php?page=admin_custom_requests&view=<?= $req['id'] ?>">Görüntüle</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php if ($viewRequest): ?>
<section class="card" style="margin-top:2rem;">
    <h2>Talep #<?= $viewRequest['id'] ?> - <?= htmlspecialchars($viewRequest['user_name']) ?></h2>
    <p><strong>Seçenek:</strong> <?= htmlspecialchars($viewRequest['option_title']) ?> | <strong>Tutar:</strong> <?= formatCurrency((float)$viewRequest['total_price']) ?></p>
    <form method="post" class="form-group-inline" style="margin-bottom:1.5rem;">
        <input type="hidden" name="csrf_token" value="<?= $token ?>">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="id" value="<?= $viewRequest['id'] ?>">
        <div class="form-group">
            <label>Durum</label>
            <select name="status">
                <option value="acik" <?= $viewRequest['status'] === 'acik' ? 'selected' : '' ?>>Açık</option>
                <option value="islemde" <?= $viewRequest['status'] === 'islemde' ? 'selected' : '' ?>>İşlemde</option>
                <option value="tamamlandi" <?= $viewRequest['status'] === 'tamamlandi' ? 'selected' : '' ?>>Tamamlandı</option>
                <option value="kapatildi" <?= $viewRequest['status'] === 'kapatildi' ? 'selected' : '' ?>>Kapatıldı</option>
            </select>
        </div>
        <button class="btn btn-primary" type="submit">Durumu Güncelle</button>
    </form>
    <div class="support-thread" data-auto-scroll>
        <?php foreach ($messages as $message): ?>
            <div class="message-bubble">
                <div class="message-meta">
                    <span><?= htmlspecialchars($message['sender_name']) ?> (<?= $message['sender_role'] ?>)</span>
                    <span><?= htmlspecialchars($message['created_at']) ?></span>
                </div>
                <div><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                <?php if ($message['attachment_path']): ?>
                    <div><a href="uploads/<?= htmlspecialchars($message['attachment_path']) ?>" target="_blank">Dosyayı indir</a></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="post" enctype="multipart/form-data" style="margin-top:1.5rem;gap:1rem;" class="grid">
        <input type="hidden" name="csrf_token" value="<?= $token ?>">
        <input type="hidden" name="action" value="add_message">
        <input type="hidden" name="request_id" value="<?= $viewRequest['id'] ?>">
        <div class="form-group">
            <label>Mesaj</label>
            <textarea name="message" required></textarea>
        </div>
        <div class="form-group">
            <label>Dosya</label>
            <input type="file" name="attachment">
        </div>
        <button class="btn btn-primary" type="submit">Gönder</button>
    </form>
</section>
<?php endif; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
