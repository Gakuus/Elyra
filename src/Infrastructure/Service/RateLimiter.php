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
            @mkdir($dir, 0640, true);
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

    public static function checkResetAttempts(string $ip): bool
    {
        return self::check("reset:{$ip}", 3, 3600);
    }

    public static function incrementResetAttempts(string $ip): int
    {
        return self::increment("reset:{$ip}", 3600);
    }

    public static function checkUploadAttempts(string $ip): bool
    {
        return self::check("upload:{$ip}", 10, 3600);
    }

    public static function incrementUploadAttempts(string $ip): int
    {
        return self::increment("upload:{$ip}", 3600);
    }

    public static function checkGeneral(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        return self::check($key, $maxAttempts, $windowSeconds);
    }

    public static function incrementGeneral(string $key, int $windowSeconds): int
    {
        return self::increment($key, $windowSeconds);
    }

    private static function storagePath(string $key): string
    {
        $dir = self::$storageDir ?: sys_get_temp_dir() . '/elyra_rate_limit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0640, true);
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

        $fp = @fopen($file, 'r');
        if ($fp === false) {
            return true;
        }
        flock($fp, LOCK_SH);
        $data = @stream_get_contents($fp);
        flock($fp, LOCK_UN);
        fclose($fp);

        if ($data === false || $data === '') {
            return true;
        }

        $parsed = explode(':', trim($data));
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

        $fp = @fopen($file, 'c+');
        if ($fp !== false) {
            flock($fp, LOCK_EX);
            ftruncate($fp, 0);
            rewind($fp);

            $data = @stream_get_contents($fp);
            if ($data !== false && $data !== '') {
                $parsed = explode(':', trim($data));
                $existingCount = (int) $parsed[0];
                $existingStart = (int) ($parsed[1] ?? 0);

                if (time() - $existingStart <= $windowSeconds) {
                    $count = $existingCount + 1;
                    $windowStart = $existingStart;
                }
            }

            fwrite($fp, "{$count}:{$windowStart}");
            flock($fp, LOCK_UN);
            fclose($fp);
        }

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
