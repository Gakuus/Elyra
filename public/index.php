<?php

// Load .env early for base path detection
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Serve static files directly (nginx routes everything through PHP)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(parse_url((string)($_ENV['APP_URL'] ?? ''), PHP_URL_PATH) ?: '', '/');
$relative = $basePath !== '' ? substr($uri, strlen($basePath)) : $uri;

if ($relative !== '' && $relative !== '/') {
    $file = __DIR__ . $relative;
    if (is_file($file)) {
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'png'  => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'svg'  => 'image/svg+xml',
            'ico'  => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2'=> 'font/woff2',
            'ttf'  => 'font/ttf',
            default => null,
        };
        if ($mime !== null) {
            header('Content-Type: ' . $mime);
            header('Cache-Control: public, max-age=3600');
            readfile($file);
            exit;
        }
    }
}

require_once __DIR__ . '/../index.php';
