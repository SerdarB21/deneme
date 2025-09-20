<?php
$token = generateCsrfToken();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Geçersiz oturum anahtarı';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $user = authenticate($email, $password);
        if ($user) {
            loginUser($user);
            header('Location: index.php?page=' . ($user['role'] === 'admin' ? 'admin_dashboard' : 'bayi_dashboard'));
            exit;
        }
        $error = 'E-posta veya şifre hatalı';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Bayilik Sistemi</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card">
        <h1 class="auth-title">Bayilik Sistemi</h1>
        <p class="auth-subtitle">Lütfen hesabınıza giriş yapın</p>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?= $token ?>">
            <div class="form-group">
                <label for="email">E-posta</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Şifre</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Giriş Yap</button>
            <p class="auth-hint">İlk giriş için admin@bayi.local / admin123</p>
        </form>
    </div>
</div>
<footer class="auth-footer">Bu proje <a href="https://ewreka.net" target="_blank">Ewreka Digital</a> tarafından geliştirilmiştir.</footer>
</body>
</html>
