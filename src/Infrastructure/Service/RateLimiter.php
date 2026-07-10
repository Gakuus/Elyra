<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

class RateLimiter
{
    private static string $storageDir = '';

    public static function setStorageDir(string $dir): void
    {
        self::$storageDir = $dir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    public static function checkLoginAttempts(string $ip): bool
    {
        return self::check("login:{$ip}", 5, 900);
    }

    public static function incrementLoginAttempts(string $ip): int
    {
        return self::increment("login:{$ip}", 900);
    }

    public static function resetLoginAttempts(string $ip): void
    {
        self::reset("login:{$ip}");
    }

    public static function checkPublicRequest(string $ip): bool
    {
        return self::check("public:{$ip}", 100, 60);
    }

    public static function incrementPublicRequest(string $ip): int
    {
        return self::increment("public:{$ip}", 60);
    }

    public static function checkRegistrationAttempts(string $ip): bool
    {
        return self::check("registro:{$ip}", 3, 3600);
    }

    public static function incrementRegistrationAttempts(string $ip): int
    {
        return self::increment("registro:{$ip}", 3600);
    }

    public static function checkSurveySubmission(string $ip): bool
    {
        return self::check("survey:{$ip}", 1, 300);
    }

    public static function incrementSurveySubmission(string $ip): int
    {
        return self::increment("survey:{$ip}", 300);
    }

    public static function checkAccountLockout(string $username): bool
    {
        return self::check("account:{$username}", 5, 900);
    }

    public static function incrementAccountAttempts(string $username): int
    {
        return self::increment("account:{$username}", 900);
    }

    public static function resetAccountAttempts(string $username): void
    {
        self::reset("account:{$username}");
    }

    private static function storagePath(string $key): string
    {
        $dir = self::$storageDir ?: sys_get_temp_dir() . '/elyra_rate_limit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $safe = preg_replace('/[^a-zA-Z0-9:]/', '_', $key);
        return $dir . '/' . $safe . '.lock';
    }

    private static function check(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $file = self::storagePath($key);
        if (!file_exists($file)) {
            return true;
        }

        $data = @file_get_contents($file);
        if ($data === false) {
            return true;
        }

        $parsed = explode(':', $data);
        $count = (int) $parsed[0];
        $windowStart = (int) ($parsed[1] ?? 0);

        if (time() - $windowStart > $windowSeconds) {
            @unlink($file);
            return true;
        }

        return $count < $maxAttempts;
    }

    private static function increment(string $key, int $windowSeconds): int
    {
        $file = self::storagePath($key);
        $count = 1;
        $windowStart = time();

        if (file_exists($file)) {
            $data = @file_get_contents($file);
            if ($data !== false) {
                $parsed = explode(':', $data);
                $existingCount = (int) $parsed[0];
                $existingStart = (int) ($parsed[1] ?? 0);

                if (time() - $existingStart <= $windowSeconds) {
                    $count = $existingCount + 1;
                    $windowStart = $existingStart;
                }
            }
        }

        @file_put_contents($file, "{$count}:{$windowStart}", LOCK_EX);
        return $count;
    }

    private static function reset(string $key): void
    {
        $file = self::storagePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
