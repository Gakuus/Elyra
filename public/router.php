<?php

declare(strict_types=1);

/**
 * Router script para el servidor built-in de PHP.
 *
 * Uso: php -S 0.0.0.0:8000 public/router.php
 *
 * Si el archivo solicitado existe en public/, lo sirve directamente.
 * Si no, ejecuta index.php (front controller).
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$publicPath = __DIR__ . $uri;

if ($uri !== '/' && is_file($publicPath)) {
    return false;
}

require __DIR__ . '/index.php';
