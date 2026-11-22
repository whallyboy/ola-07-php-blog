<?php

declare(strict_types=1);

function upload_cover(array $file): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [true, null];
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [false, 'The image upload failed.'];
    }
    if (!isset($file['tmp_name'], $file['size']) || !is_uploaded_file($file['tmp_name'])) {
        return [false, 'The upload is not a valid HTTP upload.'];
    }
    if ($file['size'] > 2 * 1024 * 1024) {
        return [false, 'The image must be 2 MB or smaller.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        return [false, 'Only real JPG, PNG, and WebP images are allowed.'];
    }
    $info = getimagesize($file['tmp_name']);
    if ($info === false || $info[0] < 1 || $info[1] < 1) {
        return [false, 'Please upload a valid image file.'];
    }

    $root = dirname(__DIR__);
    $directory = $root . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $name)) {
        return [false, 'Could not save the uploaded image.'];
    }
    return [true, $name];
}
