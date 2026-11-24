<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/app/bootstrap.php';
require_once dirname(dirname(__DIR__)) . '/app/admin_layout.php';
require_once dirname(dirname(__DIR__)) . '/app/upload.php';
require_login();

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM posts WHERE id = ?');
$stmt->execute([$id]);
$post = $stmt->fetch();
if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}
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
            $slug = unique_post_slug($pdo, $title, $id);
            $cover = $cover ?? $post['cover_image'];
            $update = $pdo->prepare(
                'UPDATE posts SET category_id = ?, title = ?, slug = ?, excerpt = ?, body = ?, cover_image = ?, status = ?,
                 published_at = CASE
                    WHEN ? = "published" AND published_at IS NULL THEN NOW()
                    WHEN ? = "draft" THEN NULL
                    ELSE published_at
                 END
                 WHERE id = ?'
            );
            $update->execute([$categoryId, $title, $slug, $excerpt ?: null, $body, $cover, $status, $status, $status, $id]);
            if ($cover !== $post['cover_image'] && $post['cover_image']) {
                $old = dirname(dirname(__DIR__)) . '/uploads/' . basename($post['cover_image']);
                if (is_file($old)) unlink($old);
            }
            flash('success', 'Post updated.');
            redirect('admin/posts/index.php');
        }
    }
}

admin_header('Edit Post');
if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif;
?>
<form method="post" enctype="multipart/form-data" class="form">
    <?= csrf_field() ?>
    <label>Title <input type="text" name="title" maxlength="255" value="<?= e($_POST['title'] ?? $post['title']) ?>" required></label>
    <label>Category
        <select name="category_id" required>
            <?php foreach ($categories as $category):
                $selected = (int) ($_POST['category_id'] ?? $post['category_id']) === (int) $category['id']; ?>
                <option value="<?= (int) $category['id'] ?>" <?= $selected ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Status
        <select name="status">
            <option value="draft" <?= (($_POST['status'] ?? $post['status']) === 'draft') ? 'selected' : '' ?>>Draft</option>
            <option value="published" <?= (($_POST['status'] ?? $post['status']) === 'published') ? 'selected' : '' ?>>Published</option>
        </select>
    </label>
    <?php if ($post['cover_image']): ?>
        <p>Current cover: <img src="<?= e(url('uploads/' . $post['cover_image'])) ?>" alt="" class="cover-preview"></p>
    <?php endif; ?>
    <label>Replace cover image <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"></label>
    <label>Excerpt <textarea name="excerpt" rows="3" maxlength="500"><?= e($_POST['excerpt'] ?? $post['excerpt']) ?></textarea></label>
    <label>Body <textarea name="body" rows="16" required><?= e($_POST['body'] ?? $post['body']) ?></textarea></label>
    <button type="submit">Save changes</button>
</form>
<?php admin_footer(); ?>
