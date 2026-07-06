<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Middleware;

use Elyra\Infrastructure\Service\SessionManager;

class CsrfMiddleware
{
    public static function handle(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            return;
        }

        $token = $_POST['_csrf_token'] ?? '';
        if (empty($token)) {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (str_contains($contentType, 'application/json')) {
                $input = json_decode(file_get_contents('php://input'), true);
                $token = $input['_csrf_token'] ?? '';
            } elseif (str_contains($contentType, 'application/x-www-form-urlencoded') || str_contains($contentType, 'multipart/form-data')) {
                $token = $_POST['_csrf_token'] ?? '';
            } else {
                $input = json_decode(file_get_contents('php://input'), true);
                $token = $input['_csrf_token'] ?? '';
            }

            $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!empty($headerToken)) {
                $token = $headerToken;
            }
        }

        $sessionToken = SessionManager::getCsrfToken();

        if (empty($token) || !hash_equals($sessionToken, $token)) {
            http_response_code(419);
            if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'CSRF token inválido o expirado']);
            } else {
                $_SESSION['error'] = 'CSRF token inválido o expirado. Intente de nuevo.';
                $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
                header("Location: {$referer}");
            }
            exit;
        }
    }
}
