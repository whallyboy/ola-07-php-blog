<?php

declare(strict_types=1);

function admin_header(string $title = 'Admin'): void
{
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - Blog Admin</title>
    <link rel="stylesheet" href="<?= e(url('assets/admin.css')) ?>">
</head>
<body>
<aside class="sidebar">
    <a class="brand" href="<?= e(url('admin/index.php')) ?>">Blog CMS</a>
    <nav>
        <a href="<?= e(url('admin/index.php')) ?>">Dashboard</a>
        <a href="<?= e(url('admin/posts/index.php')) ?>">Posts</a>
        <a href="<?= e(url('admin/categories/index.php')) ?>">Categories</a>
        <a href="<?= e(url()) ?>" target="_blank">View site</a>
        <a href="<?= e(url('admin/logout.php')) ?>">Logout</a>
    </nav>
</aside>
<main class="admin-main">
    <h1><?= e($title) ?></h1>
    <?php
    if ($message = flash('success')): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif;
    if ($message = flash('error')): ?>
        <div class="alert error"><?= e($message) ?></div>
    <?php endif;
}

function admin_footer(): void
{
    ?>
</main>
</body>
</html>
    <?php
}
