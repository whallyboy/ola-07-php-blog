<?php

declare(strict_types=1);

function site_header(string $title = 'Blog'): void
{
    ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?></title>
    <link rel="stylesheet" href="<?= e(url('assets/site.css')) ?>">
</head>
<body>
<header class="site-header">
    <a class="site-title" href="<?= e(url()) ?>">My Blog</a>
    <nav>
        <a href="<?= e(url()) ?>">Home</a>
        <form class="search-form" action="<?= e(url('search.php')) ?>" method="get">
            <input name="q" value="<?= e($_GET['q'] ?? null) ?>" placeholder="Search posts" aria-label="Search">
            <button type="submit">Search</button>
        </form>
    </nav>
</header>
<main class="site-main">
    <?php
}

function site_footer(): void
{
    ?>
</main>
</body>
</html>
    <?php
}
