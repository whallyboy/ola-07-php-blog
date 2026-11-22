<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/site_layout.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ? LIMIT 1');
$stmt->execute([$slug]);
$category = $stmt->fetch();
if (!$category) {
    http_response_code(404);
    site_header('Category not found');
    echo '<h1>Category not found</h1>';
    site_footer();
    exit;
}

$stmt = $pdo->prepare(
    'SELECT p.*, a.name AS author_name
     FROM posts p
     JOIN admins a ON a.id = p.author_id
     WHERE p.category_id = ? AND p.status = "published"
     ORDER BY COALESCE(p.published_at, p.created_at) DESC'
);
$stmt->execute([$category['id']]);
$posts = $stmt->fetchAll();

site_header($category['name']);
?>
<h1><?= e($category['name']) ?></h1>
<?php foreach ($posts as $post): ?>
    <article class="post-card compact">
        <?php if ($post['cover_image']): ?>
            <img src="<?= e(url('uploads/' . $post['cover_image'])) ?>" alt="">
        <?php endif; ?>
        <h2><a href="<?= e(url('post/' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2>
        <p class="meta">By <?= e($post['author_name']) ?> · <?= format_date($post['published_at']) ?></p>
        <p><?= e($post['excerpt'] ?: mb_substr(strip_tags($post['body']), 0, 180) . '...') ?></p>
    </article>
<?php endforeach;
if (!$posts): ?><p>No published posts in this category.</p><?php endif;
site_footer();
