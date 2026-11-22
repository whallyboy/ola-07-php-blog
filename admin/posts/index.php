<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/app/bootstrap.php';
require_once dirname(dirname(__DIR__)) . '/app/admin_layout.php';
require_login();

$filter = $_GET['status'] ?? 'all';
$where = '';
$params = [];
if ($filter === 'draft' || $filter === 'published') {
    $where = ' WHERE p.status = ?';
    $params[] = $filter;
}
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id'
    . $where .
    ' ORDER BY p.created_at DESC'
);
$stmt->execute($params);
$posts = $stmt->fetchAll();

admin_header('Posts');
?>
<p><a class="button" href="<?= e(url('admin/posts/create.php')) ?>">Create post</a></p>
<form method="get" class="inline-form" style="margin-bottom:16px">
    <label>Status
        <select name="status">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="published" <?= $filter === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= $filter === 'draft' ? 'selected' : '' ?>>Draft</option>
        </select>
    </label>
    <button type="submit">Filter</button>
</form>
<table>
    <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Author</th><th>Updated</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($posts as $post): ?>
        <tr>
            <td><?= e($post['title']) ?></td>
            <td><?= e($post['category_name']) ?></td>
            <td><span class="status <?= e($post['status']) ?>"><?= e($post['status']) ?></span></td>
            <td><?= e($post['author_name']) ?></td>
            <td><?= format_date($post['updated_at']) ?></td>
            <td>
                <a href="<?= e(url('admin/posts/edit.php?id=' . $post['id'])) ?>">Edit</a>
                <?php if ($post['status'] === 'published'): ?>
                    <a href="<?= e(url('post.php?slug=' . urlencode($post['slug']))) ?>" target="_blank">View</a>
                <?php endif; ?>
                <form method="post" action="<?= e(url('admin/posts/delete.php')) ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                    <button class="link danger" type="submit" onclick="return confirm('Delete this post?')">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php admin_footer(); ?>
