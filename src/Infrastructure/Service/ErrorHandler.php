<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

class ErrorHandler
{
    private static ?string $logDir = null;

    public static function register(?string $logDir = null): void
    {
        self::$logDir = $logDir ?? dirname(__DIR__, 3) . '/storage/logs';
        if (!is_dir(self::$logDir)) {
            @mkdir(self::$logDir, 0775, true);
        }

        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleException(\Throwable $e): void
    {
        self::log('CRITICAL', $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $debug = ($_ENV['APP_DEBUG'] ?? false) === true;

        http_response_code(500);
        if ($debug) {
            echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        } else {
            require dirname(__DIR__, 3) . '/views/errors/500.php';
        }
        exit;
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        self::log('ERROR', "{$message} in {$file}:{$line}");

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        if (self::$logDir === null) {
            return;
        }

        $date = date('Y-m-d');
        $time = date('H:i:s');
        $logFile = self::$logDir . "/{$date}.log";
        $contextStr = !empty($context) ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';

        $line = "[{$time}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
