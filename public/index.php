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

// HTTPS redirect
if (
    ($_ENV['APP_ENV'] ?? 'development') === 'production'
    && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')
    && (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || $_SERVER['HTTP_X_FORWARDED_PROTO'] !== 'https')
    && (!isset($_SERVER['HTTP_X_FORWARDED_SSL']) || $_SERVER['HTTP_X_FORWARDED_SSL'] !== 'on')
) {
    $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? '';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: https://' . $host . $uri, 301);
    exit;
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

// Middleware: Honeypot
$router->addMiddleware(function () {
    $redirect = \Elyra\Infrastructure\Web\Middleware\HoneypotMiddleware::handle();
    if ($redirect !== null) {
        header("Location: {$redirect}");
        exit;
    }
});

// CSP Headers
$router->addMiddleware(function () {
    $nonce = \Elyra\Infrastructure\Service\SessionManager::getNonce();
    $csp = "default-src 'self'; script-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com 'nonce-{$nonce}' 'strict-dynamic'; script-src-attr 'unsafe-inline'; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://unpkg.com 'unsafe-inline'; img-src 'self' data: https://*.tile.openstreetmap.org; font-src 'self' https://cdn.jsdelivr.net; connect-src 'self'; frame-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; worker-src 'self'";
    header("Content-Security-Policy: {$csp}");
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 0');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(self), interest-cohort=()');
});

// Run middleware
$router->runMiddleware();

// Dispatch
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip base path for subdirectory installs
$appUrlPath = parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH);
if (is_string($appUrlPath) && $appUrlPath !== '' && $appUrlPath !== '/') {
    $basePath = rtrim($appUrlPath, '/');
    $uriPath = parse_url($uri, PHP_URL_PATH);
    if (is_string($uriPath) && str_starts_with($uriPath, $basePath)) {
        $stripped = substr($uriPath, strlen($basePath)) ?: '/';
        $uri = $stripped . (parse_url($uri, PHP_URL_QUERY) !== null ? '?' . parse_url($uri, PHP_URL_QUERY) : '');
    }
}

$route = $router->dispatch($method, $uri);

if ($route === null) {
    http_response_code(404);
    $nonce = \Elyra\Infrastructure\Service\SessionManager::getNonce();
    require __DIR__ . '/../views/errors/404.php';
    exit;
}

$controllerClass = "Elyra\\Infrastructure\\Web\\Controller\\{$route['controller']}";

if (!class_exists($controllerClass)) {
    http_response_code(500);
    if ($debug) {
        echo htmlspecialchars("Controller {$controllerClass} no encontrado.", ENT_QUOTES, 'UTF-8');
    } else {
        $nonce = \Elyra\Infrastructure\Service\SessionManager::getNonce();
        require __DIR__ . '/../views/errors/500.php';
    }
    exit;
}

$controller = new $controllerClass();

if (!method_exists($controller, $route['action'])) {
    http_response_code(500);
    if ($debug) {
        echo htmlspecialchars("Método {$route['action']} no encontrado en {$route['controller']}.", ENT_QUOTES, 'UTF-8');
    } else {
        $nonce = \Elyra\Infrastructure\Service\SessionManager::getNonce();
        require __DIR__ . '/../views/errors/500.php';
    }
    exit;
}

// Auth check
$noAuthRoutes = ['/', '/login', '/registro', '/recuperar-contrasena', '/restablecer-contrasena', '/offline'];
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
        $bp = rtrim(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?: '', '/');
        header('Location: ' . $bp . '/login');
        exit;
    }
}

$controller->{$route['action']}(...$route['params']);
