<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Middleware;

use Elyra\Infrastructure\Service\SessionManager;

class CsrfMiddleware
{
    public static function handle(): void
    {
        /** @var string $method */
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            return;
        }

        /** @var string $token */
        $token = $_POST['_csrf_token'] ?? '';
        if ($token === '') {
            /** @var string $contentType */
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (str_contains($contentType, 'application/json')) {
                $rawBody = file_get_contents('php://input');
                /** @var array<string, mixed>|null $input */
                $input = $rawBody !== false ? json_decode($rawBody, true) : null;
                /** @var string $token */
                $token = is_array($input) ? ($input['_csrf_token'] ?? '') : '';
            } elseif (str_contains($contentType, 'application/x-www-form-urlencoded') || str_contains($contentType, 'multipart/form-data')) {
                /** @var string $token */
                $token = $_POST['_csrf_token'] ?? '';
            } else {
                $rawBody = file_get_contents('php://input');
                /** @var array<string, mixed>|null $input */
                $input = $rawBody !== false ? json_decode($rawBody, true) : null;
                /** @var string $token */
                $token = is_array($input) ? ($input['_csrf_token'] ?? '') : '';
            }

            /** @var string $headerToken */
            $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if ($headerToken !== '') {
                $token = $headerToken;
            }
        }

        $sessionToken = SessionManager::getCsrfToken();

        if ($token === '' || !hash_equals($sessionToken, $token)) {
            http_response_code(419);
            /** @var string $accept */
            $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
            if (str_contains($accept, 'application/json')) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'CSRF token inválido o expirado']);
            } else {
                $_SESSION['error'] = 'CSRF token inválido o expirado. Intente de nuevo.';
                header('Location: /dashboard');
            }
            exit;
        }
    }
}
