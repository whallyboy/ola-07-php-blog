<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only run from the command line.');
}

if ($argc < 3) {
    fwrite(STDERR, "Usage: php create-admin.php \"Your Name\" you@example.com\n");
    exit(1);
}

$name = trim($argv[1]);
$email = strtolower(trim($argv[2]));
if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "A name and valid email address are required.\n");
    exit(1);
}

$password = '';
echo "Password: ";
$password = trim((string) fgets(STDIN));
echo "Confirm password: ";
$confirmation = trim((string) fgets(STDIN));
if ($password === '' || strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    exit(1);
}
if (!hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Passwords do not match.\n");
    exit(1);
}

require_once __DIR__ . '/app/db.php';

try {
    $check = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        fwrite(STDERR, "An administrator with that email already exists.\n");
        exit(1);
    }
    $stmt = $pdo->prepare('INSERT INTO admins (name, email, password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
    echo "Administrator created successfully.\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Could not create administrator. Check the database and schema.\n");
    exit(1);
}
