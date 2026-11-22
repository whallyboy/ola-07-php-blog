<?php

declare(strict_types=1);

require_once dirname(dirname(__DIR__)) . '/app/bootstrap.php';
require_once dirname(dirname(__DIR__)) . '/app/admin_layout.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $id = (int) ($_POST['id'] ?? 0);
    if ($name === '') {
        flash('error', 'Category name is required.');
    } elseif ($id > 0) {
        $base = slugify($name);
        $slug = $base;
        $n = 2;
        while (true) {
            $check = $pdo->prepare('SELECT id FROM categories WHERE slug = ? AND id <> ?');
            $check->execute([$slug, $id]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . $n++;
        }
        $stmt = $pdo->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?');
        $stmt->execute([$name, $slug, $id]);
        flash('success', 'Category updated.');
    } else {
        $base = slugify($name);
        $slug = $base;
        $n = 2;
        while (true) {
            $check = $pdo->prepare('SELECT id FROM categories WHERE slug = ?');
            $check->execute([$slug]);
            if (!$check->fetch()) break;
            $slug = $base . '-' . $n++;
        }
        $stmt = $pdo->prepare('INSERT INTO categories (name, slug) VALUES (?, ?)');
        $stmt->execute([$name, $slug]);
        flash('success', 'Category created.');
    }
    redirect('admin/categories/index.php');
}

$categories = $pdo->query('SELECT c.*, COUNT(p.id) AS post_count FROM categories c LEFT JOIN posts p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
admin_header('Categories');
?>
<section class="panel">
    <h2>Add category</h2>
    <form method="post" class="inline-form">
        <?= csrf_field() ?>
        <input type="text" name="name" maxlength="120" required placeholder="Category name">
        <button type="submit">Add</button>
    </form>
</section>
<table>
    <thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($categories as $category): ?>
        <tr>
            <td><?= e($category['name']) ?></td>
            <td><?= e($category['slug']) ?></td>
            <td><?= (int) $category['post_count'] ?></td>
            <td>
                <form method="post" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                    <input type="text" name="name" value="<?= e($category['name']) ?>" maxlength="120" required>
                    <button type="submit">Save</button>
                </form>
                <?php if ((int) $category['post_count'] === 0): ?>
                <form method="post" action="delete.php" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int) $category['id'] ?>">
                    <button class="danger" type="submit" onclick="return confirm('Delete this category?')">Delete</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php admin_footer(); ?>
