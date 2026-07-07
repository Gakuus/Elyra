<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// .env
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

// Error handler
$debug = ($_ENV['APP_DEBUG'] ?? false) === true;
if (!$debug) {
    error_reporting(0);
    ini_set('display_errors', '0');
}
\Elyra\Infrastructure\Service\ErrorHandler::register();

// Session
\Elyra\Infrastructure\Service\SessionManager::start();
\Elyra\Infrastructure\Service\RateLimiter::setStorageDir(__DIR__ . '/../storage/rate-limit');

// Router
$router = new \Elyra\Infrastructure\Web\Router();

$routeDefinitions = require __DIR__ . '/../src/Infrastructure/Web/Routes/web.php';
$router->loadRoutes($routeDefinitions);

// Middleware: rate limiting (public routes)
$router->addMiddleware(function () {
    $publicPrefixes = ['/publico'];
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    foreach ($publicPrefixes as $prefix) {
        if (str_starts_with($uri, $prefix)) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            if (!\Elyra\Infrastructure\Service\RateLimiter::checkPublicRequest($ip)) {
                http_response_code(429);
                echo json_encode(['error' => 'Demasiadas solicitudes. Intente de nuevo en 1 minuto.']);
                exit;
            }
            \Elyra\Infrastructure\Service\RateLimiter::incrementPublicRequest($ip);
            break;
        }
    }
});

// Middleware: CSRF
$router->addMiddleware(function () {
    \Elyra\Infrastructure\Web\Middleware\CsrfMiddleware::handle();
});

// CSP Headers
$router->addMiddleware(function () {
    $csp = "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com 'unsafe-inline'; img-src 'self' data:; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self'; frame-src 'self'; object-src 'self'; base-uri 'self'; form-action 'self'";
    header("Content-Security-Policy: {$csp}");
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 0');
    header('Referrer-Policy: strict-origin-when-cross-origin');
});

// Run middleware
$router->runMiddleware();

// Dispatch
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$route = $router->dispatch($method, $uri);

if ($route === null) {
    http_response_code(404);
    require __DIR__ . '/../views/errors/404.php';
    exit;
}

$controllerClass = "Elyra\\Infrastructure\\Web\\Controller\\{$route['controller']}";

if (!class_exists($controllerClass)) {
    http_response_code(500);
    if ($debug) {
        echo "Controller {$controllerClass} no encontrado.";
    } else {
        require __DIR__ . '/../views/errors/500.php';
    }
    exit;
}

$controller = new $controllerClass();

if (!method_exists($controller, $route['action'])) {
    http_response_code(500);
    if ($debug) {
        echo "Método {$route['action']} no encontrado en {$route['controller']}.";
    } else {
        require __DIR__ . '/../views/errors/500.php';
    }
    exit;
}

// Auth check
$noAuthRoutes = ['/', '/login'];
$publicPrefixes = ['/publico'];

$isPublic = false;
foreach ($publicPrefixes as $prefix) {
    if (str_starts_with(parse_url($uri, PHP_URL_PATH), $prefix)) {
        $isPublic = true;
        break;
    }
}

if (!$isPublic && !in_array(parse_url($uri, PHP_URL_PATH), $noAuthRoutes, true)) {
    if (!\Elyra\Infrastructure\Service\SessionManager::isAuthenticated()) {
        header('Location: /login');
        exit;
    }
}

$controller->{$route['action']}(...$route['params']);
