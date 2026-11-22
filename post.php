<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/site_layout.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.slug = ? AND p.status = "published" LIMIT 1'
);
$stmt->execute([$slug]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
    require_once __DIR__ . '/app/site_layout.php';
    site_header('Post not found');
    echo '<h1>Post not found</h1>';
    site_footer();
    exit;
}

site_header($post['title']);
$words = str_word_count(trim(strip_tags($post['body'])));
$minutes = max(1, (int) ceil($words / 200));

$relatedStmt = $pdo->prepare(
    'SELECT p.slug, p.title, p.cover_image, p.excerpt, p.body, p.published_at
     FROM posts p
     WHERE p.status = "published" AND p.category_id = ? AND p.id <> ?
     ORDER BY COALESCE(p.published_at, p.created_at) DESC
     LIMIT 3'
);
$relatedStmt->execute([$post['category_id'], $post['id']]);
$relatedPosts = $relatedStmt->fetchAll();
?>
<article class="post-full">
    <a class="category" href="<?= e(url('category/' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a>
    <h1><?= e($post['title']) ?></h1>
    <p class="meta">By <?= e($post['author_name']) ?> · <?= format_date($post['published_at']) ?> · <?= $minutes ?> min read</p>
    <?php if ($post['cover_image']): ?>
        <img class="post-cover" src="<?= e(url('uploads/' . $post['cover_image'])) ?>" alt="">
    <?php endif; ?>
    <div class="post-body"><?= render_body($post['body']) ?></div>
</article>
<?php if ($relatedPosts): ?>
<section class="related-posts">
    <h2>Related posts</h2>
    <div class="related-grid">
    <?php foreach ($relatedPosts as $related): ?>
        <article>
            <?php if ($related['cover_image']): ?>
                <img src="<?= e(url('uploads/' . $related['cover_image'])) ?>" alt="">
            <?php endif; ?>
            <h3><a href="<?= e(url('post/' . rawurlencode($related['slug']))) ?>"><?= e($related['title']) ?></a></h3>
            <p><?= e($related['excerpt'] ?: mb_substr(strip_tags($related['body']), 0, 120) . '...') ?></p>
        </article>
    <?php endforeach; ?>
    </div>
</section>
<?php endif;
?>
<?php site_footer(); ?>
