<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

class SessionManager
{
    private const TIMEOUT = 1800;
    private const SESSION_NAME = 'elyra_session';

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(self::SESSION_NAME);

        $cookieParams = [
            'httponly' => true,
            'secure' => isset($_SERVER['HTTPS']),
            'samesite' => 'Strict',
        ];

        if (PHP_SESSION_NONE === session_status()) {
            session_set_cookie_params($cookieParams);
            session_start();
        }

        self::checkTimeout();
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
        $_SESSION['_created_at'] = time();
    }

    public static function destroy(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $sessionName = session_name();
            if ($sessionName === false) {
                return;
            }
            $params = session_get_cookie_params();
            setcookie(
                $sessionName,
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function getUserId(): ?int
    {
        $userId = $_SESSION['user_id'] ?? null;
        return is_numeric($userId) ? (int) $userId : null;
    }

    public static function getUserRole(): ?string
    {
        $role = $_SESSION['user_role'] ?? null;
        return is_string($role) ? $role : null;
    }

    public static function isPaciente(): bool
    {
        return ($_SESSION['user_role'] ?? null) === 'paciente';
    }

    public static function isAdmin(): bool
    {
        $role = $_SESSION['user_role'] ?? null;
        return $role !== null && $role !== 'paciente';
    }

    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION['_csrf_token'];
        return is_string($token) ? $token : '';
    }

    private static function checkTimeout(): void
    {
        if (isset($_SESSION['_created_at'])) {
            if (time() - $_SESSION['_created_at'] > self::TIMEOUT) {
                self::destroy();
                self::start();
            }
        }

        $_SESSION['_created_at'] = time();
    }

    public static function login(int $userId, string $role, string $nombre = ''): void
    {
        self::regenerate();
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_nombre'] = $nombre;
    }

    public static function logout(): void
    {
        self::destroy();
    }
}
