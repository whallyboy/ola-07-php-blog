<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/admin_layout.php';
require_login();

$published = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$draft = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn();
$categories = (int) $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

admin_header('Dashboard');
?>
<div class="stats">
    <a href="<?= e(url('admin/posts/index.php')) ?>"><strong><?= $published ?></strong><span>Published posts</span></a>
    <a href="<?= e(url('admin/posts/index.php')) ?>"><strong><?= $draft ?></strong><span>Draft posts</span></a>
    <a href="<?= e(url('admin/categories/index.php')) ?>"><strong><?= $categories ?></strong><span>Categories</span></a>
</div>
<?php admin_footer(); ?>
