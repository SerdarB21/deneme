<?php
$title = 'Ödeme Bildirimi';
$user = $currentUser;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Geçersiz güvenlik anahtarı');
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'create') {
            $amount = (float) $_POST['amount'];
            $iban = trim($_POST['iban']);
            $note = trim($_POST['note']);
            $subject = 'Ödeme Bildirimi - ' . formatCurrency($amount);
            $ticketId = createTicket($user['id'], $subject . ' (' . $iban . ')', 'odeme');
            $message = "Ödeme tutarı: " . formatCurrency($amount) . "\nIBAN: $iban\nAçıklama: $note";
            $attachment = null;
            if (!empty($_FILES['receipt']['tmp_name'])) {
                try {
                    $attachment = handleUpload($_FILES['receipt']);
                } catch (Exception $e) {
                    setFlash('error', $e->getMessage());
                }
            }
            addTicketMessage($ticketId, $user['id'], $message, $attachment);
            setFlash('success', 'Ödeme bildiriminiz alındı');
        } elseif ($action === 'add_message') {
            $attachment = null;
            if (!empty($_FILES['attachment']['tmp_name'])) {
                try {
                    $attachment = handleUpload($_FILES['attachment']);
                } catch (Exception $e) {
                    setFlash('error', $e->getMessage());
                }
            }
            addTicketMessage((int) $_POST['ticket_id'], $user['id'], trim($_POST['message']), $attachment);
            setFlash('success', 'Mesaj gönderildi');
        }
    }
    header('Location: index.php?page=bayi_payments' . (!empty($_POST['ticket_id']) ? '&view=' . (int) $_POST['ticket_id'] : ''));
    exit;
}
$tickets = getTickets(['user_id' => $user['id'], 'type' => 'odeme']);
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
<section class="grid">
    <div class="card">
        <h2>Ödeme Bildir</h2>
        <form method="post" enctype="multipart/form-data" class="grid" style="gap:1rem;">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <input type="hidden" name="action" value="create">
            <div class="form-group">
                <label>Tutar</label>
                <input type="number" step="0.01" name="amount" required>
            </div>
            <div class="form-group">
                <label>IBAN</label>
                <input type="text" name="iban" required>
            </div>
            <div class="form-group">
                <label>Açıklama</label>
                <textarea name="note" required></textarea>
            </div>
            <div class="form-group">
                <label>Dekont (opsiyonel)</label>
                <input type="file" name="receipt">
            </div>
            <button class="btn btn-primary" type="submit">Bildir</button>
        </form>
    </div>
</section>
<section class="card" style="margin-top:2rem;">
    <h2>Bildirimlerim</h2>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
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
                <td><?= htmlspecialchars($ticket['subject']) ?></td>
                <td><?= htmlspecialchars($ticket['status']) ?></td>
                <td><?= htmlspecialchars($ticket['created_at']) ?></td>
                <td><a class="btn btn-secondary" href="index.php?page=bayi_payments&view=<?= $ticket['id'] ?>">Görüntüle</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php if ($viewTicket): ?>
<section class="card" style="margin-top:2rem;">
    <h2>Ödeme #<?= $viewTicket['id'] ?></h2>
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
