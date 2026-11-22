<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/app/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/categories/index.php');
}
verify_csrf();
$id = (int) ($_POST['id'] ?? 0);
$check = $pdo->prepare('SELECT COUNT(*) FROM posts WHERE category_id = ?');
$check->execute([$id]);
if ((int) $check->fetchColumn() > 0) {
    flash('error', 'Categories with posts cannot be deleted.');
} else {
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    flash('success', 'Category deleted.');
}
redirect('admin/categories/index.php');
