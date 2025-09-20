<?php
$title = 'Ödeme Bildirimleri';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'update_status') {
            updateTicketStatus((int) $_POST['ticket_id'], $_POST['status']);
            setFlash('success', 'Ödeme bildirimi güncellendi');
        } elseif ($action === 'add_message') {
            $attachment = null;
            if (!empty($_FILES['attachment']['tmp_name'])) {
                try {
                    $attachment = handleUpload($_FILES['attachment']);
                } catch (Exception $e) {
                    setFlash('error', $e->getMessage());
                }
            }
            addTicketMessage((int) $_POST['ticket_id'], $currentUser['id'], trim($_POST['message']), $attachment);
            setFlash('success', 'Mesaj gönderildi');
        }
    }
    header('Location: index.php?page=admin_payments' . (!empty($_POST['ticket_id']) ? '&view=' . (int) $_POST['ticket_id'] : ''));
    exit;
}
$tickets = getTickets(['type' => 'odeme']);
$viewId = isset($_GET['view']) ? (int) $_GET['view'] : null;
$viewTicket = null;
$messages = [];
if ($viewId) {
    foreach ($tickets as $ticket) {
        if ((int)$ticket['id'] === $viewId) {
            $viewTicket = $ticket;
            break;
        }
    }
    if ($viewTicket) {
        $messages = getTicketMessages($viewId);
    }
}
require __DIR__ . '/../partials/header.php';
?>
<section class="card">
    <h2>Ödeme Kayıtları</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Bayi</th>
            <th>Konu</th>
            <th>Durum</th>
            <th>Tarih</th>
            <th>İşlem</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td>#<?= $ticket['id'] ?></td>
                <td><?= htmlspecialchars($ticket['user_name']) ?></td>
                <td><?= htmlspecialchars($ticket['subject']) ?></td>
                <td><?= htmlspecialchars($ticket['status']) ?></td>
                <td><?= htmlspecialchars($ticket['created_at']) ?></td>
                <td><a class="btn btn-secondary" href="index.php?page=admin_payments&view=<?= $ticket['id'] ?>">Görüntüle</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php if ($viewTicket): ?>
<section class="card" style="margin-top:2rem;">
    <h2>Ödeme Bildirimi #<?= $viewTicket['id'] ?></h2>
    <form method="post" class="form-group-inline" style="margin-bottom:1.5rem;">
        <input type="hidden" name="csrf_token" value="<?= $token ?>">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="ticket_id" value="<?= $viewTicket['id'] ?>">
        <div class="form-group">
            <label>Durum</label>
            <select name="status">
                <option value="acik" <?= $viewTicket['status'] === 'acik' ? 'selected' : '' ?>>Açık</option>
                <option value="kontrolde" <?= $viewTicket['status'] === 'kontrolde' ? 'selected' : '' ?>>Kontrolde</option>
                <option value="onaylandi" <?= $viewTicket['status'] === 'onaylandi' ? 'selected' : '' ?>>Onaylandı</option>
                <option value="reddedildi" <?= $viewTicket['status'] === 'reddedildi' ? 'selected' : '' ?>>Reddedildi</option>
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
                    <div><a href="uploads/<?= htmlspecialchars($message['attachment_path']) ?>" target="_blank">Dosya</a></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="post" enctype="multipart/form-data" style="margin-top:1.5rem;gap:1rem;" class="grid">
        <input type="hidden" name="csrf_token" value="<?= $token ?>">
        <input type="hidden" name="action" value="add_message">
        <input type="hidden" name="ticket_id" value="<?= $viewTicket['id'] ?>">
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
