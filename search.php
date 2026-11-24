<?php

declare(strict_types=1);

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/site_layout.php';

$query = trim((string) ($_GET['q'] ?? ''));
$posts = [];
if ($query !== '') {
    $stmt = $pdo->prepare(
        'SELECT p.*, c.name AS category_name, c.slug AS category_slug
         FROM posts p JOIN categories c ON c.id = p.category_id
         WHERE p.status = "published"
           AND MATCH(p.title, p.body) AGAINST(? IN NATURAL LANGUAGE MODE)
         ORDER BY COALESCE(p.published_at, p.created_at) DESC
         LIMIT 50'
    );
    $stmt->execute([$query]);
    $posts = $stmt->fetchAll();

    if (mb_strlen($query, 'UTF-8') < 3 || !$posts) {
        $like = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query) . '%';
        $stmt = $pdo->prepare(
            'SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM posts p JOIN categories c ON c.id = p.category_id
             WHERE p.status = "published"
               AND (p.title LIKE ? ESCAPE \'!\' OR p.body LIKE ? ESCAPE \'!\')
             ORDER BY COALESCE(p.published_at, p.created_at) DESC
             LIMIT 50'
        );
        $stmt->execute([$like, $like]);
        $posts = $stmt->fetchAll();
    }
}

site_header('Search');
?>
<h1>Search</h1>
<form class="search-form large" method="get">
    <input name="q" value="<?= e($query) ?>" placeholder="Search posts">
    <button type="submit">Search</button>
</form>
<?php if ($query !== ''): ?>
    <p><?= count($posts) ?> result<?= count($posts) === 1 ? '' : 's' ?> for “<?= e($query) ?>”</p>
    <?php foreach ($posts as $post): ?>
        <article class="post-card compact">
            <a class="category" href="<?= e(url('category/' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a>
            <h2><a href="<?= e(url('post/' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2>
        </article>
    <?php endforeach;
endif;
site_footer();
