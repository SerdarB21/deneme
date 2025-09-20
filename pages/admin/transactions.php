<?php
$title = 'Bakiye Hareketleri';
$bayiler = getBayiList();
$selectedId = isset($_GET['bayi']) ? (int) $_GET['bayi'] : ($bayiler[0]['id'] ?? null);
$transactions = $selectedId ? getTransactions($selectedId) : [];
require __DIR__ . '/../partials/header.php';
?>
<section class="card">
    <h2>Bayi Hareketleri</h2>
    <form method="get" class="form-group-inline" style="margin-bottom:1rem;">
        <input type="hidden" name="page" value="admin_transactions">
        <div class="form-group">
            <label>Bayi</label>
            <select name="bayi" onchange="this.form.submit()">
                <?php foreach ($bayiler as $bayi): ?>
                    <option value="<?= $bayi['id'] ?>" <?= $selectedId == $bayi['id'] ? 'selected' : '' ?>><?= htmlspecialchars($bayi['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
    <table class="table">
        <thead>
        <tr>
            <th>Tarih</th>
            <th>Tür</th>
            <th>Tutar</th>
            <th>Açıklama</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($transactions as $tx): ?>
            <tr>
                <td><?= htmlspecialchars($tx['created_at']) ?></td>
                <td><?= htmlspecialchars($tx['type']) ?></td>
                <td><?= formatCurrency((float)$tx['amount']) ?></td>
                <td><?= htmlspecialchars($tx['description']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php require __DIR__ . '/../partials/footer.php'; ?>
