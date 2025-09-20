<?php
$title = 'Bildirimler';
$user = $currentUser;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        if ($_POST['action'] === 'read') {
            markNotificationAsRead((int) $_POST['notification_user_id']);
        }
    }
    header('Location: index.php?page=bayi_notifications');
    exit;
}
$notifications = getUserNotifications($user['id']);
require __DIR__ . '/../partials/header.php';
?>
<section class="card">
    <h2>Bildirimler</h2>
    <table class="table">
        <thead>
        <tr>
            <th>Başlık</th>
            <th>Mesaj</th>
            <th>Tarih</th>
            <th>Durum</th>
            <th>İşlem</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($notifications as $notification): ?>
            <tr>
                <td><?= htmlspecialchars($notification['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($notification['body'])) ?></td>
                <td><?= htmlspecialchars($notification['created_at']) ?></td>
                <td><?= $notification['is_read'] ? 'Okundu' : 'Yeni' ?></td>
                <td>
                    <?php if (!$notification['is_read']): ?>
                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?= $token ?>">
                            <input type="hidden" name="action" value="read">
                            <input type="hidden" name="notification_user_id" value="<?= $notification['id'] ?>">
                            <button class="btn btn-secondary" type="submit">Okundu İşaretle</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
