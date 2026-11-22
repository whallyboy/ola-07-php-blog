<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/site_layout.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;
$offset = ($page - 1) * $perPage;
$total = (int) $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.status = "published"
     ORDER BY COALESCE(p.published_at, p.created_at) DESC
     LIMIT :limit OFFSET :offset'
);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

site_header('Blog');
foreach ($posts as $post):
?>
<article class="post-card">
    <?php if ($post['cover_image']): ?>
        <img src="<?= e(url('uploads/' . $post['cover_image'])) ?>" alt="">
    <?php endif; ?>
    <div>
        <a class="category" href="<?= e(url('category/' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a>
        <h2><a href="<?= e(url('post/' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2>
        <p class="meta">By <?= e($post['author_name']) ?> · <?= format_date($post['published_at']) ?></p>
        <p><?= e($post['excerpt'] ?: mb_substr(strip_tags($post['body']), 0, 180) . '...') ?></p>
    </div>
</article>
<?php endforeach;
if (!$posts): ?><p>No published posts yet.</p><?php endif;
$pages = (int) ceil($total / $perPage);
if ($pages > 1): ?>
<nav class="pagination">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="<?= e(url('index.php?page=' . $i)) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</nav>
<?php endif;
site_footer();
