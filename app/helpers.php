<?php

declare(strict_types=1);

const BASE_URL = '/swd/ola-07-php-blog';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
    exit;
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION[$key] = $message;
        return null;
    }
    $value = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return is_string($value) ? $value : null;
}

function slugify(string $text): string
{
    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: strtolower($text);
    } else {
        $text = strtolower($text);
    }
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-') ?: 'item-' . bin2hex(random_bytes(3));
}

function unique_post_slug(PDO $pdo, string $title, ?int $excludeId = null): string
{
    $base = slugify($title);
    $slug = $base;
    $number = 2;

    while (true) {
        $sql = $excludeId === null
            ? 'SELECT id FROM posts WHERE slug = ? LIMIT 1'
            : 'SELECT id FROM posts WHERE slug = ? AND id <> ? LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $excludeId === null ? $stmt->execute([$slug]) : $stmt->execute([$slug, $excludeId]);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $number++;
    }
}

function format_date(?string $date): string
{
    if (!$date) {
        return '';
    }
    $timestamp = strtotime($date);
    return $timestamp ? date('F j, Y', $timestamp) : '';
}

function reading_time(string $body): int
{
    $words = str_word_count(trim(strip_tags($body)));
    return max(1, (int) ceil($words / 200));
}

function render_body(string $body): string
{
    $body = str_replace(["\r\n", "\r"], "\n", $body);
    $paragraphs = preg_split('/\n\s*\n/', trim($body)) ?: [];
    $html = '';
    foreach ($paragraphs as $paragraph) {
        $paragraph = trim($paragraph);
        if ($paragraph !== '') {
            $html .= '<p>' . nl2br(e($paragraph), false) . '</p>';
        }
    }
    return $html;
}
