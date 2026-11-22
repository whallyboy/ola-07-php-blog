<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/app/bootstrap.php';
require_once dirname(dirname(__DIR__)) . '/app/admin_layout.php';
require_once dirname(dirname(__DIR__)) . '/app/upload.php';
require_login();

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim((string) ($_POST['title'] ?? ''));
    $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
    $body = trim((string) ($_POST['body'] ?? ''));
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $status = ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';

    if ($title === '' || $body === '' || $categoryId < 1) {
        $error = 'Title, body, and category are required.';
    } elseif (mb_strlen($excerpt) > 500) {
        $error = 'Excerpt must be 500 characters or fewer.';
    } else {
        [$ok, $cover] = upload_cover($_FILES['cover_image'] ?? []);
        if (!$ok) {
            $error = $cover;
        } else {
            $slug = unique_post_slug($pdo, $title);
            $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
            $stmt = $pdo->prepare(
                'INSERT INTO posts (category_id, author_id, title, slug, excerpt, body, cover_image, status, published_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$categoryId, $_SESSION['admin_id'], $title, $slug, $excerpt ?: null, $body, $cover, $status, $publishedAt]);
            flash('success', 'Post created.');
            redirect('admin/posts/index.php');
        }
    }
}

admin_header('Create Post');
if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif;
?>
<form method="post" enctype="multipart/form-data" class="form">
    <?= csrf_field() ?>
    <label>Title <input type="text" name="title" maxlength="255" value="<?= e($_POST['title'] ?? null) ?>" required></label>
    <label>Category
        <select name="category_id" required>
            <option value="">Select category</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Status
        <select name="status">
            <option value="draft" <?= (($_POST['status'] ?? 'draft') === 'draft') ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= (($_POST['status'] ?? '') === 'published') ? 'selected' : '' ?>>Published</option>
        </select>
    </label>
    <label>Cover image <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></label>
    <label>Excerpt <textarea name="excerpt" rows="3" maxlength="500"><?= e($_POST['excerpt'] ?? null) ?></textarea></label>
    <label>Body <textarea name="body" rows="16" required><?= e($_POST['body'] ?? null) ?></textarea></label>
    <button type="submit">Create post</button>
</form>
<?php admin_footer(); ?>
