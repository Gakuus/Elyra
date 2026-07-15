<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Middleware;

final class HoneypotMiddleware
{
    public static function handle(): ?string
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }

        $honeypot = $_POST['website'] ?? '';
        if (is_string($honeypot) && $honeypot !== '') {
            $bp = rtrim(parse_url((string)($_ENV['APP_URL'] ?? ''), PHP_URL_PATH) ?: '', '/');
            return $bp . '/';
        }

        return null;
    }
}
