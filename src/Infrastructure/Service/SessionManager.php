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

        if (isset($_SESSION['_session_id'])) {
            $sid = $_SESSION['_session_id'];
            if (!is_string($sid)) {
                return;
            }
            $userId = self::getUserId();
            if ($userId === null) {
                return;
            }
            $file = self::sessionFilePath($userId);
            if (!file_exists($file)) {
                return;
            }
            $data = @file_get_contents($file);
            if ($data === false) {
                return;
            }
            $sessions = json_decode($data, true);
            if (!is_array($sessions) || !in_array($sid, $sessions, true)) {
                self::destroy();
                self::start();
            }
        }
    }

    public static function login(int $userId, string $role, string $nombre = ''): void
    {
        self::regenerate();

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_nombre'] = $nombre;
        $_SESSION['_session_id'] = session_id();

        self::saveActiveSession($userId);
    }

    public static function logout(): void
    {
        $userId = self::getUserId();
        if ($userId !== null) {
            self::removeActiveSession($userId);
        }
        self::destroy();
    }

    private static function sessionsDir(): string
    {
        $dir = dirname(__DIR__, 3) . '/storage/sessions';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }

    private static function sessionFilePath(int $userId): string
    {
        return self::sessionsDir() . "/user_{$userId}.json";
    }

    private static function saveActiveSession(int $userId): void
    {
        $file = self::sessionFilePath($userId);
        $currentSessionId = session_id();

        $sessions = [];
        if (file_exists($file)) {
            $data = @file_get_contents($file);
            if ($data !== false) {
                $sessions = json_decode($data, true);
            }
        }

        if (!is_array($sessions)) {
            $sessions = [];
        }

        $sessions[] = $currentSessionId;

        @file_put_contents($file, json_encode($sessions), LOCK_EX);
    }

    private static function removeActiveSession(int $userId): void
    {
        $file = self::sessionFilePath($userId);
        if (!file_exists($file)) {
            return;
        }

        $data = @file_get_contents($file);
        if ($data === false) {
            return;
        }

        $sessions = json_decode($data, true);
        if (!is_array($sessions)) {
            return;
        }

        $currentSessionId = session_id();
        $sessions = array_values(
            array_filter($sessions, fn ($sid) => $sid !== $currentSessionId)
        );

        if (empty($sessions)) {
            @unlink($file);
        } else {
            @file_put_contents($file, json_encode($sessions), LOCK_EX);
        }
    }
}
