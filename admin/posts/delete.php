<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/app/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/posts/index.php');
}
verify_csrf();
$id = (int) ($_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT cover_image FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();
if ($post) {
    $delete = $pdo->prepare('DELETE FROM posts WHERE id = ?');
    $delete->execute([$id]);
    if ($post['cover_image']) {
        $file = dirname(dirname(__DIR__)) . '/uploads/' . basename($post['cover_image']);
        if (is_file($file)) unlink($file);
    }
    flash('success', 'Post deleted.');
}
redirect('admin/posts/index.php');
