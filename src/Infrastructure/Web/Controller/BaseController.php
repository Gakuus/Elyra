<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Infrastructure\Service\SessionManager;

abstract class BaseController
{
    protected static ?string $basePath = null;

    protected static function basePath(): string
    {
        if (self::$basePath === null) {
            $path = parse_url((string)($_ENV['APP_URL'] ?? ''), PHP_URL_PATH) ?: '';
            self::$basePath = rtrim($path, '/');
        }
        return self::$basePath;
    }

    /** @param array<string, mixed> $data */
    protected function render(string $view, array $data = []): void
    {
        header('Content-Type: text/html; charset=UTF-8');
        $nonce = \Elyra\Infrastructure\Service\SessionManager::getNonce();
        extract($data, EXTR_SKIP);
        require __DIR__ . "/../../../../views/{$view}.php";
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    protected function redirect(string $url): void
    {
        if ($url !== '' && $url[0] === '/' && !str_starts_with($url, '//')) {
            $url = self::basePath() . $url;
        }
        header("Location: {$url}");
        exit;
    }

    protected function requireAuth(): void
    {
        if (!SessionManager::isAuthenticated()) {
            $this->redirect('/login');
        }
    }

    protected function requireRole(string ...$roles): void
    {
        $this->requireAuth();
        $userRole = SessionManager::getUserRole();
        if (!in_array($userRole, $roles, true)) {
            \Elyra\Infrastructure\Service\AuditLogger::log(
                'access_denied',
                'auth',
                SessionManager::getUserId() !== null ? (string) SessionManager::getUserId() : null,
                ['required_roles' => $roles, 'actual_role' => $userRole, 'uri' => $_SERVER['REQUEST_URI'] ?? ''],
            );
            $this->redirect('/dashboard');
        }
    }

    protected function denyPaciente(): void
    {
        if (SessionManager::isPaciente()) {
            $this->redirect('/dashboard');
        }
    }
}
