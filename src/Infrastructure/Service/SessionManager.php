<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

class SessionManager
{
    private const TIMEOUT = 1800;
    private const SESSION_NAME = 'elyra_session';

    private static function isSecure(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
            || !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'
            || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on';
    }

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(self::SESSION_NAME);

        $cookieParams = [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => self::isSecure(),
            'httponly' => true,
            'samesite' => 'Lax',
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
            if ($sessionName !== false) {
                setcookie(
                    $sessionName,
                    '',
                    [
                        'expires' => time() - 42000,
                        'path' => '/',
                        'domain' => '',
                        'secure' => self::isSecure(),
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]
                );
            }
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
        return $role !== null && in_array($role, ['admin', 'superadmin'], true);
    }

    public static function getCsrfToken(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION['_csrf_token'];
        return is_string($token) ? $token : '';
    }

    private static ?string $nonce = null;

    public static function getNonce(): string
    {
        if (self::$nonce === null) {
            self::$nonce = bin2hex(random_bytes(16));
        }
        return self::$nonce;
    }

    private static function checkTimeout(): void
    {
        if (isset($_SESSION['_user_agent']) && $_SESSION['_user_agent'] !== self::getUserAgent()) {
            self::destroy();
            self::start();
            return;
        }

        if (isset($_SESSION['_created_at'])) {
            /** @var int $createdAt */
            $createdAt = $_SESSION['_created_at'];
            if (time() - $createdAt > self::TIMEOUT) {
                self::destroy();
                self::start();
                return;
            }
        }

        $_SESSION['_created_at'] = time();
    }

    private static function getUserAgent(): string
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        return is_string($ua) ? $ua : '';
    }

    public static function login(int $userId, string $role, string $nombre = ''): void
    {
        self::regenerate();

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_nombre'] = $nombre;
        $_SESSION['_user_agent'] = self::getUserAgent();

        unset($_SESSION['_csrf_token']);
    }

    public static function logout(): void
    {
        self::destroy();
    }

    public static function destroyAllSessionsForUser(int $userId): void
    {
        self::destroy();
    }
}
