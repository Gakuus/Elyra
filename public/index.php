<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

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

session_start();

error_reporting($_ENV['APP_DEBUG'] ?? false ? E_ALL : 0);
ini_set('display_errors', $_ENV['APP_DEBUG'] ?? false ? '1' : '0');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$publicRoutes = [
    '/publico/doc',
    '/publico/encuesta',
    '/publico/encuesta/enviar',
];

$isPublic = false;
foreach ($publicRoutes as $pr) {
    if (str_starts_with($uri, $pr)) {
        $isPublic = true;
        break;
    }
}

$noAuthRoutes = ['/', '/logout'];

$routes = require __DIR__ . '/../src/Infrastructure/Web/Routes/web.php';

if (isset($routes[$uri])) {
    [$controllerName, $method] = $routes[$uri];

    $controllerClass = "Elyra\\Infrastructure\\Web\\Controller\\{$controllerName}";

    if (!class_exists($controllerClass)) {
        http_response_code(500);
        echo "Error: Controller {$controllerName} no encontrado.";
        exit;
    }

    $controller = new $controllerClass();

    if (!method_exists($controller, $method)) {
        http_response_code(500);
        echo "Error: Método {$method} no encontrado en {$controllerName}.";
        exit;
    }

    if (!$isPublic && !in_array($uri, $noAuthRoutes, true)) {
        if (!isset($_SESSION['user'])) {
            header('Location: /');
            exit;
        }
    }

    $controller->$method();
} else {
    http_response_code(404);
    echo "404 - Página no encontrada";
}
