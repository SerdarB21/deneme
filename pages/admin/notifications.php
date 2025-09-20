<?php
$title = 'Bildirimler';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
    } else {
        createNotification(trim($_POST['title']), trim($_POST['body']), $currentUser['id']);
        setFlash('success', 'Bildirim gönderildi');
    }
    header('Location: index.php?page=admin_notifications');
    exit;
}
$pdo = getPDO();
$notifications = $pdo->query('SELECT * FROM notifications ORDER BY created_at DESC')->fetchAll();
require __DIR__ . '/../partials/header.php';
?>
<section class="grid">
    <div class="card">
        <h2>Yeni Bildirim Gönder</h2>
        <form method="post" class="grid" style="gap:1rem;">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <div class="form-group">
                <label>Başlık</label>
                <input type="text" name="title" required>
            </div>
            <div class="form-group">
                <label>Mesaj</label>
                <textarea name="body" required></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Gönder</button>
        </form>
    </div>
</section>
<section class="card" style="margin-top:2rem;">
    <h2>Geçmiş Bildirimler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Başlık</th>
            <th>Mesaj</th>
            <th>Tarih</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($notifications as $notification): ?>
            <tr>
                <td><?= htmlspecialchars($notification['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($notification['body'])) ?></td>
                <td><?= htmlspecialchars($notification['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
