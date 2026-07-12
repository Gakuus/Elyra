<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Service;

final class LocationBroadcaster
{
    private static ?self $instance = null;
    private string $eventDir;

    private function __construct()
    {
        $this->eventDir = sys_get_temp_dir() . '/elyra_sse';
        if (!is_dir($this->eventDir)) {
            @mkdir($this->eventDir, 0777, true);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** @param array<string, mixed> $data */
    public function broadcast(array $data): void
    {
        $event = [
            'id' => time() . '.' . mt_rand(1000, 9999),
            'data' => $data,
            'time' => microtime(true),
        ];

        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);

        $listeners = glob($this->eventDir . '/listener_*');
        if ($listeners === false) {
            return;
        }

        foreach ($listeners as $file) {
            $fp = @fopen($file, 'a');
            if ($fp !== false) {
                fwrite($fp, "id: {$event['id']}\n");
                fwrite($fp, "data: {$payload}\n\n");
                fclose($fp);
            }
        }
    }

    public function registerListener(): string
    {
        $id = 'listener_' . getmypid() . '_' . mt_rand(10000, 99999);
        $path = $this->eventDir . '/' . $id;
        touch($path);
        return $path;
    }

    public function removeListener(string $path): void
    {
        if (file_exists($path)) {
            @unlink($path);
        }
    }

    public function readEvents(string $listenerPath, float $since): string
    {
        if (!file_exists($listenerPath)) {
            return '';
        }

        $content = file_get_contents($listenerPath);
        if ($content === false || $content === '') {
            return '';
        }

        $fp = @fopen($listenerPath, 'r+');
        if ($fp !== false) {
            ftruncate($fp, 0);
            fclose($fp);
        }

        return $content;
    }

    public function cleanStaleListeners(int $maxAgeSeconds = 30): void
    {
        $listeners = glob($this->eventDir . '/listener_*');
        if ($listeners === false) {
            return;
        }

        $now = time();
        foreach ($listeners as $file) {
            if ($now - filemtime($file) > $maxAgeSeconds) {
                @unlink($file);
            }
        }
    }
}
