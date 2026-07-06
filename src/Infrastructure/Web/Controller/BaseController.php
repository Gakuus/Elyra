<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Infrastructure\Service\SessionManager;

abstract class BaseController
{
    protected function render(string $view, array $data = []): void
    {
        extract($data);
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
        header("Location: {$url}");
        exit;
    }

    protected function requireAuth(): void
    {
        if (!SessionManager::isAuthenticated()) {
            $this->redirect('/login');
        }
    }
}
