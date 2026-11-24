<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';
require_once dirname(__DIR__) . '/app/admin_layout.php';
require_login();

$stats = $pdo->query(
    "SELECT
        COUNT(*) AS total_posts,
        COALESCE(SUM(status = 'published'), 0) AS published_posts,
        COALESCE(SUM(status = 'draft'), 0) AS draft_posts,
        (SELECT COUNT(*) FROM categories) AS total_categories
     FROM posts"
)->fetch();

$latestPosts = $pdo->query(
    'SELECT p.id, p.title, p.slug, p.status, p.created_at,
            c.name AS category_name, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     ORDER BY p.created_at DESC, p.id DESC
     LIMIT 5'
)->fetchAll();

$postsPerCategory = $pdo->query(
    "SELECT c.id, c.name, c.slug,
            COUNT(p.id) AS post_count,
            COALESCE(SUM(p.status = 'published'), 0) AS published_count,
            COALESCE(SUM(p.status = 'draft'), 0) AS draft_count
     FROM categories c
     LEFT JOIN posts p ON p.category_id = c.id
     GROUP BY c.id, c.name, c.slug
     ORDER BY post_count DESC, c.name ASC"
)->fetchAll();

admin_header('Dashboard');
?>
<section class="stats">
    <a href="<?= e(url('admin/posts/index.php?status=published')) ?>">
        <strong><?= (int) $stats['published_posts'] ?></strong><span>Published posts</span>
    </a>
    <a href="<?= e(url('admin/posts/index.php?status=draft')) ?>">
        <strong><?= (int) $stats['draft_posts'] ?></strong><span>Draft posts</span>
    </a>
    <a href="<?= e(url('admin/categories/index.php')) ?>">
        <strong><?= (int) $stats['total_categories'] ?></strong><span>Categories</span>
    </a>
</section>

<section class="dashboard-section">
    <h2>Latest posts</h2>
    <?php if ($latestPosts): ?>
    <table>
        <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Status</th><th>Created</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($latestPosts as $post): ?>
            <tr>
                <td><?= e($post['title']) ?></td>
                <td><?= e($post['category_name']) ?></td>
                <td><?= e($post['author_name']) ?></td>
                <td><span class="status <?= e($post['status']) ?>"><?= e($post['status']) ?></span></td>
                <td><?= e(format_date($post['created_at'])) ?></td>
                <td><a href="<?= e(url('admin/posts/edit.php?id=' . (int) $post['id'])) ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No posts yet. <a href="<?= e(url('admin/posts/create.php')) ?>">Create your first post</a>.</p>
    <?php endif; ?>
</section>

<section class="dashboard-section">
    <h2>Posts per category</h2>
    <?php if ($postsPerCategory): ?>
    <table>
        <thead><tr><th>Category</th><th>Total posts</th><th>Published</th><th>Drafts</th></tr></thead>
        <tbody>
        <?php foreach ($postsPerCategory as $category): ?>
            <tr>
                <td><a href="<?= e(url('admin/categories/index.php')) ?>"><?= e($category['name']) ?></a></td>
                <td><?= (int) $category['post_count'] ?></td>
                <td><?= (int) $category['published_count'] ?></td>
                <td><?= (int) $category['draft_count'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No categories yet. <a href="<?= e(url('admin/categories/index.php')) ?>">Add a category</a>.</p>
    <?php endif; ?>
</section>
<?php admin_footer(); ?>
