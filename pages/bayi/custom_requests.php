<?php
$title = 'Talepli Ürünlerim';
$user = $currentUser;
$options = getCustomRequestOptions();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            createCustomRequest($user, (int) $_POST['option_id'], trim($_POST['message']));
            setFlash('success', 'Talebiniz oluşturuldu');
        } elseif ($action === 'add_message') {
            $attachment = null;
            if (!empty($_FILES['attachment']['tmp_name'])) {
                try {
                    $attachment = handleUpload($_FILES['attachment']);
                } catch (Exception $e) {
                    setFlash('error', $e->getMessage());
                }
            }
            addCustomRequestMessage((int) $_POST['request_id'], $user['id'], trim($_POST['message']), $attachment);
            setFlash('success', 'Mesaj gönderildi');
        }
    }
    header('Location: index.php?page=bayi_custom_requests' . (!empty($_POST['request_id']) ? '&view=' . (int) $_POST['request_id'] : ''));
    exit;
}
$requests = getCustomRequests(['user_id' => $user['id']]);
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
<section class="grid">
    <div class="card">
        <h2>Yeni Talep Oluştur</h2>
        <form method="post" class="grid" style="gap:1rem;">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label>Ürün Seçeneği</label>
                <select name="option_id" required>
                    <?php foreach ($options as $option): ?>
                        <option value="<?= $option['id'] ?>"><?= htmlspecialchars($option['title']) ?> - <?= formatCurrency((float)$option['price']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Talep Detayı</label>
                <textarea name="message" required></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Gönder</button>
        </form>
    </div>
</section>
<section class="card" style="margin-top:2rem;">
    <h2>Taleplerim</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Seçenek</th>
            <th>Tutar</th>
            <th>Durum</th>
            <th>Oluşturma</th>
            <th>İşlem</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $req): ?>
            <tr>
                <td>#<?= $req['id'] ?></td>
                <td><?= htmlspecialchars($req['option_title']) ?></td>
                <td><?= formatCurrency((float)$req['total_price']) ?></td>
                <td><?= htmlspecialchars($req['status']) ?></td>
                <td><?= htmlspecialchars($req['created_at']) ?></td>
                <td><a class="btn btn-secondary" href="index.php?page=bayi_custom_requests&view=<?= $req['id'] ?>">Görüntüle</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php if ($viewRequest): ?>
<section class="card" style="margin-top:2rem;">
    <h2>Talep #<?= $viewRequest['id'] ?> - <?= htmlspecialchars($viewRequest['option_title']) ?></h2>
    <div class="support-thread" data-auto-scroll>
        <?php foreach ($messages as $message): ?>
            <div class="message-bubble">
                <div class="message-meta">
                    <span><?= htmlspecialchars($message['sender_name']) ?> (<?= $message['sender_role'] ?>)</span>
                    <span><?= htmlspecialchars($message['created_at']) ?></span>
                </div>
                <div><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                <?php if ($message['attachment_path']): ?>
                    <div><a href="uploads/<?= htmlspecialchars($message['attachment_path']) ?>" target="_blank">Dosya indir</a></div>
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
            <label>Dosya (opsiyonel)</label>
            <input type="file" name="attachment">
        </div>
        <button class="btn btn-primary" type="submit">Gönder</button>
    </form>
</section>
<?php endif; ?>
<?php require __DIR__ . '/../partials/footer.php'; ?>
