<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

if (is_logged_in()) {
    redirect('admin/index.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!login_admin($pdo, $email, $password)) {
        $error = 'Invalid email or password.';
        usleep(300000);
    } else {
        redirect('admin/index.php');
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="<?= e(url('assets/admin.css')) ?>">
</head>
<body class="login-page">
<main class="login-card">
    <h1>Admin Login</h1>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_field() ?>
        <label>Email <input type="email" name="email" value="<?= e($_POST['email'] ?? null) ?>" required autofocus></label>
        <label>Password <input type="password" name="password" required></label>
        <button type="submit">Sign in</button>
    </form>
</main>
</body>
</html>
